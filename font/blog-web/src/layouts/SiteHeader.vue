<template>
  <header class="site-header" :class="{ 'menu-open': mobileMenuOpen }">
    <div class="header-inner page-container">
      <router-link to="/" class="logo">
        <span class="logo-icon">K</span>
        <span class="logo-text gradient-text">Kaiii 博客</span>
      </router-link>

      <nav class="nav-menu desktop-nav">
        <router-link to="/" class="nav-link" :class="{ active: $route.path === '/' }">
          <el-icon><House /></el-icon> 首页
        </router-link>
        <router-link to="/articles" class="nav-link" active-class="active">
          <el-icon><Notebook /></el-icon> 文章
        </router-link>
        <router-link to="/tutorials" class="nav-link" active-class="active">
          <el-icon><Reading /></el-icon> 教程
        </router-link>
      </nav>

      <div class="header-actions">
        <div class="search-bar" @click="goSearch">
          <el-icon><Search /></el-icon>
          <span class="search-placeholder">搜索</span>
        </div>

        <button
          ref="themeBtnRef"
          class="theme-toggle"
          @click="handleToggleTheme"
          :title="appStore.darkMode ? '切换日间模式' : '切换夜间模式'"
        >
          <el-icon v-if="appStore.darkMode"><Sunny /></el-icon>
          <el-icon v-else><Moon /></el-icon>
        </button>

        <template v-if="userStore.isLoggedIn">
          <el-dropdown trigger="click" class="desktop-user">
            <span class="user-avatar">
              <el-avatar :size="32" :icon="UserFilled" />
              <span class="username">{{ userStore.nickname || '用户' }}</span>
            </span>
            <template #dropdown>
              <el-dropdown-menu>
                <el-dropdown-item @click="$router.push('/member/profile')">
                  <el-icon><User /></el-icon> 会员中心
                </el-dropdown-item>
                <el-dropdown-item @click="$router.push('/member/favorites')">
                  <el-icon><Star /></el-icon> 我的收藏
                </el-dropdown-item>
                <el-dropdown-item divided @click="handleLogout">
                  <el-icon><SwitchButton /></el-icon> 退出登录
                </el-dropdown-item>
              </el-dropdown-menu>
            </template>
          </el-dropdown>
        </template>
        <template v-else>
          <el-button class="nav-btn login-btn" @click="$router.push('/login')">登录</el-button>
          <el-button class="nav-btn gradient-btn register-btn" @click="$router.push('/register')">注册</el-button>
        </template>

        <button class="hamburger" @click="toggleMobileMenu" :class="{ active: mobileMenuOpen }">
          <span></span>
          <span></span>
          <span></span>
        </button>
      </div>
    </div>

    <div class="mobile-overlay" v-if="mobileMenuOpen" @click="closeMobileMenu"></div>

    <div class="mobile-menu" :class="{ open: mobileMenuOpen }">
      <div class="mobile-menu-header">
        <span class="gradient-text">导航</span>
        <button class="close-btn" @click="closeMobileMenu">
          <el-icon><Close /></el-icon>
        </button>
      </div>

      <nav class="mobile-nav">
        <router-link to="/" class="mobile-nav-link" @click="closeMobileMenu">
          <el-icon><House /></el-icon> 首页
        </router-link>
        <router-link to="/articles" class="mobile-nav-link" @click="closeMobileMenu">
          <el-icon><Notebook /></el-icon> 文章
        </router-link>
        <router-link to="/tutorials" class="mobile-nav-link" @click="closeMobileMenu">
          <el-icon><Reading /></el-icon> 教程
        </router-link>
        <router-link to="/search" class="mobile-nav-link" @click="closeMobileMenu">
          <el-icon><Search /></el-icon> 搜索
        </router-link>
      </nav>

      <div class="mobile-user-section">
        <template v-if="userStore.isLoggedIn">
          <div class="mobile-user-info">
            <el-avatar :size="40" :icon="UserFilled" />
            <div>
              <p class="mobile-nickname">{{ userStore.nickname || '用户' }}</p>
              <p class="mobile-email">{{ userStore.userInfo?.email || '' }}</p>
            </div>
          </div>
          <router-link to="/member/profile" class="mobile-menu-link" @click="closeMobileMenu">
            <el-icon><User /></el-icon> 会员中心
          </router-link>
          <router-link to="/member/favorites" class="mobile-menu-link" @click="closeMobileMenu">
            <el-icon><Star /></el-icon> 我的收藏
          </router-link>
          <router-link to="/member/comments" class="mobile-menu-link" @click="closeMobileMenu">
            <el-icon><ChatDotSquare /></el-icon> 我的评论
          </router-link>
          <a class="mobile-menu-link logout-link" @click="handleLogout">
            <el-icon><SwitchButton /></el-icon> 退出登录
          </a>
        </template>
        <template v-else>
          <div class="mobile-auth-btns">
            <el-button class="gradient-btn" @click="$router.push('/login'); closeMobileMenu()" style="flex: 1">登录</el-button>
            <el-button class="nav-btn" @click="$router.push('/register'); closeMobileMenu()" style="flex: 1">注册</el-button>
          </div>
        </template>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, nextTick } from 'vue'
