<template>
  <header class="site-header">
    <div class="page-container header-inner">
      <router-link to="/" class="brand">
        <span class="brand-badge">K</span>
        <div class="brand-copy">
          <strong>{{ siteName }}</strong>
          <span>{{ siteTagline }}</span>
        </div>
      </router-link>

      <nav class="desktop-nav">
        <router-link to="/" class="nav-link" :class="{ active: $route.path === '/' }">首页</router-link>
        <router-link to="/articles" class="nav-link" :class="{ active: $route.path.startsWith('/articles') }">文章</router-link>
        <router-link to="/tutorials" class="nav-link" :class="{ active: $route.path.startsWith('/tutorials') }">教程</router-link>
        <router-link to="/search" class="nav-link" :class="{ active: $route.path.startsWith('/search') }">搜索</router-link>
      </nav>

      <div class="header-actions">
        <button class="theme-toggle" type="button" @click="appStore.toggleDarkMode()">
          <span class="theme-icon">{{ appStore.darkMode ? '☀' : '☾' }}</span>
          <span class="theme-label">{{ appStore.darkMode ? '浅色' : '深色' }}</span>
        </button>

        <template v-if="userStore.isLoggedIn">
          <el-dropdown trigger="click">
            <button class="user-entry" type="button">
              <el-avatar :size="30" :icon="UserFilled" />
              <span>{{ userStore.nickname || '用户' }}</span>
            </button>
            <template #dropdown>
              <el-dropdown-menu>
                <el-dropdown-item @click="$router.push('/member/profile')">个人资料</el-dropdown-item>
                <el-dropdown-item @click="$router.push('/member/favorites')">我的收藏</el-dropdown-item>
                <el-dropdown-item @click="$router.push('/member/comments')">我的评论</el-dropdown-item>
                <el-dropdown-item divided @click="handleLogout">退出登录</el-dropdown-item>
              </el-dropdown-menu>
            </template>
          </el-dropdown>
        </template>
        <template v-else>
          <el-button class="ghost-btn" @click="$router.push('/login')">登录</el-button>
          <el-button class="primary-btn" @click="$router.push('/register')">注册</el-button>
        </template>

        <button class="menu-toggle" type="button" @click="mobileMenuOpen = !mobileMenuOpen">
          <span></span>
          <span></span>
          <span></span>
        </button>
      </div>
    </div>

    <transition name="mobile-fade">
      <div v-if="mobileMenuOpen" class="mobile-mask" @click="mobileMenuOpen = false"></div>
    </transition>

    <transition name="mobile-slide">
      <aside v-if="mobileMenuOpen" class="mobile-panel">
        <div class="mobile-panel-head">
          <div>
            <strong>{{ siteName }}</strong>
            <p>{{ siteTagline }}</p>
          </div>
          <button type="button" class="mobile-close" @click="mobileMenuOpen = false">×</button>
        </div>

        <nav class="mobile-nav">
          <router-link to="/" class="mobile-link" @click="closeMobileMenu">首页</router-link>
          <router-link to="/articles" class="mobile-link" @click="closeMobileMenu">文章</router-link>
          <router-link to="/tutorials" class="mobile-link" @click="closeMobileMenu">教程</router-link>
          <router-link to="/search" class="mobile-link" @click="closeMobileMenu">搜索</router-link>
        </nav>

        <div class="mobile-actions">
          <button class="theme-toggle mobile-theme" type="button" @click="appStore.toggleDarkMode()">
            <span class="theme-icon">{{ appStore.darkMode ? '☀' : '☾' }}</span>
            <span class="theme-label">{{ appStore.darkMode ? '切换到浅色模式' : '切换到深色模式' }}</span>
          </button>

          <template v-if="userStore.isLoggedIn">
            <router-link to="/member/profile" class="mobile-action" @click="closeMobileMenu">个人资料</router-link>
            <router-link to="/member/favorites" class="mobile-action" @click="closeMobileMenu">我的收藏</router-link>
            <router-link to="/member/comments" class="mobile-action" @click="closeMobileMenu">我的评论</router-link>
            <button class="mobile-action danger" type="button" @click="handleLogout">退出登录</button>
          </template>
          <template v-else>
            <el-button class="ghost-btn full-width" @click="$router.push('/login'); closeMobileMenu()">登录</el-button>
            <el-button class="primary-btn full-width" @click="$router.push('/register'); closeMobileMenu()">注册</el-button>
          </template>
        </div>
      </aside>
    </transition>
  </header>
</template>

<script setup>
import { computed, ref } from 'vue'
import { UserFilled } from '@element-plus/icons-vue'
import { useRouter } from 'vue-router'
import { useAppStore } from '../stores/app'
import { useUserStore } from '../stores/user'

