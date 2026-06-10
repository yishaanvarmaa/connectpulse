import fs from 'fs';
import path from 'path';
import makeWASocket, {
    DisconnectReason,
    useMultiFileAuthState,
    fetchLatestBaileysVersion,
} from '@whiskeysockets/baileys';
import Pino from 'pino';
import QRCode from 'qrcode';
import { config } from './config.js';

const logger = Pino({ level: 'warn' });

class SessionManager {
    constructor() {
        this.sessions = new Map();
        this.qrCodes = new Map();
    }

    getSessionPath(organizationId) {
        const basePath = path.resolve(config.sessionsPath);
        return path.join(basePath, String(organizationId));
    }

    ensureSessionDir(organizationId) {
        const sessionPath = this.getSessionPath(organizationId);
        if (!fs.existsSync(sessionPath)) {
            fs.mkdirSync(sessionPath, { recursive: true });
        }
        return sessionPath;
    }

    getSession(organizationId) {
        return this.sessions.get(String(organizationId));
    }

    getStatus(organizationId) {
        const session = this.getSession(String(organizationId));

        if (!session) {
            const qr = this.qrCodes.get(String(organizationId));
            return {
                connected: false,
                phone: null,
                status: qr ? 'qr_required' : 'disconnected',
            };
        }

        return {
            connected: session.connected,
            phone: session.phone || null,
            status: session.connected ? 'connected' : (this.qrCodes.get(String(organizationId)) ? 'qr_required' : 'reconnecting'),
        };
    }

    getQr(organizationId) {
        return this.qrCodes.get(String(organizationId)) || null;
    }

    async initSession(organizationId) {
        const id = String(organizationId);

        if (this.sessions.has(id) && this.sessions.get(id).connected) {
            return { success: true, status: 'connected' };
        }

        await this.createSocket(id);
        return { success: true, status: 'qr_required' };
    }

    async createSocket(organizationId) {
        const sessionPath = this.ensureSessionDir(organizationId);
        const { state, saveCreds } = await useMultiFileAuthState(sessionPath);
        const { version } = await fetchLatestBaileysVersion();

        const sock = makeWASocket({
            version,
            auth: state,
            logger,
            printQRInTerminal: false,
            generateHighQualityLinkPreview: false,
            syncFullHistory: false,
        });

        const sessionData = {
            sock,
            connected: false,
            phone: null,
            organizationId,
        };

        this.sessions.set(organizationId, sessionData);

        sock.ev.on('creds.update', saveCreds);

        sock.ev.on('connection.update', async (update) => {
            const { connection, lastDisconnect, qr } = update;

            if (qr) {
                try {
                    const qrDataUrl = await QRCode.toDataURL(qr);
                    this.qrCodes.set(organizationId, qrDataUrl);
                } catch (err) {
                    logger.error({ err }, 'Failed to generate QR code');
                }
            }

            if (connection === 'open') {
                sessionData.connected = true;
                this.qrCodes.delete(organizationId);
                const user = sock.user;
                sessionData.phone = user?.id?.split(':')[0] || null;
            }

            if (connection === 'close') {
                sessionData.connected = false;
                const statusCode = lastDisconnect?.error?.output?.statusCode;
                const shouldReconnect = statusCode !== DisconnectReason.loggedOut;

                if (shouldReconnect) {
                    this.sessions.delete(organizationId);
                    setTimeout(() => this.createSocket(organizationId), 3000);
                } else {
                    this.sessions.delete(organizationId);
                    this.qrCodes.delete(organizationId);
                }
            }
        });
    }

    async sendMessage(organizationId, mobile, message) {
        const session = this.getSession(String(organizationId));

        if (!session || !session.connected) {
            return { success: false, error: 'WhatsApp is not connected.' };
        }

        const jid = this.formatJid(mobile);

        try {
            const result = await session.sock.sendMessage(jid, { text: message });
            return {
                success: true,
                message_id: result?.key?.id || null,
            };
        } catch (err) {
            logger.error({ err, organizationId, mobile }, 'Send message failed');
            return { success: false, error: 'Message delivery failed.' };
        }
    }

    formatJid(mobile) {
        let number = String(mobile).replace(/\D/g, '');
        if (number.length === 10) {
            number = '91' + number;
        }
        return number + '@s.whatsapp.net';
    }

    async disconnect(organizationId) {
        const id = String(organizationId);
        const session = this.sessions.get(id);

        if (session?.sock) {
            try {
                await session.sock.logout();
            } catch {
                try {
                    session.sock.end(undefined);
                } catch {
                    // ignore
                }
            }
        }

        this.sessions.delete(id);
        this.qrCodes.delete(id);

        const sessionPath = this.getSessionPath(organizationId);
        if (fs.existsSync(sessionPath)) {
            fs.rmSync(sessionPath, { recursive: true, force: true });
        }

        return { success: true };
    }
}

export const sessionManager = new SessionManager();
