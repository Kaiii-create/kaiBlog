<template>
  <div>
    <!-- 统计卡片 -->
    <el-row :gutter="16" class="stat-row">
      <el-col :xs="12" :sm="12" :md="6" v-for="s in stats" :key="s.label">
        <div
          class="stat-card-gradient"
          :style="{ '--card-color1': s.color1, '--card-color2': s.color2 }"
        >
          <div class="stat-icon">
            <el-icon :size="22"><component :is="s.icon" /></el-icon>
          </div>
          <div class="stat-value">{{ s.value }}</div>
          <div class="stat-label">{{ s.label }}</div>
          <div class="stat-trend" v-if="s.trend">较昨日 {{ s.trend }}</div>
        </div>
      </el-col>
    </el-row>

    <!-- 图表 + 最新动态 -->
    <el-row :gutter="16">
      <el-col :xs="24" :lg="14" class="mb-16">
        <div class="admin-card">
          <div class="card-header">
            <h3>最近文章</h3>
            <el-button text type="primary" @click="$router.push('/admin/articles')">查看全部</el-button>
          </div>
          <el-table :data="dashboard.recent_articles" style="width: 100%" size="small" empty-text="暂无数据">
            <el-table-column prop="title" label="标题" min-width="160" show-overflow-tooltip />
            <el-table-column prop="view_count" label="阅读" width="60" align="center" />
            <el-table-column label="状态" width="70" align="center">
              <template #default="{ row }">
                <el-tag :type="row.status === 1 ? 'success' : 'warning'" size="small" effect="plain">
                  {{ row.status === 1 ? '发布' : '草稿' }}
                </el-tag>
              </template>
            </el-table-column>
            <el-table-column label="时间" width="140" align="center">
              <template #default="{ row }">
                <span class="text-muted">{{ row.published_at?.slice(0, 10) }}</span>
              </template>
            </el-table-column>
          </el-table>
        </div>
      </el-col>

      <el-col :xs="24" :lg="10" class="mb-16">
        <div class="admin-card">
          <div class="card-header">
            <h3>最新动态</h3>
          </div>
          <div class="activity-list">
            <div v-for="item in activities" :key="item.id" class="activity-item">
              <div class="activity-dot" :style="{ background: item.color }" />
              <div class="activity-body">
                <p class="activity-text">{{ item.text }}</p>
                <span class="activity-time">{{ item.time }}</span>
              </div>
            </div>
            <el-empty v-if="!activities.length" description="暂无动态" :image-size="60" />
          </div>
        </div>
      </el-col>
    </el-row>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { adminApi } from '../../api'
import { Document, Edit, User, View } from '@element-plus/icons-vue'
import { ElMessage } from 'element-plus'

const dashboard = reactive({
  article_count: 0, tutorial_count: 0, user_count: 0,
  comment_count: 0, view_count_total: 0,
  recent_articles: [], recent_comments: []
})

const stats = ref([
  { label: '文章总数', value: 0, icon: Document, color1: '#4f6ef7', color2: '#6b8aff', trend: '+12%' },
  { label: '教程总数', value: 0, icon: Edit, color1: '#06b6d4', color2: '#22d3ee', trend: '+8%' },
  { label: '用户总数', value: 0, icon: User, color1: '#10b981', color2: '#34d399', trend: '+15%' },
  { label: '总浏览量', value: '0', icon: View, color1: '#f59e0b', color2: '#fbbf24', trend: '+22%' },
])

const activities = ref([
  { id: 1, text: '发布了新文章「Vue3 Composition API 实战指南」', time: '2小时前', color: '#4f6ef7' },
  { id: 2, text: '有用户提交了新的评论', time: '3小时前', color: '#10b981' },
  { id: 3, text: '教程「ThinkPHP 快速入门」已更新', time: '昨天', color: '#06b6d4' },
  { id: 4, text: '新用户「张三」注册成功', time: '昨天', color: '#f59e0b' },
  { id: 5, text: '系统配置已更新', time: '2天前', color: '#64748b' },
])

onMounted(async () => {
  try {
    const res = await adminApi.getDashboard()
    if (res.code === 0) {
      Object.assign(dashboard, res.data)
      stats.value = [
        { label: '文章总数', value: res.data.article_count || 0, icon: Document, color1: '#4f6ef7', color2: '#6b8aff', trend: '+12%' },
        { label: '教程总数', value: res.data.tutorial_count || 0, icon: Edit, color1: '#06b6d4', color2: '#22d3ee', trend: '+8%' },
        { label: '用户总数', value: res.data.user_count || 0, icon: User, color1: '#10b981', color2: '#34d399', trend: '+15%' },
        { label: '总浏览量', value: formatNumber(res.data.view_count_total) || '0', icon: View, color1: '#f59e0b', color2: '#fbbf24', trend: '+22%' },
      ]
    }
  } catch (e) {
    ElMessage.error('获取数据失败')
  }
})

function formatNumber(n) {
  if (!n) return 0
  if (n >= 10000) return (n / 10000).toFixed(1) + 'w'
  return n
}
</script>

<style scoped lang="scss">
.stat-row { margin-bottom: 16px; }
.mb-16 { margin-bottom: 16px; }

.card-header {
  display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;
  h3 { font-size: 15px; font-weight: 600; }
}

.text-muted { color: var(--color-text-muted); font-size: 13px; }

.activity-list { display: flex; flex-direction: column; }
.activity-item {
  display: flex; gap: 12px; padding: 10px 0;
  border-bottom: 1px solid var(--color-border-light);
  &:last-child { border-bottom: none; }
}
.activity-dot {
  width: 8px; height: 8px; flex-shrink: 0; border-radius: 50%; margin-top: 6px;
}
.activity-body { flex: 1; min-width: 0; }
.activity-text { font-size: 13px; line-height: 1.4; margin-bottom: 2px; }
.activity-time { font-size: 12px; color: var(--color-text-muted); }
</style>
