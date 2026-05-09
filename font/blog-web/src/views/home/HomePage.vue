<template>
  <div class="home-page">
    <section class="hero-section">
      <div class="hero-orb hero-orb-left"></div>
      <div class="hero-orb hero-orb-right"></div>

      <div class="page-container hero-grid">
        <div class="hero-copy">
          <p class="hero-kicker">{{ site.name || 'Kaiii 博客' }}</p>
          <h1>{{ heroTitle }}</h1>
          <p class="hero-desc">{{ site.description || '写给开发者，也写给未来的自己。这里沉淀文章、教程和长期更新的学习记录。' }}</p>

          <div class="hero-actions">
            <el-button class="primary-btn hero-btn" @click="scrollToLatest">阅读最新文章</el-button>
            <el-button class="ghost-btn hero-btn" @click="$router.push('/tutorials')">进入教程中心</el-button>
          </div>

          <div class="hero-stats">
            <div class="stat-card">
              <strong>{{ homeData.latest_articles.length }}</strong>
              <span>最新文章</span>
            </div>
            <div class="stat-card">
              <strong>{{ homeData.tutorials.length }}</strong>
              <span>推荐教程</span>
            </div>
            <div class="stat-card">
              <strong>{{ homeData.hot_tags.length }}</strong>
              <span>热门标签</span>
            </div>
          </div>
        </div>

        <div class="hero-panel glass-card">
          <p class="panel-kicker">本站公告</p>
          <h2>{{ site.notice || '欢迎来到我的数字工作台' }}</h2>
          <p>{{ footerText }}</p>

          <div class="panel-list">
            <div class="panel-item">
              <span>站点状态</span>
              <strong>{{ site.status === false ? '维护中' : '正常运行' }}</strong>
            </div>
            <div class="panel-item">
              <span>联系邮箱</span>
              <strong>{{ contact.email || '暂未配置' }}</strong>
            </div>
            <div class="panel-item">
              <span>网站地址</span>
              <strong>{{ seo.site_url || 'http://www.kaiii.top' }}</strong>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section v-if="homeData.top_articles.length" class="section">
      <div class="page-container">
        <div class="section-head">
          <div>
            <p class="section-kicker">Featured</p>
            <h2>置顶推荐</h2>
          </div>
        </div>

        <div class="featured-grid">
          <article
            v-for="(article, index) in homeData.top_articles"
            :key="article.id"
            class="featured-card glass-card"
            :class="{ large: index === 0 }"
            @click="$router.push(`/articles/${article.id}`)"
          >
            <span class="featured-label">{{ index === 0 ? '主推内容' : '精选阅读' }}</span>
            <h3>{{ article.title }}</h3>
            <p>{{ article.summary || '暂无摘要。' }}</p>
            <div class="featured-meta">
              <span>{{ formatDate(article.published_at) }}</span>
              <span>{{ article.view_count || 0 }} 阅读</span>
            </div>
          </article>
        </div>
      </div>
    </section>

    <section ref="latestRef" class="section">
      <div class="page-container">
        <div class="section-head">
          <div>
            <p class="section-kicker">Latest</p>
            <h2>最新文章</h2>
          </div>
          <router-link to="/articles" class="section-link">查看全部</router-link>
        </div>

        <div v-if="homeData.latest_articles.length" class="article-grid">
          <ArticleCard v-for="article in homeData.latest_articles" :key="article.id" :article="article" />
        </div>
        <el-empty v-else description="暂无文章内容" :image-size="120" />
      </div>
    </section>

    <section class="section">
      <div class="page-container section-split">
        <div class="categories-panel glass-card">
          <div class="section-head compact">
            <div>
              <p class="section-kicker">Categories</p>
              <h2>内容栏目</h2>
            </div>
          </div>

          <div class="category-list">
            <button
              v-for="category in homeData.categories"
              :key="category.id"
              type="button"
              class="category-item"
              @click="$router.push(`/categories/${category.id}`)"
            >
              <div>
                <strong>{{ category.name }}</strong>
                <p>{{ category.description || '浏览该栏目下的文章与专题。' }}</p>
              </div>
              <span>{{ category.article_count || 0 }}</span>
            </button>
          </div>
        </div>

        <div class="tutorials-panel glass-card">
          <div class="section-head compact">
            <div>
              <p class="section-kicker">Tutorials</p>
              <h2>推荐教程</h2>
            </div>
            <router-link to="/tutorials" class="section-link">全部教程</router-link>
          </div>

          <div class="tutorial-list">
            <article
              v-for="tutorial in homeData.tutorials"
              :key="tutorial.id"
              class="tutorial-item"
              @click="$router.push(`/tutorials/${tutorial.id}`)"
            >
              <h3>{{ tutorial.title }}</h3>
              <p>{{ tutorial.description || '系统化整理的学习路径。' }}</p>
              <div class="tutorial-meta">
                <span>{{ tutorial.chapter_count || 0 }} 章节</span>
                <span>{{ tutorial.view_count || 0 }} 阅读</span>
                <span>{{ difficultyLabel(tutorial.difficulty) }}</span>
              </div>
            </article>
          </div>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="page-container">
        <div class="section-head">
          <div>
            <p class="section-kicker">Tags</p>
            <h2>热门标签</h2>
          </div>
        </div>

        <div class="tag-cloud glass-card">
          <button
            v-for="tag in homeData.hot_tags"
            :key="tag.id"
            type="button"
            class="tag-pill"
            @click="$router.push(`/search?q=${encodeURIComponent(tag.name)}`)"
          >
            # {{ tag.name }}
            <span>{{ tag.article_count || 0 }}</span>
          </button>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { storeToRefs } from 'pinia'
