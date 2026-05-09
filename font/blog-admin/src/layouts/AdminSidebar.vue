<template>
  <aside class="admin-sidebar">
    <div class="sidebar-header">
      <router-link to="/admin/dashboard" class="sidebar-logo">
        <span class="logo-icon">K</span>
        <span class="logo-text" v-show="!themeStore.sidebarCollapsed">Kaiii</span>
      </router-link>
      <div class="header-actions" v-show="!themeStore.sidebarCollapsed">
        <router-link to="/" class="header-home" target="_blank" title="返回首页">
          <el-icon :size="16"><HomeFilled /></el-icon>
        </router-link>
        <button class="collapse-btn" @click="themeStore.toggleSidebar()">
          <el-icon :size="14"><Fold /></el-icon>
        </button>
      </div>
      <button v-show="themeStore.sidebarCollapsed" class="collapse-btn expand-btn" @click="themeStore.toggleSidebar()">
        <el-icon :size="16"><Expand /></el-icon>
      </button>
    </div>

    <div class="sidebar-menu-wrap">
      <template v-for="menu in dynamicMenus" :key="menu.id">
        <!-- 有子菜单：手风琴组 -->
        <div class="nav-group" v-if="menu.children?.length">
          <div class="nav-group-title" :class="{ open: openGroup === menu.slug }" @click="toggleGroup(menu.slug)">
            <el-icon :size="20" class="group-icon"><component :is="getIcon(menu.icon)" /></el-icon>
            <span class="group-label" v-show="!themeStore.sidebarCollapsed">{{ menu.name }}</span>
            <el-icon :size="16" class="group-arrow" v-show="!themeStore.sidebarCollapsed"><ArrowRight /></el-icon>
          </div>
          <div class="nav-children" v-show="openGroup === menu.slug && !themeStore.sidebarCollapsed">
            <router-link v-for="child in menu.children" :key="child.id" :to="child.route_path"
              class="nav-child-item" :class="{ active: $route.path === child.route_path }">
              <span class="child-badge" />
              <span class="child-label">{{ child.name }}</span>
            </router-link>
          </div>
        </div>
        <!-- 无子菜单：直链 -->
        <div class="nav-group" v-else>
          <router-link :to="menu.route_path"
            class="nav-group-title nav-link"
            :class="{ active: $route.path === menu.route_path }">
            <el-icon :size="20" class="group-icon"><component :is="getIcon(menu.icon)" /></el-icon>
            <span class="group-label" v-show="!themeStore.sidebarCollapsed">{{ menu.name }}</span>
          </router-link>
        </div>
      </template>
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

// 初始化 + 路由变化：展开当前路由所属组
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
  left: 12px;
  top: 12px;
  bottom: 12px;
  width: var(--sidebar-actual-width);
  background: var(--sidebar-bg);
  border-radius: 14px;
  box-shadow: var(--shadow-sidebar);
  display: flex;
  flex-direction: column;
  z-index: 100;
  overflow: hidden;
  transition: width 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.sidebar-header {
  height: var(--admin-header-height);
  display: flex; align-items: center; justify-content: space-between;
  padding: 0 12px 0 16px;
  border-bottom: 1px solid var(--sidebar-border);
  flex-shrink: 0;
}
.sidebar-logo {
  display: flex; align-items: center; gap: 8px; text-decoration: none; overflow: hidden;
  .logo-icon {
    width: 28px; height: 28px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    background: var(--gradient-primary); border-radius: 7px;
    color: #fff; font-weight: 800; font-size: 14px;
  }
  .logo-text { font-size: 16px; font-weight: 700; color: var(--sidebar-text-active); white-space: nowrap; }
}
.header-actions { display: flex; align-items: center; gap: 2px; }
.header-home {
  width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;
  border-radius: 6px; color: var(--sidebar-text); text-decoration: none; transition: 0.15s;
  &:hover { color: var(--sidebar-text-active); background: var(--sidebar-hover); }
}
.collapse-btn {
  width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;
  border: none; background: transparent; cursor: pointer; border-radius: 6px;
  color: var(--sidebar-text); transition: 0.15s;
  &:hover { color: var(--sidebar-text-active); background: var(--sidebar-hover); }
}
.expand-btn {
  position: absolute; bottom: 12px; left: 50%; transform: translateX(-50%);
  width: 36px; height: 36px; background: var(--sidebar-bg);
  border-radius: 10px; box-shadow: var(--shadow-sidebar);
}

// ---- 菜单滚动区 ----
.sidebar-menu-wrap {
  flex: 1; overflow-y: auto; padding: 10px 10px 14px;
}

// ---- 手风琴 ----
.nav-group { margin-bottom: 2px; }

.nav-group-title {
  display: flex; align-items: center; gap: 10px;
  padding: 10px 12px;
  border-radius: 10px;
  cursor: pointer;
  color: var(--sidebar-text);
  text-decoration: none;
  user-select: none;
  transition: all 0.18s ease;
  position: relative;
  &:hover {
    color: var(--sidebar-text-active);
    background: var(--sidebar-hover);
  }
  &.open {
    color: var(--sidebar-text-active);
    background: rgba(79, 110, 247, 0.08);
    .group-arrow { transform: rotate(90deg); }
  }
  &.active {
    color: #fff;
    background: var(--color-primary);
    .group-icon { color: #fff; }
  }
}
.group-icon {
  flex-shrink: 0;
  width: 22px; height: 22px;
  display: flex; align-items: center; justify-content: center;
}
.group-label {
  flex: 1;
  font-size: 14px;
  font-weight: 500;
  line-height: 1;
}
.group-arrow {
  flex-shrink: 0;
  transition: transform 0.25s ease;
  opacity: 0.5;
}

// ---- 子菜单 ----
.nav-children {
  padding: 8px 0 8px 20px;
}
.nav-child-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  margin-bottom: 4px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 400;
  color: var(--sidebar-text);
  text-decoration: none;
  transition: all 0.15s;
  position: relative;
  &:hover {
    color: var(--sidebar-text-active);
    background: var(--sidebar-hover);
  }
  &.active {
    color: var(--color-primary);
    background: rgba(79, 110, 247, 0.1);
    font-weight: 600;
    .child-badge {
      background: var(--color-primary);
      width: 6px;
      height: 6px;
    }
  }
}
.child-badge {
  width: 4px;
  height: 4px;
  border-radius: 50%;
  background: var(--sidebar-text);
  opacity: 0.4;
  flex-shrink: 0;
  transition: all 0.15s;
}
.child-label {
  line-height: 1;
}
</style>
