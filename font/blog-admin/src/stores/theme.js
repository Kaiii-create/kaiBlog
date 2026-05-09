import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useThemeStore = defineStore('theme', () => {
  const isDark = ref(false)
  const sidebarCollapsed = ref(false)

  function init() {
    const saved = localStorage.getItem('admin_theme')
    if (saved === 'dark') {
      isDark.value = true
      document.documentElement.classList.add('dark')
    }
    syncSidebarWidth()
  }

  function syncSidebarWidth() {
    const root = document.documentElement
    const w = sidebarCollapsed.value
      ? getComputedStyle(root).getPropertyValue('--admin-sidebar-collapsed').trim()
      : getComputedStyle(root).getPropertyValue('--admin-sidebar-width').trim()
    root.style.setProperty('--sidebar-actual-width', w)
  }

  function toggleTheme() {
    isDark.value = !isDark.value
    document.documentElement.classList.toggle('dark', isDark.value)
    localStorage.setItem('admin_theme', isDark.value ? 'dark' : 'light')
  }

  function toggleSidebar() {
    sidebarCollapsed.value = !sidebarCollapsed.value
    syncSidebarWidth()
  }

  return { isDark, sidebarCollapsed, init, toggleTheme, toggleSidebar }
})
