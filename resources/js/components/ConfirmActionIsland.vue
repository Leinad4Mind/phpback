<script setup lang="ts">
import { ref } from 'vue'
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogTrigger,
} from '@/components/ui/alert-dialog'

const props = defineProps<{
  triggerText: string
  triggerClass?: string
  title: string
  description: string
  confirmText?: string
  cancelText?: string
  actionUrl?: string
  method?: string
  payload?: Record<string, any>
  csrfName?: string
  csrfHash?: string
  existingFormId?: string
}>()

const formRef = ref<HTMLFormElement | null>(null)

function confirmAction() {
  if (props.existingFormId) {
    const form = document.getElementById(props.existingFormId) as HTMLFormElement
    if (form) form.submit()
  } else if (formRef.value) {
    formRef.value.submit()
  }
}
</script>

<template>
  <AlertDialog>
    <AlertDialogTrigger asChild>
      <button :class="triggerClass || 'inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-destructive text-destructive-foreground hover:bg-destructive/90 h-9 px-4'">
        {{ triggerText }}
      </button>
    </AlertDialogTrigger>
    <AlertDialogContent>
      <AlertDialogHeader>
        <AlertDialogTitle>{{ title }}</AlertDialogTitle>
        <AlertDialogDescription>{{ description }}</AlertDialogDescription>
      </AlertDialogHeader>
      <AlertDialogFooter>
        <AlertDialogCancel>{{ cancelText || 'Cancel' }}</AlertDialogCancel>
        <AlertDialogAction @click="confirmAction" class="bg-destructive text-destructive-foreground hover:bg-destructive/90">
          {{ confirmText || 'Continue' }}
        </AlertDialogAction>
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>

  <!-- Hidden form for native submission -->
  <form v-if="actionUrl && !existingFormId" ref="formRef" :action="actionUrl" :method="method || 'post'" class="hidden">
    <input v-if="csrfName && csrfHash" type="hidden" :name="csrfName" :value="csrfHash" />
    <template v-if="payload">
      <input v-for="(val, key) in payload" :key="key" type="hidden" :name="key" :value="val" />
    </template>
  </form>
</template>
