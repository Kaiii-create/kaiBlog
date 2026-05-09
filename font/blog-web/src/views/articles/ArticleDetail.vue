<template>
  <div class="article-detail-page">
    <div class="page-container">
      <!-- 加载骨架 -->
      <template v-if="loading">
        <div class="glass-card detail-skeleton">
          <el-skeleton animated>
            <template #template>
              <el-skeleton-item variant="h1" style="width:70%;height:36px;margin-bottom:16px;" />
              <el-skeleton-item variant="p" style="width:40%;margin-bottom:32px;" />
              <el-skeleton-item variant="text" v-for="n in 6" :key="n" style="margin-bottom:12px;" />
            </template>
          </el-skeleton>
        </div>
      </template>

      <!-- 文章内容 -->
      <template v-if="article && !loading">
        <div class="detail-header glass-card">
          <div class="detail-breadcrumb">
            <router-link to="/"><el-icon><House /></el-icon> 首页</router-link>
            <el-icon><ArrowRight /></el-icon>
            <router-link :to="category ? '/categories/' + category.id : '/articles'">{{ category?.name || '文章' }}</router-link>
            <el-icon><ArrowRight /></el-icon>
            <span class="current">{{ article.title }}</span>
          </div>
          <h1 class="detail-title">{{ article.title }}</h1>
          <div class="detail-meta">
            <span><el-icon><User /></el-icon> {{ article.author || '站长' }}</span>
            <span><el-icon><Clock /></el-icon> {{ formatDate(article.published_at) }}</span>
            <span><el-icon><View /></el-icon> {{ article.view_count }} 阅读</span>
            <span><el-icon><StarFilled /></el-icon> {{ article.like_count }} 点赞</span>
            <span><el-icon><ChatDotSquare /></el-icon> {{ article.comment_count || 0 }} 评论</span>
          </div>
          <div class="detail-tags" v-if="tagsList.length">
            <el-tag
              v-for="tag in tagsList"
              :key="getTagId(tag)"
              :color="tag.color || '#3b82f6'"
              effect="dark"
              size="small"
            >{{ getTagName(tag) }}</el-tag>
          </div>
        </div>

        <div class="detail-content glass-card" v-html="article.content"></div>

        <!-- 点赞 / 收藏 操作栏 -->
        <div class="detail-actions glass-card">
          <el-button
            :type="isLiked ? 'primary' : 'default'"
            :class="{ 'action-active': isLiked }"
            @click="handleLike"
            :loading="likeLoading"
          >
            <el-icon><StarFilled /></el-icon>
            {{ isLiked ? '已点赞' : '点赞' }} ({{ article.like_count }})
          </el-button>
          <el-button
            :type="isFavorited ? 'warning' : 'default'"
            :class="{ 'action-active': isFavorited }"
            @click="handleFavorite"
            :loading="favLoading"
          >
            <el-icon><Star /></el-icon>
            {{ isFavorited ? '已收藏' : '收藏' }}
          </el-button>
        </div>

        <!-- 相关文章 -->
        <div class="related-section" v-if="relatedArticles.length">
          <h2 class="section-title gradient-text">相关文章</h2>
          <div class="related-grid">
            <div
              v-for="item in relatedArticles"
              :key="item.id"
              class="related-card glass-card"
              @click="$router.push('/articles/' + item.id)"
            >
              <h3>{{ item.title }}</h3>
              <p>{{ item.summary }}</p>
              <div class="related-meta">
                <span><el-icon><View /></el-icon> {{ item.view_count }}</span>
                <span>{{ formatDate(item.published_at) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- 评论区 -->
        <div class="comment-section">
          <h2 class="section-title gradient-text">
            评论 ({{ commentTotal }})
          </h2>

          <!-- 评论输入框 -->
          <div class="comment-form glass-card" v-if="userStore.isLoggedIn">
            <div class="comment-form-header">
              <el-avatar :size="32" :icon="UserFilled" />
              <span class="comment-form-nick">{{ userStore.nickname }}</span>
            </div>
            <el-input
              v-model="commentContent"
              type="textarea"
              :rows="3"
              placeholder="写下你的评论..."
              maxlength="500"
              show-word-limit
            />
            <div class="comment-form-actions">
              <el-button
                class="gradient-btn"
                size="small"
                :loading="commentLoading"
                @click="handlePostComment"
                :disabled="!commentContent.trim()"
              >发表评论</el-button>
            </div>
          </div>
          <div class="comment-login-tip glass-card" v-else>
            <el-icon><ChatDotSquare /></el-icon>
            <span>请 <router-link to="/login">登录</router-link> 后发表评论</span>
          </div>

          <!-- 评论列表 -->
          <div class="comment-list" v-if="comments.length">
            <div
              v-for="comment in comments"
              :key="comment.id"
              class="comment-item glass-card"
            >
              <div class="comment-header">
                <el-avatar :size="36" :icon="UserFilled" :src="comment.user?.avatar" />
                <div class="comment-user">
                  <span class="comment-nickname">{{ comment.user?.nickname || '匿名用户' }}</span>
                  <span class="comment-time">{{ formatDate(comment.created_at) }}</span>
                </div>
              </div>
              <p class="comment-content">{{ comment.content }}</p>
            </div>
          </div>

          <el-empty v-else-if="!loadingComments" description="暂无评论，来发表第一条吧" :image-size="80" style="margin-top:20px" />

          <el-skeleton :loading="loadingComments" animated :count="3" style="margin-top:20px">
            <div v-for="n in 3" :key="n" class="glass-card" style="padding:16px;margin-bottom:12px;">
              <el-skeleton-item variant="circle" style="width:36px;height:36px;display:inline-block" />
              <el-skeleton-item variant="p" style="width:30%;margin-top:10px" />
              <el-skeleton-item variant="text" style="margin-top:10px" />
            </div>
          </el-skeleton>
        </div>
      </template>

      <el-empty v-if="!loading && !article" description="文章不存在" :image-size="120" />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import {
  House, ArrowRight, User, Clock, View, StarFilled,
  ChatDotSquare, Star
} from '@element-plus/icons-vue'
import { ElMessage } from 'element-plus'
import { webApi, userApi } from '../../api'
import { useUserStore } from '../../stores/user'

const route = useRoute()
const router = useRouter()
const userStore = useUserStore()

const article = ref(null)
const category = ref(null)
const relatedArticles = ref([])
const loading = ref(true)
const likeLoading = ref(false)
const favLoading = ref(false)
const isLiked = ref(false)
const isFavorited = ref(false)

// 评论
const comments = ref([])
const commentTotal = ref(0)
const commentContent = ref('')
const commentLoading = ref(false)
const loadingComments = ref(false)

// 标签统一处理
const tagsList = computed(() => {
  if (!article.value?.tags) return []
  return article.value.tags.map(t => typeof t === 'object' ? t : { id: t, name: t })
})

function getTagId(tag) { return tag.id ?? tag }
function getTagName(tag) { return tag.name || `标签${tag.id}` }

onMounted(async () => {
  const id = route.params.id
  await fetchArticle(id)
  await fetchComments(id)
})

async function fetchArticle(id) {
  loading.value = true
  try {
    const res = await webApi.getArticleDetail(id)
    if (res.code === 0 && res.data) {
      const data = res.data
      article.value = data.article || data
      relatedArticles.value = data.related || []
      // 获取分类信息
      if (article.value?.category_id) {
        const catRes = await webApi.getCategories()
        if (catRes.code === 0) {
          const catList = catRes.data || []
          category.value = Array.isArray(catList) ? catList.find(c => c.id == article.value.category_id) : null
        }
      }
    }
  } catch (e) {
    console.error('获取文章详情失败:', e)
    ElMessage.error(e.message || '获取文章详情失败')
  } finally {
    loading.value = false
  }
}

async function fetchComments(targetId) {
  loadingComments.value = true
  try {
    const res = await webApi.getComments('article', targetId)
    if (res.code === 0) {
      const data = res.data
      if (Array.isArray(data)) {
        comments.value = data
        commentTotal.value = data.length
      } else if (data?.list) {
        comments.value = data.list
        commentTotal.value = data.pagination?.total || data.list.length
      }
    }
  } catch (e) {
    // 评论接口可能未实现，静默处理
    console.warn('获取评论失败:', e)
  } finally {
    loadingComments.value = false
  }
}

async function handleLike() {
  if (!userStore.isLoggedIn) {
    ElMessage.info('请先登录')
    router.push('/login?redirect=' + route.fullPath)
    return
  }
  likeLoading.value = true
  try {
    const res = await userApi.toggleLike('article', article.value.id)
    if (res.code === 0) {
      isLiked.value = !isLiked.value
      article.value.like_count = (article.value.like_count || 0) + (isLiked.value ? 1 : -1)
      ElMessage.success(isLiked.value ? '点赞成功' : '已取消点赞')
    } else {
      ElMessage.error(res.message || '操作失败')
    }
  } catch (e) {
    ElMessage.error(e.message || '操作失败')
  } finally {
    likeLoading.value = false
  }
}

async function handleFavorite() {
  if (!userStore.isLoggedIn) {
    ElMessage.info('请先登录')
    router.push('/login?redirect=' + route.fullPath)
    return
  }
  favLoading.value = true
  try {
    const res = await userApi.toggleFavorite('article', article.value.id)
    if (res.code === 0) {
      isFavorited.value = !isFavorited.value
      ElMessage.success(isFavorited.value ? '收藏成功' : '已取消收藏')
    } else {
      ElMessage.error(res.message || '操作失败')
    }
  } catch (e) {
    ElMessage.error(e.message || '操作失败')
  } finally {
    favLoading.value = false
  }
}

async function handlePostComment() {
  if (!commentContent.value.trim()) return
  commentLoading.value = true
  try {
    const res = await userApi.postComment({
      target_type: 'article',
      target_id: article.value.id,
      content: commentContent.value.trim()
    })
    if (res.code === 0) {
      ElMessage.success('评论发表成功')
      commentContent.value = ''
      await fetchComments(article.value.id)
    } else {
      ElMessage.error(res.message || '评论失败')
    }
  } catch (e) {
    ElMessage.error(e.message || '评论失败，请重试')
  } finally {
    commentLoading.value = false
  }
}

function formatDate(dateStr) {
  if (!dateStr) return ''
  // 如果是完整 datetime，只显示日期
  return dateStr.includes(' ') ? dateStr : dateStr
}
</script>

<style scoped lang="scss">
.article-detail-page {
  padding: 24px 0 60px;
}
.detail-skeleton {
  padding: 40px;
}
.detail-header {
  padding: 32px 36px;
  margin-bottom: 24px;
}
.detail-breadcrumb {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  margin-bottom: 20px;
  a { color: var(--color-text-muted); &:hover { color: var(--color-primary-light); } }
  .current { color: var(--color-text-secondary); }
  .el-icon { font-size: 13px; color: var(--color-text-muted); }
}
.detail-title {
  font-size: 2rem;
  font-weight: 700;
  line-height: 1.4;
  margin-bottom: 16px;
}
.detail-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
  font-size: 13px;
  color: var(--color-text-secondary);
  margin-bottom: 16px;
  .el-icon { font-size: 14px; vertical-align: middle; margin-right: 4px; }
}
.detail-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}
.detail-content {
  padding: 40px 36px;
  line-height: 1.8;
  font-size: 15px;
  color: var(--color-text);
  :deep(h2) { font-size: 1.5rem; font-weight: 700; margin: 28px 0 16px; }
  :deep(h3) { font-size: 1.2rem; font-weight: 600; margin: 24px 0 12px; }
  :deep(p) { margin-bottom: 16px; }
  :deep(ul), :deep(ol) { padding-left: 24px; margin-bottom: 16px; }
  :deep(li) { margin-bottom: 8px; }
  :deep(code) { background: rgba(59,130,246,0.1); padding: 2px 8px; border-radius: 4px; font-size: 13px; color: var(--color-primary-light); }
  :deep(pre) { background: rgba(0,0,0,0.3); padding: 20px; border-radius: 12px; overflow-x: auto; margin-bottom: 16px; }
  :deep(blockquote) { border-left: 3px solid var(--color-primary); padding-left: 16px; margin: 16px 0; color: var(--color-text-secondary); }
}
.detail-actions {
  padding: 20px 36px;
  margin: 24px 0;
  display: flex;
  gap: 16px;
  justify-content: center;
}
.action-active {
  &.el-button--primary { background: var(--gradient-primary); border: none; }
  &.el-button--warning { background: linear-gradient(135deg, #f59e0b, #d97706); border: none; color: #fff; }
}

/* 相关文章 */
.related-section {
  margin-top: 40px;
}
.section-title {
  font-size: 1.4rem;
  font-weight: 700;
  margin-bottom: 20px;
}
.related-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
}
.related-card {
  padding: 20px;
  cursor: pointer;
  h3 { font-size: 14px; font-weight: 600; margin-bottom: 8px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
  p { font-size: 13px; color: var(--color-text-secondary); margin-bottom: 12px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
}
.related-meta {
  display: flex; gap: 16px; font-size: 12px; color: var(--color-text-muted);
  .el-icon { font-size: 13px; vertical-align: middle; }
}

/* 评论 */
.comment-section {
  margin-top: 40px;
}
.comment-form {
  padding: 20px;
  margin-bottom: 24px;
}
.comment-form-header {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 12px;
}
.comment-form-nick {
  font-size: 13px;
  font-weight: 600;
  color: var(--color-text);
}
.comment-form {
  :deep(.el-textarea__inner) {
    background: rgba(255,255,255,0.04);
    border: 1px solid var(--color-border);
    border-radius: 10px;
    box-shadow: none;
    color: var(--color-text);
    &:hover { border-color: var(--color-border-hover); }
    &:focus { border-color: var(--color-primary); }
  }
}
.comment-form-actions {
  display: flex;
  justify-content: flex-end;
  margin-top: 12px;
}
.comment-login-tip {
  padding: 24px;
  text-align: center;
  color: var(--color-text-secondary);
  font-size: 14px;
  margin-bottom: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  a { color: var(--color-primary-light); font-weight: 600; }
}
.comment-list {
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
  padding-left: 48px;
}

@media (max-width: 768px) {
  .article-detail-page { padding: 16px 0 40px; }
  .detail-header { padding: 20px 16px; }
  .detail-title { font-size: 1.4rem; }
  .detail-meta { gap: 12px; font-size: 12px; }
  .detail-content { padding: 20px 16px; font-size: 14px; }
  .detail-actions { padding: 16px 20px; flex-direction: column; align-items: center; }
  .detail-breadcrumb { flex-wrap: wrap; }
  .related-grid { grid-template-columns: 1fr; }
  .comment-section .section-title { font-size: 1.2rem; }
  .comment-item { padding: 16px; }
  .comment-content { padding-left: 0; margin-top: 8px; }
}
@media (min-width: 769px) and (max-width: 1024px) {
  .related-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>