import { useRouter } from 'vue-router'
import ArticleCard from '../../components/ArticleCard.vue'
import { webApi } from '../../api'
import { useAppStore } from '../../stores/app'

const router = useRouter()
const appStore = useAppStore()
const latestRef = ref(null)

const homeData = reactive({
  top_articles: [],
  latest_articles: [],
  recommend_articles: [],
  categories: [],
  hot_tags: [],
  tutorials: [],
})

const { site, contact, seo, footer } = storeToRefs(appStore)
const footerText = computed(() => footer.value.text || '分享真实经验，保持持续创作。')
const heroTitle = computed(() => `${site.value?.name || 'Kaiii 博客'}，一个持续更新的个人技术博客`)

async function fetchHomeData() {
  const res = await webApi.getHomeData()
  if (res.code === 0 && res.data) {
    Object.assign(homeData, res.data)
  }
}

function scrollToLatest() {
  latestRef.value?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

function formatDate(value) {
  if (!value) return '最近更新'
  return String(value).split(' ')[0]
}

function difficultyLabel(level) {
  return ({
    1: '入门',
    2: '进阶',
    3: '高级',
  })[level] || '教程'
}

onMounted(() => {
  fetchHomeData().catch((error) => {
    console.error('获取首页数据失败:', error)
  })
})
</script>

<style scoped lang="scss">
.home-page {
  padding-bottom: 24px;
}

.hero-section {
  position: relative;
  overflow: hidden;
  padding: 48px 0 28px;
}

.hero-orb {
  position: absolute;
  width: 420px;
  height: 420px;
  border-radius: 50%;
  filter: blur(70px);
  opacity: 0.35;
}

.hero-orb-left {
  top: -120px;
  left: -100px;
  background: rgba(15, 130, 255, 0.28);
}

.hero-orb-right {
  right: -120px;
  bottom: 20px;
  background: rgba(63, 94, 251, 0.24);
}

.hero-grid {
  position: relative;
  display: grid;
  grid-template-columns: 1.2fr 0.8fr;
  gap: 28px;
  align-items: stretch;
}

.hero-copy {
  padding: 36px 0;
}

.hero-kicker,
.section-kicker,
.panel-kicker {
  margin-bottom: 12px;
  text-transform: uppercase;
  letter-spacing: 0.16em;
  font-size: 0.76rem;
  color: var(--color-primary-light);
}

.hero-copy h1 {
  font-size: clamp(2.4rem, 5vw, 4.4rem);
  line-height: 1.05;
  margin-bottom: 18px;
  max-width: 12ch;
}

.hero-desc {
  max-width: 680px;
  color: var(--color-text-secondary);
  line-height: 1.85;
  font-size: 1.02rem;
}

.hero-actions {
  display: flex;
  gap: 14px;
  margin: 30px 0 34px;
}

.hero-btn {
  min-width: 160px;
}

.hero-stats {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 14px;
  max-width: 680px;
}

.stat-card {
  padding: 18px 20px;
  border-radius: 20px;
  border: 1px solid var(--color-border);
  background: var(--color-bg-card);
  strong {
    display: block;
    font-size: 1.8rem;
    margin-bottom: 6px;
  }
  span {
    color: var(--color-text-muted);
    font-size: 0.9rem;
  }
}

.hero-panel {
  padding: 28px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.hero-panel h2 {
  font-size: 1.8rem;
  margin-bottom: 12px;
}

.hero-panel > p {
  color: var(--color-text-secondary);
  line-height: 1.8;
}

.panel-list {
  display: grid;
  gap: 14px;
  margin-top: 22px;
}

.panel-item {
  padding: 16px 18px;
  border-radius: 18px;
  background: var(--color-bg-elevated);
  span {
    display: block;
    margin-bottom: 8px;
    color: var(--color-text-muted);
    font-size: 0.85rem;
  }
}

.section {
  padding: 42px 0;
}

.section-head {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 24px;
  h2 {
    font-size: 1.8rem;
  }
}

.section-head.compact {
  align-items: center;
}

.section-link {
  color: var(--color-primary-light);
}

.featured-grid {
  display: grid;
  grid-template-columns: 1.3fr 1fr 1fr;
  gap: 18px;
}

.featured-card {
  padding: 26px;
  cursor: pointer;
  min-height: 240px;
}

.featured-card.large {
  min-height: 300px;
}

.featured-label {
  display: inline-flex;
  margin-bottom: 18px;
  padding: 6px 12px;
  border-radius: 999px;
  background: var(--color-bg-elevated);
  color: var(--color-primary-light);
  font-size: 0.76rem;
}

.featured-card h3 {
  font-size: 1.2rem;
  margin-bottom: 12px;
}

.featured-card p,
.tutorial-item p,
.category-item p {
  color: var(--color-text-secondary);
  line-height: 1.7;
}

.featured-meta,
.tutorial-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  margin-top: 18px;
  color: var(--color-text-muted);
  font-size: 0.84rem;
}

.article-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 20px;
}

