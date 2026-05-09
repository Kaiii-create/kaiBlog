import { defineStore } from 'pinia'
import { ref, watch } from 'vue'
import { webApi } from '../api'

export const useAppStore = defineStore('app', () => {
  // 主题
  const savedTheme = localStorage.getItem('blog_theme') || 'dark'
  const darkMode = ref(savedTheme === 'dark')
  const pageLoading = ref(false)

  // 站点配置（从 /api/config 获取）
  const siteConfig = ref(null)

  function initTheme() {
    document.documentElement.setAttribute('data-theme', darkMode.value ? 'dark' : 'light')
  }

  function toggleDarkMode() {
    darkMode.value = !darkMode.value
  }

  // 获取站点配置
  async function fetchConfig() {
    try {
      const res = await webApi.getConfig()
      if (res.code === 0 && res.data) {
        siteConfig.value = res.data
      }
    } catch (e) {
      console.warn('获取配置失败:', e)
    }
  }

  watch(darkMode, (val) => {
    const theme = val ? 'dark' : 'light'
    document.documentElement.setAttribute('data-theme', theme)
    localStorage.setItem('blog_theme', theme)
    if (val) {
      document.documentElement.classList.add('dark')
    } else {
      document.documentElement.classList.remove('dark')
    }
  }, { immediate: true })

  return {
    darkMode, pageLoading, siteConfig,
    toggleDarkMode, initTheme, fetchConfig
  }
})
