<script setup lang="ts">
import { ref } from 'vue'

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

const totalVotes = ref(props.initialTotalVotes)
const userVoteId = ref<number | null>(props.initialUserVoteId)
const userVoteAmount = ref(props.initialUserVoteAmount)
const csrfHash = ref(props.initialCsrfHash)
const isLoading = ref(false)

async function vote(amount: number) {
  if (isLoading.value || userVoteAmount.value === amount) return
  isLoading.value = true

  try {
    const formData = new FormData()
    formData.append('ideaid', props.ideaId.toString())
    formData.append('votes', amount.toString())
    formData.append(props.csrfTokenName, csrfHash.value)

    const response = await fetch(props.voteUrl, {
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
        totalVotes.value = data.totalVotes
        userVoteId.value = data.userVoteId
        userVoteAmount.value = data.votes
      }
      if (data.csrfHash) {
        csrfHash.value = data.csrfHash
      }
    }
  } catch (error) {
    console.error('Failed to vote:', error)
  } finally {
    isLoading.value = false
  }
}

async function unvote() {
  if (isLoading.value || !userVoteId.value) return
  isLoading.value = true

  try {
    const formData = new FormData()
    formData.append('id', userVoteId.value.toString())
    formData.append(props.csrfTokenName, csrfHash.value)

    const response = await fetch(props.unvoteUrl, {
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
        totalVotes.value = data.totalVotes
        userVoteId.value = null
        userVoteAmount.value = 0
      }
      if (data.csrfHash) {
        csrfHash.value = data.csrfHash
      }
    }
  } catch (error) {
    console.error('Failed to remove vote:', error)
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
        @click="vote(1)" 
        :class="userVoteAmount === 1 ? 'bg-primary text-primary-foreground' : 'bg-background hover:bg-muted text-foreground'"
        class="flex-1 py-1.5 text-sm font-medium transition-colors border-r"
      >
        1
      </button>
      <button 
        @click="vote(2)" 
        :class="userVoteAmount === 2 ? 'bg-primary text-primary-foreground' : 'bg-background hover:bg-muted text-foreground'"
        class="flex-1 py-1.5 text-sm font-medium transition-colors border-r"
      >
        2
      </button>
      <button 
        @click="vote(3)" 
        :class="userVoteAmount === 3 ? 'bg-primary text-primary-foreground' : 'bg-background hover:bg-muted text-foreground'"
        class="flex-1 py-1.5 text-sm font-medium transition-colors"
      >
        3
      </button>
    </div>
    
    <div class="text-[10px] uppercase font-semibold mt-2 min-h-[16px]">
      <button 
        v-if="userVoteAmount > 0" 
        @click="unvote" 
        class="text-destructive hover:underline focus:outline-none focus:underline"
      >
        Remove Vote
      </button>
      <span v-else class="text-muted-foreground">Vote</span>
    </div>
  </div>
</template>