.section-split {
  display: grid;
  grid-template-columns: 0.9fr 1.1fr;
  gap: 20px;
}

.categories-panel,
.tutorials-panel,
.tag-cloud {
  padding: 28px;
}

.category-list,
.tutorial-list {
  display: grid;
  gap: 14px;
}

.category-item,
.tutorial-item {
  width: 100%;
  text-align: left;
  border: 1px solid var(--color-border);
  border-radius: 20px;
  background: var(--color-bg-card);
  padding: 18px 18px 16px;
  cursor: pointer;
}

.category-item {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 14px;
  span {
    min-width: 44px;
    text-align: center;
    padding: 8px 0;
    border-radius: 999px;
    background: var(--color-bg-elevated);
    color: var(--color-primary-light);
    font-weight: 700;
  }
}

.category-item strong,
.tutorial-item h3 {
  display: block;
  margin-bottom: 8px;
  font-size: 1rem;
}

.tag-cloud {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
}

.tag-pill {
  border: 1px solid var(--color-border);
  border-radius: 999px;
  background: var(--color-bg-card);
  color: var(--color-text);
  padding: 10px 14px;
  display: inline-flex;
  align-items: center;
  gap: 10px;
  span {
    display: inline-flex;
    min-width: 28px;
    justify-content: center;
    padding: 4px 8px;
    border-radius: 999px;
    background: var(--color-bg-elevated);
    color: var(--color-primary-light);
    font-size: 0.74rem;
  }
}

@media (max-width: 1024px) {
  .hero-grid,
  .section-split,
  .featured-grid {
    grid-template-columns: 1fr;
  }

  .article-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 768px) {
  .hero-actions {
    flex-direction: column;
    align-items: stretch;
  }

  .hero-stats,
  .article-grid {
    grid-template-columns: 1fr;
  }

  .hero-panel,
  .categories-panel,
  .tutorials-panel,
  .tag-cloud {
    padding: 22px;
  }
}
</style>
