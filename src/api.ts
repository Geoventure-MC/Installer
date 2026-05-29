import { fetcher } from 'itty-fetcher'

export interface FetchedData {
  installerVersion: string
  minPhpVersion: string
  phpVersion: string
  phpFullVersion: string
  phpIniPath: string
  path: string
  file: string
  htaccess: boolean
  requirements: Record<string, boolean>
  compatible: boolean
  extracted?: boolean
  windows?: boolean
  latestInstallerVersion?: string | null
}

export interface ServerAuthConfig {
  authUrl: string
  settings: string
  name?: string
  color?: string
  description?: string
}

export interface AuthConfig {
  geoventure?: ServerAuthConfig
  elandor?: ServerAuthConfig
  pokeland?: ServerAuthConfig
  [key: string]: ServerAuthConfig | undefined
}

export interface LauncherConfig {
  panelUrl: string
  generatedAt: string
  servers: Array<{
    id: string
    name: string
    color: string
    description: string
    authUrl: string
    settings: string
  }>
}

export interface Notification {
  id: number
  type: 'info' | 'warning' | 'maintenance' | 'event'
  message: string
  url?: string | null
  expiresAt?: string | null
  createdAt: string
}

export interface ModConfig {
  name: string
  fileName: string
  description: string
  recommended?: boolean
  enabled: boolean
}

export interface HealthCheckItem {
  id: string
  ok: boolean
  detail?: string
}

export interface HealthReport {
  checks: HealthCheckItem[]
  allOk: boolean
}

export interface LauncherReleases {
  version: string | null
  page: string
  windows: string | null
  mac: string | null
  linux: string | null
  error?: string
}

const client = fetcher({
  base: window.location.href,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
  },
  transformRequest(request) {
    return { ...request, url: `${request.url}?execute=php` }
  },
})

async function actionGet<T>(action: string): Promise<T> {
  const base = window.location.href.split('?')[0]
  const res = await fetch(`${base}?execute=php&action=${encodeURIComponent(action)}`, {
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
  })
  if (!res.ok) {
    const body = await res.json().catch(() => ({}))
    throw new Error((body as { message?: string })?.message ?? `HTTP ${res.status}`)
  }
  return res.json() as Promise<T>
}

export function baseFetch(): Promise<FetchedData> {
  return client.get('')
}

export function download(): Promise<void> {
  return client.post('', { action: 'download' })
}

export function getAuthConfig(): Promise<AuthConfig> {
  return actionGet<AuthConfig>('auth-config')
}

export function saveAuthConfig(config: AuthConfig): Promise<{ saved: boolean }> {
  return client.post('', { action: 'auth-config', data: config })
}

export function getLauncherConfigUrl(): string {
  const base = window.location.href.split('?')[0]
  return `${base}?execute=php&action=launcher-config`
}

export function getInstallReportUrl(): string {
  const base = window.location.href.split('?')[0]
  return `${base}?execute=php&action=install-report`
}

export function getNotifications(): Promise<Notification[]> {
  return actionGet<Notification[]>('notifications')
}

export function getModsConfig(): Promise<ModConfig[]> {
  return actionGet<ModConfig[]>('mods-config')
}

export function saveModsConfig(mods: ModConfig[]): Promise<{ saved: boolean }> {
  return client.post('', { action: 'mods-config', data: mods })
}

export function getHealthCheck(): Promise<HealthReport> {
  return actionGet<HealthReport>('health-check')
}

export function getLauncherReleases(): Promise<LauncherReleases> {
  return actionGet<LauncherReleases>('launcher-releases')
}

export function sendDiscordNotification(
  webhookUrl: string,
  panelUrl: string,
): Promise<{ sent: boolean }> {
  return client.post('', { action: 'discord-notify', data: { webhookUrl, panelUrl } })
}

export function selfDestruct(): Promise<{ deleted: string[] }> {
  return client.post('', { action: 'self-destruct' })
}
