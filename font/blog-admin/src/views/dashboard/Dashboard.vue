<template>
  <div class="dashboard-page">
    <!-- 顶部蓝色渐变Banner -->
    <div class="dashboard-banner">
      <div class="banner-content">
        <div class="banner-left">
          <h1 class="banner-title">Dashboard</h1>
          <p class="banner-subtitle">欢迎回来，{{ adminStore.nickname || '管理员' }}！今天是 {{ currentDate }}</p>
        </div>
        <div class="banner-right">
          <div class="banner-info-item">
            <span class="info-label">PHP 版本</span>
            <span class="info-value">8.1</span>
          </div>
          <div class="banner-info-item">
            <span class="info-label">当前时间</span>
            <span class="info-value">{{ currentTime }}</span>
          </div>
          <div class="banner-info-item">
            <span class="info-label">运行状态</span>
            <span class="info-value status-ok">正常</span>
          </div>
        </div>
      </div>
      <!-- 装饰图形 -->
      <div class="banner-decoration">
        <svg width="400" height="160" viewBox="0 0 400 160" fill="none">
          <circle cx="320" cy="40" r="80" fill="rgba(255,255,255,0.06)" />
          <circle cx="380" cy="120" r="60" fill="rgba(255,255,255,0.04)" />
          <circle cx="260" cy="130" r="40" fill="rgba(255,255,255,0.05)" />
        </svg>
      </div>
    </div>

    <!-- 统计卡片 -->
    <div class="stat-grid">
      <div class="stat-card" v-for="s in stats" :key="s.label">
        <div class="stat-top">
          <div class="stat-icon-wrap" :style="{ background: s.bgColor }">
            <el-icon :size="22" :style="{ color: s.color }"><component :is="s.icon" /></el-icon>
          </div>
          <div class="stat-trend" :class="{ up: s.trendUp }">
            <el-icon :size="12"><Top v-if="s.trendUp" /><Bottom v-else /></el-icon>
            {{ s.trend }}
          </div>
        </div>
        <div class="stat-value">{{ s.value }}</div>
        <div class="stat-label">{{ s.label }}</div>
      </div>
    </div>

    <!-- 主内容区：图表 + 最新动态 -->
    <div class="content-grid">
      <!-- 左侧：最近文章 -->
      <div class="content-left">
        <div class="panel">
          <div class="panel-header">
            <h3 class="panel-title">
              <span class="panel-dot" style="background: #4f6ef7" />
              最近文章
            </h3>
            <el-button text type="primary" size="small" @click="$router.push('/admin/articles')">查看全部</el-button>
          </div>
          <el-table :data="dashboard.recent_articles" style="width: 100%" size="small" empty-text="暂无数据" :header-cell-style="{ background: '#f8f9fb', fontWeight: 600, fontSize: '13px' }">
            <el-table-column prop="title" label="标题" min-width="160" show-overflow-tooltip />
            <el-table-column prop="view_count" label="阅读" width="60" align="center" />
            <el-table-column label="状态" width="70" align="center">
              <template #default="{ row }">
                <el-tag :type="row.status === 1 ? 'success' : 'warning'" size="small" effect="plain">
                  {{ row.status === 1 ? '发布' : '草稿' }}
                </el-tag>
              </template>
            </el-table-column>
            <el-table-column label="时间" width="110" align="center">
              <template #default="{ row }">
                <span class="text-muted">{{ row.published_at?.slice(0, 10) }}</span>
              </template>
            </el-table-column>
          </el-table>
        </div>
      </div>

      <!-- 右侧：最新动态 -->
      <div class="content-right">
        <div class="panel">
          <div class="panel-header">
            <h3 class="panel-title">
              <span class="panel-dot" style="background: #10b981" />
              最新动态
            </h3>
          </div>
          <div class="activity-list">
            <div v-for="item in activities" :key="item.id" class="activity-item">
              <div class="activity-icon" :style="{ background: item.bgColor }">
                <el-icon :size="14" :style="{ color: item.color }"><component :is="item.icon" /></el-icon>
              </div>
              <div class="activity-body">
                <p class="activity-text">{{ item.text }}</p>
                <span class="activity-time">{{ item.time }}</span>
              </div>
            </div>
            <el-empty v-if="!activities.length" description="暂无动态" :image-size="60" />
          </div>
        </div>

        <!-- 快捷操作 -->
        <div class="panel" style="margin-top: 16px">
          <div class="panel-header">
            <h3 class="panel-title">
              <span class="panel-dot" style="background: #f59e0b" />
              快捷操作
            </h3>
          </div>
          <div class="quick-actions">
            <div class="quick-item" @click="$router.push('/admin/articles/create')">
              <el-icon :size="20" color="#4f6ef7"><EditPen /></el-icon>
              <span>发布文章</span>
            </div>
            <div class="quick-item" @click="$router.push('/admin/tutorials/create')">
              <el-icon :size="20" color="#06b6d4"><Notebook /></el-icon>
              <span>新建教程</span>
            </div>
            <div class="quick-item" @click="$router.push('/admin/categories')">
              <el-icon :size="20" color="#10b981"><FolderOpened /></el-icon>
              <span>管理栏目</span>
            </div>
            <div class="quick-item" @click="$router.push('/admin/settings')">
              <el-icon :size="20" color="#f59e0b"><Setting /></el-icon>
              <span>系统设置</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, onBeforeUnmount } from 'vue'