import {
  House, Notebook, Reading, Search,
  UserFilled, User, Star, SwitchButton,
  Close, Sunny, Moon, ChatDotSquare
} from '@element-plus/icons-vue'
import { useUserStore } from '../stores/user'
import { useAppStore } from '../stores/app'
import { useRouter } from 'vue-router'

const userStore = useUserStore()
const appStore = useAppStore()
const router = useRouter()
const mobileMenuOpen = ref(false)
const themeBtnRef = ref(null)

function goSearch() {
  router.push('/search')
}

function handleLogout() {
  userStore.logout()
  closeMobileMenu()
  router.push('/')
}

async function handleToggleTheme() {
  const btn = themeBtnRef.value
  if (!btn) {
    appStore.toggleDarkMode()
    return
  }

  const goingDark = !appStore.darkMode
  const rect = btn.getBoundingClientRect()
  const cx = rect.left + rect.width / 2
  const cy = rect.top + rect.height / 2
  const maxR = Math.hypot(
    Math.max(cx, window.innerWidth - cx),
    Math.max(cy, window.innerHeight - cy)
  )

  const canvas = document.createElement('canvas')
  canvas.width = Math.ceil(window.innerWidth * window.devicePixelRatio)
  canvas.height = Math.ceil(window.innerHeight * window.devicePixelRatio)
  canvas.style.cssText = 'position:fixed;inset:0;width:100vw;height:100vh;z-index:9999;pointer-events:none;'
  document.body.appendChild(canvas)

  const ctx = canvas.getContext('2d')
  if (!ctx) {
    canvas.remove()
    appStore.toggleDarkMode()
    return
  }

  ctx.scale(window.devicePixelRatio, window.devicePixelRatio)

  const overlayColor = goingDark ? '#0b1124' : '#0b1124'
  const startRadius = goingDark ? maxR : 0
  const endRadius = goingDark ? 0 : maxR
  const duration = 520

  document.documentElement.classList.add('theme-switching')

  if (!goingDark) {
    appStore.toggleDarkMode()
    await nextTick()
  }

  await new Promise((resolve) => {
    const start = performance.now()

    const drawFrame = (radius) => {
      ctx.clearRect(0, 0, window.innerWidth, window.innerHeight)
      ctx.fillStyle = overlayColor
      ctx.fillRect(0, 0, window.innerWidth, window.innerHeight)
      ctx.globalCompositeOperation = 'destination-out'
      ctx.beginPath()
      ctx.arc(cx, cy, radius, 0, Math.PI * 2)
      ctx.fill()
      ctx.globalCompositeOperation = 'source-over'
    }

    const tick = (now) => {
      const progress = Math.min((now - start) / duration, 1)
      const eased = 1 - Math.pow(1 - progress, 3)
      const radius = startRadius + (endRadius - startRadius) * eased
      drawFrame(radius)

      if (progress < 1) {
        requestAnimationFrame(tick)
        return
      }

      resolve()
    }

    requestAnimationFrame(tick)
  })

  if (goingDark) {
    appStore.toggleDarkMode()
    await nextTick()
  }

  await new Promise((resolve) => {
    requestAnimationFrame(() => {
      requestAnimationFrame(resolve)
    })
  })

  document.documentElement.classList.remove('theme-switching')
  canvas.remove()
}

function toggleMobileMenu() {
  mobileMenuOpen.value = !mobileMenuOpen.value
  document.body.style.overflow = mobileMenuOpen.value ? 'hidden' : ''
}

function closeMobileMenu() {
  mobileMenuOpen.value = false
  document.body.style.overflow = ''
}
</script>

<style scoped lang="scss">
.site-header {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 100;
  background: var(--header-bg, rgba(15, 15, 26, 0.85));
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border-bottom: 1px solid var(--color-border);
}
.header-inner {
  display: flex;
  align-items: center;
  height: 70px;
  gap: 24px;
}
.logo {
  display: flex;
  align-items: center;
  gap: 8px;
  text-decoration: none;
  flex-shrink: 0;
}
.logo-icon {
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #3b82f6, #06b6d4);
  border-radius: 8px;
  color: #fff;
  font-weight: 800;
  font-size: 18px;
}
.logo-text {
  font-size: 18px;
  font-weight: 700;
}
.desktop-nav {
  display: flex;
  gap: 4px;
  flex: 1;
}
.nav-link {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  color: var(--color-text-secondary);
  text-decoration: none;
  border-radius: 8px;
  font-size: 14px;
  transition: all 0.3s;
  &:hover { color: var(--color-text); background: rgba(59,130,246,0.08); }
  &.active { color: var(--color-primary); background: rgba(59,130,246,0.12); }
}
.header-actions {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-shrink: 0;
}
.search-bar {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  background: var(--input-bg, rgba(255,255,255,0.04));
  border: 1px solid var(--color-border);
  border-radius: 20px;
  cursor: pointer;
  transition: all 0.3s;
  color: var(--color-text-muted);
  font-size: 13px;
  &:hover { border-color: var(--color-primary); color: var(--color-text-secondary); }
  .search-placeholder { display: inline; }
}

