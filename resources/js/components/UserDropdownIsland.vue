<script setup lang="ts">
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { Button } from '@/components/ui/button'

const props = defineProps<{
  username: string
  profileUrl: string
  isAdmin: boolean
  adminUrl: string
  logoutUrl: string
  csrfTokenName: string
  csrfHash: string
  labels: Record<string, string>
}>()

function logout() {
  const form = document.createElement('form')
  form.method = 'POST'
  form.action = props.logoutUrl
  
  const csrf = document.createElement('input')
  csrf.type = 'hidden'
  csrf.name = props.csrfTokenName
  csrf.value = props.csrfHash
  
  form.appendChild(csrf)
  document.body.appendChild(form)
  form.submit()
}
</script>

<template>
  <DropdownMenu>
    <DropdownMenuTrigger as-child>
      <Button variant="ghost" class="font-medium h-8 px-2 flex items-center gap-1 focus-visible:ring-offset-0 focus-visible:ring-1">
        <span class="text-muted-foreground font-normal hidden sm:inline">{{ props.labels.logged_as }}</span>
        <span>{{ props.username }}</span>
        <svg class="w-4 h-4 ml-1 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
      </Button>
    </DropdownMenuTrigger>
    <DropdownMenuContent align="end" class="w-48">
      <DropdownMenuLabel class="font-normal">
        <div class="flex flex-col space-y-1">
          <p class="text-sm font-medium leading-none">{{ props.username }}</p>
        </div>
      </DropdownMenuLabel>
      <DropdownMenuSeparator />
      <DropdownMenuItem as-child>
        <a :href="props.profileUrl" class="cursor-pointer w-full">{{ props.labels.view_profile }}</a>
      </DropdownMenuItem>
      <DropdownMenuItem v-if="props.isAdmin" as-child>
        <a :href="props.adminUrl" class="cursor-pointer w-full">{{ props.labels.admin_panel }}</a>
      </DropdownMenuItem>
      <DropdownMenuSeparator />
      <DropdownMenuItem @click="logout" class="cursor-pointer text-destructive focus:bg-destructive focus:text-destructive-foreground">
        {{ props.labels.logout }}
      </DropdownMenuItem>
    </DropdownMenuContent>
  </DropdownMenu>
</template>
