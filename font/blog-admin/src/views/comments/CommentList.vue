<template>
  <div class="page-container">
    <div class="page-header">
      <div>
        <h2 class="page-title">评论管理</h2>
        <p class="page-subtitle">审核和管理所有评论</p>
      </div>
    </div>

    <!-- 筛选 -->
    <div class="admin-card filter-card">
      <el-form :model="filters" inline>
        <el-form-item label="状态">
          <el-select v-model="filters.status" placeholder="全部状态" clearable @change="search" style="width: 140px">
            <el-option label="待审核" :value="0" />
            <el-option label="已通过" :value="1" />
            <el-option label="已拒绝" :value="2" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="search">搜索</el-button>
          <el-button @click="resetFilters">重置</el-button>
        </el-form-item>
      </el-form>
    </div>

    <!-- 表格 -->
    <div class="admin-card">
      <el-table :data="list" stripe style="width: 100%" v-loading="loading" empty-text="暂无评论数据">
        <el-table-column type="index" label="#" width="55" align="center" />
        <el-table-column prop="content" label="评论内容" min-width="280" show-overflow-tooltip>
          <template #default="{ row }">
            <div class="comment-text">{{ row.content }}</div>
          </template>
        </el-table-column>
        <el-table-column label="评论用户" width="130" align="center">
          <template #default="{ row }">
            <span class="text-muted">{{ row.user?.nickname || '匿名' }}</span>
          </template>
        </el-table-column>
        <el-table-column label="评论对象" width="120" align="center">
          <template #default="{ row }">
            <el-tag size="small" effect="dark">
              {{ row.target_type === 'article' ? '文章' : '章节' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="状态" width="90" align="center">
          <template #default="{ row }">
            <el-tag :type="statusType(row.status)" size="small" effect="dark">
              {{ statusLabel(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="时间" width="170" align="center">
          <template #default="{ row }">
            <span class="text-muted">{{ row.created_at }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="220" align="center" fixed="right">
          <template #default="{ row }">
            <template v-if="row.status !== 1">
              <el-button link type="success" size="small" @click="handleApprove(row)">通过</el-button>
            </template>
            <template v-if="row.status !== 2">
              <el-button link type="warning" size="small" @click="handleReject(row)">拒绝</el-button>
            </template>
            <el-button link type="danger" size="small" @click="handleDelete(row)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>

      <div class="pagination-wrap" v-if="pagination.total > 0">
        <el-pagination
          v-model:current-page="pagination.page"
          :page-size="pagination.page_size"
          :total="pagination.total"
          layout="->, total, prev, pager, next"
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
import { ElMessage, ElMessageBox } from 'element-plus'

const loading = ref(false)
const list = ref([])
const pagination = reactive({
  page: 1,
  page_size: 10,
  total: 0
})
const filters = reactive({
  status: ''
})

onMounted(() => {
  fetchList()
})

function search() {
  pagination.page = 1
  fetchList()
}

function resetFilters() {
  filters.status = ''
  search()
}

async function fetchList() {
  loading.value = true
  try {
    const params = { page: pagination.page }
    if (filters.status !== '') params.status = filters.status
    const res = await adminApi.getComments(params)
    if (res.code === 0) {
      list.value = res.data.list
      pagination.total = res.data.pagination.total
      pagination.page = res.data.pagination.page
    }
  } catch (e) {
    ElMessage.error('获取评论列表失败')
  } finally {
    loading.value = false
  }
}

function statusType(s) {
  if (s === 1) return 'success'
  if (s === 0) return 'warning'
  return 'danger'
}

function statusLabel(s) {
  if (s === 1) return '已通过'
  if (s === 0) return '待审核'
  return '已拒绝'
}

async function handleApprove(row) {
  try {
    const res = await adminApi.auditComment(row.id, 1)
    if (res.code === 0) {
      ElMessage.success('审核通过')
      fetchList()
    }
  } catch (e) {
    ElMessage.error('操作失败')
  }
}

async function handleReject(row) {
  try {
    const res = await adminApi.auditComment(row.id, 2)
    if (res.code === 0) {
      ElMessage.success('已拒绝')
      fetchList()
    }
  } catch (e) {
    ElMessage.error('操作失败')
  }
}

function handleDelete(row) {
  ElMessageBox.confirm('确定要删除这条评论吗？', '删除确认', {
    confirmButtonText: '确定',
    cancelButtonText: '取消',
    type: 'warning'
  }).then(async () => {
    try {
      const res = await adminApi.deleteComment(row.id)
      if (res.code === 0) {
        ElMessage.success('删除成功')
        fetchList()
      }
    } catch (e) {
      ElMessage.error('删除失败')
    }
  }).catch(() => {})
}
</script>

<style scoped lang="scss">
.page-container {
  padding: 0;
}
.page-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 24px;
  .page-title {
    font-size: 22px;
    font-weight: 700;
    margin-bottom: 4px;
  }
  .page-subtitle {
    font-size: 13px;
    color: var(--color-text-muted);
  }
}
.filter-card {
  margin-bottom: 20px;
  :deep(.el-form-item) {
    margin-bottom: 0;
  }
}
.comment-text {
  font-size: 13px;
  line-height: 1.5;
  color: var(--color-text);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.text-muted {
  color: var(--color-text-muted);
  font-size: 13px;
}
.pagination-wrap {
  margin-top: 20px;
}
</style>
