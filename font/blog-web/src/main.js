import { createApp } from 'vue'
import { createPinia } from 'pinia'
import ElementPlus from 'element-plus'
import 'element-plus/dist/index.css'
import App from './App.vue'
import router from './router'
import { useAppStore } from './stores/app'
import './assets/styles/main.scss'

// 初始化 AOS 滚动动画
import AOS from 'aos'
import 'aos/dist/aos.css'
AOS.init({
  duration: 600,
  once: true,
  offset: 80,
})

const app = createApp(App)
const pinia = createPinia()
app.use(pinia)
app.use(router)
app.use(ElementPlus)

// 主题必须在 mount 前初始化，避免闪烁
const appStore = useAppStore(pinia)
appStore.initTheme()

app.mount('#app')
