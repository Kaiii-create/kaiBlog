import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useTabsStore = defineStore('tabs', () => {
  const tabs = ref([
    { path: '/admin/dashboard', title: '控制台', closable: false }
  ])
  const activePath = ref('/admin/dashboard')

  const tabList = computed(() => tabs.value)

  function addTab(route) {
    const path = route.path
    const title = route.meta?.title || route.name || path
    const exists = tabs.value.find(t => t.path === path)
    if (!exists) {
      tabs.value.push({ path, title, closable: path !== '/admin/dashboard' })
    }
    activePath.value = path
  }

  function removeTab(path) {
    const idx = tabs.value.findIndex(t => t.path === path)
    if (idx === -1 || tabs.value[idx].closable === false) return
    tabs.value.splice(idx, 1)
    // 如果关闭的是当前激活的tab，跳转到前一个
    if (activePath.value === path) {
      const target = tabs.value[Math.min(idx, tabs.value.length - 1)]
      if (target) activePath.value = target.path
    }
  }

  function closeOther(path) {
    tabs.value = tabs.value.filter(t => t.path === path || !t.closable)
    activePath.value = path
  }

  function closeAll() {
    tabs.value = tabs.value.filter(t => !t.closable)
    activePath.value = tabs.value[0]?.path || '/admin/dashboard'
  }

  function setActive(path) {
    activePath.value = path
  }

  return { tabs, activePath, tabList, addTab, removeTab, closeOther, closeAll, setActive }
})
