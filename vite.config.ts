import { resolve, dirname } from 'node:path'
import { fileURLToPath, URL } from 'node:url'
import path from 'path'
import type { Plugin } from 'vite'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import VueI18nPlugin from '@intlify/unplugin-vue-i18n/vite'

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

  const mockAuthConfig: Record<string, Record<string, string>> = {
    geoventure: { authUrl: 'https://launcher.bmeouchi.fr/', settings: 'https://geoventure.bmeouchi.fr/', name: 'Geoventure', color: '#4ade80', description: 'Aventure & Exploration' },
    elandor:    { authUrl: '', settings: '', name: 'Elandor', color: '#a78bfa', description: 'RPG & Fantaisie' },
    pokeland:   { authUrl: '', settings: '', name: 'Pokeland', color: '#fb923c', description: 'Pokémon & Combat' },
  }

  const mockMods: Array<{ name: string; fileName: string; description: string; recommended: boolean; enabled: boolean }> = []

  const mockNotifications = [
    { id: 1, type: 'info', message: 'Bienvenue sur CentralCorp Panel !', url: null, expiresAt: null, createdAt: new Date().toISOString() },
  ]

  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  function readBody(req: any): Promise<Record<string, unknown>> {
    return new Promise((resolve) => {
      let body = ''
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      req.on('data', (chunk: any) => { body += String(chunk) })
      req.on('end', () => {
        try { resolve(JSON.parse(body) as Record<string, unknown>) } catch { resolve({}) }
      })
    })
  }

  return {
    name: 'php-backend-mock',
    configureServer(server) {
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      server.middlewares.use((req: any, res: any, next: any) => {
        const url = new URL(req.url ?? '/', 'http://localhost')
        if (url.searchParams.get('execute') !== 'php') return next()

        res.setHeader('Content-Type', 'application/json')
        const action = url.searchParams.get('action')

        if (action === 'api-schema') {
          res.end(JSON.stringify({ schemaVersion: '1.0.0', description: 'Mock schema' }))
          return
        }

        if (action === 'launcher-config') {
          res.end(JSON.stringify({
            panelUrl: 'http://localhost:5173/',
            generatedAt: new Date().toISOString(),
            servers: Object.entries(mockAuthConfig).map(([id, s]) => ({ id, ...s })),
          }))
          return
        }

        if (action === 'auth-config' && req.method === 'GET') {
          res.end(JSON.stringify(mockAuthConfig))
          return
        }

        if (action === 'notifications' && req.method === 'GET') {
          res.end(JSON.stringify(mockNotifications))
          return
        }

        if (action === 'mods-config' && req.method === 'GET') {
          res.end(JSON.stringify(mockMods))
          return
        }

        if (req.method === 'GET') {
          res.end(JSON.stringify(mockData))
          return
        }

        if (req.method === 'POST') {
          readBody(req).then((body) => {
            const postAction = (body.action as string) ?? action

            if (postAction === 'auth-config') {
              Object.assign(mockAuthConfig, (body.data as Record<string, Record<string, string>>) ?? {})
              res.end(JSON.stringify({ saved: true }))
              return
            }
            if (postAction === 'mods-config') {
              mockMods.length = 0
              mockMods.push(...((body.data as typeof mockMods) ?? []))
              res.end(JSON.stringify({ saved: true }))
              return
            }
            if (postAction === 'notifications') {
              const d = (body.data ?? {}) as Record<string, unknown>
              mockNotifications.push({ id: Date.now(), type: (d.type as string) ?? 'info', message: d.message as string, url: null, expiresAt: null, createdAt: new Date().toISOString() })
              res.end(JSON.stringify({ saved: true }))
              return
            }
            if (postAction === 'telemetry') {
              console.log('[mock telemetry]', body.data)
              res.end(JSON.stringify({ received: true }))
              return
            }
            if (postAction === 'download') {
              setTimeout(() => res.end(JSON.stringify({ ...mockData, extracted: true })), 1500)
              return
            }

            res.end(JSON.stringify({ ...mockData, extracted: true }))
          }).catch(() => next())
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
