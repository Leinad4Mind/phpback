<script setup lang="ts">
import { onMounted, ref, watch } from 'vue'

/**
 * Tab strip for server-rendered panels.
 *
 * Each tab with id `foo` controls the element `#foo-panel` in the page, so the
 * panel content stays rendered by PHP (SEO/no-JS friendly) and this island
 * only handles the switching. Replaces the five duplicated inline
 * `showXTab()` scripts that the views carried before.
 */
interface Tab {
  id: string
  label: string
  count?: number | null
  countClass?: string
}

interface TabLink {
  label: string
  href: string
  external?: boolean
}

const props = defineProps<{
  tabs: Tab[]
  /** Extra links rendered after the tabs (e.g. "Admin panel"). */
  links?: TabLink[]
  /** Tab selected on load; defaults to the first one. */
  initialTab?: string
}>()

const activeTab = ref(props.initialTab || props.tabs[0]?.id || '')

function applyVisibility() {
  for (const tab of props.tabs) {
    const panel = document.getElementById(`${tab.id}-panel`)
    if (!panel) continue
    panel.classList.toggle('hidden', tab.id !== activeTab.value)
    panel.classList.toggle('block', tab.id === activeTab.value)
  }
}

onMounted(applyVisibility)
watch(activeTab, applyVisibility)
</script>

<template>
  <div class="border-b mb-6">
    <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" role="tablist">
      <li v-for="tab in tabs" :key="tab.id" class="mr-2">
        <button
          type="button"
          role="tab"
          :aria-selected="activeTab === tab.id"
          :aria-controls="`${tab.id}-panel`"
          @click="activeTab = tab.id"
          class="inline-flex items-center gap-2 p-4 border-b-2 rounded-t-lg transition-colors cursor-pointer"
          :class="activeTab === tab.id
            ? 'border-primary text-primary'
            : 'border-transparent text-muted-foreground hover:text-foreground hover:border-muted-foreground'"
        >
          {{ tab.label }}
          <span
            v-if="typeof tab.count === 'number'"
            class="text-xs rounded-full px-2 py-0.5"
            :class="tab.countClass || 'bg-muted-foreground/20 text-foreground'"
          >
            {{ tab.count }}
          </span>
        </button>
      </li>
      <li v-for="link in props.links || []" :key="link.href" class="mr-2">
        <a
          :href="link.href"
          :target="link.external ? '_blank' : undefined"
          :rel="link.external ? 'noopener' : undefined"
          class="inline-block p-4 border-b-2 border-transparent hover:text-foreground hover:border-muted-foreground rounded-t-lg text-muted-foreground"
        >
          {{ link.label }}
        </a>
      </li>
    </ul>
  </div>
</template>