const router = useRouter()
const appStore = useAppStore()
const userStore = useUserStore()
const mobileMenuOpen = ref(false)

const siteName = computed(() => appStore.site.name || 'Kaiii 博客')
const siteTagline = computed(() => appStore.site.description || '记录开发、写作与独立表达。')

function closeMobileMenu() {
  mobileMenuOpen.value = false
}

function handleLogout() {
  userStore.logout()
  closeMobileMenu()
  router.push('/')
}
</script>

<style scoped lang="scss">
.site-header {
  position: sticky;
  top: 0;
  z-index: 120;
  backdrop-filter: blur(18px);
  -webkit-backdrop-filter: blur(18px);
  background: color-mix(in srgb, var(--color-bg) 82%, transparent);
  border-bottom: 1px solid var(--color-border);
}

.header-inner {
  min-height: 78px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 24px;
}

.brand {
  display: inline-flex;
  align-items: center;
  gap: 12px;
  color: inherit;
}

.brand-badge {
  width: 42px;
  height: 42px;
  border-radius: 14px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-weight: 800;
  background: linear-gradient(135deg, #0f82ff, #30cfd0);
  box-shadow: 0 14px 30px rgba(15, 130, 255, 0.24);
}

.brand-copy {
  display: flex;
  flex-direction: column;
  gap: 2px;
  strong {
    font-size: 1.02rem;
    line-height: 1.1;
  }
  span {
    font-size: 0.76rem;
    color: var(--color-text-muted);
  }
}

.desktop-nav {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 6px;
  border-radius: 999px;
  background: var(--color-bg-card);
  border: 1px solid var(--color-border);
}

.nav-link {
  padding: 10px 16px;
  border-radius: 999px;
  color: var(--color-text-secondary);
  font-size: 0.92rem;
  transition: 0.25s ease;
  &.active,
  &:hover {
    color: var(--color-text);
    background: var(--color-bg-elevated);
  }
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 10px;
}

.theme-toggle,
.user-entry,
.menu-toggle,
.mobile-close,
.mobile-action {
  border: 1px solid var(--color-border);
  background: var(--color-bg-card);
  color: var(--color-text);
}

.theme-toggle {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  border-radius: 999px;
  padding: 9px 14px;
}

.theme-icon {
  font-size: 1rem;
}

.theme-label {
  font-size: 0.88rem;
}

.user-entry {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  padding: 6px 12px 6px 6px;
  border-radius: 999px;
}

.ghost-btn {
  background: transparent;
  border-color: var(--color-border);
  color: var(--color-text);
}

.primary-btn {
  background: linear-gradient(135deg, #0f82ff, #356dff);
  border: none;
  color: #fff;
}

.menu-toggle {
  width: 42px;
  height: 42px;
  border-radius: 12px;
  display: none;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 4px;
  span {
    width: 18px;
    height: 2px;
    background: currentColor;
    border-radius: 999px;
  }
}

.mobile-mask {
  position: fixed;
  inset: 0;
  background: rgba(5, 10, 18, 0.48);
}

.mobile-panel {
  position: fixed;
  right: 0;
  top: 0;
  width: min(340px, 85vw);
  height: 100vh;
  padding: 22px;
  background: var(--color-bg-card);
  border-left: 1px solid var(--color-border);
  box-shadow: -20px 0 60px rgba(0, 0, 0, 0.16);
}

.mobile-panel-head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 28px;
  p {
    margin-top: 6px;
    color: var(--color-text-muted);
    font-size: 0.86rem;
  }
}

.mobile-close {
  width: 38px;
  height: 38px;
  border-radius: 12px;
  font-size: 1.5rem;
}

.mobile-nav,
.mobile-actions {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.mobile-link,
.mobile-action {
  border-radius: 14px;
  padding: 13px 14px;
  color: var(--color-text);
}

.mobile-theme {
  justify-content: center;
  margin-bottom: 10px;
}

.danger {
  color: #e05b65;
}

.full-width {
  width: 100%;
}

.mobile-fade-enter-active,
.mobile-fade-leave-active,
.mobile-slide-enter-active,
.mobile-slide-leave-active {
  transition: 0.24s ease;
}

.mobile-fade-enter-from,
.mobile-fade-leave-to {
  opacity: 0;
}

.mobile-slide-enter-from,
.mobile-slide-leave-to {
  transform: translateX(24px);
  opacity: 0;
}

@media (max-width: 980px) {
  .desktop-nav,
  .header-actions :deep(.el-dropdown),
  .header-actions .ghost-btn,
  .header-actions .primary-btn {
    display: none;
  }

  .menu-toggle {
    display: inline-flex;
  }

  .theme-label {
    display: none;
  }
}
</style>
