<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import {
  BIconPlusCircle,
  BIconTrash,
  BIconServer,
  BIconPuzzle,
  BIconCheckCircle,
} from 'bootstrap-icons-vue'
import { getAuthConfig, saveAuthConfig, getModsConfig, saveModsConfig } from '@/api'
import type { AuthConfig, ModConfig } from '@/api'

const { t } = useI18n({ useScope: 'global' })

const emit = defineEmits<{
  next: []
  error: [error: unknown]
}>()

const saving = ref(false)
const savedOk = ref(false)
const activeTab = ref<'servers' | 'mods'>('servers')

const DEFAULT_SERVERS = [
  { id: 'geoventure', name: 'Geoventure', color: '#4ade80', description: 'Aventure & Exploration' },
  { id: 'elandor',    name: 'Elandor',    color: '#a78bfa', description: 'RPG & Fantaisie' },
  { id: 'pokeland',  name: 'Pokeland',   color: '#fb923c', description: 'Pokémon & Combat' },
]

const servers = ref(
  DEFAULT_SERVERS.map(s => ({ ...s, authUrl: '', settings: '' }))
)

const mods = ref<ModConfig[]>([])

onMounted(async () => {
  try {
    const existing = await getAuthConfig()
    servers.value = DEFAULT_SERVERS.map(s => ({
      ...s,
      authUrl: existing[s.id]?.authUrl ?? '',
      settings: existing[s.id]?.settings ?? '',
      name: existing[s.id]?.name ?? s.name,
      color: existing[s.id]?.color ?? s.color,
    }))
  } catch { /* first run */ }

  try {
    const existing = await getModsConfig()
    if (existing.length) mods.value = existing
  } catch { /* first run */ }
})

function addMod() {
  mods.value.push({ name: '', fileName: '', description: '', recommended: false, enabled: true })
}

function removeMod(index: number) {
  mods.value.splice(index, 1)
}

async function saveAndContinue() {
  saving.value = true
  try {
    const authConfig: AuthConfig = {}
    for (const s of servers.value) {
      authConfig[s.id] = {
        authUrl: s.authUrl,
        settings: s.settings,
        name: s.name,
        color: s.color,
        description: s.description,
      }
    }
    await saveAuthConfig(authConfig)
    await saveModsConfig(mods.value)
    savedOk.value = true
    setTimeout(() => emit('next'), 400)
  } catch (e) {
    emit('error', e)
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <div>
    <h2 class="h5 fw-semibold mb-1">{{ t('setup.title') }}</h2>
    <p class="text-muted small mb-4">{{ t('setup.subtitle') }}</p>

    <!-- Tab switcher -->
    <ul class="nav nav-pills mb-4 gap-2">
      <li class="nav-item">
        <button
          class="nav-link d-flex align-items-center gap-2"
          :class="{ active: activeTab === 'servers' }"
          @click="activeTab = 'servers'"
        >
          <BIconServer aria-hidden="true" />
          {{ t('setup.serversTab') }}
        </button>
      </li>
      <li class="nav-item">
        <button
          class="nav-link d-flex align-items-center gap-2"
          :class="{ active: activeTab === 'mods' }"
          @click="activeTab = 'mods'"
        >
          <BIconPuzzle aria-hidden="true" />
          {{ t('setup.modsTab') }}
        </button>
      </li>
    </ul>

    <!-- Servers tab -->
    <div v-if="activeTab === 'servers'" class="d-flex flex-column gap-4 mb-4">
      <p class="text-muted small mb-0">{{ t('setup.serversHint') }}</p>
      <div
        v-for="server in servers"
        :key="server.id"
        class="card border-0"
        style="background: var(--bs-tertiary-bg);"
      >
        <div class="card-body px-3 py-3">
          <div class="d-flex align-items-center gap-2 mb-3">
            <span
              class="rounded-circle d-inline-block flex-shrink-0"
              :style="{ width: '10px', height: '10px', background: server.color }"
            ></span>
            <strong class="small">{{ server.name }}</strong>
            <span class="text-muted small ms-1">{{ server.description }}</span>
          </div>
          <div class="row g-2">
            <div class="col-sm-6">
              <label class="form-label small fw-medium mb-1">
                {{ t('setup.authUrl') }}
              </label>
              <input
                v-model="server.authUrl"
                type="url"
                class="form-control form-control-sm"
                :placeholder="t('setup.authUrlPlaceholder')"
              />
            </div>
            <div class="col-sm-6">
              <label class="form-label small fw-medium mb-1">
                {{ t('setup.settingsUrl') }}
              </label>
              <input
                v-model="server.settings"
                type="url"
                class="form-control form-control-sm"
                :placeholder="t('setup.settingsUrlPlaceholder')"
              />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Mods tab -->
    <div v-else class="mb-4">
      <p class="text-muted small mb-3">{{ t('setup.modsHint') }}</p>

      <div
        v-for="(mod, index) in mods"
        :key="index"
        class="card border-0 mb-2"
        style="background: var(--bs-tertiary-bg);"
      >
        <div class="card-body px-3 py-2">
          <div class="row g-2 align-items-center">
            <div class="col-sm-3">
              <input
                v-model="mod.name"
                class="form-control form-control-sm"
                :placeholder="t('setup.modName')"
              />
            </div>
            <div class="col-sm-3">
              <input
                v-model="mod.fileName"
                class="form-control form-control-sm"
                :placeholder="t('setup.modFileName')"
              />
            </div>
            <div class="col-sm-3">
              <input
                v-model="mod.description"
                class="form-control form-control-sm"
                :placeholder="t('setup.modDescription')"
              />
            </div>
            <div class="col-sm-2 d-flex gap-3 align-items-center">
              <div class="form-check form-check-inline mb-0">
                <input
                  v-model="mod.recommended"
                  class="form-check-input"
                  type="checkbox"
                  :id="`mod-rec-${index}`"
                />
                <label class="form-check-label small" :for="`mod-rec-${index}`">
                  {{ t('setup.modRecommended') }}
                </label>
              </div>
              <div class="form-check form-check-inline mb-0">
                <input
                  v-model="mod.enabled"
                  class="form-check-input"
                  type="checkbox"
                  :id="`mod-en-${index}`"
                />
                <label class="form-check-label small" :for="`mod-en-${index}`">
                  {{ t('setup.modEnabled') }}
                </label>
              </div>
            </div>
            <div class="col-sm-1 text-end">
              <button
                class="btn btn-link btn-sm text-danger p-0"
                @click="removeMod(index)"
                :aria-label="t('setup.removeMod')"
              >
                <BIconTrash aria-hidden="true" />
              </button>
            </div>
          </div>
        </div>
      </div>

      <button class="btn btn-outline-secondary btn-sm mt-2 d-flex align-items-center gap-2" @click="addMod">
        <BIconPlusCircle aria-hidden="true" />
        {{ t('setup.addMod') }}
      </button>
    </div>

    <!-- Save & continue -->
    <div class="d-flex justify-content-end">
      <button
        class="btn btn-primary rounded-pill px-4 d-flex align-items-center gap-2"
        :disabled="saving"
        @click="saveAndContinue"
      >
        <span v-if="saving" class="spinner-border spinner-border-sm" aria-hidden="true"></span>
        <BIconCheckCircle v-else-if="savedOk" aria-hidden="true" />
        {{ saving ? t('setup.saving') : t('continue') }}
      </button>
    </div>
  </div>
</template>