.theme-toggle {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  border: 1px solid var(--color-border);
  background: var(--input-bg);
  color: var(--color-text-secondary);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s;
  font-size: 18px;
  &:hover {
    border-color: var(--color-primary);
    color: var(--color-primary);
    box-shadow: var(--shadow-glow);
  }
}

.nav-btn {
  &.el-button--default {
    background: transparent;
    border-color: var(--color-border);
    color: var(--color-text);
    &:hover { border-color: var(--color-primary); color: var(--color-primary); }
  }
}
.login-btn { display: inline-flex; }
.register-btn { display: inline-flex; }

.user-avatar {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  padding: 4px 8px;
  border-radius: 8px;
  transition: background 0.3s;
  &:hover { background: rgba(59,130,246,0.08); }
  .username { font-size: 14px; color: var(--color-text); max-width: 80px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
}

.hamburger {
  display: none;
  width: 36px;
  height: 36px;
  background: transparent;
  border: 1px solid var(--color-border);
  border-radius: 8px;
  cursor: pointer;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 4px;
  transition: all 0.3s;
  &:hover { border-color: var(--color-primary); }
  span {
    display: block;
    width: 18px;
    height: 2px;
    background: var(--color-text);
    border-radius: 2px;
    transition: all 0.3s;
  }
  &.active span:nth-child(1) { transform: rotate(45deg) translate(4px, 4px); }
  &.active span:nth-child(2) { opacity: 0; }
  &.active span:nth-child(3) { transform: rotate(-45deg) translate(4px, -4px); }
}

.mobile-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 98;
}

.mobile-menu {
  display: none;
  position: fixed;
  top: 0;
  right: -320px;
  width: 300px;
  max-width: 80vw;
  height: 100vh;
  background: var(--color-bg);
  border-left: 1px solid var(--color-border);
  z-index: 99;
  flex-direction: column;
  padding: 20px;
  transition: right 0.35s cubic-bezier(0.4, 0, 0.2, 1);
  overflow-y: auto;
  &.open { right: 0; }

  .mobile-menu-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--color-border);
    margin-bottom: 20px;
    font-size: 18px;
    font-weight: 700;
  }
  .close-btn {
    width: 32px;
    height: 32px;
    background: transparent;
    border: 1px solid var(--color-border);
    border-radius: 8px;
    color: var(--color-text-secondary);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    &:hover { border-color: var(--color-primary); color: var(--color-primary); }
  }
}

.mobile-nav {
  display: flex;
  flex-direction: column;
  gap: 4px;
  margin-bottom: 24px;
}
.mobile-nav-link {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  border-radius: 10px;
  color: var(--color-text);
  text-decoration: none;
  font-size: 15px;
  transition: all 0.2s;
  &:hover { background: rgba(59,130,246,0.08); }
  &.router-link-active { color: var(--color-primary); background: rgba(59,130,246,0.12); }
  .el-icon { font-size: 18px; }
}

.mobile-user-section {
  padding-top: 16px;
  border-top: 1px solid var(--color-border);
}
.mobile-user-info {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 16px;
  .mobile-nickname { font-size: 15px; font-weight: 600; color: var(--color-text); }
  .mobile-email { font-size: 12px; color: var(--color-text-muted); }
}
.mobile-menu-link {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  border-radius: 10px;
  color: var(--color-text-secondary);
  text-decoration: none;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.2s;
  &:hover { background: rgba(59,130,246,0.06); color: var(--color-text); }
  .el-icon { font-size: 18px; }
}
.logout-link { color: var(--color-danger); }

.mobile-auth-btns {
  display: flex;
  gap: 12px;
}

@media (max-width: 900px) {
  .desktop-nav, .desktop-user, .login-btn, .register-btn { display: none; }
  .search-placeholder { display: none; }
  .hamburger { display: flex; }
  .mobile-overlay { display: block; }
  .mobile-menu { display: flex; }
}

@media (min-width: 901px) {
  .mobile-overlay, .mobile-menu { display: none !important; }
  .hamburger { display: none; }
}
</style>
