<template>
  <div class="home-page">
    <!-- Hero 大屏区 -->
    <section class="hero-section">
      <div class="hero-particles">
        <span v-for="n in 20" :key="n" class="particle" :style="getParticleStyle(n)"></span>
      </div>
      <div class="page-container hero-content">
        <div class="hero-badge glass-card">Kaiii 技术博客</div>
        <h1 class="hero-title">
          <span class="gradient-text">分享技术</span>
          <span class="hero-sub">记录成长</span>
        </h1>
        <p class="hero-desc">专注 Web 全栈开发，分享实用技术文章与教程，与开发者共同成长。</p>
        <div class="hero-actions">
          <el-button class="gradient-btn hero-btn" @click="scrollToLatest">
            <el-icon><Promotion /></el-icon>
            最新文章
          </el-button>
          <el-button class="hero-btn-outline" @click="$router.push('/tutorials')">
            <el-icon><Reading /></el-icon>
            浏览教程
          </el-button>
        </div>
        <div class="hero-stats">
          <div class="stat-item">
            <span class="stat-value gradient-text">{{ homeData.latest_articles?.length || 0 }}+</span>
            <span class="stat-label">文章</span>
          </div>
          <div class="stat-item">
            <span class="stat-value gradient-text">{{ homeData.tutorials?.length || 0 }}</span>
            <span class="stat-label">教程</span>
          </div>
          <div class="stat-item">
            <span class="stat-value gradient-text">{{ homeData.hot_tags?.length || 0 }}+</span>
            <span class="stat-label">标签</span>
          </div>
        </div>
      </div>
    </section>

    <!-- 推荐置顶文章 -->
    <section class="section section-featured" v-if="homeData.top_articles?.length">
      <div class="page-container">
        <div class="section-header">
          <h2 class="section-title"><span class="gradient-text">推荐置顶</span></h2>
          <p class="section-desc">精选优质内容，不容错过</p>
        </div>
        <div class="featured-grid">
          <div
            v-for="(article, idx) in homeData.top_articles"
            :key="article.id"
            class="featured-card glass-card"
            :class="{ 'featured-primary': idx === 0 }"
            @click="$router.push(`/articles/${article.id}`)"
          >
            <div class="featured-badge" v-if="idx === 0">精选</div>
            <div class="featured-cover">
              <el-icon><Notebook /></el-icon>
            </div>
            <div class="featured-body">
              <h3>{{ article.title }}</h3>
              <p>{{ article.summary }}</p>
              <div class="featured-meta">
                <span><el-icon><Clock /></el-icon> {{ formatDate(article.published_at) }}</span>
                <span><el-icon><View /></el-icon> {{ article.view_count }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- 最新文章列表 -->
    <section class="section section-latest" ref="latestRef">
      <div class="page-container">
        <div class="section-header">
          <h2 class="section-title"><span class="gradient-text">最新文章</span></h2>
          <p class="section-desc">掌握最新技术动态</p>
          <router-link to="/articles" class="section-more">
            查看全部 <el-icon><ArrowRight /></el-icon>
          </router-link>
        </div>
        <div class="article-grid" v-if="homeData.latest_articles?.length">
          <ArticleCard v-for="article in homeData.latest_articles" :key="article.id" :article="article" />
        </div>
        <el-skeleton :loading="!homeData.latest_articles?.length" animated :count="6">
          <div class="article-grid">
            <div v-for="n in 6" :key="n" class="glass-card" style="padding:20px;height:320px;">
              <el-skeleton-item variant="image" style="height:160px;width:100%;border-radius:10px;" />
              <el-skeleton-item variant="p" style="margin-top:14px;width:80%" />
              <el-skeleton-item variant="p" style="width:60%" />
            </div>
          </div>
        </el-skeleton>
      </div>
    </section>

    <!-- 栏目快速入口 -->
    <section class="section section-categories">
      <div class="page-container">
        <div class="section-header">
          <h2 class="section-title"><span class="gradient-text">内容栏目</span></h2>
          <p class="section-desc">按分类浏览感兴趣的内容</p>
        </div>
        <div class="categories-grid">
          <div
            v-for="cat in homeData.categories"
            :key="cat.id"
            class="category-card glass-card"
            @click="$router.push(`/categories/${cat.id}`)"
          >
            <div class="category-icon" :style="{ background: getCategoryGradient(cat.id) }">
              <el-icon><component :is="getCategoryIcon(cat.id)" /></el-icon>
            </div>
            <h3>{{ cat.name }}</h3>
            <p>{{ cat.description || '浏览该栏目下的文章' }}</p>
            <span class="category-count">{{ cat.article_count || 0 }} 篇文章</span>
          </div>
        </div>
      </div>
    </section>

    <!-- 推荐教程区域 -->
    <section class="section section-tutorials" v-if="homeData.tutorials?.length">
      <div class="page-container">
        <div class="section-header">
          <h2 class="section-title"><span class="gradient-text">推荐教程</span></h2>
          <p class="section-desc">系统学习，循序渐进</p>
          <router-link to="/tutorials" class="section-more">
            全部教程 <el-icon><ArrowRight /></el-icon>
          </router-link>
        </div>
        <div class="tutorials-grid">
          <div
            v-for="tutorial in homeData.tutorials"
            :key="tutorial.id"
            class="tutorial-card glass-card"
            @click="$router.push(`/tutorials/${tutorial.id}`)"
          >
            <div class="tutorial-icon">
              <el-icon><Reading /></el-icon>
            </div>
            <h3>{{ tutorial.title }}</h3>
            <p>{{ tutorial.description }}</p>
            <div class="tutorial-meta">
              <span><el-icon><Collection /></el-icon> {{ tutorial.chapter_count }} 章节</span>
              <span>
                <el-icon><View /></el-icon> {{ tutorial.view_count }}
              </span>
              <span class="difficulty" :class="'diff-' + tutorial.difficulty">
                {{ difficultyLabel(tutorial.difficulty) }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- 标签云 -->
    <section class="section section-tags">
      <div class="page-container">
        <div class="section-header">
          <h2 class="section-title"><span class="gradient-text">热门标签</span></h2>
          <p class="section-desc">通过标签快速发现相关内容</p>
        </div>
        <div class="tags-cloud glass-card">
          <el-tag
            v-for="tag in homeData.hot_tags"
            :key="tag.id"
            :color="tag.color || '#3b82f6'"
            effect="dark"
            class="tag-item"
            :style="{ fontSize: getTagSize(tag.article_count) + 'px' }"
            @click="$router.push('/articles?tag=' + tag.id)"
          >
            {{ tag.name }}
          </el-tag>
        </div>
      </div>
    </section>

  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import {
  Promotion, Reading, Notebook, Clock, View, ArrowRight,
  ArrowUp, Collection
} from '@element-plus/icons-vue'
import { webApi } from '../../api'
import ArticleCard from '../../components/ArticleCard.vue'

const router = useRouter()
const latestRef = ref(null)

const homeData = reactive({
  top_articles: [],
  latest_articles: [],
  categories: [],
  hot_tags: [],
  tutorials: []
})

onMounted(async () => {
  try {
      const res = await webApi.getHomeData()
      if (res.code === 0 && res.data) {
        Object.assign(homeData, res.data)
        // 尝试获取网站配置以更新统计
        const configRes = await webApi.getConfig().catch(() => null)
        if (configRes?.code === 0) {
          // 可选：使用 config 数据
        }
      }
  } catch (e) {
    console.error('获取首页数据失败:', e)
  }
})

function scrollToLatest() {
  latestRef.value?.scrollIntoView({ behavior: 'smooth' })
}

function scrollToTop() {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

function formatDate(dateStr) {
  if (!dateStr) return ''
  return dateStr.split(' ')[0]
}

function getParticleStyle(n) {
  const size = 4 + Math.random() * 6
  return {
    left: Math.random() * 100 + '%',
    top: Math.random() * 100 + '%',
    width: size + 'px',
    height: size + 'px',
    animationDelay: Math.random() * 5 + 's',
    animationDuration: 3 + Math.random() * 4 + 's'
  }
}

function getCategoryGradient(id) {
  const gradients = [
    'linear-gradient(135deg, #3b82f6, #2563eb)',
    'linear-gradient(135deg, #06b6d4, #0891b2)',
    'linear-gradient(135deg, #f59e0b, #d97706)',
    'linear-gradient(135deg, #10b981, #059669)'
  ]
  return gradients[(id - 1) % gradients.length]
}

function getCategoryIcon(id) {
  const icons = ['Monitor', 'Server', 'ChatDotSquare', 'Camera']
  return icons[(id - 1) % icons.length]
}

function difficultyLabel(d) {
  return ['', '入门', '进阶', '高级'][d] || ''
}

function getTagSize(count) {
  return 13 + Math.min(count, 5) * 2
}
</script>

<style scoped lang="scss">
/* Hero 区域 */
.hero-section {
  position: relative;
  min-height: 85vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--gradient-hero);
  overflow: hidden;
}
.hero-particles {
  position: absolute;
  inset: 0;
  pointer-events: none;
}
.particle {
  position: absolute;
  background: rgba(59, 130, 246, 0.3);
  border-radius: 50%;
  animation: float 4s ease-in-out infinite;
  &::after {
    content: '';
    position: absolute;
    inset: -2px;
    border-radius: 50%;
    background: rgba(59, 130, 246, 0.1);
    filter: blur(4px);
  }
}
@keyframes float {
  0%, 100% { transform: translateY(0) scale(1); opacity: 0.4; }
  50% { transform: translateY(-30px) scale(1.5); opacity: 0.8; }
}
.hero-content {
  position: relative;
  text-align: center;
  z-index: 1;
  padding: 60px 0;
}
.hero-badge {
  display: inline-flex;
  padding: 6px 20px;
  font-size: 13px;
  color: var(--color-primary-light);
  margin-bottom: 24px;
  border-radius: 20px;
}
.hero-title {
  font-size: 3.2rem;
  font-weight: 800;
  margin-bottom: 16px;
  line-height: 1.2;
}
.hero-sub {
  display: block;
  color: var(--color-text);
  margin-top: 4px;
}
.hero-desc {
  font-size: 1.1rem;
  color: var(--color-text-secondary);
  max-width: 560px;
  margin: 0 auto 32px;
  line-height: 1.7;
}
.hero-actions {
  display: flex;
  gap: 16px;
  justify-content: center;
  margin-bottom: 48px;
}
.hero-btn {
  padding: 12px 32px;
  font-size: 15px;
  display: flex;
  align-items: center;
  gap: 8px;
}
.hero-btn-outline {
  background: transparent;
  border: 1px solid var(--color-border);
  color: var(--color-text);
  padding: 12px 32px;
  font-size: 15px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  transition: all 0.3s;
  &:hover {
    border-color: var(--color-primary);
    color: var(--color-primary);
  }
}
.hero-stats {
  display: flex;
  justify-content: center;
  gap: 48px;
}
.stat-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
}
.stat-value {
  font-size: 2rem;
  font-weight: 800;
}
.stat-label {
  font-size: 13px;
  color: var(--color-text-muted);
}

/* 通用 section */
.section {
  padding: 64px 0;
}
.section-header {
  text-align: center;
  margin-bottom: 40px;
  position: relative;
}
.section-title {
  font-size: 1.8rem;
  font-weight: 700;
  margin-bottom: 8px;
}
.section-desc {
  color: var(--color-text-secondary);
  font-size: 14px;
}
.section-more {
  position: absolute;
  right: 0;
  top: 50%;
  transform: translateY(-50%);
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 14px;
  color: var(--color-primary-light);
  &:hover { color: var(--color-primary); }
}

/* 推荐置顶 */
.featured-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}
.featured-card {
  padding: 24px;
  display: flex;
  gap: 20px;
  cursor: pointer;
  position: relative;
  overflow: hidden;
  min-height: 180px;
}
.featured-primary {
  grid-column: 1 / -1;
  .featured-badge {
    position: absolute;
    top: 12px;
    right: 12px;
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: #fff;
    padding: 4px 12px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
  }
}
.featured-cover {
  width: 80px;
  height: 80px;
  flex-shrink: 0;
  background: linear-gradient(135deg, rgba(59,130,246,0.1), rgba(6,182,212,0.05));
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  .el-icon { font-size: 36px; color: rgba(59,130,246,0.4); }
}
.featured-body {
  flex: 1;
  h3 { font-size: 16px; font-weight: 600; margin-bottom: 8px; }
  p { font-size: 13px; color: var(--color-text-secondary); display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; margin-bottom: 12px; }
}
.featured-meta {
  display: flex; gap: 16px; font-size: 12px; color: var(--color-text-muted);
  .el-icon { font-size: 13px; vertical-align: middle; }
}

