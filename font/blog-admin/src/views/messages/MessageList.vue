<template>
  <div>
    <div class="page-header">
      <div>
        <h2 class="page-title">消息管理</h2>
        <p class="page-subtitle">查看和管理系统通知消息</p>
      </div>
      <div class="header-actions">
        <el-button plain size="default" @click="markAll">
          <el-icon><Select /></el-icon> 全部已读
        </el-button>
      </div>
    </div>

    <div class="admin-card">
      <div class="filter-bar">
        <el-select v-model="filterType" placeholder="消息类型" clearable style="width:160px" @change="fetchList">
          <el-option label="全部" value="" />
          <el-option label="评论通知" value="comment" />
          <el-option label="系统通知" value="system" />
          <el-option label="用户通知" value="user" />
        </el-select>
        <el-select v-model="filterStatus" placeholder="阅读状态" clearable style="width:140px" @change="fetchList">
          <el-option label="全部" value="" />
          <el-option label="未读" value="unread" />
          <el-option label="已读" value="read" />
        </el-select>
      </div>

      <el-table :data="list" v-loading="loading" style="width:100%" empty-text="暂无消息">
        <el-table-column label="内容" min-width="300">
          <template #default="{ row }">
            <div class="msg-cell">
              <span class="msg-dot" :class="{ unread: row.is_read === 0 || row.is_read === false }" />
              <span :class="{ 'msg-unread': row.is_read === 0 || row.is_read === false }">{{ row.content }}</span>
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="type" label="类型" width="100" align="center">
          <template #default="{ row }">
            <el-tag :type="typeTag(row.type)" size="small" effect="plain">{{ typeLabel(row.type) }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="时间" width="170" align="center">
          <template #default="{ row }">
            <span class="text-muted">{{ row.created_at }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="160" align="center" fixed="right">
          <template #default="{ row }">
            <el-button v-if="row.is_read === 0 || row.is_read === false" link type="primary" size="small" @click="markRead(row)">标为已读</el-button>
            <el-button link type="danger" size="small" @click="handleDelete(row)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>

      <div class="pagination-wrap" v-if="pagination.total > 0">
        <el-pagination
          v-model:page="pagination.page"
          :page-size="pagination.page_size"
          :total="pagination.total"
          layout="prev, pager, next, total"
          background
          @current-change="fetchList"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { adminApi } from '../../api'
import { Select } from '@element-plus/icons-vue'
import { ElMessage, ElMessageBox } from 'element-plus'

const loading = ref(false)
const list = ref([])
const filterType = ref('')
const filterStatus = ref('')
const pagination = reactive({ page: 1, page_size: 15, total: 0 })

onMounted(() => fetchList())

async function fetchList() {
  loading.value = true
  try {
    const params = { page: pagination.page, page_size: pagination.page_size }
    if (filterType.value) params.type = filterType.value
    if (filterStatus.value) params.is_read = filterStatus.value === 'unread' ? 0 : 1
    const res = await adminApi.getMessages(params)
    if (res.code === 0) {
      list.value = res.data.list || []
      Object.assign(pagination, res.data.pagination || { page: 1, total: 0 })
    }
  } catch (e) {
    ElMessage.error('加载消息失败')
  } finally {
    loading.value = false
  }
}

async function markRead(row) {
  try {
    const res = await adminApi.markMessageRead(row.id)
    if (res.code === 0) {
      row.is_read = 1
      ElMessage.success('已标为已读')
    }
  } catch (e) {
    ElMessage.error('操作失败')
  }
}

async function markAll() {
  try {
    const res = await adminApi.markAllRead()
    if (res.code === 0) {
      ElMessage.success('全部已读')
      fetchList()
    }
  } catch (e) {
    ElMessage.error('操作失败')
  }
}

function handleDelete(row) {
  ElMessageBox.confirm('确定删除该消息吗？', '确认', {
    type: 'warning', confirmButtonText: '确定', cancelButtonText: '取消'
  }).then(async () => {
    try {
      const res = await adminApi.deleteMessage(row.id)
      if (res.code === 0) { ElMessage.success('已删除'); fetchList() }
    } catch (e) { ElMessage.error('删除失败') }
  }).catch(() => {})
}

function typeTag(t) {
  if (t === 'comment') return 'primary'
  if (t === 'system') return 'warning'
  if (t === 'user') return 'success'
  return 'info'
}
function typeLabel(t) {
  if (t === 'comment') return '评论'
  if (t === 'system') return '系统'
  if (t === 'user') return '用户'
  return t
}
</script>

<style scoped lang="scss">
.page-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 20px; }
.page-title { font-size: 20px; font-weight: 700; margin-bottom: 4px; }
.page-subtitle { font-size: 13px; color: var(--color-text-muted); }
.header-actions { display: flex; gap: 8px; }

.filter-bar { display: flex; gap: 12px; margin-bottom: 16px; }

.msg-cell {
  display: flex; align-items: center; gap: 10px;
}
.msg-dot {
  width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0;
  background: var(--color-border);
  &.unread { background: var(--color-primary); }
}
.msg-unread { font-weight: 600; }

.text-muted { color: var(--color-text-muted); font-size: 13px; }
.pagination-wrap { display: flex; justify-content: flex-end; padding-top: 16px; }
</style>
