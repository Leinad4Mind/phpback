import { ref } from 'vue'

/**
 * Shared CSRF state for every island on the page.
 *
 * CI4 is configured with Security::$regenerate = true, so each POST returns a
 * fresh hash. Keeping the hash in a module-level singleton (instead of one ref
 * per island) means that after any island posts, all the others keep working.
 */
const tokenName = ref('')
const tokenHash = ref('')

export function initCsrf(name: string, hash: string): void {
  // Islands rendered by the same request share the same token; first one wins.
  if (name && !tokenName.value) tokenName.value = name
  if (hash && !tokenHash.value) tokenHash.value = hash
}

export function updateCsrfHash(hash?: string): void {
  if (hash) tokenHash.value = hash
}

/** Exposed for tests. */
export function resetCsrf(): void {
  tokenName.value = ''
  tokenHash.value = ''
}

export function csrfState(): { tokenName: string; tokenHash: string } {
  return { tokenName: tokenName.value, tokenHash: tokenHash.value }
}

export interface JsonResponse {
  success: boolean
  error?: string
  csrfHash?: string
}

/**
 * POSTs form fields with the shared CSRF token and JSON negotiation headers,
 * refreshes the shared hash from the response, and throws on failure.
 */
export async function postForm<T extends JsonResponse>(
  url: string,
  fields: Record<string, string | number>,
): Promise<T> {
  const body = new FormData()
  for (const [key, value] of Object.entries(fields)) {
    body.append(key, String(value))
  }
  if (tokenName.value) {
    body.append(tokenName.value, tokenHash.value)
  }

  const response = await fetch(url, {
    method: 'POST',
    headers: {
      Accept: 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
    body,
  })

  let data: T | null = null
  try {
    data = (await response.json()) as T
  } catch {
    // Non-JSON response (e.g. a 403 CSRF error page).
  }

  if (data?.csrfHash) {
    updateCsrfHash(data.csrfHash)
  }

  if (!response.ok || data === null) {
    throw new Error(data?.error || `Request failed (HTTP ${response.status})`)
  }

  return data
}