import { adminApi } from '../../api'
import { useAdminStore } from '../../stores/admin'
import { Document, Edit, User, View, Top, Bottom, EditPen, Notebook, FolderOpened, Setting, Bell, ChatDotSquare } from '@element-plus/icons-vue'
import { ElMessage } from 'element-plus'

const adminStore = useAdminStore()

const dashboard = reactive({
  article_count: 0, tutorial_count: 0, user_count: 0,
  comment_count: 0, view_count_total: 0,
  recent_articles: [], recent_comments: []
})

const currentDate = new Date().toLocaleDateString('zh-CN', { year: 'numeric', month: 'long', day: 'numeric', weekday: 'long' })
const currentTime = ref(new Date().toLocaleTimeString('zh-CN', { hour: '2-digit', minute: '2-digit', second: '2-digit' }))
let timer = null

const stats = ref([
  { label: '文章总数', value: 0, icon: Document, color: '#4f6ef7', bgColor: 'rgba(79,110,247,0.1)', trend: '+12%', trendUp: true },
  { label: '教程总数', value: 0, icon: Edit, color: '#06b6d4', bgColor: 'rgba(6,182,212,0.1)', trend: '+8%', trendUp: true },
  { label: '用户总数', value: 0, icon: User, color: '#10b981', bgColor: 'rgba(16,185,129,0.1)', trend: '+15%', trendUp: true },
  { label: '总浏览量', value: '0', icon: View, color: '#f59e0b', bgColor: 'rgba(245,158,11,0.1)', trend: '+22%', trendUp: true },
])

const activities = ref([
  { id: 1, text: '发布了新文章「Vue3 Composition API 实战指南」', time: '2小时前', color: '#4f6ef7', bgColor: 'rgba(79,110,247,0.1)', icon: Document },
  { id: 2, text: '新用户「张三」注册成功', time: '3小时前', color: '#10b981', bgColor: 'rgba(16,185,129,0.1)', icon: User },
  { id: 3, text: '有用户提交了新的评论', time: '5小时前', color: '#f59e0b', bgColor: 'rgba(245,158,11,0.1)', icon: ChatDotSquare },
  { id: 4, text: '教程「ThinkPHP 快速入门」已更新', time: '昨天', color: '#06b6d4', bgColor: 'rgba(6,182,212,0.1)', icon: Edit },
  { id: 5, text: '系统配置已更新', time: '2天前', color: '#64748b', bgColor: 'rgba(100,116,139,0.1)', icon: Setting },
])

onMounted(async () => {
  // 时钟
  timer = setInterval(() => {
    currentTime.value = new Date().toLocaleTimeString('zh-CN', { hour: '2-digit', minute: '2-digit', second: '2-digit' })
  }, 1000)

  try {
    const res = await adminApi.getDashboard()
    if (res.code === 0) {
      Object.assign(dashboard, res.data)
      stats.value = [
        { label: '文章总数', value: res.data.article_count || 0, icon: Document, color: '#4f6ef7', bgColor: 'rgba(79,110,247,0.1)', trend: '+12%', trendUp: true },
        { label: '教程总数', value: res.data.tutorial_count || 0, icon: Edit, color: '#06b6d4', bgColor: 'rgba(6,182,212,0.1)', trend: '+8%', trendUp: true },
        { label: '用户总数', value: res.data.user_count || 0, icon: User, color: '#10b981', bgColor: 'rgba(16,185,129,0.1)', trend: '+15%', trendUp: true },
        { label: '总浏览量', value: formatNumber(res.data.view_count_total) || '0', icon: View, color: '#f59e0b', bgColor: 'rgba(245,158,11,0.1)', trend: '+22%', trendUp: true },
      ]
    }
  } catch (e) {
    ElMessage.error('获取数据失败')
  }
})

onBeforeUnmount(() => { if (timer) clearInterval(timer) })

function formatNumber(n) {
  if (!n) return 0
  if (n >= 10000) return (n / 10000).toFixed(1) + 'w'
  return n
}
</script>

<style scoped lang="scss">
.dashboard-page {
  padding: 0;
}

