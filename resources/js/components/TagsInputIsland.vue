<script setup lang="ts">
import { ref, watch } from 'vue'
import { TagsInput, TagsInputInput, TagsInputItem, TagsInputItemDelete, TagsInputItemText } from '@/components/ui/tags-input'

const props = defineProps<{
  initialTags?: string
  inputName?: string
  placeholder?: string
}>()

const tags = ref<string[]>([])

if (props.initialTags) {
  tags.value = props.initialTags.split(',').map(t => t.trim()).filter(t => t.length > 0)
}

const joinedTags = ref(tags.value.join(','))

watch(tags, (newTags) => {
  joinedTags.value = newTags.join(',')
}, { deep: true })
</script>

<template>
  <div>
    <!-- Hidden input that submits the comma-separated string back to CI4 -->
    <input type="hidden" :name="props.inputName || 'tags'" :value="joinedTags">
    
    <TagsInput v-model="tags" class="w-full">
      <TagsInputItem v-for="tag in tags" :key="tag" :value="tag">
        <TagsInputItemText />
        <TagsInputItemDelete />
      </TagsInputItem>

      <TagsInputInput :placeholder="props.placeholder || 'Add a tag...'" />
    </TagsInput>
  </div>
</template>
