<script setup lang="ts">
import { ref, watch } from 'vue'
import { Checkbox } from '@/components/ui/checkbox'

const props = defineProps<{
  id: string
  name: string
  checked?: boolean
  label?: string
  value?: string
}>()

const isChecked = ref(props.checked || false)

// Sync the hidden input value
const hiddenInputValue = ref(isChecked.value ? (props.value || 'on') : '')

watch(isChecked, (newVal) => {
  hiddenInputValue.value = newVal ? (props.value || 'on') : ''
})
</script>

<template>
  <div class="flex items-center space-x-2">
    <Checkbox
      :id="props.id"
      :model-value="isChecked"
      @update:model-value="(v) => isChecked = v === true"
    />
    <label v-if="props.label" :for="props.id" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
      {{ props.label }}
    </label>
    
    <!-- Hidden input to pass state back to traditional forms -->
    <input type="hidden" :name="props.name" :value="hiddenInputValue" v-if="isChecked" />
  </div>
</template>
