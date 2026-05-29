<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import {
  BIconCheckCircleFill,
  BIconXCircleFill,
  BIconArrowRight,
  BIconBoxArrowUpRight,
  BIconDownload,
  BIconArrowRepeat,
  BIconWindows,
  BIconApple,
  BIconUbuntu,
  BIconDiscord,
  BIconTrash,
  BIconShieldLock,
} from 'bootstrap-icons-vue'
import {
  getLauncherConfigUrl,
  getInstallReportUrl,
  getHealthCheck,
  getLauncherReleases,
  sendDiscordNotification,
  selfDestruct,
  type HealthReport,
  type LauncherReleases,
} from '@/api'

const { t } = useI18n({ useScope: 'global' })

const launcherConfigUrl = getLauncherConfigUrl()
const installReportUrl = getInstallReportUrl()

const panelUrl = computed(() => window.location.href.split('?')[0].replace(/index\.php$/, ''))

// Health check
const health = ref<HealthReport | null>(null)
const healthLoading = ref(false)
async function runHealthCheck() {
  healthLoading.value = true
  try {
    health.value = await getHealthCheck()
  } catch {
    health.value = null
  } finally {
    healthLoading.value = false
  }
}

// Launcher releases
const releases = ref<LauncherReleases | null>(null)
const qrUrl = computed(() => {
  const target = releases.value?.page ?? 'https://github.com/Geoventure-MC/Launcher/releases'
  return `https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=${encodeURIComponent(target)}`
})
const hasAnyBuild = computed(
  () => !!(releases.value && (releases.value.windows || releases.value.mac || releases.value.linux)),
)

// Discord notification
const webhook = ref('')
const discordState = ref<'idle' | 'sending' | 'sent' | 'error'>('idle')
async function notifyDiscord() {
  if (!webhook.value) return
  discordState.value = 'sending'
  try {
    await sendDiscordNotification(webhook.value, panelUrl.value)
    discordState.value = 'sent'
  } catch {
    discordState.value = 'error'
  }
}

// Self-destruct
const destroyState = ref<'idle' | 'confirm' | 'deleting' | 'done' | 'error'>('idle')
async function removeInstaller() {
  if (destroyState.value !== 'confirm') {
    destroyState.value = 'confirm'
    return
  }
  destroyState.value = 'deleting'
  try {
    await selfDestruct()
    destroyState.value = 'done'
  } catch {
    destroyState.value = 'error'
  }
}

onMounted(async () => {
  runHealthCheck()
  try {
    releases.value = await getLauncherReleases()
  } catch {
    releases.value = null
  }
})
</script>

