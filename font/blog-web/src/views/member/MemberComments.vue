<template>
  <div class="member-comments-page">
    <div class="profile-card glass-card">
      <h2 class="page-section-title gradient-text">我的评论</h2>

      <div class="comments-list" v-if="comments.length">
        <div
          v-for="comment in comments"
          :key="comment.id"
          class="comment-item glass-card"
        >
          <div class="comment-header">
            <el-avatar :size="32" :icon="UserFilled" />
            <div class="comment-user">
              <span class="comment-nickname">{{ comment.user?.nickname || '用户' }}</span>
              <span class="comment-time">{{ comment.created_at }}</span>
            </div>
            <el-tag size="small" effect="dark" color="#3b82f6">
              {{ comment.target_type === 'article' ? '文章' : '教程' }}
            </el-tag>
          </div>
          <p class="comment-content">{{ comment.content }}</p>
        </div>
      </div>

      <el-empty v-else description="暂无评论" :image-size="100" />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { UserFilled } from '@element-plus/icons-vue'
import { userApi } from '../../api'

const comments = ref([])

onMounted(async () => {
  try {
    const res = await userApi.getMyComments()
    if (res.code === 0 && res.data) {
      comments.value = res.data
    }
  } catch (e) {
    console.error('获取评论列表失败:', e)
  }
})
</script>

<style scoped lang="scss">
.member-comments-page {
  min-height: 400px;
}
.profile-card {
  padding: 32px;
}
.page-section-title {
  font-size: 1.3rem;
  font-weight: 700;
  margin-bottom: 24px;
}
.comments-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.comment-item {
  padding: 20px;
}
.comment-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 12px;
}
.comment-user {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.comment-nickname {
  font-size: 13px;
  font-weight: 600;
  color: var(--color-text);
}
.comment-time {
  font-size: 11px;
  color: var(--color-text-muted);
}
.comment-content {
  font-size: 14px;
  color: var(--color-text-secondary);
  line-height: 1.7;
  padding-left: 44px;
}
@media (max-width: 768px) {
  .profile-card { padding: 20px; }
  .comment-content { padding-left: 0; margin-top: 8px; }
}
</style>
