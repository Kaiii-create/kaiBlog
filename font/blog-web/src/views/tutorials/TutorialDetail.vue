<template>
  <div class="tutorial-detail-page">
    <div class="page-container">
      <!-- 教程信息头 -->
      <div class="tutorial-header glass-card">
        <div class="header-icon">
          <el-icon><Reading /></el-icon>
        </div>
        <div class="header-info">
          <div class="header-meta-top">
            <span class="difficulty-badge" :class="'diff-' + (tutorial.difficulty || 1)">
              {{ difficultyLabel(tutorial.difficulty) }}
            </span>
            <span v-if="tutorial.is_recommend" class="recommend-badge">推荐</span>
          </div>
          <h1>{{ tutorial.title }}</h1>
          <p>{{ tutorial.description }}</p>
          <div class="header-stats">
            <span><el-icon><Collection /></el-icon> {{ tutorial.chapter_count || chapters.length }} 章节</span>
            <span><el-icon><View /></el-icon> {{ tutorial.view_count }} 次浏览</span>
            <span><el-icon><Clock /></el-icon> {{ formatDate(tutorial.published_at) }}</span>
          </div>
          <el-button class="gradient-btn start-btn" @click="startLearning">
            <el-icon><VideoPlay /></el-icon> 开始学习
          </el-button>
        </div>
      </div>

      <!-- 章节目录 -->
      <div class="chapter-section">
        <h2 class="section-title gradient-text">课程目录</h2>
        <div class="chapter-list">
          <div
            v-for="(chapter, idx) in chapters"
            :key="chapter.id"
            class="chapter-item glass-card"
            @click="$router.push(`/tutorials/${tutorial.id}/chapter/${chapter.id}`)"
          >
            <div class="chapter-index">{{ String(idx + 1).padStart(2, '0') }}</div>
            <div class="chapter-info">
              <h3>{{ chapter.title }}</h3>
              <p>{{ chapter.summary || '暂无简介' }}</p>
            </div>
            <div class="chapter-meta">
              <span><el-icon><View /></el-icon> {{ chapter.view_count || 0 }}</span>
            </div>
            <div class="chapter-action">
              <el-icon><ArrowRight /></el-icon>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { Reading, Collection, View, Clock, VideoPlay, ArrowRight } from '@element-plus/icons-vue'
import { webApi } from '../../api'

const route = useRoute()
const router = useRouter()

const tutorial = ref({})
const chapters = ref([])

onMounted(async () => {
  const id = route.params.id
  try {
    const res = await webApi.getTutorialDetail(id)
    if (res.code === 0 && res.data) {
      tutorial.value = res.data.tutorial || {}
      chapters.value = res.data.chapters || []
    }
  } catch (e) {
    console.error('获取教程详情失败:', e)
  }
})

function startLearning() {
  if (chapters.value.length) {
    router.push(`/tutorials/${tutorial.value.id}/chapter/${chapters.value[0].id}`)
  }
}

function difficultyLabel(d) {
  return ['', '入门', '进阶', '高级'][d] || '入门'
}

function formatDate(dateStr) {
  if (!dateStr) return ''
  return dateStr.split(' ')[0]
}
</script>

<style scoped lang="scss">
.tutorial-detail-page {
  padding: 24px 0 60px;
}
.tutorial-header {
  padding: 36px;
  display: flex;
  gap: 28px;
  margin-bottom: 40px;
}
.header-icon {
  width: 80px;
  height: 80px;
  flex-shrink: 0;
  background: linear-gradient(135deg, rgba(59,130,246,0.15), rgba(6,182,212,0.08));
  border-radius: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  .el-icon { font-size: 38px; color: var(--color-primary-light); }
}
.header-info {
  flex: 1;
  h1 { font-size: 1.6rem; font-weight: 700; margin-bottom: 10px; }
  p { font-size: 14px; color: var(--color-text-secondary); margin-bottom: 16px; line-height: 1.6; }
}
.header-meta-top {
  display: flex;
  gap: 8px;
  margin-bottom: 12px;
}
.difficulty-badge {
  padding: 2px 10px;
  border-radius: 4px;
  font-size: 11px;
}
.diff-1 { background: rgba(16, 185, 129, 0.15); color: #10b981; }
.diff-2 { background: rgba(245, 158, 11, 0.15); color: #f59e0b; }
.diff-3 { background: rgba(239, 68, 68, 0.15); color: #ef4444; }
.recommend-badge {
  padding: 2px 10px;
  border-radius: 4px;
  font-size: 11px;
  background: linear-gradient(135deg, #f59e0b, #d97706);
  color: #fff;
}
.header-stats {
  display: flex;
  gap: 20px;
  font-size: 13px;
  color: var(--color-text-muted);
  margin-bottom: 20px;
  .el-icon { font-size: 14px; vertical-align: middle; margin-right: 4px; }
}
.start-btn {
  padding: 10px 24px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

/* 章节 */
.section-title {
  font-size: 1.4rem;
  font-weight: 700;
  margin-bottom: 20px;
}
.chapter-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.chapter-item {
  display: flex;
  align-items: center;
  gap: 20px;
  padding: 20px 24px;
  cursor: pointer;
  transition: all 0.3s;
  &:hover {
    border-color: var(--color-primary);
    .chapter-action .el-icon { color: var(--color-primary-light); transform: translateX(4px); }
  }
}
.chapter-index {
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, rgba(59,130,246,0.12), rgba(6,182,212,0.06));
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  font-weight: 700;
  color: var(--color-primary-light);
  flex-shrink: 0;
}
.chapter-info {
  flex: 1;
  h3 { font-size: 15px; font-weight: 600; margin-bottom: 4px; }
  p { font-size: 13px; color: var(--color-text-secondary); }
}
.chapter-meta {
  font-size: 12px; color: var(--color-text-muted);
  .el-icon { vertical-align: middle; margin-right: 2px; }
}
.chapter-action {
  .el-icon { font-size: 18px; color: var(--color-text-muted); transition: all 0.3s; }
}

@media (max-width: 768px) {
  .tutorial-header { flex-direction: column; padding: 24px; }
  .header-icon { width: 60px; height: 60px; .el-icon { font-size: 28px; } }
  .header-info h1 { font-size: 1.3rem; }
  .header-stats { flex-wrap: wrap; gap: 12px; }
  .chapter-item { padding: 16px; gap: 12px; }
  .chapter-index { width: 32px; height: 32px; font-size: 12px; }
}
</style>
