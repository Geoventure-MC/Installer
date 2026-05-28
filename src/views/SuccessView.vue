<script setup lang="ts">
import {
  BIconCheckCircleFill,
  BIconArrowRight,
  BIconBoxArrowUpRight,
  BIconInfoCircleFill,
} from 'bootstrap-icons-vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n({ useScope: 'global' })
</script>

<template>
  <div class="text-center">
    <!-- Success icon -->
    <div class="mb-4">
      <BIconCheckCircleFill
        class="text-success"
        style="font-size: 4rem;"
        aria-hidden="true"
      />
    </div>

    <!-- Title -->
    <h2 class="h3 fw-bold mb-2">{{ t('success.title') }}</h2>
    <p class="text-muted mb-4">{{ t('success.subtitle') }}</p>

    <!-- Next steps -->
    <div
      class="card border-0 text-start mb-4 mx-auto"
      style="max-width: 420px; background: var(--bs-tertiary-bg);"
    >
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

    <!-- CTAs -->
    <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center mb-4">
      <a
        href="/"
        class="btn btn-primary btn-lg rounded-pill px-4"
      >
        {{ t('success.openPanel') }}
        <BIconArrowRight class="ms-2" aria-hidden="true" />
      </a>
      <a
        href="https://github.com/Geoventure-MC/Launcher/releases/latest"
        target="_blank"
        rel="noopener noreferrer"
        class="btn btn-outline-secondary btn-lg rounded-pill px-4"
      >
        {{ t('success.getLauncher') }}
        <BIconBoxArrowUpRight class="ms-2" aria-hidden="true" />
      </a>
    </div>

    <!-- Cleanup note -->
    <div class="alert alert-info d-flex align-items-start gap-2 text-start" role="note">
      <BIconInfoCircleFill class="flex-shrink-0 mt-1" aria-hidden="true" />
      <small>{{ t('success.deleteNote') }}</small>
    </div>
  </div>
</template>
