<template>
  <div class="category-page">
    <div class="page-container">
      <!-- 栏目信息 -->
      <div class="category-header glass-card">
        <div class="category-info">
          <div class="category-icon" :style="{ background: getCategoryGradient(category?.id) }">
            <el-icon><component :is="getCategoryIcon(category?.id)" /></el-icon>
          </div>
          <div>
            <h1>{{ category?.name || '栏目' }}</h1>
            <p>{{ category?.description || '' }}</p>
          </div>
        </div>
        <div class="category-stats">
          <span>{{ total }} 篇文章</span>
        </div>
      </div>

      <!-- 文章列表 -->
      <div class="article-grid" v-if="articles.length">
        <ArticleCard v-for="article in articles" :key="article.id" :article="article" />
      </div>

      <el-empty v-else description="该栏目暂无文章" :image-size="120" />

      <!-- 分页 -->
      <div class="pagination-wrap" v-if="total > pageSize">
        <el-pagination
          background
          layout="prev, pager, next"
          :total="total"
          :page-size="pageSize"
          :current-page="currentPage"
          @current-change="handlePageChange"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { webApi } from '../../api'
import ArticleCard from '../../components/ArticleCard.vue'
import { Monitor, Connection, ChatDotSquare, Camera } from '@element-plus/icons-vue'

const route = useRoute()
const router = useRouter()

const category = ref(null)
const articles = ref([])
const total = ref(0)
const currentPage = ref(1)
const pageSize = ref(9)

async function fetchData() {
  const catId = route.params.id
  if (!catId) return

  // 获取分类信息
  try {
    const catRes = await webApi.getCategories()
    if (catRes.code === 0) {
      category.value = catRes.data.find(c => c.id == catId)
    }
  } catch (e) {
    console.error('获取栏目信息失败:', e)
  }

  // 获取文章
  try {
    const res = await webApi.getCategoryArticles(catId, {
      page: currentPage.value,
      page_size: pageSize.value
    })
    if (res.code === 0 && res.data) {
      articles.value = res.data.list || []
      total.value = res.data.pagination?.total || 0
    }
  } catch (e) {
    console.error('获取栏目文章失败:', e)
  }
}

function handlePageChange(page) {
  currentPage.value = page
  window.scrollTo({ top: 0, behavior: 'smooth' })
  fetchData()
}

function getCategoryGradient(id) {
  const gradients = [
    'linear-gradient(135deg, #3b82f6, #2563eb)',
    'linear-gradient(135deg, #06b6d4, #0891b2)',
    'linear-gradient(135deg, #f59e0b, #d97706)',
    'linear-gradient(135deg, #10b981, #059669)'
  ]
  return gradients[((Number(id) || 1) - 1) % gradients.length]
}

function getCategoryIcon(id) {
  const icons = ['Monitor', 'Connection', 'ChatDotSquare', 'Camera']
  const name = icons[((Number(id) || 1) - 1) % icons.length]
  return name
}

onMounted(fetchData)
</script>

<style scoped lang="scss">
.category-page {
  padding-bottom: 60px;
}
.category-header {
  padding: 28px 32px;
  margin-bottom: 28px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.category-info {
  display: flex;
  align-items: center;
  gap: 20px;
  h1 { font-size: 1.4rem; font-weight: 700; margin-bottom: 6px; }
  p { font-size: 13px; color: var(--color-text-secondary); }
}
.category-icon {
  width: 56px;
  height: 56px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  .el-icon { font-size: 26px; color: #fff; }
}
.category-stats {
  font-size: 13px;
  color: var(--color-text-muted);
}
.article-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
}
.pagination-wrap {
  display: flex;
  justify-content: center;
  margin-top: 48px;
}
@media (max-width: 768px) {
  .article-grid { grid-template-columns: 1fr; }
  .category-header { flex-direction: column; align-items: flex-start; gap: 12px; padding: 20px; }
  .category-info { gap: 12px; }
  .category-info h1 { font-size: 1.2rem; }
  .category-icon { width: 44px; height: 44px; .el-icon { font-size: 20px; } }
}
@media (min-width: 769px) and (max-width: 1024px) {
  .article-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>