// ---- Banner ----
.dashboard-banner {
  position: relative;
  background: linear-gradient(135deg, #4f6ef7 0%, #6366f1 40%, #7c3aed 100%);
  border-radius: 14px;
  padding: 28px 32px;
  margin-bottom: 20px;
  color: #fff;
  overflow: hidden;
  box-shadow: 0 4px 24px rgba(79, 110, 247, 0.25);
}
.banner-content {
  position: relative;
  z-index: 2;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.banner-title {
  font-size: 24px;
  font-weight: 700;
  margin-bottom: 6px;
  letter-spacing: -0.5px;
}
.banner-subtitle {
  font-size: 14px;
  opacity: 0.8;
}
.banner-right {
  display: flex;
  gap: 32px;
}
.banner-info-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  .info-label { font-size: 12px; opacity: 0.7; }
  .info-value { font-size: 16px; font-weight: 600; }
  .status-ok {
    color: #86efac;
    &::before {
      content: '';
      display: inline-block;
      width: 6px; height: 6px;
      background: #86efac;
      border-radius: 50%;
      margin-right: 6px;
      animation: pulse-dot 2s infinite;
    }
  }
}
.banner-decoration {
  position: absolute;
  right: 0;
  top: 0;
  z-index: 1;
  pointer-events: none;
}
@keyframes pulse-dot {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.4; }
}

// ---- 统计卡片 ----
.stat-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
  margin-bottom: 20px;
}
.stat-card {
  background: #fff;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04), 0 1px 2px rgba(0, 0, 0, 0.02);
  transition: all 0.25s;
  cursor: default;
  &:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
    transform: translateY(-2px);
  }
}
.stat-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 14px;
}
.stat-icon-wrap {
  width: 44px; height: 44px;
  display: flex; align-items: center; justify-content: center;
  border-radius: 10px;
}
.stat-trend {
  font-size: 12px;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 2px;
  color: #ef4444;
  &.up { color: #10b981; }
}
.stat-value {
  font-size: 26px;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 2px;
  letter-spacing: -0.5px;
}
.stat-label {
  font-size: 13px;
  color: #94a3b8;
}

// ---- 内容区域 ----
.content-grid {
  display: grid;
  grid-template-columns: 1.4fr 1fr;
  gap: 16px;
}
.content-left, .content-right {
  display: flex;
  flex-direction: column;
}

// ---- Panel ----
.panel {
  background: #fff;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04), 0 1px 2px rgba(0, 0, 0, 0.02);
  transition: box-shadow 0.2s;
  &:hover { box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); }
}
.panel-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 16px;
}
.panel-title {
  font-size: 15px;
  font-weight: 600;
  color: #1e293b;
  display: flex;
  align-items: center;
  gap: 8px;
}
.panel-dot {
  width: 4px;
  height: 16px;
  border-radius: 2px;
}

.text-muted { color: #94a3b8; font-size: 13px; }

// ---- 动态列表 ----
.activity-list { display: flex; flex-direction: column; }
.activity-item {
  display: flex; gap: 12px; padding: 10px 0;
  border-bottom: 1px solid #f0f1f3;
  &:last-child { border-bottom: none; }
}
.activity-icon {
  width: 32px; height: 32px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  border-radius: 8px;
}
.activity-body { flex: 1; min-width: 0; }
.activity-text { font-size: 13px; line-height: 1.5; color: #475569; margin-bottom: 2px; }
.activity-time { font-size: 12px; color: #94a3b8; }

// ---- 快捷操作 ----
.quick-actions {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 12px;
}
.quick-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  padding: 16px 8px;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.2s;
  background: #f8f9fb;
  &:hover {
    background: rgba(79, 110, 247, 0.06);
    transform: translateY(-1px);
  }
  span {
    font-size: 12px;
    color: #64748b;
    font-weight: 500;
  }
}

// ---- 响应式 ----
@media (max-width: 1200px) {
  .content-grid { grid-template-columns: 1fr; }
}
@media (max-width: 900px) {
  .stat-grid { grid-template-columns: repeat(2, 1fr); }
  .banner-right { display: none; }
}
@media (max-width: 600px) {
  .stat-grid { grid-template-columns: 1fr; }
  .quick-actions { grid-template-columns: repeat(2, 1fr); }
}

// ---- 暗色主题适配 ----
html.dark {
  .dashboard-banner {
    box-shadow: 0 4px 24px rgba(79, 110, 247, 0.15);
  }
  .stat-card, .panel {
    background: rgba(255, 255, 255, 0.03);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
    &:hover { box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3); }
  }
  .stat-value { color: #e2e8f0; }
  .panel-title { color: #e2e8f0; }
  .activity-text { color: #94a3b8; }
  .quick-item {
    background: rgba(255, 255, 255, 0.04);
    &:hover { background: rgba(79, 110, 247, 0.1); }
    span { color: #94a3b8; }
  }
}
</style>
