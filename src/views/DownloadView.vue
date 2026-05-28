<script setup lang="ts">
import { BIconCloudDownloadFill, BIconArrowRepeat } from 'bootstrap-icons-vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const emit = defineEmits<{ download: [] }>()
defineProps<{ loading: boolean }>()
</script>

<template>
  <div class="text-center py-3">
    <!-- Download icon -->
    <div class="mb-4">
      <BIconCloudDownloadFill
        class="text-primary display-1"
        aria-hidden="true"
      />
    </div>

    <!-- Title -->
    <h2 class="h4 fw-semibold mb-3">{{ t('download.title') }}</h2>

    <!-- Info text -->
    <p class="text-muted mb-4 mx-auto" style="max-width: 400px;">
      {{ t('download.info') }}
    </p>

    <!-- Download button -->
    <button
      type="button"
      @click="emit('download')"
      :disabled="loading"
      class="btn btn-primary btn-lg rounded-pill px-5"
      :aria-busy="loading"
    >
      <template v-if="loading">
        <BIconArrowRepeat class="spin me-2" aria-hidden="true" />
        <span>{{ t('download.downloading') }}</span>
      </template>
      <template v-else>
        <BIconCloudDownloadFill class="me-2" aria-hidden="true" />
        <span>{{ t('download.go') }}</span>
      </template>
    </button>

    <!-- Loading indicator -->
    <div v-if="loading" class="mt-4" role="status" aria-live="polite">
      <div class="progress" style="height: 6px; max-width: 300px; margin: 0 auto;">
        <div
          class="progress-bar progress-bar-striped progress-bar-animated"
          role="progressbar"
          style="width: 100%"
          aria-label="Download in progress"
        ></div>
      </div>
      <p class="text-muted small mt-2 mb-0">{{ t('download.pleaseWait') }}</p>
    </div>
  </div>
</template>

<style scoped>
@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.spin {
  animation: spin 1s linear infinite;
}

.display-1 {
  font-size: 4rem;
}
</style>