<template>
  <div class="text-center">
    <div class="mb-4">
      <BIconCheckCircleFill class="text-success" style="font-size: 4rem;" aria-hidden="true" />
    </div>
    <h2 class="h3 fw-bold mb-2">{{ t('success.title') }}</h2>
    <p class="text-muted mb-4">{{ t('success.subtitle') }}</p>

    <div class="card border-0 text-start mb-4 mx-auto" style="max-width: 480px; background: var(--bs-tertiary-bg);">
      <div class="card-body px-4 py-3">
        <h3 class="h6 fw-semibold mb-3">{{ t('success.nextSteps') }}</h3>
        <ol class="mb-0 ps-3 small text-muted d-flex flex-column gap-2">
          <li>{{ t('success.step1') }}</li>
          <li>{{ t('success.step2') }}</li>
          <li>{{ t('success.step3') }}</li>
          <li>{{ t('success.step4') }}</li>
        </ol>
      </div>
    </div>

    <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center mb-3">
      <a href="/" class="btn btn-primary btn-lg rounded-pill px-4">
        {{ t('success.openPanel') }}
        <BIconArrowRight class="ms-2" aria-hidden="true" />
      </a>
      <a :href="launcherConfigUrl" download="launcher-config.json" class="btn btn-outline-primary btn-lg rounded-pill px-4 d-inline-flex align-items-center justify-content-center gap-2">
        <BIconDownload aria-hidden="true" />
        {{ t('success.downloadLauncherConfig') }}
      </a>
    </div>
    <p class="text-muted small mb-4">{{ t('success.downloadLauncherConfigHint') }}</p>

    <!-- Health check -->
    <div class="card text-start mb-4 mx-auto" style="max-width: 600px;">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h3 class="h6 fw-semibold mb-0">{{ t('success.healthTitle') }}</h3>
          <button class="btn btn-sm btn-outline-secondary" :disabled="healthLoading" @click="runHealthCheck">
            <BIconArrowRepeat :class="{ spin: healthLoading }" aria-hidden="true" />
            {{ healthLoading ? t('success.healthChecking') : t('success.healthRerun') }}
          </button>
        </div>
        <ul v-if="health" class="list-unstyled mb-2">
          <li v-for="check in health.checks" :key="check.id" class="py-1">
            <BIconCheckCircleFill v-if="check.ok" class="text-success" aria-hidden="true" />
            <BIconXCircleFill v-else class="text-danger" aria-hidden="true" />
            <span class="ms-2">{{ t(`success.health_${check.id}`) }}</span>
            <small v-if="check.detail" class="text-muted ms-2">({{ check.detail }})</small>
          </li>
        </ul>
        <div v-if="health" class="alert mb-0 py-2" :class="health.allOk ? 'alert-success' : 'alert-warning'">
          {{ health.allOk ? t('success.healthAllOk') : t('success.healthSomeFailed') }}
        </div>
      </div>
    </div>

    <!-- Launcher download -->
    <div class="card text-start mb-4 mx-auto" style="max-width: 600px;">
      <div class="card-body">
        <h3 class="h6 fw-semibold">
          {{ t('success.launcherTitle') }}
          <small v-if="releases?.version" class="text-muted">{{ releases.version }}</small>
        </h3>
        <div class="row align-items-center g-3">
          <div class="col">
            <div class="d-grid gap-2">
              <a v-if="releases?.windows" :href="releases.windows" class="btn btn-outline-dark d-inline-flex align-items-center justify-content-center gap-2">
                <BIconWindows aria-hidden="true" /> {{ t('success.launcherWindows') }}
              </a>
              <a v-if="releases?.mac" :href="releases.mac" class="btn btn-outline-dark d-inline-flex align-items-center justify-content-center gap-2">
                <BIconApple aria-hidden="true" /> {{ t('success.launcherMac') }}
              </a>
              <a v-if="releases?.linux" :href="releases.linux" class="btn btn-outline-dark d-inline-flex align-items-center justify-content-center gap-2">
                <BIconUbuntu aria-hidden="true" /> {{ t('success.launcherLinux') }}
              </a>
              <a v-if="!hasAnyBuild" :href="releases?.page ?? 'https://github.com/Geoventure-MC/Launcher/releases'" target="_blank" rel="noopener noreferrer" class="btn btn-outline-secondary d-inline-flex align-items-center justify-content-center gap-2">
                <BIconBoxArrowUpRight aria-hidden="true" /> {{ t('success.launcherUnavailable') }}
              </a>
            </div>
          </div>
          <div class="col-auto text-center">
            <img :src="qrUrl" alt="QR" width="120" height="120" style="border-radius: 8px;" />
            <div class="text-muted small mt-1">{{ t('success.launcherQrHint') }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Report & Discord -->
    <div class="card text-start mb-4 mx-auto" style="max-width: 600px;">
      <div class="card-body">
        <h3 class="h6 fw-semibold">{{ t('success.reportTitle') }}</h3>
        <a :href="installReportUrl" download="install-report.json" class="btn btn-outline-secondary mb-3 d-inline-flex align-items-center gap-2">
          <BIconDownload aria-hidden="true" /> {{ t('success.downloadReport') }}
        </a>
        <div class="input-group">
          <span class="input-group-text"><BIconDiscord aria-hidden="true" /></span>
          <input v-model="webhook" type="url" class="form-control" :placeholder="t('success.discordPlaceholder')" />
          <button class="btn btn-outline-primary" :disabled="!webhook || discordState === 'sending'" @click="notifyDiscord">
            {{ discordState === 'sending' ? t('success.discordSending') : t('success.discordSend') }}
          </button>
        </div>
        <div v-if="discordState === 'sent'" class="text-success small mt-2">{{ t('success.discordSent') }}</div>
        <div v-if="discordState === 'error'" class="text-danger small mt-2">{{ t('success.discordError') }}</div>
      </div>
    </div>

    <!-- Security: self-destruct -->
    <div class="card text-start mb-4 mx-auto border-danger" style="max-width: 600px;">
      <div class="card-body">
        <h3 class="h6 fw-semibold text-danger d-flex align-items-center gap-2">
          <BIconShieldLock aria-hidden="true" /> {{ t('success.securityTitle') }}
        </h3>
        <template v-if="destroyState === 'done'">
          <div class="alert alert-success mb-0 py-2">{{ t('success.securityDone') }}</div>
        </template>
        <template v-else>
          <p class="text-muted small">{{ t('success.securityHint') }}</p>
          <p v-if="destroyState === 'confirm'" class="text-danger small fw-bold">{{ t('success.securityConfirm') }}</p>
          <button class="btn d-inline-flex align-items-center gap-2" :class="destroyState === 'confirm' ? 'btn-danger' : 'btn-outline-danger'" :disabled="destroyState === 'deleting'" @click="removeInstaller">
            <BIconTrash aria-hidden="true" />
            {{ destroyState === 'deleting' ? t('success.securityDeleting') : t('success.securityButton') }}
          </button>
          <div v-if="destroyState === 'error'" class="text-danger small mt-2">{{ t('success.securityError') }}</div>
        </template>
      </div>
    </div>
  </div>
</template>

<style scoped>
.spin { animation: spin 0.8s linear infinite; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>
