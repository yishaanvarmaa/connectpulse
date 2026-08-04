import fs from 'fs';
import path from 'path';
import makeWASocket, {
    DisconnectReason,
    useMultiFileAuthState,
    fetchLatestBaileysVersion,
    makeCacheableSignalKeyStore,
    Browsers,
} from '@whiskeysockets/baileys';
import Pino from 'pino';
import QRCode from 'qrcode';
import { config } from './config.js';

const logger = Pino({ level: 'info' });
const MAX_RECONNECT_ATTEMPTS = 5;
const RECONNECT_DELAY_MS = 3000;
const MAX_STORED_MESSAGES = 200;

class MessageStore {
    constructor() {
        this.messages = new Map();
    }

    keyOf(key) {
        return `${key.remoteJid}|${key.id}|${key.fromMe ? 1 : 0}`;
    }

    set(key, message) {
        if (!key?.id || !key?.remoteJid) {
            return;
        }
        this.messages.set(this.keyOf(key), message);
        if (this.messages.size > MAX_STORED_MESSAGES) {
            const first = this.messages.keys().next().value;
            this.messages.delete(first);
        }
    }

    get(key) {
        if (!key?.id) {
            return undefined;
        }
        return this.messages.get(this.keyOf(key));
    }

    clear() {
        this.messages.clear();
    }
}

class SessionManager {
    constructor() {
        this.sessions = new Map();
        this.qrCodes = new Map();
        this.reconnectAttempts = new Map();
        this.connecting = new Set();
        this.messageStores = new Map();
    }

    getMessageStore(organizationId) {
        const id = String(organizationId);
        if (!this.messageStores.has(id)) {
            this.messageStores.set(id, new MessageStore());
        }
        return this.messageStores.get(id);
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
            this.getMessageStore(id).clear();
        }
    }

    /**
     * Full crypto reset: logout, wipe auth files + message cache, require new QR.
     */
    async resetCrypto(organizationId) {
        const id = String(organizationId);
        const session = this.sessions.get(id);

        if (session?.sock) {
            try {
                await session.sock.logout();
            } catch {
                // ignore
            }
        }

        await this.teardownSession(id, true);
        logger.info({ organizationId: id }, 'Crypto session fully reset — new QR required');

        return { success: true, status: 'disconnected' };
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
            const msgStore = this.getMessageStore(id);

            const sock = makeWASocket({
                version,
                auth: {
                    creds: state.creds,
                    keys: makeCacheableSignalKeyStore(state.keys, logger),
                },
                logger,
                printQRInTerminal: false,
                browser: Browsers.ubuntu('Chrome'),
                markOnlineOnConnect: false,
                syncFullHistory: false,
                generateHighQualityLinkPreview: false,
                // Critical: WhatsApp retry receipts need the original plaintext
                getMessage: async (key) => msgStore.get(key),
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

                    await this.teardownSocket(id);

                    if (loggedOut || statusCode === 401 || statusCode === 403) {
                        this.clearSessionFiles(organizationId);
                        this.getMessageStore(id).clear();
                        this.qrCodes.delete(id);
                        this.reconnectAttempts.delete(id);
                        logger.warn({ organizationId: id, statusCode }, 'Logged out — new QR required');
                        return;
                    }

                    const attempts = (this.reconnectAttempts.get(id) || 0) + 1;
                    this.reconnectAttempts.set(id, attempts);

                    if (attempts >= MAX_RECONNECT_ATTEMPTS) {
                        logger.warn({ organizationId: id, attempts }, 'Max reconnect attempts — clearing session');
                        this.reconnectAttempts.delete(id);
                        this.clearSessionFiles(organizationId);
                        this.getMessageStore(id).clear();
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

    normalizeNumber(mobile) {
        let number = String(mobile).replace(/\D/g, '');
        if (number.length === 10) {
            number = '91' + number;
        }
        return number;
    }

    async resolveJid(sock, mobile) {
        const number = this.normalizeNumber(mobile);

        try {
            const results = await sock.onWhatsApp(number);
            const match = Array.isArray(results) ? results.find((r) => r?.exists) : null;
            if (match?.jid) {
                return match.jid;
            }
        } catch (err) {
            logger.warn({ err, mobile: number }, 'onWhatsApp lookup failed — falling back to PN jid');
        }

        return number + '@s.whatsapp.net';
    }

    async sendMessage(organizationId, mobile, message) {
        const id = String(organizationId);
        const session = this.getSession(id);

        if (!session || !session.connected || !session.sock) {
            return { success: false, error: 'WhatsApp is not connected.' };
        }

        try {
            const jid = await this.resolveJid(session.sock, mobile);
            const content = { text: message };
            const result = await session.sock.sendMessage(jid, content);

            if (result?.key) {
                this.getMessageStore(id).set(result.key, content);
            }

            logger.info({
                organizationId: id,
                jid,
                message_id: result?.key?.id || null,
            }, 'Message sent');

            return {
                success: true,
                message_id: result?.key?.id || null,
                jid,
            };
        } catch (err) {
            logger.error({ err, organizationId: id, mobile }, 'Send message failed');
            return { success: false, error: err?.message || 'Message delivery failed.' };
        }
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
