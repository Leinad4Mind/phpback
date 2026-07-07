<script setup lang="ts">
import { ref } from 'vue'
import { initCsrf, postForm, type JsonResponse } from '@/lib/csrf'

interface Comment {
  id: number
  user: string
  userid: number
  date: string
  content: string
}

interface CommentResponse extends JsonResponse {
  comment?: Comment
}

const props = defineProps<{
  ideaId: number
  isLoggedIn: boolean
  isAdmin: boolean
  initialComments: Comment[]
  csrfTokenName: string
  initialCsrfHash: string
  submitUrl: string
  deleteUrl: string
  flagUrl: string
  baseUrl: string
  labels?: {
    comments?: string
    leaveComment?: string
    submit?: string
    submitting?: string
    delete?: string
    flag?: string
    noComments?: string
    sureDelete?: string
    sureFlag?: string
    flagged?: string
    error?: string
  }
}>()

initCsrf(props.csrfTokenName, props.initialCsrfHash)

const comments = ref(props.initialComments)
const newCommentContent = ref('')
const isSubmitting = ref(false)
const errorMessage = ref('')

function genericError(): string {
  return props.labels?.error || 'Something went wrong. Please try again.'
}

function profileUrl(userid: number): string {
  return `${props.baseUrl.replace(/\/$/, '')}/home/profile/${userid}`
}

async function submitComment() {
  if (isSubmitting.value || !newCommentContent.value.trim()) return
  isSubmitting.value = true
  errorMessage.value = ''

  try {
    const data = await postForm<CommentResponse>(props.submitUrl, {
      idea_id: props.ideaId,
      content: newCommentContent.value,
    })
    if (data.success && data.comment) {
      comments.value.push(data.comment)
      newCommentContent.value = ''
    } else {
      errorMessage.value = data.error || genericError()
    }
  } catch {
    errorMessage.value = genericError()
  } finally {
    isSubmitting.value = false
  }
}

async function deleteComment(id: number) {
  if (!confirm(props.labels?.sureDelete || 'Are you sure you want to delete this comment?')) return
  errorMessage.value = ''

  try {
    const data = await postForm<JsonResponse>(props.deleteUrl, { id })
    if (data.success) {
      comments.value = comments.value.filter(c => c.id !== id)
    } else {
      errorMessage.value = data.error || genericError()
    }
  } catch {
    errorMessage.value = genericError()
  }
}

async function flagComment(id: number) {
  if (!confirm(props.labels?.sureFlag || 'Flag this comment as inappropriate?')) return
  errorMessage.value = ''

  try {
    const data = await postForm<JsonResponse>(props.flagUrl, {
      cid: id,
      idea_id: props.ideaId,
    })
    if (data.success) {
      alert(props.labels?.flagged || 'Comment flagged for review.')
    } else {
      errorMessage.value = data.error || genericError()
    }
  } catch {
    errorMessage.value = genericError()
  }
}

function getInitial(name: string) {
  return name ? name.charAt(0).toUpperCase() : '?'
}
</script>

<template>
  <div class="sm:ml-32">
    <h3 class="text-xl font-bold mb-6 flex items-center gap-2">
      <svg class="w-5 h-5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path>
      </svg>
      {{ props.labels?.comments || 'Comments' }} ({{ comments.length }})
    </h3>

    <div v-if="props.isLoggedIn" class="bg-card text-card-foreground border rounded-lg p-4 mb-8 shadow-sm">
      <form @submit.prevent="submitComment" class="space-y-4">
        <div>
          <label class="block text-sm font-medium mb-2">{{ props.labels?.leaveComment || 'Leave a comment' }}</label>
          <textarea
            v-model="newCommentContent"
            required
            rows="4"
            :disabled="isSubmitting"
            class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:opacity-50"
          ></textarea>
        </div>
        <button
          type="submit"
          :disabled="isSubmitting"
          class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 disabled:opacity-50 cursor-pointer"
        >
          {{ isSubmitting ? (props.labels?.submitting || 'Submitting...') : (props.labels?.submit || 'Submit') }}
        </button>
      </form>
    </div>

    <p v-if="errorMessage" class="text-destructive text-sm mb-4" role="alert">
      {{ errorMessage }}
    </p>

    <div class="space-y-4">
      <div v-for="comment in comments" :key="comment.id" class="bg-background border rounded-lg p-4 shadow-sm relative group">
        <div class="flex items-center gap-3 mb-3">
          <div class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-sm uppercase">
            {{ getInitial(comment.user) }}
          </div>
          <div>
            <a :href="profileUrl(comment.userid)" class="font-semibold text-sm hover:underline text-foreground">{{ comment.user }}</a>
            <div class="text-xs text-muted-foreground">{{ comment.date }}</div>
          </div>

          <div class="ml-auto opacity-0 group-hover:opacity-100 transition-opacity">
            <template v-if="props.isAdmin">
              <button @click="deleteComment(comment.id)" class="text-xs text-destructive hover:underline p-1 cursor-pointer">
                {{ props.labels?.delete || 'Delete' }}
              </button>
            </template>
            <template v-else-if="props.isLoggedIn">
              <button @click="flagComment(comment.id)" class="text-xs text-muted-foreground hover:text-destructive hover:underline p-1 flex items-center gap-1 cursor-pointer">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"></path>
                </svg>
                {{ props.labels?.flag || 'Flag' }}
              </button>
            </template>
          </div>
        </div>
        <div class="text-sm whitespace-pre-wrap text-foreground/90 pl-11">{{ comment.content }}</div>
      </div>

      <div v-if="comments.length === 0" class="text-center py-12 text-muted-foreground bg-muted/20 rounded-lg border border-dashed">
        <svg class="w-12 h-12 mx-auto mb-3 text-muted-foreground/50" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
        </svg>
        <p>{{ props.labels?.noComments || 'No comments yet. Be the first to share your thoughts!' }}</p>
      </div>
    </div>
  </div>
</template>
