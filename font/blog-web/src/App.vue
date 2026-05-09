<template>
  <router-view />
  <div
    v-if="cursorEnabled"
    ref="cursorDotRef"
    class="cursor-dot"
  ></div>
  <div
    v-if="cursorEnabled"
    ref="cursorRingRef"
    class="cursor-ring"
  ></div>
</template>

<script setup>
import { onMounted, onBeforeUnmount, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useAppStore } from './stores/app'
import { useUserStore } from './stores/user'
import { userApi, webApi } from './api'

const route = useRoute()
const appStore = useAppStore()
const userStore = useUserStore()
const cursorEnabled = ref(false)
const cursorDotRef = ref(null)
const cursorRingRef = ref(null)

let removeMouseMove = null
let removeMouseOver = null
let removeMouseOut = null
let removeMouseDown = null
let removeMouseUp = null
let animationFrameId = 0
let pointerX = -100
let pointerY = -100
let ringX = -100
let ringY = -100

function ensureMetaTag(name, attr = 'name') {
  let tag = document.head.querySelector(`meta[${attr}="${name}"]`)
  if (!tag) {
    tag = document.createElement('meta')
    tag.setAttribute(attr, name)
    document.head.appendChild(tag)
  }
  return tag
}

function applySeoMeta(payload = {}) {
  const defaultDescription = appStore.site.description || ''
  const defaultKeywords = appStore.site.keywords || ''

  document.title = payload.title || appStore.resolveSiteTitle(route.meta.title || '')
  ensureMetaTag('description').setAttribute('content', payload.description || defaultDescription)
  ensureMetaTag('keywords').setAttribute('content', payload.keywords || defaultKeywords)
  ensureMetaTag('og:title', 'property').setAttribute('content', payload.og_title || payload.title || document.title)
  ensureMetaTag('og:description', 'property').setAttribute('content', payload.og_description || payload.description || defaultDescription)
  ensureMetaTag('og:image', 'property').setAttribute('content', payload.og_image || appStore.seo.og_image || '')

  const canonicalHref = payload.canonical_url || `${window.location.origin}${route.fullPath}`
  let canonical = document.head.querySelector('link[rel="canonical"]')
  if (!canonical) {
    canonical = document.createElement('link')
    canonical.setAttribute('rel', 'canonical')
    document.head.appendChild(canonical)
  }
  canonical.setAttribute('href', canonicalHref)
}

async function refreshPageSeo() {
  const pageTitle = route.meta.title || ''
  const seoType = route.meta.seoType
  const seoIdKey = route.meta.seoIdKey

  if (!seoType) {
    applySeoMeta({ title: appStore.resolveSiteTitle(pageTitle) })
    return
  }

  try {
    const seoId = seoIdKey ? route.params[seoIdKey] : undefined
    const res = await webApi.getSeo(seoType, seoId)
    if (res.code === 0 && res.data) {
      applySeoMeta({
        ...res.data,
        title: res.data.title || appStore.resolveSiteTitle(pageTitle),
      })
      return
    }
  } catch (_) {
    // Fallback below
  }

  applySeoMeta({ title: appStore.resolveSiteTitle(pageTitle) })
}

onMounted(async () => {
  const media = window.matchMedia('(pointer: fine)')
  cursorEnabled.value = media.matches

  if (cursorEnabled.value) {
    const interactiveSelector = [
      'a',
      'button',
      '.glass-card',
      '.article-card',
      '.nav-link',
      '.filter-chip',
      '.tag-pill',
      '.mobile-action',
      '.category-item',
      '.tutorial-item',
      '.featured-card',
      '.section-link',
      '.theme-toggle',
      '.user-entry',
    ].join(',')

    const updateDot = () => {
      if (cursorDotRef.value) {
        cursorDotRef.value.style.transform = `translate3d(${pointerX}px, ${pointerY}px, 0)`
      }
    }

    const updateRing = () => {
      ringX += (pointerX - ringX) * 0.18
      ringY += (pointerY - ringY) * 0.18

      if (cursorRingRef.value) {
        cursorRingRef.value.style.transform = `translate3d(${ringX}px, ${ringY}px, 0)`
      }

      animationFrameId = window.requestAnimationFrame(updateRing)
    }

    const toggleCursorState = (active) => {
      cursorDotRef.value?.classList.toggle('active', active)
      cursorRingRef.value?.classList.toggle('active', active)
    }

    const togglePressedState = (active) => {
      cursorDotRef.value?.classList.toggle('pressed', active)
      cursorRingRef.value?.classList.toggle('pressed', active)
    }

    const handleMove = (event) => {
      pointerX = event.clientX
      pointerY = event.clientY
      updateDot()
    }

    const handleOver = (event) => {
      toggleCursorState(!!event.target.closest(interactiveSelector))
    }

    const handleOut = (event) => {
      const related = event.relatedTarget
      if (!(related instanceof Element) || !related.closest(interactiveSelector)) {
        toggleCursorState(false)
      }
    }

    const handleDown = () => togglePressedState(true)
    const handleUp = () => togglePressedState(false)

    window.addEventListener('mousemove', handleMove, { passive: true })
    document.addEventListener('mouseover', handleOver, { passive: true })
    document.addEventListener('mouseout', handleOut, { passive: true })
    window.addEventListener('mousedown', handleDown, { passive: true })
    window.addEventListener('mouseup', handleUp, { passive: true })

    removeMouseMove = () => window.removeEventListener('mousemove', handleMove)
    removeMouseOver = () => document.removeEventListener('mouseover', handleOver)
    removeMouseOut = () => document.removeEventListener('mouseout', handleOut)
    removeMouseDown = () => window.removeEventListener('mousedown', handleDown)
    removeMouseUp = () => window.removeEventListener('mouseup', handleUp)
    updateDot()
    updateRing()
  }

  await appStore.fetchConfig()

  if (userStore.isLoggedIn && !userStore.userInfo?.id) {
    try {
      const res = await userApi.getUserInfo()
      if (res.code === 0 && res.data) {
        userStore.setUserInfo(res.data)
      }
    } catch (_) {
      userStore.logout()
    }
  }

  refreshPageSeo()
})

onBeforeUnmount(() => {
  removeMouseMove?.()
  removeMouseOver?.()
  removeMouseOut?.()
  removeMouseDown?.()
  removeMouseUp?.()
  if (animationFrameId) {
    window.cancelAnimationFrame(animationFrameId)
  }
})

watch(
  () => [route.fullPath, appStore.siteConfig],
  () => {
    refreshPageSeo()
  }
)
</script>
