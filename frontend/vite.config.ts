import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import path from 'path';

// Get parent folder name dynamically
const getParentFolderName = () => {
  try {
    return path.basename(path.resolve(__dirname, '..'));
  } catch {
    return 'larable-laravel-staterkit';
  }
};

const folderName = getParentFolderName();
const fallbackBackendUrl = `http://${folderName}.test:8000`;

export default defineConfig({
  plugins: [react()],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
    },
  },
  server: {
    host: '0.0.0.0',
    port: 3000,
    allowedHosts: ['.test', 'localhost', '127.0.0.1'],
    watch: {
      usePolling: true,
    },
    proxy: {
      '/api': {
        target: process.env.BACKEND_URL || fallbackBackendUrl,
        changeOrigin: true,
        secure: false,
      },
      '/sanctum': {
        target: process.env.BACKEND_URL || fallbackBackendUrl,
        changeOrigin: true,
        secure: false,
      },
    },
  },

});
