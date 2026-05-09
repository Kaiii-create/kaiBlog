<template>
  <router-view />
</template>

<script setup>
import { onMounted } from 'vue'
import { useAppStore } from './stores/app'
import { useUserStore } from './stores/user'

const appStore = useAppStore()
const userStore = useUserStore()

onMounted(() => {
  // 拉取站点配置（页脚、联系方式等用）
  appStore.fetchConfig()

  // 如果已登录，拉取最新用户信息
  if (userStore.isLoggedIn && !userStore.userInfo?.email) {
    import('./api').then(({ userApi }) => {
      userApi.getUserInfo().then(res => {
        if (res.code === 0 && res.data) {
          userStore.setUserInfo(res.data)
        }
      }).catch(() => {})
    })
  }
})
</script>

<style>
body { margin: 0; }
</style>