/* 文章网格 */
.article-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
}

/* 栏目 */
.categories-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
}
.category-card {
  padding: 28px 20px;
  text-align: center;
  cursor: pointer;
  h3 { font-size: 16px; font-weight: 600; margin-bottom: 8px; }
  p { font-size: 13px; color: var(--color-text-secondary); margin-bottom: 12px; }
  .category-count { font-size: 12px; color: var(--color-text-muted); }
}
.category-icon {
  width: 52px;
  height: 52px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 16px;
  .el-icon { font-size: 24px; color: #fff; }
}

/* 教程 */
.tutorials-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
}
.tutorial-card {
  padding: 28px 24px;
  cursor: pointer;
  text-align: center;
  .tutorial-icon {
    width: 56px; height: 56px;
    background: linear-gradient(135deg, rgba(59,130,246,0.15), rgba(6,182,212,0.1));
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 16px;
    .el-icon { font-size: 28px; color: var(--color-primary-light); }
  }
  h3 { font-size: 16px; font-weight: 600; margin-bottom: 10px; }
  p { font-size: 13px; color: var(--color-text-secondary); margin-bottom: 16px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
}
.tutorial-meta {
  display: flex; justify-content: center; gap: 16px; font-size: 12px; color: var(--color-text-muted);
  .el-icon { font-size: 13px; vertical-align: middle; }
  .difficulty { padding: 2px 8px; border-radius: 4px; font-size: 11px; }
  .diff-1 { background: rgba(16, 185, 129, 0.15); color: #10b981; }
  .diff-2 { background: rgba(245, 158, 11, 0.15); color: #f59e0b; }
  .diff-3 { background: rgba(239, 68, 68, 0.15); color: #ef4444; }
}

/* 标签云 */
.tags-cloud {
  padding: 32px 40px;
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 12px;
}
.tag-item {
  cursor: pointer;
  padding: 6px 14px;
  transition: transform 0.3s;
  &:hover { transform: scale(1.1); }
}

@media (max-width: 768px) {
  .hero-section { min-height: 70vh; }
  .hero-title { font-size: 1.8rem; }
  .hero-desc { font-size: 0.95rem; padding: 0 16px; }
  .hero-actions { flex-direction: column; align-items: center; gap: 12px; }
  .hero-btn, .hero-btn-outline { width: 200px; justify-content: center; }
  .hero-stats { gap: 24px; }
  .stat-value { font-size: 1.5rem; }

  .section { padding: 40px 0; }
  .section-header { margin-bottom: 24px; }
  .section-title { font-size: 1.4rem; }
  .section-more { position: static; transform: none; justify-content: center; margin-top: 8px; }

  .featured-grid { grid-template-columns: 1fr; }
  .featured-card { flex-direction: column; padding: 16px; min-height: auto; }
  .featured-cover { width: 100%; height: 120px; }

  .article-grid { grid-template-columns: 1fr; }
  .categories-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
  .category-card { padding: 20px 12px; }

  .tutorials-grid { grid-template-columns: 1fr; }
  .tutorial-card { padding: 20px 16px; }

  .tags-cloud { padding: 20px; }
}

@media (max-width: 480px) {
  .hero-title { font-size: 1.5rem; }
  .hero-badge { font-size: 12px; padding: 4px 14px; }
  .categories-grid { grid-template-columns: 1fr 1fr; gap: 8px; }
  .category-card { padding: 16px 10px; }
  .category-icon { width: 42px; height: 42px; margin-bottom: 10px; .el-icon { font-size: 20px; } }
}

@media (min-width: 769px) and (max-width: 1024px) {
  .article-grid { grid-template-columns: repeat(2, 1fr); }
  .tutorials-grid { grid-template-columns: repeat(2, 1fr); }
  .categories-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>
