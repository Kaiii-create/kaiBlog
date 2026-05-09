<template>
  <aside class="admin-sidebar">
    <div class="sidebar-header">
      <router-link to="/admin/dashboard" class="sidebar-logo">
        <div class="logo-icon">K</div>
        <transition name="fade-text">
          <span class="logo-text" v-show="!themeStore.sidebarCollapsed">Kaiii</span>
        </transition>
      </router-link>
      <div class="header-actions" v-show="!themeStore.sidebarCollapsed">
        <a href="/" class="action-btn" target="_blank" title="返回首页">
          <el-icon :size="15"><HomeFilled /></el-icon>
        </a>
        <button class="action-btn" @click="themeStore.toggleSidebar()" title="折叠">
          <el-icon :size="15"><Fold /></el-icon>
        </button>
      </div>
      <button v-show="themeStore.sidebarCollapsed" class="action-btn expand-btn" @click="themeStore.toggleSidebar()" title="展开">
        <el-icon :size="16"><Expand /></el-icon>
      </button>
    </div>

    <div class="sidebar-menu-wrap">
      <template v-for="menu in dynamicMenus" :key="menu.id">
        <!-- 有子菜单 -->
        <div class="nav-group" v-if="menu.children?.length">
          <div class="nav-group-title" :class="{ open: openGroup === menu.slug }" @click="toggleGroup(menu.slug)">
            <div class="menu-icon-wrap">
              <el-icon :size="18"><component :is="getIcon(menu.icon)" /></el-icon>
            </div>
            <span class="menu-label" v-show="!themeStore.sidebarCollapsed">{{ menu.name }}</span>
            <el-icon :size="14" class="menu-arrow" v-show="!themeStore.sidebarCollapsed"><ArrowRight /></el-icon>
          </div>
          <transition name="slide-down">
            <div class="nav-children" v-show="openGroup === menu.slug && !themeStore.sidebarCollapsed">
              <router-link v-for="child in menu.children" :key="child.id" :to="child.route_path"
                class="nav-child-item" :class="{ active: $route.path === child.route_path }">
                <span class="child-dot" />
                <span class="child-label">{{ child.name }}</span>
              </router-link>
            </div>
          </transition>
        </div>
        <!-- 无子菜单 -->
        <div class="nav-group" v-else>
          <router-link :to="menu.route_path"
            class="nav-group-title nav-link"
            :class="{ active: $route.path === menu.route_path }">
            <div class="menu-icon-wrap">
              <el-icon :size="18"><component :is="getIcon(menu.icon)" /></el-icon>
            </div>
            <span class="menu-label" v-show="!themeStore.sidebarCollapsed">{{ menu.name }}</span>
          </router-link>
        </div>
      </template>
    </div>

    <!-- 底部版本信息 -->
    <div class="sidebar-footer" v-show="!themeStore.sidebarCollapsed">
      <span class="version-text">v1.0.0</span>
    </div>
  </aside>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useAdminStore } from '../stores/admin'
import { useThemeStore } from '../stores/theme'
import * as ElIcons from '@element-plus/icons-vue'
import { HomeFilled, Fold, Expand, ArrowRight } from '@element-plus/icons-vue'

const route = useRoute()
const adminStore = useAdminStore()
const themeStore = useThemeStore()

const dynamicMenus = computed(() => adminStore.menus)

const openGroup = ref('')

function autoOpenGroup() {
  if (!adminStore.menus.length) return
  const active = adminStore.menus.find(m =>
    m.children?.some(c => c.route_path === route.path)
  )
  if (active) openGroup.value = active.slug
}

watch(() => [adminStore.menus, route.path], () => { autoOpenGroup() }, { immediate: true })

function toggleGroup(slug) {
  openGroup.value = openGroup.value === slug ? '' : slug
}

function getIcon(name) {
  if (!name) return 'Menu'
  return ElIcons[name] || ElIcons['Menu']
}
</script>

<style scoped lang="scss">
.admin-sidebar {
  position: fixed;
  left: 0;
  top: 0;
  bottom: 0;
  width: var(--sidebar-actual-width);
  background: var(--sidebar-bg);
  border-right: 1px solid var(--sidebar-border);
  display: flex;
  flex-direction: column;
  z-index: 100;
  overflow: hidden;
  transition: width 0.28s cubic-bezier(0.4, 0, 0.2, 1);
}

