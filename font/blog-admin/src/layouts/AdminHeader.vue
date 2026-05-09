<template>
  <header class="admin-header">
    <div class="header-left">
      <el-breadcrumb separator="/">
        <el-breadcrumb-item :to="{ path: '/admin/dashboard' }">首页</el-breadcrumb-item>
        <el-breadcrumb-item v-if="breadcrumb.length > 1" v-for="(item, i) in breadcrumb.slice(1)" :key="i">{{ item }}</el-breadcrumb-item>
      </el-breadcrumb>
    </div>

    <div class="header-right">
      <button class="icon-btn" @click="searchVisible = true" title="搜索 (⌘K)">
        <el-icon :size="16"><Search /></el-icon>
      </button>

      <el-badge :value="unreadCount" :hidden="unreadCount === 0" class="notif-badge">
        <button class="icon-btn" @click="openMessageDrawer()" title="消息">
          <el-icon :size="17"><Bell /></el-icon>
        </button>
      </el-badge>

      <button class="icon-btn" @click="toggleFullscreen" :title="isFullscreen ? '退出全屏' : '全屏'">
        <el-icon :size="16"><FullScreen v-if="!isFullscreen" /><Close v-else /></el-icon>
      </button>

      <button class="icon-btn" @click="themeStore.toggleTheme()" :title="themeStore.isDark ? '亮色模式' : '暗色模式'">
        <el-icon :size="16"><Moon v-if="!themeStore.isDark" /><Sunny v-else /></el-icon>
      </button>

      <div class="header-divider" />

      <el-dropdown trigger="click">
        <span class="user-btn">
          <el-avatar :size="30" icon="UserFilled" style="background: linear-gradient(135deg, #4f6ef7, #6366f1);" />
          <span class="user-name">{{ adminStore.nickname || '管理员' }}</span>
          <el-icon :size="12" class="user-arrow"><ArrowDown /></el-icon>
        </span>
        <template #dropdown>
          <el-dropdown-menu>
            <el-dropdown-item @click="handleLogout"><el-icon><SwitchButton /></el-icon> 退出登录</el-dropdown-item>
          </el-dropdown-menu>
        </template>
      </el-dropdown>
    </div>

    <!-- 搜索弹窗 -->
    <el-dialog v-model="searchVisible" title="搜索菜单" width="480px" :close-on-click-modal="true" top="16vh" append-to-body>
      <el-input v-model="searchKeyword" placeholder="输入菜单名称..." size="large" clearable @keyup.enter="doSearch">
        <template #prefix><el-icon><Search /></el-icon></template>
      </el-input>
      <div class="search-results" v-if="searchKeyword">
        <div v-for="item in searchResults" :key="item.path" class="search-item" @click="goTo(item)">
          <el-icon :size="16"><component :is="item.icon" /></el-icon><span>{{ item.title }}</span>
        </div>
        <el-empty v-if="!searchResults.length" description="未找到匹配" :image-size="40" />
      </div>
    </el-dialog>

    <!-- 消息抽屉 -->
    <el-drawer v-model="messageDrawer" title="消息通知" size="380px" :z-index="2100">
      <div v-for="msg in messages" :key="msg.id" class="msg-item">
        <div class="msg-dot" :class="{ unread: !msg.read }" />
        <div class="msg-body">
          <p class="msg-text">{{ msg.content }}</p>
          <span class="msg-time">{{ msg.time }}</span>
        </div>
      </div>
      <el-empty v-if="!messages.length" description="暂无消息" :image-size="60" />
      <div class="drawer-footer">
        <el-button text type="primary" size="small" @click="messageDrawer = false; router.push('/admin/messages')">
          查看全部消息
        </el-button>
      </div>
    </el-drawer>
  </header>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAdminStore } from '../stores/admin'
import { useThemeStore } from '../stores/theme'
import { Search, Bell, FullScreen, Close, Moon, Sunny, SwitchButton, ArrowDown, DataAnalysis, FolderOpened, Document, Reading, User, ChatDotSquare, Folder, Tools } from '@element-plus/icons-vue'
import { adminApi } from '../api'

const route = useRoute()
const router = useRouter()
const adminStore = useAdminStore()
const themeStore = useThemeStore()

const searchVisible = ref(false)
const searchKeyword = ref('')
const messageDrawer = ref(false)
const isFullscreen = ref(false)
const messages = ref([])
const unreadCount = ref(0)

const breadcrumb = computed(() => {
  const t = route.meta?.title || ''
  return t ? ['首页', t] : ['首页']
})

function openMessageDrawer() {
  messageDrawer.value = true
  adminApi.getMessages({ page: 1, page_size: 20 }).then(res => {
    if (res.code === 0) messages.value = (res.data.list || []).map(m => ({
      id: m.id, content: m.content, time: m.created_at, read: m.is_read === 1 || m.is_read === true
    }))
  }).catch(() => {})
}

