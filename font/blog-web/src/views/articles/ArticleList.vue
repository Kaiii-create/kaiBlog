<template>
  <div class="article-list-page">
    <div class="page-container">
      <div class="page-header">
        <h1 class="gradient-text">文章列表</h1>
        <p>探索精选技术文章，获取实用开发知识</p>
      </div>

      <!-- 分类 tab 切换 -->
      <div class="category-tabs glass-card">
        <div class="tabs-scroll">
          <span
            class="tab-item"
            :class="{ active: currentCategory === 0 }"
            @click="switchCategory(0)"
          >全部</span>
          <span
            v-for="cat in categories"
            :key="cat.id"
            class="tab-item"
            :class="{ active: currentCategory === cat.id }"
            @click="switchCategory(cat.id)"
          >{{ cat.name }}</span>
        </div>
      </div>

      <!-- 文章列表 -->
      <div class="article-grid" v-if="articles.length">
        <ArticleCard v-for="article in articles" :key="article.id" :article="article" />
      </div>

      <el-empty v-else description="暂无文章" :image-size="120" />

      <!-- 分页 -->
      <div class="pagination-wrap" v-if="total > 0">
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
import { ref, reactive, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { webApi } from '../../api'
import ArticleCard from '../../components/ArticleCard.vue'

const route = useRoute()
const router = useRouter()

const articles = ref([])
const categories = ref([])
const total = ref(0)
const currentPage = ref(1)
const pageSize = ref(9)
const currentCategory = ref(0)

async function fetchCategories() {
  try {
    const res = await webApi.getCategories()
    if (res.code === 0 && res.data) {
      categories.value = res.data.filter(c => c.parent_id === 0)
    }
  } catch (e) {
    console.error('获取栏目列表失败:', e)
  }
}

async function fetchArticles() {
  const params = {
    page: currentPage.value,
    page_size: pageSize.value
  }
  if (currentCategory.value > 0) {
    params.category_id = currentCategory.value
  }
  try {
    const res = await webApi.getArticles(params)
    if (res.code === 0 && res.data) {
      articles.value = res.data.list || []
      total.value = res.data.pagination?.total || 0
    }
  } catch (e) {
    console.error('获取文章列表失败:', e)
  }
}

function switchCategory(catId) {
  currentCategory.value = catId
  currentPage.value = 1
  router.replace({ query: { category: catId > 0 ? catId : undefined } })
  fetchArticles()
}

function handlePageChange(page) {
  currentPage.value = page
  window.scrollTo({ top: 0, behavior: 'smooth' })
  fetchArticles()
}

onMounted(() => {
  fetchCategories()
  fetchArticles()
})
</script>

<style scoped lang="scss">
.article-list-page {
  padding-bottom: 60px;
}
.category-tabs {
  padding: 12px 20px;
  margin-bottom: 28px;
  margin-top: -8px;
}
.tabs-scroll {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}
.tab-item {
  padding: 8px 20px;
  font-size: 14px;
  color: var(--color-text-secondary);
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s;
  &:hover { background: rgba(59, 130, 246, 0.08); color: var(--color-text); }
  &.active {
    background: var(--gradient-primary);
    color: #fff;
    font-weight: 500;
  }
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
}
@media (min-width: 769px) and (max-width: 1024px) {
  .article-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>
