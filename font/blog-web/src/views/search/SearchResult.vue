<template>
  <div class="search-page">
    <div class="page-container">
      <section class="search-hero glass-card">
        <p class="hero-kicker">Search</p>
        <h1>搜索内容</h1>
        <p>支持按关键词搜索文章内容，也可以直接从热门标签开始浏览。</p>

        <div class="search-input-wrap">
          <el-input
            v-model="keyword"
            size="large"
            clearable
            :prefix-icon="Search"
            placeholder="输入关键词，例如 Vue、ThinkPHP、部署..."
            @keyup.enter="handleSearch"
            @clear="handleClear"
          >
            <template #append>
              <el-button class="primary-btn search-btn" @click="handleSearch">搜索</el-button>
            </template>
          </el-input>
        </div>
      </section>

      <section v-if="searched" class="search-results">
        <div class="result-summary">
          搜索 “{{ lastKeyword }}”，共找到 <strong>{{ total }}</strong> 条结果
        </div>

        <div v-if="articles.length" class="result-list">
          <article
            v-for="article in articles"
            :key="article.id"
            class="result-card glass-card"
            @click="$router.push(`/articles/${article.id}`)"
          >
            <div class="result-badge">{{ (article.title || 'A').slice(0, 1).toUpperCase() }}</div>
            <div class="result-body">
              <h3>{{ article.title }}</h3>
              <p>{{ article.summary || '暂无摘要。' }}</p>
              <div class="result-meta">
                <span>{{ formatDate(article.published_at) }}</span>
                <span>{{ article.view_count || 0 }} 次阅读</span>
                <span>{{ article.like_count || 0 }} 喜欢</span>
              </div>
            </div>
          </article>
        </div>
        <el-empty v-else description="没有找到匹配内容" :image-size="120" />

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
      </section>

      <section v-else class="tag-panel glass-card">
        <div class="section-head">
          <div>
            <p class="hero-kicker">Hot Tags</p>
            <h2>热门标签</h2>
          </div>
        </div>

        <div class="tag-list">
          <button
            v-for="tag in hotTags"
            :key="tag.id"
            type="button"
            class="tag-pill"
            @click="searchByTag(tag)"
          >
            # {{ tag.name }}
          </button>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { Search } from '@element-plus/icons-vue'
import { webApi } from '../../api'

const route = useRoute()
const router = useRouter()
const keyword = ref('')
const lastKeyword = ref('')
const articles = ref([])
const total = ref(0)
const currentPage = ref(1)
const pageSize = ref(10)
const searched = ref(false)
const hotTags = ref([])

async function fetchHotTags() {
  const res = await webApi.getTags()
  if (res.code === 0) {
    hotTags.value = (res.data || []).slice(0, 10)
  }
}

async function fetchResults() {
  const res = await webApi.search(lastKeyword.value, {
    page: currentPage.value,
    page_size: pageSize.value,
  })
  if (res.code === 0 && res.data) {
    articles.value = res.data.list || []
    total.value = res.data.pagination?.total || 0
  }
}

async function handleSearch() {
  const nextKeyword = keyword.value.trim()
  if (!nextKeyword) return
  lastKeyword.value = nextKeyword
  searched.value = true
  currentPage.value = 1
  router.replace({ query: { q: nextKeyword } })
  await fetchResults()
}

function handleClear() {
  searched.value = false
  lastKeyword.value = ''
  articles.value = []
  total.value = 0
  router.replace({ query: {} })
}

function handlePageChange(page) {
  currentPage.value = page
  window.scrollTo({ top: 0, behavior: 'smooth' })
  fetchResults().catch(console.error)
}

function searchByTag(tag) {
  keyword.value = tag.name
  handleSearch().catch(console.error)
}

function formatDate(value) {
  if (!value) return '最近更新'
  return String(value).split(' ')[0]
}

onMounted(async () => {
  try {
    await fetchHotTags()
    if (route.query.q) {
      keyword.value = String(route.query.q)
      await handleSearch()
    }
  } catch (error) {
    console.error('初始化搜索页失败:', error)
  }
})
</script>

<style scoped lang="scss">
.search-page {
  padding: 24px 0 48px;
}

.search-hero,
.tag-panel {
  padding: 28px 30px;
}

.hero-kicker {
  margin-bottom: 10px;
  color: var(--color-primary-light);
  text-transform: uppercase;
  letter-spacing: 0.16em;
  font-size: 0.76rem;
}

.search-hero h1 {
  font-size: 2rem;
  margin-bottom: 10px;
}

.search-hero > p {
  color: var(--color-text-secondary);
  margin-bottom: 22px;
}

.search-input-wrap {
  max-width: 680px;
}

.search-btn {
  height: 100%;
  border-radius: 0 14px 14px 0 !important;
}

.result-summary {
  margin: 22px 0 18px;
  color: var(--color-text-secondary);
  strong {
    color: var(--color-primary-light);
  }
}

.result-list {
  display: grid;
  gap: 14px;
}

.result-card {
  display: flex;
  gap: 18px;
  padding: 22px;
  cursor: pointer;
}

.result-badge {
  width: 70px;
  height: 70px;
  flex-shrink: 0;
  border-radius: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, rgba(15, 130, 255, 0.18), rgba(48, 207, 208, 0.08));
  color: var(--color-primary-light);
  font-size: 2rem;
  font-weight: 800;
}

.result-body h3 {
  font-size: 1.08rem;
  margin-bottom: 8px;
}

.result-body p {
  color: var(--color-text-secondary);
  line-height: 1.7;
  margin-bottom: 12px;
}

.result-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  color: var(--color-text-muted);
  font-size: 0.84rem;
}

.pagination-wrap {
  display: flex;
  justify-content: center;
  margin-top: 34px;
}

.section-head {
  margin-bottom: 18px;
}

.tag-list {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
}

.tag-pill {
  border: 1px solid var(--color-border);
  background: var(--color-bg-card);
  color: var(--color-text);
  border-radius: 999px;
  padding: 10px 14px;
}

@media (max-width: 768px) {
  .search-hero,
  .tag-panel {
    padding: 22px;
  }

  .result-card {
    flex-direction: column;
  }
}
</style>
