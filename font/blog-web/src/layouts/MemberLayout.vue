<template>
  <div class="member-layout">
    <div class="page-container">
      <div class="member-container">
        <aside class="member-sidebar glass-card">
          <div class="member-info">
            <el-avatar :size="64" :icon="UserFilled" />
            <h3>{{ userStore.nickname || '用户' }}</h3>
          </div>
          <el-menu :default-active="$route.path" router class="member-menu">
            <el-menu-item index="/member/profile">
              <el-icon><User /></el-icon>
              <span>个人资料</span>
            </el-menu-item>
            <el-menu-item index="/member/favorites">
              <el-icon><Star /></el-icon>
              <span>我的收藏</span>
            </el-menu-item>
            <el-menu-item index="/member/comments">
              <el-icon><ChatDotSquare /></el-icon>
              <span>我的评论</span>
            </el-menu-item>
          </el-menu>
        </aside>
        <main class="member-content">
          <router-view />
        </main>
      </div>
    </div>
  </div>
</template>

<script setup>
import { UserFilled, User, Star, ChatDotSquare } from '@element-plus/icons-vue'
import { useUserStore } from '../stores/user'
const userStore = useUserStore()
</script>

<style scoped lang="scss">
.member-layout { padding: 40px 0; }
.member-container {
  display: flex; gap: 24px; min-height: 500px;
}
.member-sidebar {
  width: 240px; padding: 24px; flex-shrink: 0;
}
.member-info {
  text-align: center; padding-bottom: 20px; border-bottom: 1px solid var(--color-border); margin-bottom: 16px;
  h3 { margin-top: 12px; font-size: 16px; }
}
.member-menu {
  background: transparent; border: none;
  :deep(.el-menu-item) { color: var(--color-text-secondary); border-radius: 8px; margin-bottom: 4px; &:hover { background: rgba(59,130,246,0.08); } &.is-active { color: var(--color-primary); background: rgba(59,130,246,0.12); } }
}
.member-content { flex: 1; }
@media (max-width: 768px) {
  .member-layout { padding: 20px 0; }
  .member-container { flex-direction: column; }
  .member-sidebar { width: 100%; padding: 16px; }
  .member-menu :deep(.el-menu-item) { padding: 0 12px; }
}
</style>
