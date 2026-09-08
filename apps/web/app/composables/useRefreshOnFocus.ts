export function useRefreshOnFocus(
  callback: () => void,
  options: { minIntervalMs?: number } = {},
): void {
  const minInterval = options.minIntervalMs ?? 15_000
  let lastRun = 0

  function handler(): void {
    if (document.visibilityState !== 'visible') return

    const now = Date.now()
    if (now - lastRun < minInterval) return

    lastRun = now
    callback()
  }

  onMounted(() => {
    document.addEventListener('visibilitychange', handler)
    window.addEventListener('focus', handler)
  })

  onBeforeUnmount(() => {
    document.removeEventListener('visibilitychange', handler)
    window.removeEventListener('focus', handler)
  })
}
