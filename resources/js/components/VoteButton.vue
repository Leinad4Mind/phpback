<script setup lang="ts">
import { ref } from 'vue'
import { initCsrf, postForm, type JsonResponse } from '@/lib/csrf'

interface VoteResponse extends JsonResponse {
  totalVotes: number
  userVoteId: number | null
  votes: number
}

interface UnvoteResponse extends JsonResponse {
  totalVotes: number
}

const props = defineProps<{
  ideaId: number
  initialTotalVotes: number
  initialUserVoteId: number | null
  initialUserVoteAmount: number
  csrfTokenName: string
  initialCsrfHash: string
  voteUrl: string
  unvoteUrl: string
}>()

initCsrf(props.csrfTokenName, props.initialCsrfHash)

const totalVotes = ref(props.initialTotalVotes)
const userVoteId = ref<number | null>(props.initialUserVoteId)
const userVoteAmount = ref(props.initialUserVoteAmount)
const isLoading = ref(false)
const errorMessage = ref('')

async function vote(amount: number) {
  if (isLoading.value || userVoteAmount.value === amount) return
  isLoading.value = true
  errorMessage.value = ''

  try {
    const data = await postForm<VoteResponse>(props.voteUrl, {
      ideaid: props.ideaId,
      votes: amount,
    })
    if (data.success) {
      totalVotes.value = data.totalVotes
      userVoteId.value = data.userVoteId
      userVoteAmount.value = data.votes
    } else {
      errorMessage.value = data.error || 'Could not save your vote.'
    }
  } catch (error) {
    errorMessage.value = error instanceof Error ? error.message : 'Could not save your vote.'
  } finally {
    isLoading.value = false
  }
}

async function unvote() {
  if (isLoading.value || !userVoteId.value) return
  isLoading.value = true
  errorMessage.value = ''

  try {
    const data = await postForm<UnvoteResponse>(props.unvoteUrl, {
      id: userVoteId.value,
    })
    if (data.success) {
      totalVotes.value = data.totalVotes
      userVoteId.value = null
      userVoteAmount.value = 0
    } else {
      errorMessage.value = data.error || 'Could not remove your vote.'
    }
  } catch (error) {
    errorMessage.value = error instanceof Error ? error.message : 'Could not remove your vote.'
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div class="flex flex-col items-center sm:w-24 shrink-0 bg-muted/30 rounded-lg p-4 border border-dashed transition-opacity" :class="{'opacity-50 pointer-events-none': isLoading}">
    <div class="text-3xl font-bold text-primary mb-1">{{ totalVotes.toLocaleString() }}</div>
    <div class="text-xs uppercase tracking-wider text-muted-foreground font-semibold mb-4">Votes</div>

    <div class="flex w-full overflow-hidden rounded-md border shadow-sm transition-colors">
      <button
        v-for="amount in [1, 2, 3]"
        :key="amount"
        @click="vote(amount)"
        :class="[
          userVoteAmount === amount ? 'bg-primary text-primary-foreground' : 'bg-background hover:bg-muted text-foreground',
          amount < 3 ? 'border-r' : '',
        ]"
        class="flex-1 py-1.5 text-sm font-medium transition-colors cursor-pointer"
      >
        {{ amount }}
      </button>
    </div>

    <div class="text-[10px] uppercase font-semibold mt-2 min-h-[16px]">
      <button
        v-if="userVoteAmount > 0"
        @click="unvote"
        class="text-destructive hover:underline focus:outline-none focus:underline cursor-pointer"
      >
        Remove Vote
      </button>
      <span v-else class="text-muted-foreground">Vote</span>
    </div>

    <p v-if="errorMessage" class="text-destructive text-xs text-center mt-2" role="alert">
      {{ errorMessage }}
    </p>
  </div>
</template>
