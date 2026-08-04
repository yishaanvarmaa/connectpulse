module.exports = {
    apps: [
        {
            name: 'connectpulse-bridge',
            script: 'src/index.js',
            cwd: __dirname,
            instances: 1,
            exec_mode: 'fork',
            autorestart: true,
            watch: false,
            max_memory_restart: '500M',
            env: {
                NODE_ENV: 'production',
                BRIDGE_PORT: 3001,
                BRIDGE_HOST: '127.0.0.1',
                BRIDGE_SECRET: 'change-me-in-production',
                SESSIONS_PATH: '../storage/app/whatsapp',
            },
        },
    ],
};
