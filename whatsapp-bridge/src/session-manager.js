import fs from 'fs';
import path from 'path';
import makeWASocket, {
    DisconnectReason,
    useMultiFileAuthState,
    fetchLatestBaileysVersion,
    Browsers,
} from '@whiskeysockets/baileys';
import Pino from 'pino';
import QRCode from 'qrcode';
import { config } from './config.js';

const logger = Pino({ level: 'info' });
const MAX_RECONNECT_ATTEMPTS = 5;
const RECONNECT_DELAY_MS = 3000;

class SessionManager {
    constructor() {
        this.sessions = new Map();
        this.qrCodes = new Map();
        this.reconnectAttempts = new Map();
        this.connecting = new Set();
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

    clearSessionFiles(organizationId) {
        const sessionPath = this.getSessionPath(organizationId);
        if (fs.existsSync(sessionPath)) {
            fs.rmSync(sessionPath, { recursive: true, force: true });
        }
    }

    hasSessionFiles(organizationId) {
        const sessionPath = this.getSessionPath(organizationId);
        if (!fs.existsSync(sessionPath)) {
            return false;
        }
        try {
            return fs.readdirSync(sessionPath).length > 0;
        } catch {
            return false;
        }
    }

    async teardownSocket(organizationId) {
        const id = String(organizationId);
        const session = this.sessions.get(id);

        if (session?.sock) {
            try {
                session.sock.ev.removeAllListeners('connection.update');
                session.sock.ev.removeAllListeners('creds.update');
                session.sock.end(undefined);
            } catch {
                // ignore
            }
        }

        this.sessions.delete(id);
        this.connecting.delete(id);
    }

    async teardownSession(organizationId, clearFiles = false) {
        const id = String(organizationId);
        await this.teardownSocket(id);
        this.qrCodes.delete(id);
        this.reconnectAttempts.delete(id);

        if (clearFiles) {
            this.clearSessionFiles(organizationId);
        }
    }

    getSession(organizationId) {
        return this.sessions.get(String(organizationId));
    }

    getStatus(organizationId) {
        const id = String(organizationId);
        const session = this.getSession(id);

        if (!session) {
            const qr = this.qrCodes.get(id);
            if (qr) {
                return { connected: false, phone: null, status: 'qr_required' };
            }
            if (this.hasSessionFiles(id)) {
                return { connected: false, phone: null, status: 'reconnecting' };
            }
            return { connected: false, phone: null, status: 'disconnected' };
        }

        if (session.connected) {
            return {
                connected: true,
                phone: session.phone || null,
                status: 'connected',
            };
        }

        if (session.loggingIn) {
            return {
                connected: false,
                phone: null,
                status: 'logging_in',
            };
        }

        const qr = this.qrCodes.get(id);
        if (qr) {
            return {
                connected: false,
                phone: null,
                status: 'qr_required',
            };
        }

        return {
            connected: false,
            phone: null,
            status: 'reconnecting',
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

        // Fresh connect: clear old auth so a new QR is issued
        await this.teardownSession(id, true);
        await this.createSocket(id);

        return { success: true, status: 'qr_required' };
    }

    async createSocket(organizationId) {
        const id = String(organizationId);

        if (this.connecting.has(id)) {
            return;
        }

        this.connecting.add(id);

        try {
            const sessionPath = this.ensureSessionDir(organizationId);
            const { state, saveCreds } = await useMultiFileAuthState(sessionPath);
            const { version } = await fetchLatestBaileysVersion();

            const sock = makeWASocket({
                version,
                auth: state,
                logger,
                printQRInTerminal: false,
                browser: Browsers.ubuntu('Chrome'),
                markOnlineOnConnect: false,
                syncFullHistory: false,
                generateHighQualityLinkPreview: false,
                getMessage: async () => undefined,
            });

            const sessionData = {
                sock,
                connected: false,
                loggingIn: false,
                phone: null,
                organizationId: id,
            };

            this.sessions.set(id, sessionData);

            sock.ev.on('creds.update', saveCreds);

            sock.ev.on('connection.update', async (update) => {
                const { connection, lastDisconnect, qr } = update;

                if (qr) {
                    try {
                        const qrDataUrl = await QRCode.toDataURL(qr);
                        this.qrCodes.set(id, qrDataUrl);
                        sessionData.loggingIn = false;
                        this.reconnectAttempts.delete(id);
                        logger.info({ organizationId: id }, 'QR code ready');
                    } catch (err) {
                        logger.error({ err }, 'Failed to generate QR code');
                    }
                }

                if (connection === 'connecting') {
                    // After QR scan WhatsApp often sits here briefly
                    if (!this.qrCodes.has(id) || this.hasSessionFiles(id)) {
                        sessionData.loggingIn = true;
                        this.qrCodes.delete(id);
                    }
                }

                if (connection === 'open') {
                    sessionData.connected = true;
                    sessionData.loggingIn = false;
                    this.qrCodes.delete(id);
                    this.reconnectAttempts.delete(id);
                    const user = sock.user;
                    sessionData.phone = user?.id?.split(':')[0] || null;
                    logger.info({ organizationId: id, phone: sessionData.phone }, 'WhatsApp connected');
                }

                if (connection === 'close') {
                    sessionData.connected = false;
                    sessionData.loggingIn = false;

                    const statusCode = lastDisconnect?.error?.output?.statusCode;
                    const loggedOut = statusCode === DisconnectReason.loggedOut;
                    const restartRequired = statusCode === DisconnectReason.restartRequired;

                    logger.warn({ organizationId: id, statusCode, loggedOut, restartRequired }, 'WhatsApp connection closed');

                    // Close socket only — do NOT wipe auth on restartRequired.
                    // After QR scan Baileys often closes with 515 restartRequired;
                    // clearing files here causes endless "Logging in…" hang.
                    await this.teardownSocket(id);

                    if (loggedOut || statusCode === 401 || statusCode === 403) {
                        this.clearSessionFiles(organizationId);
                        this.qrCodes.delete(id);
                        this.reconnectAttempts.delete(id);
                        logger.warn({ organizationId: id, statusCode }, 'Logged out — new QR required');
                        return;
                    }

                    // restartRequired / transient close: reconnect keeping creds
                    const attempts = (this.reconnectAttempts.get(id) || 0) + 1;
                    this.reconnectAttempts.set(id, attempts);

                    if (attempts >= MAX_RECONNECT_ATTEMPTS) {
                        logger.warn({ organizationId: id, attempts }, 'Max reconnect attempts — clearing session');
                        this.reconnectAttempts.delete(id);
                        this.clearSessionFiles(organizationId);
                        this.qrCodes.delete(id);
                        return;
                    }

                    const delay = restartRequired ? 1500 : RECONNECT_DELAY_MS;
                    setTimeout(() => this.createSocket(id), delay);
                }
            });
        } catch (err) {
            logger.error({ err, organizationId: id }, 'Failed to create WhatsApp socket');
            await this.teardownSocket(id);
        } finally {
            this.connecting.delete(id);
        }
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

        await this.teardownSession(id, true);

        return { success: true };
    }
}

export const sessionManager = new SessionManager();
