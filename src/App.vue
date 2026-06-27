<script setup lang="ts">
import CentralCorpInstaller from './views/CentralCorpInstaller.vue'
import { useI18n } from 'vue-i18n'
import logoUrl from '@/assets/logo.png'

const { t, locale } = useI18n()
const currentYear = new Date().getFullYear()

const languages = [
  { code: 'en', label: 'English', flag: '🇬🇧' },
  { code: 'fr', label: 'Français', flag: '🇫🇷' },
]

function setLanguage(code: string) {
  locale.value = code
}
</script>

<template>
  <div id="app" class="min-vh-100 d-flex flex-column">
    <a href="#main-content" class="visually-hidden-focusable skip-link">
      Skip to main content
    </a>

    <!-- Language selector -->
    <nav class="language-nav py-2" aria-label="Language selection">
      <div class="container">
        <div class="d-flex justify-content-end">
          <div class="btn-group btn-group-sm" role="group" aria-label="Select language">
            <button
              v-for="lang in languages"
              :key="lang.code"
              type="button"
              class="btn"
              :class="locale === lang.code ? 'btn-primary' : 'btn-outline-secondary'"
              @click="setLanguage(lang.code)"
              :aria-pressed="locale === lang.code"
              :title="lang.label"
            >
              <span aria-hidden="true">{{ lang.flag }}</span>
              <span class="ms-1 d-none d-sm-inline">{{ lang.code.toUpperCase() }}</span>
              <span class="visually-hidden">{{ lang.label }}</span>
            </button>
          </div>
        </div>
      </div>
    </nav>

    <main id="main-content" class="flex-grow-1 d-flex align-items-center py-4">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-12 col-md-10 col-lg-8 col-xl-7">
            <article class="card shadow-sm border-0" role="region" aria-labelledby="installer-title">
              <div class="card-body p-4 p-md-5">
                <header class="text-center mb-4">
                  <img
                    :src="logoUrl"
                    alt="CentralCorp Panel Logo"
                    class="mb-3" 
                    width="280"
                    height="auto"
                    loading="eager"
                  />
                  <h1 id="installer-title" class="h3 fw-semibold mb-2">
                    {{ t('title') }}
                  </h1>
                </header>

                <CentralCorpInstaller />
              </div>
            </article>
          </div>
        </div>
      </div>
    </main>

    <footer class="py-3 text-center text-muted small" role="contentinfo">
      <div class="container">
        <p class="mb-0">
          {{ t('copyright', { year: currentYear }) }}
        </p>
      </div>
    </footer>
  </div>
</template>

<style scoped>
.skip-link {
  position: absolute;
  top: 0;
  left: 50%;
  transform: translateX(-50%);
  z-index: 1000;
  padding: 0.5rem 1rem;
  background: var(--bs-primary);
  color: white;
  text-decoration: none;
  border-radius: 0 0 0.25rem 0.25rem;
}

.skip-link:focus {
  clip: auto !important;
  width: auto !important;
  height: auto !important;
}

.card {
  border-radius: 1rem;
}

.language-nav {
  background: transparent;
}

.btn-group .btn {
  border-radius: 0.5rem !important;
  padding: 0.25rem 0.75rem;
  font-size: 0.875rem;
}

.btn-group .btn:not(:last-child) {
  margin-right: 0.25rem;
}
</style>
