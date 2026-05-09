<template>
  <div class="member-favorites-page">
    <div class="profile-card glass-card">
      <h2 class="page-section-title gradient-text">我的收藏</h2>

      <div class="favorites-list" v-if="articles.length">
        <div
          v-for="article in articles"
          :key="article.id"
          class="favorite-item"
          @click="$router.push('/articles/' + article.id)"
        >
          <div class="fav-cover">
            <el-icon><Notebook /></el-icon>
          </div>
          <div class="fav-info">
            <h3>{{ article.title }}</h3>
            <p>{{ article.summary }}</p>
            <div class="fav-meta">
              <span><el-icon><Clock /></el-icon> {{ formatDate(article.published_at) }}</span>
              <span><el-icon><View /></el-icon> {{ article.view_count }}</span>
            </div>
          </div>
        </div>
      </div>

      <el-empty v-else description="暂无收藏" :image-size="100" />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Notebook, Clock, View } from '@element-plus/icons-vue'
import { ElMessage } from 'element-plus'
import { userApi } from '../../api'

const articles = ref([])

onMounted(async () => {
  try {
    const res = await userApi.getFavorites()
    if (res.code === 0 && res.data) {
      articles.value = res.data
    }
  } catch (e) {
    console.error('获取收藏列表失败:', e)
    ElMessage.error('获取收藏列表失败')
  }
})

function formatDate(dateStr) {
  if (!dateStr) return ''
  return dateStr.split(' ')[0]
}
</script>

<style scoped lang="scss">
.member-favorites-page {
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
.favorites-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.favorite-item {
  display: flex;
  gap: 16px;
  padding: 16px;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s;
  border: 1px solid transparent;
  &:hover {
    background: rgba(59, 130, 246, 0.04);
    border-color: var(--color-border-hover);
  }
}
.fav-cover {
  width: 72px;
  height: 72px;
  flex-shrink: 0;
  background: linear-gradient(135deg, rgba(59,130,246,0.1), rgba(6,182,212,0.05));
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  .el-icon { font-size: 32px; color: rgba(59,130,246,0.3); }
}
.fav-info {
  flex: 1;
  h3 { font-size: 15px; font-weight: 600; margin-bottom: 6px; }
  p { font-size: 13px; color: var(--color-text-secondary); display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; margin-bottom: 8px; }
}
.fav-meta {
  display: flex;
  gap: 16px;
  font-size: 12px;
  color: var(--color-text-muted);
  .el-icon { font-size: 13px; vertical-align: middle; margin-right: 2px; }
}
@media (max-width: 768px) {
  .profile-card { padding: 20px; }
  .favorite-item { flex-direction: column; padding: 12px; }
  .fav-cover { width: 100%; height: 100px; }
}
</style>
