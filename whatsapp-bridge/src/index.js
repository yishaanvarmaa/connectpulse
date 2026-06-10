import express from 'express';
import { config } from './config.js';
import { sessionManager } from './session-manager.js';

const app = express();
app.use(express.json());

function authenticate(req, res, next) {
    const secret = req.headers['x-bridge-secret'];
    if (!secret || secret !== config.secret) {
        return res.status(401).json({ success: false, error: 'Unauthorized' });
    }
    next();
}

app.use(authenticate);

app.get('/health', (req, res) => {
    res.json({ success: true, status: 'ok' });
});

app.post('/init', async (req, res) => {
    const { organization_id } = req.body;
    if (!organization_id) {
        return res.status(400).json({ success: false, error: 'organization_id is required' });
    }
    const result = await sessionManager.initSession(organization_id);
    res.json(result);
});

app.get('/qr', (req, res) => {
    const organizationId = req.query.organization_id;
    if (!organizationId) {
        return res.status(400).json({ success: false, error: 'organization_id is required' });
    }
    res.json({ qr: sessionManager.getQr(String(organizationId)) });
});

app.get('/status', (req, res) => {
    const organizationId = req.query.organization_id;
    if (!organizationId) {
        return res.status(400).json({ success: false, error: 'organization_id is required' });
    }
    res.json(sessionManager.getStatus(String(organizationId)));
});

app.post('/send', async (req, res) => {
    const { organization_id, mobile, message } = req.body;
    if (!organization_id || !mobile || !message) {
        return res.status(400).json({ success: false, error: 'organization_id, mobile, and message are required' });
    }
    const result = await sessionManager.sendMessage(organization_id, mobile, message);
    res.json(result);
});

app.post('/disconnect', async (req, res) => {
    const { organization_id } = req.body;
    if (!organization_id) {
        return res.status(400).json({ success: false, error: 'organization_id is required' });
    }
    const result = await sessionManager.disconnect(organization_id);
    res.json(result);
});

app.listen(config.port, config.host, () => {
    console.log(`ConnectPulse WhatsApp Bridge running on ${config.host}:${config.port}`);
});
