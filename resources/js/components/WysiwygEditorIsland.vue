<script setup lang="ts">
import { ref, onBeforeUnmount, watch } from 'vue'
import { useEditor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import Placeholder from '@tiptap/extension-placeholder'
import {
  Bold,
  Italic,
  Strikethrough,
  List,
  ListOrdered,
  Quote,
  Undo,
  Redo,
  Heading2
} from '@lucide/vue'

const props = defineProps<{
  name: string
  initialContent?: string
  placeholder?: string
  minlength?: number
  maxlength?: number
  required?: boolean
}>()

const content = ref(props.initialContent || '')

const editor = useEditor({
  content: content.value,
  extensions: [
    StarterKit,
    Placeholder.configure({
      placeholder: props.placeholder || 'Write something...',
    }),
  ],
  onUpdate: ({ editor }) => {
    content.value = editor.getHTML()
    // If it's just an empty paragraph, consider it empty for required validation
    if (editor.isEmpty) {
      content.value = ''
    }
  },
  editorProps: {
    attributes: {
      class: 'p-3 focus:outline-none min-h-[120px]',
    },
  },
})

watch(() => props.initialContent, (newContent) => {
  if (editor.value && newContent !== editor.value.getHTML()) {
    editor.value.commands.setContent(newContent || '')
  }
})

onBeforeUnmount(() => {
  editor.value?.destroy()
})
</script>

<template>
  <div class="flex flex-col border border-input rounded-md bg-background overflow-hidden ring-offset-background focus-within:ring-2 focus-within:ring-ring focus-within:ring-offset-2">
    
    <!-- Toolbar -->
    <div v-if="editor" class="flex flex-wrap items-center gap-1 border-b border-input p-1 bg-muted/50">
      <button 
        type="button" 
        @click="editor.chain().focus().toggleBold().run()" 
        :class="['p-1.5 rounded-sm hover:bg-muted text-muted-foreground hover:text-foreground transition-colors', { 'bg-muted text-foreground': editor.isActive('bold') }]"
        title="Bold"
      >
        <Bold class="w-4 h-4" />
      </button>
      <button 
        type="button" 
        @click="editor.chain().focus().toggleItalic().run()" 
        :class="['p-1.5 rounded-sm hover:bg-muted text-muted-foreground hover:text-foreground transition-colors', { 'bg-muted text-foreground': editor.isActive('italic') }]"
        title="Italic"
      >
        <Italic class="w-4 h-4" />
      </button>
      <button 
        type="button" 
        @click="editor.chain().focus().toggleStrike().run()" 
        :class="['p-1.5 rounded-sm hover:bg-muted text-muted-foreground hover:text-foreground transition-colors', { 'bg-muted text-foreground': editor.isActive('strike') }]"
        title="Strikethrough"
      >
        <Strikethrough class="w-4 h-4" />
      </button>

      <div class="w-px h-4 bg-border mx-1"></div>

      <button 
        type="button" 
        @click="editor.chain().focus().toggleHeading({ level: 2 }).run()" 
        :class="['p-1.5 rounded-sm hover:bg-muted text-muted-foreground hover:text-foreground transition-colors', { 'bg-muted text-foreground': editor.isActive('heading', { level: 2 }) }]"
        title="Heading 2"
      >
        <Heading2 class="w-4 h-4" />
      </button>
      <button 
        type="button" 
        @click="editor.chain().focus().toggleBulletList().run()" 
        :class="['p-1.5 rounded-sm hover:bg-muted text-muted-foreground hover:text-foreground transition-colors', { 'bg-muted text-foreground': editor.isActive('bulletList') }]"
        title="Bullet List"
      >
        <List class="w-4 h-4" />
      </button>
      <button 
        type="button" 
        @click="editor.chain().focus().toggleOrderedList().run()" 
        :class="['p-1.5 rounded-sm hover:bg-muted text-muted-foreground hover:text-foreground transition-colors', { 'bg-muted text-foreground': editor.isActive('orderedList') }]"
        title="Numbered List"
      >
        <ListOrdered class="w-4 h-4" />
      </button>
      <button 
        type="button" 
        @click="editor.chain().focus().toggleBlockquote().run()" 
        :class="['p-1.5 rounded-sm hover:bg-muted text-muted-foreground hover:text-foreground transition-colors', { 'bg-muted text-foreground': editor.isActive('blockquote') }]"
        title="Blockquote"
      >
        <Quote class="w-4 h-4" />
      </button>

      <div class="w-px h-4 bg-border mx-1"></div>

      <button 
        type="button" 
        @click="editor.chain().focus().undo().run()" 
        :disabled="!editor.can().undo()"
        class="p-1.5 rounded-sm text-muted-foreground hover:bg-muted hover:text-foreground transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
        title="Undo"
      >
        <Undo class="w-4 h-4" />
      </button>
      <button 
        type="button" 
        @click="editor.chain().focus().redo().run()" 
        :disabled="!editor.can().redo()"
        class="p-1.5 rounded-sm text-muted-foreground hover:bg-muted hover:text-foreground transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
        title="Redo"
      >
        <Redo class="w-4 h-4" />
      </button>
    </div>

    <!-- Editor -->
    <EditorContent :editor="editor" class="flex-grow cursor-text" @click="editor?.commands.focus()" />

    <!-- Hidden input for form submission -->
    <textarea 
      :name="props.name" 
      :required="props.required" 
      :minlength="props.minlength"
      :maxlength="props.maxlength"
      class="hidden" 
      v-model="content"
    ></textarea>
  </div>
</template>