// ---- Header ----
.sidebar-header {
  height: 56px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 16px;
  border-bottom: 1px solid var(--sidebar-border);
  flex-shrink: 0;
}
.sidebar-logo {
  display: flex;
  align-items: center;
  gap: 10px;
  text-decoration: none;
  overflow: hidden;
}
.logo-icon {
  width: 32px;
  height: 32px;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #4f6ef7, #6366f1);
  border-radius: 8px;
  color: #fff;
  font-weight: 800;
  font-size: 15px;
}
.logo-text {
  font-size: 17px;
  font-weight: 700;
  color: var(--sidebar-text-active);
  white-space: nowrap;
}
.header-actions {
  display: flex;
  align-items: center;
  gap: 2px;
}
.action-btn {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: none;
  background: transparent;
  cursor: pointer;
  border-radius: 8px;
  color: var(--sidebar-text);
  text-decoration: none;
  transition: all 0.15s;
  &:hover {
    color: var(--sidebar-text-active);
    background: var(--sidebar-hover);
  }
}
.expand-btn {
  position: absolute;
  bottom: 60px;
  left: 50%;
  transform: translateX(-50%);
  width: 36px;
  height: 36px;
  background: var(--color-primary);
  color: #fff;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(79, 110, 247, 0.3);
  &:hover {
    background: var(--color-primary-light);
    color: #fff;
  }
}

// ---- Menu ----
.sidebar-menu-wrap {
  flex: 1;
  overflow-y: auto;
  padding: 8px;
  &::-webkit-scrollbar { width: 3px; }
}

.nav-group { margin-bottom: 2px; }

.nav-group-title {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  border-radius: 8px;
  cursor: pointer;
  color: var(--sidebar-text);
  text-decoration: none;
  user-select: none;
  transition: all 0.2s ease;
  margin: 1px 0;
  &:hover {
    color: var(--sidebar-text-active);
    background: var(--sidebar-hover);
  }
  &.open {
    color: var(--color-primary);
    background: rgba(79, 110, 247, 0.06);
    .menu-arrow { transform: rotate(90deg); opacity: 1; }
  }
  &.active {
    color: #fff;
    background: var(--color-primary);
    .menu-icon-wrap {
      background: rgba(255, 255, 255, 0.2);
      color: #fff;
    }
    .menu-label { color: #fff; font-weight: 600; }
  }
}

.menu-icon-wrap {
  flex-shrink: 0;
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  transition: all 0.2s;
}

.menu-label {
  flex: 1;
  font-size: 13.5px;
  font-weight: 500;
  white-space: nowrap;
}

.menu-arrow {
  flex-shrink: 0;
  transition: transform 0.25s ease, opacity 0.2s;
  opacity: 0.3;
}

// ---- 子菜单 ----
.nav-children {
  padding: 4px 0 4px 20px;
  margin-left: 14px;
  border-left: 1.5px solid var(--sidebar-border);
}

.nav-child-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 12px;
  margin-bottom: 1px;
  border-radius: 6px;
  font-size: 13px;
  color: var(--sidebar-text);
  text-decoration: none;
  transition: all 0.18s ease;
  &:hover {
    color: var(--sidebar-text-active);
    background: var(--sidebar-hover);
  }
  &.active {
    color: var(--color-primary);
    background: rgba(79, 110, 247, 0.08);
    font-weight: 600;
    .child-dot {
      background: var(--color-primary);
      transform: scale(1.3);
    }
  }
}

.child-dot {
  width: 5px;
  height: 5px;
  border-radius: 50%;
  background: var(--sidebar-text);
  opacity: 0.4;
  transition: all 0.2s;
  flex-shrink: 0;
}

.child-label {
  white-space: nowrap;
}

// ---- Footer ----
.sidebar-footer {
  padding: 12px 16px;
  border-top: 1px solid var(--sidebar-border);
  text-align: center;
}
.version-text {
  font-size: 11px;
  color: var(--sidebar-text);
  opacity: 0.5;
}

// ---- 过渡动画 ----
.fade-text-enter-active, .fade-text-leave-active { transition: opacity 0.2s; }
.fade-text-enter-from, .fade-text-leave-to { opacity: 0; }

.slide-down-enter-active, .slide-down-leave-active {
  transition: all 0.2s ease;
  overflow: hidden;
}
.slide-down-enter-from, .slide-down-leave-to {
  opacity: 0;
  max-height: 0;
  padding-top: 0;
  padding-bottom: 0;
}
.slide-down-enter-to, .slide-down-leave-from {
  max-height: 300px;
}
</style>
