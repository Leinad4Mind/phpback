<script setup lang="ts">
import { ref } from 'vue'

const props = defineProps<{
  ideaId: number
  initialStatus: string
  csrfTokenName: string
  initialCsrfHash: string
  updateUrl: string
  statuses: Record<string, string>
}>()

const currentStatus = ref(props.initialStatus)
const csrfHash = ref(props.initialCsrfHash)
const isUpdating = ref(false)

async function changeStatus(event: Event) {
  const target = event.target as HTMLSelectElement
  const newStatus = target.value

  if (isUpdating.value || currentStatus.value === newStatus) return
  isUpdating.value = true

  try {
    const formData = new FormData()
    formData.append('id', props.ideaId.toString())
    formData.append('status', newStatus)
    formData.append(props.csrfTokenName, csrfHash.value)

    const response = await fetch(props.updateUrl, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: formData
    })
    
    if (response.ok) {
      const data = await response.json()
      if (data.success) {
        currentStatus.value = data.newStatus
        // You could emit an event here to update the status badge on the page if desired
      } else {
        // Revert on failure
        target.value = currentStatus.value
        alert(data.error || 'Failed to update status')
      }
      if (data.csrfHash) csrfHash.value = data.csrfHash
    } else {
      target.value = currentStatus.value
    }
  } catch (error) {
    console.error('Failed to change status:', error)
    target.value = currentStatus.value
  } finally {
    isUpdating.value = false
  }
}
</script>

<template>
  <div class="flex items-center gap-2">
    <select 
      :value="currentStatus" 
      @change="changeStatus"
      :disabled="isUpdating"
      class="flex h-9 w-[150px] rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:opacity-50"
    >
      <option v-for="(label, value) in props.statuses" :key="value" :value="value">
        {{ label }}
      </option>
    </select>
  </div>
</template>
