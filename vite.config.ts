import { resolve, dirname } from 'node:path'
import { fileURLToPath, URL } from 'node:url'
import path from 'path'
import type { Plugin } from 'vite'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import VueI18nPlugin from '@intlify/unplugin-vue-i18n/vite'

/**
 * Mock du backend PHP pour le développement local.
 * Intercepte les requêtes /?execute=php sans nécessiter un serveur PHP.
 */
function phpBackendMock(): Plugin {
  const requirements: Record<string, boolean> = {
    php: true, writable: true, 'function-symlink': true, rewrite: true,
    'extension-bcmath': true, 'extension-ctype': true, 'extension-json': true,
    'extension-mbstring': true, 'extension-openssl': true, 'extension-PDO': true,
    'extension-tokenizer': true, 'extension-xml': true, 'extension-xmlwriter': true,
    'extension-curl': true, 'extension-fileinfo': true, 'extension-zip': true,
  }

  const mockData = {
    installerVersion: '1.2.0',
    minPhpVersion: '8.2',
    phpVersion: '8.2',
    phpFullVersion: '8.2.26',
    phpIniPath: '/etc/php/8.2/apache2/php.ini',
    path: '/var/www/html',
    file: '/var/www/html/index.php',
    htaccess: true,
    webConfig: false,
    windows: false,
    requirements,
    compatible: true,
    downloaded: false,
    extracted: false,
    latestInstallerVersion: null,
  }

  return {
    name: 'php-backend-mock',
    configureServer(server) {
      server.middlewares.use((req, res, next) => {
        const url = new URL(req.url ?? '/', 'http://localhost')
        if (url.searchParams.get('execute') !== 'php') return next()

        res.setHeader('Content-Type', 'application/json')

        if (req.method === 'GET') {
          res.end(JSON.stringify(mockData))
          return
        }

        if (req.method === 'POST') {
          // Simule un téléchargement de 1.5s
          setTimeout(() => res.end(JSON.stringify({ ...mockData, extracted: true })), 1500)
          return
        }

        next()
      })
    },
  }
}

export default defineConfig({
  plugins: [
    vue(),
    VueI18nPlugin({
      include: resolve(
        dirname(fileURLToPath(import.meta.url)),
        './src/locales/**',
      ),
    }),
    phpBackendMock(),
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
      '~bootstrap': path.resolve(__dirname, 'node_modules/bootstrap'),
    },
  },
})
