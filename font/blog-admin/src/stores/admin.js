import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useAdminStore = defineStore('admin', () => {
  const token = ref(localStorage.getItem('admin_token') || '')
  const adminInfo = ref(JSON.parse(localStorage.getItem('admin_info') || 'null'))
  const isSuperAdmin = ref(localStorage.getItem('admin_super') === 'true')
  const menus = ref(JSON.parse(localStorage.getItem('admin_menus') || '[]'))
  const buttonPermissions = ref(JSON.parse(localStorage.getItem('admin_btns') || '[]'))

  const isLoggedIn = computed(() => !!token.value)
  const nickname = computed(() => adminInfo.value?.nickname || '')

  function setToken(t) {
    token.value = t
    localStorage.setItem('admin_token', t)
  }

  function setAdminInfo(info) {
    adminInfo.value = info
    localStorage.setItem('admin_info', JSON.stringify(info))
  }

  function setProfile(data) {
    if (!data) return
    isSuperAdmin.value = data.is_super_admin || false
    menus.value = data.menus || []
    buttonPermissions.value = data.button_permissions || []
    // 持久化到 localStorage（刷新后恢复）
    localStorage.setItem('admin_super', isSuperAdmin.value)
    localStorage.setItem('admin_menus', JSON.stringify(menus.value))
    localStorage.setItem('admin_btns', JSON.stringify(buttonPermissions.value))
  }

  function hasPermission(slug) {
    if (isSuperAdmin.value) return true
    return buttonPermissions.value.includes(slug)
  }

  function logout() {
    token.value = ''
    adminInfo.value = null
    isSuperAdmin.value = false
    menus.value = []
    buttonPermissions.value = []
    localStorage.removeItem('admin_token')
    localStorage.removeItem('admin_info')
    localStorage.removeItem('admin_super')
    localStorage.removeItem('admin_menus')
    localStorage.removeItem('admin_btns')
  }

  return { token, adminInfo, isSuperAdmin, menus, buttonPermissions, isLoggedIn, nickname,
           setToken, setAdminInfo, setProfile, hasPermission, logout }
})
