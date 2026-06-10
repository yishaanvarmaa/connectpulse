export const config = {
    port: parseInt(process.env.BRIDGE_PORT || '3001', 10),
    secret: process.env.BRIDGE_SECRET || 'change-me-in-production',
    sessionsPath: process.env.SESSIONS_PATH || '../storage/app/whatsapp',
    host: process.env.BRIDGE_HOST || '127.0.0.1',
};
