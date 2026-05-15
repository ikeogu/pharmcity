const { app, BrowserWindow } = require('electron');
const path = require('path');
const { spawn } = require('child_process');

let mainWindow;
let phpServer;

function createWindow() {
  mainWindow = new BrowserWindow({
    width: 1280,
    height: 800,
    webPreferences: {
      nodeIntegration: false,
    },
  });

  // Point to local Laravel server
  const startUrl = 'http://127.0.0.1:8000';
  mainWindow.loadURL(startUrl);
}

function startLaravelServer() {
  const laravelPath = path.join(__dirname); // project root

  // Start Laravel's built-in PHP server
   const phpPath = path.join(__dirname, 'php', 'php.exe');
   phpServer = spawn(phpPath, ['artisan', 'serve', '--host=127.0.0.1', '--port=8000'], {

    cwd: laravelPath,
    detached: true,
    stdio: 'ignore'
  });

  phpServer.unref();
}

app.whenReady().then(() => {
  startLaravelServer();

  // Wait a bit for PHP to start
  setTimeout(createWindow, 3000);

  app.on('activate', () => {
    if (BrowserWindow.getAllWindows().length === 0) createWindow();
  });
});

app.on('window-all-closed', () => {
  if (phpServer) {
    phpServer.kill();
  }
  if (process.platform !== 'darwin') app.quit();
});
