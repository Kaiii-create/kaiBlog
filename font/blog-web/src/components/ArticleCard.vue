<template>
  <div class="article-card glass-card" @click="goDetail">
    <div class="card-cover" v-if="article.cover">
      <img :src="article.cover" :alt="article.title" />
    </div>
    <div class="card-cover card-cover-placeholder" v-else>
      <el-icon><Notebook /></el-icon>
    </div>
    <div class="card-body">
      <div class="card-meta-top">
        <el-tag
          v-if="article.is_top"
          size="small"
          type="danger"
          effect="dark"
          class="top-tag"
        >置顶</el-tag>
        <el-tag
          v-if="article.is_recommend"
          size="small"
          type="warning"
          effect="dark"
          class="recommend-tag"
        >推荐</el-tag>
      </div>
      <h3 class="card-title">{{ article.title }}</h3>
      <p class="card-summary">{{ article.summary }}</p>
      <div class="card-footer">
        <div class="card-meta">
          <span class="meta-item">
            <el-icon><Clock /></el-icon>
            {{ formatDate(article.published_at) }}
          </span>
          <span class="meta-item">
            <el-icon><View /></el-icon>
            {{ article.view_count }}
          </span>
          <span class="meta-item" v-if="article.like_count !== undefined">
            <el-icon><StarFilled /></el-icon>
            {{ article.like_count }}
          </span>
        </div>
        <div class="card-tags" v-if="tagsList.length">
          <el-tag
            v-for="tag in tagsList"
            :key="getTagId(tag)"
            size="small"
            :color="tag.color || '#3b82f6'"
            effect="dark"
          >{{ getTagName(tag) }}</el-tag>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Notebook, Clock, View, StarFilled } from '@element-plus/icons-vue'
import { useRouter } from 'vue-router'

const props = defineProps({
  article: { type: Object, required: true }
})

const router = useRouter()

// 将 tags 统一转为对象数组（兼容 tags=[{id,name,color}] 或 tags=[1,2,3]）
const tagsList = computed(() => {
  const tags = props.article.tags
  if (!tags || !tags.length) return []
  return tags.map(t => typeof t === 'object' ? t : { id: t, name: t })
})

function getTagId(tag) {
  return tag.id ?? tag
}

function getTagName(tag) {
  if (tag.name) return tag.name
  if (typeof tag === 'string') return tag
  return `标签${tag.id}`
}

function goDetail() {
  router.push(`/articles/${props.article.id}`)
}

function formatDate(dateStr) {
  if (!dateStr) return ''
  return dateStr.split(' ')[0]
}
</script>

<style scoped lang="scss">
.article-card {
  cursor: pointer;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}
.card-cover {
  width: 100%;
  height: 180px;
  overflow: hidden;
  img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
  }
  &:hover img {
    transform: scale(1.05);
  }
}
.card-cover-placeholder {
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, rgba(59,130,246,0.1), rgba(6,182,212,0.05));
  .el-icon {
    font-size: 48px;
    color: rgba(59,130,246,0.3);
  }
}
.card-body {
  padding: 20px;
  flex: 1;
  display: flex;
  flex-direction: column;
}
.card-meta-top {
  display: flex;
  gap: 6px;
  margin-bottom: 10px;
}
.top-tag {
  background: linear-gradient(135deg, #ef4444, #dc2626) !important;
  border: none;
}
.recommend-tag {
  background: linear-gradient(135deg, #f59e0b, #d97706) !important;
  border: none;
}
.card-title {
  font-size: 16px;
  font-weight: 600;
  color: var(--color-text);
  line-height: 1.5;
  margin-bottom: 8px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.card-summary {
  font-size: 13px;
  color: var(--color-text-secondary);
  line-height: 1.6;
  margin-bottom: 16px;
  flex: 1;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.card-footer {
  border-top: 1px solid var(--color-border);
  padding-top: 12px;
}
.card-meta {
  display: flex;
  gap: 16px;
  margin-bottom: 10px;
}
.meta-item {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 12px;
  color: var(--color-text-muted);
  .el-icon { font-size: 13px; }
}
.card-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}
</style>
