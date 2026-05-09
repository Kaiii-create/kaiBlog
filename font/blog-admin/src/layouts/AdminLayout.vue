<template>
  <div class="admin-layout">
    <AdminSidebar />
    <div class="admin-main">
      <AdminHeader />

      <!-- Tab 标签栏 -->
      <div class="admin-content-card">
        <div class="admin-tabs-bar">
          <div class="tabs-scroll">
            <div v-for="tab in tabsStore.tabList" :key="tab.path"
              class="tab-item" :class="{ active: tabsStore.activePath === tab.path }"
              @click="switchTab(tab)"
              @contextmenu.prevent="openContextMenu($event, tab)">
              <span>{{ tab.title }}</span>
              <el-icon v-if="tab.closable" class="tab-close" @click.stop="tabsStore.removeTab(tab.path)"><Close /></el-icon>
            </div>
          </div>
        </div>

        <div class="admin-content">
          <router-view v-slot="{ Component, route }">
            <transition name="fade" mode="out-in">
              <keep-alive :max="10">
                <component :is="Component" :key="route.path" />
              </keep-alive>
            </transition>
          </router-view>
        </div>
      </div>
    </div>

    <!-- 右键菜单 -->
    <teleport to="body">
      <div v-if="contextMenu.visible" class="tab-context-menu"
        :style="{ left: contextMenu.x + 'px', top: contextMenu.y + 'px' }"
        @click.stop @contextmenu.prevent>
        <div class="ctx-item" @click="closeCurrent">关闭当前</div>
        <div class="ctx-item" @click="closeOthers">关闭全部</div>
        <div class="ctx-item" @click="closeRight">关闭右侧</div>
      </div>
      <div v-if="contextMenu.visible" class="ctx-overlay" @click="closeContextMenu" />
    </teleport>
  </div>
</template>

<script setup>
import { ref, reactive, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AdminSidebar from './AdminSidebar.vue'
import AdminHeader from './AdminHeader.vue'
import { useThemeStore } from '../stores/theme'
import { useTabsStore } from '../stores/tabs'
import { Close } from '@element-plus/icons-vue'

const route = useRoute()
const router = useRouter()
const themeStore = useThemeStore()
const tabsStore = useTabsStore()

const contextMenu = reactive({ visible: false, x: 0, y: 0, tab: null })

watch(() => route.path, (path) => {
  if (path.startsWith('/admin/')) tabsStore.addTab(route)
}, { immediate: true })

function switchTab(tab) {
  if (tabsStore.activePath !== tab.path) {
    tabsStore.setActive(tab.path); router.push(tab.path)
  }
}

// ---- 右键菜单 ----
function openContextMenu(e, tab) {
  contextMenu.x = e.clientX
  contextMenu.y = e.clientY
  contextMenu.tab = tab
  contextMenu.visible = true
}

function closeContextMenu() {
  contextMenu.visible = false
  contextMenu.tab = null
}

function closeCurrent() {
  if (contextMenu.tab?.closable !== false) {
    tabsStore.removeTab(contextMenu.tab.path)
  }
  closeContextMenu()
}

function closeOthers() {
  tabsStore.closeOther(contextMenu.tab.path)
  closeContextMenu()
}

function closeRight() {
  const tabs = tabsStore.tabList
  const idx = tabs.findIndex(t => t.path === contextMenu.tab.path)
  if (idx >= 0) {
    const toRemove = tabs.slice(idx + 1).filter(t => t.closable)
    toRemove.forEach(t => tabsStore.removeTab(t.path))
    // 如果关闭了当前激活的tab
    if (!tabsStore.tabList.find(t => t.path === tabsStore.activePath)) {
      tabsStore.setActive(contextMenu.tab.path)
      router.push(contextMenu.tab.path)
    }
  }
  closeContextMenu()
}
</script>

<style scoped lang="scss">
.admin-layout {
  display: flex;
  gap: 12px;
  padding: 12px;
  min-height: 100vh;
  background: var(--color-bg);
  transition: background 0.25s;
}

.admin-main {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 12px;
  min-width: 0;
  margin-left: calc(var(--sidebar-actual-width) + 12px);
  transition: margin-left 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.admin-content-card {
  flex: 1;
  background: var(--color-bg-card);
  border-radius: 14px;
  box-shadow: var(--shadow-card);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.admin-tabs-bar {
  height: 38px;
  display: flex;
  align-items: center;
  padding: 0 16px;
  border-bottom: 1px solid var(--color-border-light);
  overflow-x: auto;
  flex-shrink: 0;
  &::-webkit-scrollbar { height: 0; }
}
.tabs-scroll { display: flex; gap: 0; white-space: nowrap; }
.tab-item {
  display: flex; align-items: center; gap: 6px;
  padding: 8px 16px; font-size: 13px; cursor: pointer;
  color: var(--color-text-muted);
  border-bottom: 2px solid transparent;
  transition: all 0.15s;
  user-select: none;
  &:hover { color: var(--color-text); }
  &.active { color: var(--color-primary); font-weight: 500; border-bottom-color: var(--color-primary); }
}
.tab-close {
  font-size: 12px; border-radius: 3px; padding: 2px; opacity: 0.4;
  &:hover { opacity: 1; color: var(--color-danger); background: rgba(239,68,68,0.08); }
}

.admin-content {
  flex: 1;
  padding: 20px;
  overflow-y: auto;
}

// ---- 右键菜单 ----
.tab-context-menu {
  position: fixed;
  z-index: 9999;
  background: var(--color-bg-card);
  border: 1px solid var(--color-border);
  border-radius: 10px;
  box-shadow: var(--shadow-dropdown);
  padding: 4px;
  min-width: 140px;
}
.ctx-item {
  padding: 8px 14px;
  font-size: 13px;
  border-radius: 6px;
  cursor: pointer;
  color: var(--color-text-secondary);
  transition: all 0.1s;
  &:hover { background: var(--color-primary-bg); color: var(--color-primary); }
}
.ctx-overlay {
  position: fixed;
  inset: 0;
  z-index: 9998;
}

.fade-enter-active, .fade-leave-active { transition: opacity 0.12s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

@media (max-width: 768px) {
  .admin-layout { padding: 8px; gap: 8px; }
  .admin-main { margin-left: 0; }
}
</style>
