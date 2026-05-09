<template>
  <article class="article-card glass-card" @click="goDetail">
    <div class="card-cover" :class="{ empty: !article.cover }">
      <img v-if="article.cover" :src="article.cover" :alt="article.title" />
      <div v-else class="cover-fallback">
        <span>{{ firstLetter }}</span>
      </div>
    </div>

    <div class="card-body">
      <div class="card-flags">
        <span v-if="Number(article.is_top) === 1" class="flag danger">置顶</span>
        <span v-if="Number(article.is_recommend) === 1" class="flag warm">推荐</span>
      </div>

      <h3 class="card-title">{{ article.title }}</h3>
      <p class="card-summary">{{ article.summary || '暂无摘要。' }}</p>

      <div class="card-footer">
        <div class="meta-row">
          <span>{{ formatDate(article.published_at) }}</span>
          <span>{{ article.view_count || 0 }} 次阅读</span>
          <span v-if="typeof article.like_count !== 'undefined'">{{ article.like_count }} 喜欢</span>
        </div>

        <div v-if="tags.length" class="tag-row">
          <span v-for="tag in tags" :key="tag.id || tag.name" class="tag-chip">
            {{ tag.name || tag }}
          </span>
        </div>
      </div>
    </div>
  </article>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'

const props = defineProps({
  article: {
    type: Object,
    required: true,
  },
})

const router = useRouter()

const firstLetter = computed(() => (props.article.title || 'A').slice(0, 1).toUpperCase())
const tags = computed(() => Array.isArray(props.article.tags) ? props.article.tags.slice(0, 3) : [])

function goDetail() {
  router.push(`/articles/${props.article.id}`)
}

function formatDate(value) {
  if (!value) return '最近更新'
  return String(value).split(' ')[0]
}
</script>

<style scoped lang="scss">
.article-card {
  overflow: hidden;
  cursor: pointer;
  display: flex;
  flex-direction: column;
}

.card-cover {
  height: 190px;
  background: linear-gradient(135deg, rgba(15, 130, 255, 0.18), rgba(48, 207, 208, 0.08));
  img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }
}

.cover-fallback {
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  span {
    font-size: 3rem;
    font-weight: 800;
    color: rgba(255, 255, 255, 0.9);
  }
}

.card-body {
  padding: 22px;
  display: flex;
  flex-direction: column;
  gap: 14px;
  flex: 1;
}

.card-flags {
  display: flex;
  gap: 8px;
}

.flag {
  display: inline-flex;
  align-items: center;
  padding: 5px 10px;
  border-radius: 999px;
  font-size: 0.76rem;
  font-weight: 600;
  color: #fff;
}

.danger {
  background: linear-gradient(135deg, #f45b69, #d7263d);
}

.warm {
  background: linear-gradient(135deg, #ffb347, #ff7b54);
}

.card-title {
  font-size: 1.08rem;
  line-height: 1.45;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.card-summary {
  color: var(--color-text-secondary);
  line-height: 1.7;
  flex: 1;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.card-footer {
  padding-top: 14px;
  border-top: 1px solid var(--color-border);
}

.meta-row {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  font-size: 0.82rem;
  color: var(--color-text-muted);
}

.tag-row {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 12px;
}

.tag-chip {
  padding: 5px 10px;
  border-radius: 999px;
  background: var(--color-bg-elevated);
  color: var(--color-primary-light);
  font-size: 0.76rem;
}
</style>
