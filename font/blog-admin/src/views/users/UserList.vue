<template>
  <div class="page-container">
    <div class="page-header">
      <div>
        <h2 class="page-title">用户管理</h2>
        <p class="page-subtitle">管理所有注册用户</p>
      </div>
    </div>

    <!-- 筛选 -->
    <div class="admin-card filter-card">
      <el-form :model="filters" inline>
        <el-form-item label="关键词">
          <el-input v-model="filters.keyword" placeholder="用户名/昵称搜索" clearable @clear="search" @keyup.enter="search" />
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="filters.status" placeholder="全部状态" clearable @change="search" style="width: 130px">
            <el-option label="启用" :value="1" />
            <el-option label="禁用" :value="0" />
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
      <el-table :data="list" stripe style="width: 100%" v-loading="loading" empty-text="暂无用户数据">
        <el-table-column type="index" label="#" width="55" align="center" />
        <el-table-column label="用户" min-width="160">
          <template #default="{ row }">
            <div class="user-cell">
              <el-avatar :size="32" icon="UserFilled" style="background: #6366f1" />
              <div class="user-info">
                <span class="user-name">{{ row.nickname || row.username }}</span>
                <span class="user-username">@{{ row.username }}</span>
              </div>
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="email" label="邮箱" min-width="180">
          <template #default="{ row }">
            <span class="text-muted">{{ row.email || '-' }}</span>
          </template>
        </el-table-column>
        <el-table-column label="状态" width="80" align="center">
          <template #default="{ row }">
            <el-switch
              :model-value="row.status === 1"
              :loading="row._statusLoading"
              @change="(val) => handleToggleStatus(row, val)"
              active-color="#10b981"
              inactive-color="#475569"
            />
          </template>
        </el-table-column>
        <el-table-column label="注册时间" width="170" align="center">
          <template #default="{ row }">
            <span class="text-muted">{{ row.created_at }}</span>
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
import { ElMessage } from 'element-plus'

const loading = ref(false)
const list = ref([])
const pagination = reactive({
  page: 1,
  page_size: 10,
  total: 0
})
const filters = reactive({
  keyword: '',
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
  filters.keyword = ''
  filters.status = ''
  search()
}

async function fetchList() {
  loading.value = true
  try {
    const params = { page: pagination.page }
    const res = await adminApi.getUsers(params)
    if (res.code === 0) {
      list.value = res.data.list
      pagination.total = res.data.pagination.total
      pagination.page = res.data.pagination.page
    }
  } catch (e) {
    ElMessage.error('获取用户列表失败')
  } finally {
    loading.value = false
  }
}

async function handleToggleStatus(row, val) {
  row._statusLoading = true
  try {
    const res = await adminApi.updateUserStatus(row.id, val ? 1 : 0)
    if (res.code === 0) {
      row.status = val ? 1 : 0
      ElMessage.success('状态已更新')
    }
  } catch (e) {
    ElMessage.error('更新失败')
  } finally {
    row._statusLoading = false
  }
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
.user-cell {
  display: flex;
  align-items: center;
  gap: 10px;
  .user-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
    .user-name {
      font-size: 14px;
      font-weight: 500;
      color: var(--color-text);
    }
    .user-username {
      font-size: 12px;
      color: var(--color-text-muted);
    }
  }
}
.text-muted {
  color: var(--color-text-muted);
  font-size: 13px;
}
.pagination-wrap {
  margin-top: 20px;
}
</style>
