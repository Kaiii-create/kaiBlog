import { computed, ref, watch } from 'vue'
import { defineStore } from 'pinia'
import { webApi } from '../api'

export const useAppStore = defineStore('app', () => {
  const savedTheme = localStorage.getItem('blog_theme') || 'dark'
  const darkMode = ref(savedTheme === 'dark')
  const pageLoading = ref(false)
  const siteConfig = ref(null)

  const site = computed(() => siteConfig.value?.site || {})
  const contact = computed(() => siteConfig.value?.contact || {})
  const footer = computed(() => siteConfig.value?.footer || {})
  const seo = computed(() => siteConfig.value?.seo || {})

  function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme)
    document.documentElement.classList.toggle('dark', theme === 'dark')
    localStorage.setItem('blog_theme', theme)
  }

  function initTheme() {
    applyTheme(darkMode.value ? 'dark' : 'light')
  }

  function toggleDarkMode() {
    darkMode.value = !darkMode.value
  }

  async function fetchConfig() {
    try {
      const res = await webApi.getConfig()
      if (res.code === 0 && res.data) {
        siteConfig.value = res.data
      }
      return res
    } catch (error) {
      console.warn('获取站点配置失败:', error?.message || error)
      return null
    }
  }

  function resolveSiteTitle(pageTitle = '') {
    const siteName = site.value?.name || 'Kaiii 博客'
    return pageTitle ? `${pageTitle} - ${siteName}` : siteName
  }

  watch(darkMode, (value) => {
    applyTheme(value ? 'dark' : 'light')
  }, { immediate: true })

  return {
    darkMode,
    pageLoading,
    siteConfig,
    site,
    contact,
    footer,
    seo,
    toggleDarkMode,
    initTheme,
    fetchConfig,
    resolveSiteTitle,
  }
})
