<template>
  <div class="search-page">
    <div class="page-container">
      <div class="search-header glass-card">
        <h1 class="gradient-text">搜索</h1>
        <p>搜索文章、教程等内容</p>
        <div class="search-input-wrap">
          <el-input
            v-model="keyword"
            placeholder="输入关键词搜索文章..."
            size="large"
            :prefix-icon="Search"
            clearable
            @keyup.enter="handleSearch"
            @clear="handleClear"
          >
            <template #append>
              <el-button class="gradient-btn search-btn" @click="handleSearch">搜索</el-button>
            </template>
          </el-input>
        </div>
      </div>

      <!-- 搜索结果 -->
      <div class="search-results" v-if="searched">
        <div class="result-info">
          搜索 "{{ lastKeyword }}" 共找到 <strong>{{ total }}</strong> 条结果
        </div>

        <div class="result-list" v-if="articles.length">
          <div
            v-for="article in articles"
            :key="article.id"
            class="result-item glass-card"
            @click="$router.push('/articles/' + article.id)"
          >
            <div class="result-cover">
              <el-icon><Notebook /></el-icon>
            </div>
            <div class="result-body">
              <h3>{{ article.title }}</h3>
              <p>{{ article.summary }}</p>
              <div class="result-meta">
                <span><el-icon><Clock /></el-icon> {{ formatDate(article.published_at) }}</span>
                <span><el-icon><View /></el-icon> {{ article.view_count }}</span>
                <span><el-icon><StarFilled /></el-icon> {{ article.like_count || 0 }}</span>
              </div>
            </div>
          </div>
        </div>

        <el-empty v-else description="未找到相关文章" :image-size="120" />

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

      <!-- 未搜索时展示热门标签 -->
      <div class="search-hint" v-if="!searched">
        <div class="hint-tags glass-card">
          <h3>热门标签</h3>
          <div class="tags-list">
            <el-tag
              v-for="tag in hotTags"
              :key="tag.id"
              :color="tag.color || '#3b82f6'"
              effect="dark"
              class="hint-tag"
              @click="searchByTag(tag)"
            >{{ tag.name }}</el-tag>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { Search, Notebook, Clock, View, StarFilled } from '@element-plus/icons-vue'
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
  try {
    const res = await webApi.getTags()
    if (res.code === 0) {
      hotTags.value = (res.data || []).slice(0, 8)
    }
  } catch (e) {
    console.error('获取标签失败:', e)
  }
}

async function handleSearch() {
  const kw = keyword.value.trim()
  if (!kw) return
  lastKeyword.value = kw
  currentPage.value = 1
  searched.value = true
  router.replace({ query: { q: kw } })
  await fetchResults()
}

async function fetchResults() {
  try {
    const res = await webApi.search(lastKeyword.value, {
      page: currentPage.value,
      page_size: pageSize.value
    })
    if (res.code === 0 && res.data) {
      articles.value = res.data.list || []
      total.value = res.data.pagination?.total || 0
    }
  } catch (e) {
    console.error('搜索失败:', e)
  }
}

function handleClear() {
  searched.value = false
  articles.value = []
  total.value = 0
  router.replace({ query: {} })
}

function handlePageChange(page) {
  currentPage.value = page
  window.scrollTo({ top: 0, behavior: 'smooth' })
  fetchResults()
}

function searchByTag(tag) {
  keyword.value = tag.name
  handleSearch()
}

function formatDate(dateStr) {
  if (!dateStr) return ''
  return dateStr.split(' ')[0]
}

onMounted(() => {
  fetchHotTags()
  // 如果 URL 中有搜索参数
  if (route.query.q) {
    keyword.value = route.query.q
    handleSearch()
  }
})
</script>

<style scoped lang="scss">
.search-page {
  padding: 24px 0 60px;
}
.search-header {
  padding: 36px;
  text-align: center;
  margin-bottom: 28px;
  h1 { font-size: 1.8rem; font-weight: 700; margin-bottom: 8px; }
  p { font-size: 14px; color: var(--color-text-secondary); margin-bottom: 24px; }
}
.search-input-wrap {
  max-width: 600px;
  margin: 0 auto;
  :deep(.el-input__wrapper) {
    background: rgba(255,255,255,0.04);
    border: 1px solid var(--color-border);
    border-radius: 10px 0 0 10px;
    box-shadow: none;
    &:hover { border-color: var(--color-border-hover); }
  }
  :deep(.el-input__inner) { color: var(--color-text); &::placeholder { color: var(--color-text-muted); } }
  :deep(.el-input__prefix-inner) .el-icon { color: var(--color-text-muted); }
  :deep(.el-input-group__append) { background: transparent; border: none; padding: 0; }
}
.search-btn {
  border-radius: 0 10px 10px 0 !important;
  height: 100%;
}

/* 搜索结果 */
.search-results {
  margin-top: 16px;
}
.result-info {
  font-size: 14px;
  color: var(--color-text-secondary);
  margin-bottom: 20px;
  strong { color: var(--color-primary-light); }
}
.result-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.result-item {
  display: flex;
  gap: 20px;
  padding: 20px 24px;
  cursor: pointer;
}
.result-cover {
  width: 80px;
  height: 80px;
  flex-shrink: 0;
  background: linear-gradient(135deg, rgba(59,130,246,0.1), rgba(6,182,212,0.05));
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  .el-icon { font-size: 36px; color: rgba(59,130,246,0.3); }
}
.result-body {
  flex: 1;
  h3 { font-size: 16px; font-weight: 600; margin-bottom: 8px; }
  p { font-size: 13px; color: var(--color-text-secondary); display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; margin-bottom: 10px; }
}
.result-meta {
  display: flex;
  gap: 20px;
  font-size: 12px;
  color: var(--color-text-muted);
  .el-icon { font-size: 13px; vertical-align: middle; margin-right: 2px; }
}
.pagination-wrap {
  display: flex;
  justify-content: center;
  margin-top: 32px;
}

/* 提示区域 */
.search-hint {
  margin-top: 24px;
}
.hint-tags {
  padding: 28px;
  h3 { font-size: 14px; font-weight: 600; margin-bottom: 16px; color: var(--color-text); }
}
.tags-list {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}
.hint-tag {
  cursor: pointer;
  padding: 6px 14px;
  &:hover { transform: scale(1.05); }
}

@media (max-width: 768px) {
  .search-header { padding: 24px 16px; }
  .search-header h1 { font-size: 1.4rem; }
  .result-item { flex-direction: column; padding: 16px; }
  .result-cover { width: 100%; height: 120px; }
  .result-body h3 { font-size: 14px; }
}
@media (max-width: 480px) {
  .search-input-wrap :deep(.el-input-group__append) .el-button { padding: 8px 12px; }
}
</style>
