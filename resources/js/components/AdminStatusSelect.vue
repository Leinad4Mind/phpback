<script setup lang="ts">
import { ref } from 'vue'
import { initCsrf, postForm, type JsonResponse } from '@/lib/csrf'

interface StatusResponse extends JsonResponse {
  newStatus?: string
}

const props = defineProps<{
  ideaId: number
  initialStatus: string
  csrfTokenName: string
  initialCsrfHash: string
  updateUrl: string
  statuses: Record<string, string>
  errorLabel?: string
}>()

initCsrf(props.csrfTokenName, props.initialCsrfHash)

const currentStatus = ref(props.initialStatus)
const isUpdating = ref(false)
const errorMessage = ref('')

async function changeStatus(event: Event) {
  const target = event.target as HTMLSelectElement
  const newStatus = target.value

  if (isUpdating.value || currentStatus.value === newStatus) return
  isUpdating.value = true
  errorMessage.value = ''

  try {
    const data = await postForm<StatusResponse>(props.updateUrl, {
      id: props.ideaId,
      status: newStatus,
    })
    if (data.success && data.newStatus) {
      currentStatus.value = data.newStatus
    } else {
      target.value = currentStatus.value
      errorMessage.value = data.error || props.errorLabel || 'Something went wrong. Please try again.'
    }
  } catch {
    target.value = currentStatus.value
    errorMessage.value = props.errorLabel || 'Something went wrong. Please try again.'
  } finally {
    isUpdating.value = false
  }
}
</script>

<template>
  <div class="flex flex-col gap-1">
    <select
      :value="currentStatus"
      @change="changeStatus"
      :disabled="isUpdating"
      class="flex h-9 w-[150px] rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:opacity-50 cursor-pointer"
    >
      <option v-for="(label, value) in props.statuses" :key="value" :value="value">
        {{ label }}
      </option>
    </select>
    <p v-if="errorMessage" class="text-destructive text-xs" role="alert">
      {{ errorMessage }}
    </p>
  </div>
</template>
