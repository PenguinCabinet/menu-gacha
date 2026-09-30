process.env.MENU_SYNC_LISTEN = 'true';
process.env.MENU_SYNC_APP_URL ??= 'http://127.0.0.1:8000';

await import('./menu-sync-server.js');
