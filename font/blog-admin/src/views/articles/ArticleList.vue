<template>
  <div class="page-container">
    <div class="page-header">
      <div>
        <h2 class="page-title">文章管理</h2>
        <p class="page-subtitle">管理所有博客文章</p>
      </div>
      <el-button type="primary" @click="$router.push('/admin/articles/create')">
        <el-icon><Plus /></el-icon> 发布文章
      </el-button>
    </div>

    <!-- 筛选 -->
    <div class="admin-card filter-card">
      <el-form :model="filters" inline>
        <el-form-item label="关键词">
          <el-input v-model="filters.keyword" placeholder="标题搜索" clearable @clear="search" @keyup.enter="search" />
        </el-form-item>
        <el-form-item label="栏目">
          <el-select v-model="filters.category_id" placeholder="全部栏目" clearable @change="search" style="width: 160px">
            <el-option v-for="c in categories" :key="c.id" :label="c.name" :value="c.id" />
          </el-select>
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="filters.status" placeholder="全部状态" clearable @change="search" style="width: 130px">
            <el-option label="已发布" :value="1" />
            <el-option label="草稿" :value="0" />
            <el-option label="隐藏" :value="2" />
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
      <el-table :data="list" stripe style="width: 100%" v-loading="loading" empty-text="暂无文章数据">
        <el-table-column type="index" label="#" width="55" align="center" />
        <el-table-column prop="title" label="标题" min-width="200" show-overflow-tooltip>
          <template #default="{ row }">
            <span class="article-title">{{ row.title }}</span>
          </template>
        </el-table-column>
        <el-table-column label="栏目" width="120" align="center">
          <template #default="{ row }">
            <span class="text-muted">{{ getCategoryName(row.category_id) }}</span>
          </template>
        </el-table-column>
        <el-table-column label="状态" width="90" align="center">
          <template #default="{ row }">
            <el-tag :type="statusType(row.status)" size="small" effect="dark">
              {{ statusLabel(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="view_count" label="浏览量" width="80" align="center" />
        <el-table-column label="发布时间" width="170" align="center">
          <template #default="{ row }">
            <span class="text-muted">{{ row.published_at || row.created_at }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="200" align="center" fixed="right">
          <template #default="{ row }">
            <el-button link type="primary" size="small" @click="$router.push(`/admin/articles/edit/${row.id}`)">编辑</el-button>
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
import { Plus } from '@element-plus/icons-vue'
import { ElMessage, ElMessageBox } from 'element-plus'

const loading = ref(false)
const list = ref([])
const categories = ref([])
const pagination = reactive({
  page: 1,
  page_size: 10,
  total: 0
})
const filters = reactive({
  keyword: '',
  category_id: '',
  status: ''
})

onMounted(async () => {
  await loadCategories()
  fetchList()
})

async function loadCategories() {
  try {
    const res = await adminApi.getAllCategories()
    if (res.code === 0) categories.value = res.data.list || res.data
  } catch (e) {}
}

function getCategoryName(id) {
  const c = categories.value.find(c => c.id === id)
  return c ? c.name : '-'
}

function search() {
  pagination.page = 1
  fetchList()
}

function resetFilters() {
  filters.keyword = ''
  filters.category_id = ''
  filters.status = ''
  search()
}

async function fetchList() {
  loading.value = true
  try {
    const params = { page: pagination.page, page_size: pagination.page_size }
    if (filters.category_id) params.category_id = filters.category_id
    if (filters.status !== '') params.status = filters.status
    const res = await adminApi.getArticles(params)
    if (res.code === 0) {
      list.value = res.data.list
      pagination.total = res.data.pagination.total
      pagination.page = res.data.pagination.page
    }
  } catch (e) {
    ElMessage.error('获取文章列表失败')
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
  if (s === 1) return '已发布'
  if (s === 0) return '草稿'
  return '隐藏'
}

function handleDelete(row) {
  ElMessageBox.confirm(`确定要删除文章「${row.title}」吗？`, '删除确认', {
    confirmButtonText: '确定',
    cancelButtonText: '取消',
    type: 'warning'
  }).then(async () => {
    try {
      const res = await adminApi.deleteArticle(row.id)
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
.article-title {
  font-weight: 500;
}
.text-muted {
  color: var(--color-text-muted);
  font-size: 13px;
}
.pagination-wrap {
  margin-top: 20px;
}
</style>
