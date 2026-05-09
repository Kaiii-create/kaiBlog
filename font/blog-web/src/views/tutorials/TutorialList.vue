<template>
  <div class="tutorial-list-page">
    <div class="page-container">
      <div class="page-header">
        <h1 class="gradient-text">教程中心</h1>
        <p>系统化学习，从入门到精通</p>
      </div>

      <div class="tutorial-grid" v-if="tutorials.length">
        <div
          v-for="tutorial in tutorials"
          :key="tutorial.id"
          class="tutorial-card glass-card"
          @click="$router.push('/tutorials/' + tutorial.id)"
        >
          <div class="tutorial-cover">
            <el-icon><Reading /></el-icon>
          </div>
          <div class="tutorial-body">
            <div class="tutorial-difficulty" :class="'diff-' + tutorial.difficulty">
              {{ difficultyLabel(tutorial.difficulty) }}
            </div>
            <h3>{{ tutorial.title }}</h3>
            <p>{{ tutorial.description }}</p>
            <div class="tutorial-footer">
              <div class="tutorial-meta">
                <span><el-icon><Collection /></el-icon> {{ tutorial.chapter_count }} 章</span>
                <span><el-icon><View /></el-icon> {{ tutorial.view_count }}</span>
                <span><el-icon><Clock /></el-icon> {{ formatDate(tutorial.published_at) }}</span>
              </div>
              <el-button class="start-btn" size="small">
                开始学习 <el-icon><ArrowRight /></el-icon>
              </el-button>
            </div>
          </div>
        </div>
      </div>

      <el-empty v-else description="暂无教程" :image-size="120" />

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
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Reading, Collection, View, Clock, ArrowRight } from '@element-plus/icons-vue'
import { webApi } from '../../api'

const tutorials = ref([])
const total = ref(0)
const currentPage = ref(1)
const pageSize = ref(6)

async function fetchData() {
  try {
    const res = await webApi.getTutorials({ page: currentPage.value, page_size: pageSize.value })
    if (res.code === 0 && res.data) {
      tutorials.value = res.data.list || []
      total.value = res.data.pagination?.total || 0
    }
  } catch (e) {
    console.error('获取教程列表失败:', e)
  }
}

function handlePageChange(page) {
  currentPage.value = page
  window.scrollTo({ top: 0, behavior: 'smooth' })
  fetchData()
}

function difficultyLabel(d) {
  return ['', '入门', '进阶', '高级'][d] || ''
}

function formatDate(dateStr) {
  if (!dateStr) return ''
  return dateStr.split(' ')[0]
}

onMounted(fetchData)
</script>

<style scoped lang="scss">
.tutorial-list-page {
  padding-bottom: 60px;
}
.tutorial-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 24px;
}
.tutorial-card {
  display: flex;
  overflow: hidden;
  cursor: pointer;
  transition: all 0.3s;
}
.tutorial-cover {
  width: 160px;
  flex-shrink: 0;
  background: linear-gradient(135deg, rgba(59,130,246,0.12), rgba(6,182,212,0.06));
  display: flex;
  align-items: center;
  justify-content: center;
  .el-icon { font-size: 56px; color: rgba(59,130,246,0.35); }
}
.tutorial-body {
  padding: 24px;
  flex: 1;
  display: flex;
  flex-direction: column;
}
.tutorial-difficulty {
  display: inline-block;
  padding: 2px 10px;
  border-radius: 4px;
  font-size: 11px;
  margin-bottom: 12px;
  align-self: flex-start;
}
.diff-1 { background: rgba(16, 185, 129, 0.15); color: #10b981; }
.diff-2 { background: rgba(245, 158, 11, 0.15); color: #f59e0b; }
.diff-3 { background: rgba(239, 68, 68, 0.15); color: #ef4444; }

.tutorial-body h3 {
  font-size: 17px;
  font-weight: 600;
  margin-bottom: 8px;
}
.tutorial-body p {
  font-size: 13px;
  color: var(--color-text-secondary);
  line-height: 1.6;
  flex: 1;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  margin-bottom: 16px;
}
.tutorial-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 12px;
  border-top: 1px solid var(--color-border);
}
.tutorial-meta {
  display: flex;
  gap: 16px;
  font-size: 12px;
  color: var(--color-text-muted);
  .el-icon { font-size: 13px; vertical-align: middle; margin-right: 2px; }
}
.start-btn {
  color: var(--color-primary-light);
  &:hover { color: var(--color-primary); }
}
.pagination-wrap {
  display: flex;
  justify-content: center;
  margin-top: 48px;
}
@media (max-width: 768px) {
  .tutorial-grid { grid-template-columns: 1fr; gap: 16px; }
  .tutorial-card { flex-direction: column; }
  .tutorial-cover { width: 100%; height: 120px; }
  .tutorial-body { padding: 16px; }
  .tutorial-footer { flex-direction: column; gap: 12px; align-items: flex-start; }
  .tutorial-meta { flex-wrap: wrap; gap: 12px; }
}
@media (min-width: 769px) and (max-width: 1024px) {
  .tutorial-grid { grid-template-columns: 1fr; }
}
</style>