async function loadUnreadCount() {
  try {
    const res = await adminApi.getUnreadCount()
    if (res.code === 0) unreadCount.value = res.data.count || res.data
  } catch (e) {}
}

onMounted(() => {
  loadUnreadCount()
  setInterval(loadUnreadCount, 60000)
  document.addEventListener('fullscreenchange', () => { isFullscreen.value = !!document.fullscreenElement })
  document.addEventListener('keydown', e => {
    if ((e.metaKey || e.ctrlKey) && e.key === 'k') { e.preventDefault(); searchVisible.value = true }
  })
})

const menuList = [
  { path: '/admin/dashboard', title: '控制台', icon: DataAnalysis },
  { path: '/admin/categories', title: '栏目管理', icon: FolderOpened },
  { path: '/admin/articles', title: '文章管理', icon: Document },
  { path: '/admin/tutorials', title: '教程管理', icon: Reading },
  { path: '/admin/users', title: '用户管理', icon: User },
  { path: '/admin/comments', title: '评论管理', icon: ChatDotSquare },
  { path: '/admin/uploads', title: '文件管理', icon: Folder },
  { path: '/admin/settings', title: '系统设置', icon: Tools },
]
const searchResults = computed(() => {
  if (!searchKeyword.value) return []
  return menuList.filter(m => m.title.toLowerCase().includes(searchKeyword.value.toLowerCase()))
})

function doSearch() { if (searchResults.value.length) goTo(searchResults.value[0]) }
function goTo(item) { searchVisible.value = false; searchKeyword.value = ''; router.push(item.path) }
function toggleFullscreen() {
  if (!document.fullscreenElement) { document.documentElement.requestFullscreen() }
  else { document.exitFullscreen() }
}
function handleLogout() { adminStore.logout(); router.push('/admin/login') }
</script>

<style scoped lang="scss">
.admin-header {
  flex-shrink: 0;
  height: 56px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 20px;
  background: var(--color-bg-card);
  border-bottom: 1px solid var(--color-border-light);
}

.header-left { display: flex; align-items: center; }
.header-right { display: flex; align-items: center; gap: 4px; }

.header-divider {
  width: 1px;
  height: 20px;
  background: var(--color-border);
  margin: 0 8px;
}

:deep(.el-breadcrumb) { font-size: 13px; }
:deep(.el-breadcrumb__inner) { color: var(--color-text-muted) !important; }
:deep(.el-breadcrumb__item:last-child .el-breadcrumb__inner) { color: var(--color-text) !important; font-weight: 600; }

.icon-btn {
  width: 34px; height: 34px; display: flex; align-items: center; justify-content: center;
  border: none; background: transparent; cursor: pointer; border-radius: 8px;
  color: var(--color-text-secondary); transition: all 0.15s;
  &:hover { color: var(--color-primary); background: rgba(79,110,247,0.06); }
}
html.dark .icon-btn:hover { background: rgba(79,110,247,0.12); }

.notif-badge { :deep(.el-badge__content) { top: 4px; right: 6px; font-size: 11px; height: 16px; line-height: 16px; } }

.user-btn {
  display: flex; align-items: center; gap: 6px;
  cursor: pointer; padding: 4px 10px; border-radius: 8px;
  transition: background 0.15s;
  &:hover { background: rgba(79,110,247,0.06); }
  .user-name { font-size: 13px; font-weight: 500; color: var(--color-text); }
  .user-arrow { color: var(--color-text-muted); }
}
html.dark .user-btn:hover { background: rgba(79,110,247,0.12); }

.search-results { margin-top: 12px; }
.search-item {
  display: flex; align-items: center; gap: 10px;
  padding: 8px 12px; border-radius: 8px; cursor: pointer;
  color: var(--color-text-secondary); font-size: 14px;
  &:hover { background: var(--color-primary-bg); color: var(--color-primary); }
}

.msg-item {
  display: flex; gap: 12px; padding: 12px 0; border-bottom: 1px solid var(--color-border-light);
  &:last-child { border-bottom: none; }
  .msg-dot { width: 8px; height: 8px; border-radius: 50%; margin-top: 5px; background: var(--color-border); &.unread { background: var(--color-primary); } }
  .msg-body { flex: 1; }
  .msg-text { font-size: 14px; margin-bottom: 2px; line-height: 1.4; }
  .msg-time { font-size: 12px; color: var(--color-text-muted); }
}
.drawer-footer { text-align: center; padding-top: 12px; border-top: 1px solid var(--color-border-light); margin-top: 8px; }

@media (max-width: 768px) { .user-name { display: none; } }
</style>
