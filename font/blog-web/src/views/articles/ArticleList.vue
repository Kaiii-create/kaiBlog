<template>
  <div class="article-list-page">
    <div class="page-container">
      <header class="page-banner glass-card">
        <p class="banner-kicker">Articles</p>
        <h1>文章列表</h1>
        <p>按分类浏览最近发布的内容，查看我持续更新的技术文章与经验记录。</p>
      </header>

      <section class="filters glass-card">
        <button
          type="button"
          class="filter-chip"
          :class="{ active: currentCategory === 0 }"
          @click="switchCategory(0)"
        >
          全部
        </button>
        <button
          v-for="category in categories"
          :key="category.id"
          type="button"
          class="filter-chip"
          :class="{ active: currentCategory === category.id }"
          @click="switchCategory(category.id)"
        >
          {{ category.name }}
        </button>
      </section>

      <div v-if="articles.length" class="article-grid">
        <ArticleCard v-for="article in articles" :key="article.id" :article="article" />
      </div>
      <el-empty v-else description="暂无文章内容" :image-size="120" />

      <div v-if="total > pageSize" class="pagination-wrap">
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
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import ArticleCard from '../../components/ArticleCard.vue'
import { webApi } from '../../api'

const router = useRouter()
const articles = ref([])
const categories = ref([])
const total = ref(0)
const currentPage = ref(1)
const pageSize = ref(9)
const currentCategory = ref(0)

async function fetchCategories() {
  const res = await webApi.getCategories()
  if (res.code === 0) {
    categories.value = (res.data || []).filter((item) => !item.parent_id)
  }
}

async function fetchArticles() {
  const params = {
    page: currentPage.value,
    page_size: pageSize.value,
  }

  if (currentCategory.value) {
    params.category_id = currentCategory.value
  }

  const res = await webApi.getArticles(params)
  if (res.code === 0 && res.data) {
    articles.value = res.data.list || []
    total.value = res.data.pagination?.total || 0
  }
}

function switchCategory(categoryId) {
  currentCategory.value = categoryId
  currentPage.value = 1
  router.replace({ query: categoryId ? { category: categoryId } : {} })
  fetchArticles().catch(console.error)
}

function handlePageChange(page) {
  currentPage.value = page
  window.scrollTo({ top: 0, behavior: 'smooth' })
  fetchArticles().catch(console.error)
}

onMounted(async () => {
  try {
    await Promise.all([fetchCategories(), fetchArticles()])
  } catch (error) {
    console.error('获取文章列表失败:', error)
  }
})
</script>

<style scoped lang="scss">
.article-list-page {
  padding: 24px 0 48px;
}

.page-banner {
  padding: 28px 30px;
  margin-bottom: 22px;
}

.banner-kicker {
  margin-bottom: 10px;
  color: var(--color-primary-light);
  text-transform: uppercase;
  letter-spacing: 0.16em;
  font-size: 0.76rem;
}

.page-banner h1 {
  font-size: 2rem;
  margin-bottom: 10px;
}

.page-banner p:last-child {
  color: var(--color-text-secondary);
  max-width: 620px;
}

.filters {
  padding: 14px;
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-bottom: 24px;
}

.filter-chip {
  border: 1px solid var(--color-border);
  background: var(--color-bg-card);
  color: var(--color-text-secondary);
  border-radius: 999px;
  padding: 10px 16px;
  &.active {
    background: linear-gradient(135deg, #0f82ff, #356dff);
    border-color: transparent;
    color: #fff;
  }
}

.article-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 20px;
}

.pagination-wrap {
  display: flex;
  justify-content: center;
  margin-top: 40px;
}

@media (max-width: 1024px) {
  .article-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 768px) {
  .page-banner,
  .filters {
    padding: 20px;
  }

  .article-grid {
    grid-template-columns: 1fr;
  }
}
</style>
