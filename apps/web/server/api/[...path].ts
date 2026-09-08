export default defineEventHandler((event) => {
  const { apiInternalUrl } = useRuntimeConfig()

  const path = event.context.params?.path ?? ''
  const { search } = getRequestURL(event)

  return proxyRequest(event, `${apiInternalUrl}/api/${path}${search}`)
})
