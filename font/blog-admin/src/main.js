import { createApp } from 'vue'
import { createPinia } from 'pinia'
import ElementPlus from 'element-plus'
import 'element-plus/dist/index.css'
import * as ElementPlusIconsVue from '@element-plus/icons-vue'
import App from './App.vue'
import router from './router'
import { useThemeStore } from './stores/theme'
import { useAdminStore } from './stores/admin'
import { adminApi } from './api'
import './assets/styles/main.scss'

const app = createApp(App)
const pinia = createPinia()
app.use(pinia)
app.use(router)
app.use(ElementPlus)

// 初始化主题
const themeStore = useThemeStore()
themeStore.init()

// 注册所有图标
for (const [key, component] of Object.entries(ElementPlusIconsVue)) {
  app.component(key, component)
}

// 如果已有 token，启动时加载权限和菜单
const adminStore = useAdminStore()
if (adminStore.isLoggedIn) {
  adminApi.getProfile().then(res => {
    if (res.code === 0) adminStore.setProfile(res.data)
  }).catch(() => {
    // getProfile 失败（如 token 过期），强制跳转登录
    if (!window.location.pathname.includes('/admin/login')) {
      window.location.href = '/admin/login'
    }
  })
}

app.mount('#app')
