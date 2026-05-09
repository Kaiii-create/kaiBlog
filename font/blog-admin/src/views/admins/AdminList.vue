<template>
  <div class="page-container">
    <div class="page-header">
      <div>
        <h2 class="page-title">管理员管理</h2>
        <p class="page-subtitle">管理系统管理员账号</p>
      </div>
      <el-button type="primary" @click="openDialog(null)">
        <el-icon><Plus /></el-icon> 新增管理员
      </el-button>
    </div>

    <!-- 搜索 -->
    <div class="admin-card filter-card">
      <el-form :model="filters" inline>
        <el-form-item label="关键词">
          <el-input
            v-model="filters.keyword"
            placeholder="用户名/昵称搜索"
            clearable
            @clear="search"
            @keyup.enter="search"
          />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="search">搜索</el-button>
          <el-button @click="resetFilters">重置</el-button>
        </el-form-item>
      </el-form>
    </div>

    <!-- 表格 -->
    <div class="admin-card">
      <el-table
        :data="list"
        stripe
        style="width: 100%"
        v-loading="loading"
        empty-text="暂无管理员数据"
      >
        <el-table-column type="index" label="#" width="55" align="center" />
        <el-table-column prop="id" label="ID" width="70" align="center" />
        <el-table-column prop="username" label="用户名" min-width="130">
          <template #default="{ row }">
            <span class="admin-username">@{{ row.username }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="nickname" label="昵称" min-width="120">
          <template #default="{ row }">
            <span>{{ row.nickname || '-' }}</span>
          </template>
        </el-table-column>
        <el-table-column label="角色" min-width="160">
          <template #default="{ row }">
            <template v-if="row.roles && row.roles.length">
              <el-tag
                v-for="role in row.roles"
                :key="role.id"
                size="small"
                style="margin-right: 4px; margin-bottom: 2px"
              >
                {{ role.name }}
              </el-tag>
            </template>
            <span v-else class="text-muted">-</span>
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
        <el-table-column label="创建时间" width="170" align="center">
          <template #default="{ row }">
            <span class="text-muted">{{ row.created_at }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="180" align="center" fixed="right">
          <template #default="{ row }">
            <el-button link type="primary" size="small" @click="openDialog(row)">编辑</el-button>
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

    <!-- 新增/编辑弹窗 -->
    <el-dialog
      v-model="dialogVisible"
      :title="editId ? '编辑管理员' : '新增管理员'"
      width="520px"
      :close-on-click-modal="false"
      class="admin-dialog"
    >
      <el-form ref="formRef" :model="form" :rules="rules" label-width="80px">
        <el-form-item label="用户名" prop="username">
          <el-input v-model="form.username" placeholder="请输入用户名" :disabled="!!editId" />
        </el-form-item>
        <el-form-item label="密码" prop="password">
          <el-input
            v-model="form.password"
            type="password"
            show-password
            :placeholder="editId ? '留空则不修改' : '请输入密码'"
          />
        </el-form-item>
        <el-form-item label="昵称" prop="nickname">
          <el-input v-model="form.nickname" placeholder="请输入昵称" />
        </el-form-item>
        <el-form-item label="角色" prop="role_ids">
          <el-select
            v-model="form.role_ids"
            multiple
            placeholder="请选择角色"
            style="width: 100%"
          >
            <el-option
              v-for="role in roleOptions"
              :key="role.id"
              :label="role.name"
              :value="role.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="状态" prop="status">
          <el-radio-group v-model="form.status">
            <el-radio :value="1">启用</el-radio>
            <el-radio :value="0">禁用</el-radio>
          </el-radio-group>
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="submitLoading" @click="handleSubmit">确认</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { adminApi } from '../../api'
import { Plus } from '@element-plus/icons-vue'
import { ElMessage, ElMessageBox } from 'element-plus'

const loading = ref(false)
const list = ref([])
const roleOptions = ref([])
const pagination = reactive({
  page: 1,
  page_size: 10,
  total: 0
})
const filters = reactive({
  keyword: ''
})

const dialogVisible = ref(false)
const editId = ref(null)
const submitLoading = ref(false)
const formRef = ref(null)

const form = reactive({
  username: '',
  password: '',
  nickname: '',
  role_ids: [],
  status: 1
})

const rules = {
  username: [{ required: true, message: '请输入用户名', trigger: 'blur' }],
  nickname: [{ required: true, message: '请输入昵称', trigger: 'blur' }],
  role_ids: [{ required: true, message: '请选择角色', trigger: 'change' }]
}

onMounted(() => {
  fetchList()
  fetchRoles()
})

function search() {
  pagination.page = 1
  fetchList()
}

function resetFilters() {
  filters.keyword = ''
  search()
}

async function fetchList() {
  loading.value = true
  try {
    const params = {
      page: pagination.page,
      keyword: filters.keyword || undefined
    }
    const res = await adminApi.getAdmins(params)
    if (res.code === 0) {
      list.value = res.data.list
      pagination.total = res.data.pagination.total
      pagination.page = res.data.pagination.page
    }
  } catch (e) {
    ElMessage.error('获取管理员列表失败')
  } finally {
    loading.value = false
  }
}

async function fetchRoles() {
  try {
    const res = await adminApi.getRoles()
    if (res.code === 0) {
      roleOptions.value = res.data.list || res.data
    }
  } catch (e) {
    // silent
  }
}

async function handleToggleStatus(row, val) {
  row._statusLoading = true
  try {
    const res = await adminApi.updateAdmin(row.id, { status: val ? 1 : 0 })
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

function openDialog(row) {
  if (row) {
    editId.value = row.id
    form.username = row.username
    form.password = ''
    form.nickname = row.nickname || ''
    form.role_ids = row.roles ? row.roles.map(r => r.id) : []
    form.status = row.status
  } else {
    editId.value = null
    form.username = ''
    form.password = ''
    form.nickname = ''
    form.role_ids = []
    form.status = 1
  }
  dialogVisible.value = true
}

async function handleSubmit() {
  const valid = await formRef.value.validate().catch(() => false)
  if (!valid) return

  submitLoading.value = true
  try {
    let res
    const data = {
      nickname: form.nickname,
      role_ids: form.role_ids,
      status: form.status
    }
    if (editId.value) {
      if (form.password) data.password = form.password
      res = await adminApi.updateAdmin(editId.value, data)
    } else {
      data.username = form.username
      data.password = form.password
      res = await adminApi.createAdmin(data)
    }
    if (res.code === 0) {
      ElMessage.success(res.message || '操作成功')
      dialogVisible.value = false
      fetchList()
    } else {
      ElMessage.error(res.message || '操作失败')
    }
  } catch (e) {
    ElMessage.error('操作失败')
  } finally {
    submitLoading.value = false
  }
}

function handleDelete(row) {
  ElMessageBox.confirm(`确定要删除管理员「${row.username}」吗？`, '删除确认', {
    confirmButtonText: '确定',
    cancelButtonText: '取消',
    type: 'warning'
  }).then(async () => {
    try {
      const res = await adminApi.deleteAdmin(row.id)
      if (res.code === 0) {
        ElMessage.success('删除成功')
        const totalPage = Math.ceil((pagination.total - 1) / pagination.page_size)
        if (pagination.page > totalPage && pagination.page > 1) {
          pagination.page = totalPage
        }
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
.admin-username {
  font-weight: 500;
  font-family: 'SF Mono', 'Fira Code', monospace;
  font-size: 13px;
}
.text-muted {
  color: var(--color-text-muted);
  font-size: 13px;
}
.pagination-wrap {
  margin-top: 20px;
}
.admin-dialog {
  :deep(.el-dialog) {
    background: var(--color-bg-card);
    border: 1px solid var(--color-border);
    border-radius: 16px;
  }
  :deep(.el-dialog__header) {
    padding: 20px 24px 0;
    font-weight: 600;
  }
  :deep(.el-dialog__body) {
    padding: 20px 24px;
  }
  :deep(.el-dialog__footer) {
    padding: 0 24px 20px;
  }
}
</style>
