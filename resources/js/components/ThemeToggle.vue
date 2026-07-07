<script setup lang="ts">
import { useDark } from '@vueuse/core'
import { Button } from '@/components/ui/button'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { Moon, Sun } from 'lucide-vue-next'

const props = defineProps<{
  /** Extra classes for the trigger button, e.g. to fit a dark header bar. */
  buttonClass?: string
  labels?: {
    light?: string
    dark?: string
    system?: string
    toggle?: string
  }
}>()

const isDark = useDark({
  valueDark: 'dark',
  valueLight: 'light',
  storageKey: 'theme',
})

function setTheme(theme: 'light' | 'dark' | 'system') {
  if (theme === 'system') {
    localStorage.removeItem('theme')
    isDark.value = window.matchMedia('(prefers-color-scheme: dark)').matches
  } else {
    isDark.value = theme === 'dark'
  }
}
</script>

<template>
  <DropdownMenu>
    <DropdownMenuTrigger as-child>
      <Button variant="ghost" size="icon" class="h-9 w-9 border-none outline-none cursor-pointer" :class="props.buttonClass || 'text-muted-foreground hover:text-foreground'">
        <Sun class="h-[1.2rem] w-[1.2rem] rotate-0 scale-100 transition-all dark:-rotate-90 dark:scale-0" />
        <Moon class="absolute h-[1.2rem] w-[1.2rem] rotate-90 scale-0 transition-all dark:rotate-0 dark:scale-100" />
        <span class="sr-only">{{ props.labels?.toggle || 'Toggle theme' }}</span>
      </Button>
    </DropdownMenuTrigger>
    <DropdownMenuContent align="end">
      <DropdownMenuItem @click="setTheme('light')">
        {{ props.labels?.light || 'Light' }}
      </DropdownMenuItem>
      <DropdownMenuItem @click="setTheme('dark')">
        {{ props.labels?.dark || 'Dark' }}
      </DropdownMenuItem>
      <DropdownMenuItem @click="setTheme('system')">
        {{ props.labels?.system || 'System' }}
      </DropdownMenuItem>
    </DropdownMenuContent>
  </DropdownMenu>
</template>
