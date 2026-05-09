<template>
  <div class="role-list-container">
    <!-- 搜索栏 -->
    <el-card class="search-card" shadow="never">
      <el-form :model="queryParams" inline>
        <el-form-item label="角色名称">
          <el-input
            v-model="queryParams.name"
            placeholder="请输入角色名称"
            clearable
            style="width: 200px"
            @keyup.enter="handleSearch"
          />
        </el-form-item>
        <el-form-item label="状态">
          <el-select
            v-model="queryParams.status"
            placeholder="请选择状态"
            clearable
            style="width: 140px"
          >
            <el-option label="全部" :value="undefined" />
            <el-option label="启用" :value="1" />
            <el-option label="禁用" :value="0" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleSearch">搜索</el-button>
          <el-button @click="handleReset">重置</el-button>
        </el-form-item>
      </el-form>
    </el-card>

    <!-- 操作栏 -->
    <el-card class="table-card" shadow="never">
      <div class="table-header">
        <el-button type="primary" @click="handleAdd">
          <el-icon><Plus /></el-icon>新增角色
        </el-button>
      </div>

      <!-- 角色列表 -->
      <el-table
        v-loading="loading"
        :data="roleList"
        border
        stripe
        style="width: 100%"
      >
        <el-table-column prop="id" label="ID" width="70" align="center" />
        <el-table-column prop="name" label="名称" min-width="140" show-overflow-tooltip />
        <el-table-column prop="description" label="描述" min-width="200" show-overflow-tooltip />
        <el-table-column label="状态" width="90" align="center">
          <template #default="{ row }">
            <el-switch
              :model-value="row.status === 1"
              :loading="row._switchLoading"
              @change="(val) => handleStatusChange(row, val)"
            />
          </template>
        </el-table-column>
        <el-table-column
          prop="created_at"
          label="创建时间"
          width="180"
          align="center"
        />
        <el-table-column label="操作" width="280" align="center" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" link size="small" @click="handleEdit(row)">
              编辑
            </el-button>
            <el-button type="warning" link size="small" @click="handleAssignPermissions(row)">
              分配权限
            </el-button>
            <el-button type="danger" link size="small" @click="handleDelete(row)">
              删除
            </el-button>
          </template>
        </el-table-column>
      </el-table>

      <!-- 分页 -->
      <div class="pagination-wrapper">
        <el-pagination
          v-model:current-page="queryParams.page"
          v-model:page-size="queryParams.pageSize"
          :page-sizes="[10, 20, 50, 100]"
          :total="total"
          layout="total, sizes, prev, pager, next, jumper"
          background
          @size-change="getRoleList"
          @current-change="getRoleList"
        />
      </div>
    </el-card>

    <!-- 新增/编辑弹窗 -->
    <el-dialog
      v-model="dialogVisible"
      :title="isEdit ? '编辑角色' : '新增角色'"
      width="520px"
      :close-on-click-modal="false"
      @close="resetForm"
    >
      <el-form
        ref="formRef"
        :model="formData"
        :rules="formRules"
        label-width="80px"
      >
        <el-form-item label="角色名称" prop="name">
          <el-input
            v-model="formData.name"
            placeholder="请输入角色名称"
            maxlength="50"
            show-word-limit
          />
        </el-form-item>
        <el-form-item label="描述" prop="description">
          <el-input
            v-model="formData.description"
            type="textarea"
            placeholder="请输入角色描述"
            maxlength="200"
            show-word-limit
            :rows="3"
          />
        </el-form-item>
        <el-form-item label="状态" prop="status">
          <el-radio-group v-model="formData.status">
            <el-radio :value="1">启用</el-radio>
            <el-radio :value="0">禁用</el-radio>
          </el-radio-group>
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="submitLoading" @click="handleSubmit">
          确认
        </el-button>
      </template>
    </el-dialog>

    <!-- 权限分配弹窗 -->
    <el-dialog
      v-model="permDialogVisible"
      title="分配权限"
      width="480px"
      :close-on-click-modal="false"
      @close="resetPermDialog"
    >
      <div class="perm-dialog-body">
        <div v-if="permLoading" class="perm-loading">
          <el-icon class="is-loading" :size="24"><Loading /></el-icon>
          <span>加载权限数据中...</span>
        </div>
        <template v-else>
          <div class="perm-role-info">
            当前角色：<el-tag size="small">{{ currentRole?.name }}</el-tag>
          </div>
          <el-tree
            ref="permTreeRef"
            :data="permissionTree"
            :props="treeProps"
            node-key="id"
            show-checkbox
            default-expand-all
            check-strictly
            class="perm-tree"
          />
        </template>
      </div>
      <template #footer>
        <el-button @click="permDialogVisible = false">取消</el-button>
        <el-button
          type="primary"
          :loading="permSubmitLoading"
          :disabled="permLoading"
          @click="handlePermSubmit"
        >
          保存
        </el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus, Loading } from '@element-plus/icons-vue'
import { adminApi } from '../../api'

// ─── 查询参数 ───
const queryParams = reactive({
  page: 1,
  pageSize: 10,
  name: undefined,
  status: undefined,
})

const roleList = ref([])
const total = ref(0)
const loading = ref(false)

// ─── 获取角色列表 ───
const getRoleList = async () => {
  loading.value = true
  try {
    const params = { ...queryParams }
    // 清除 undefined 字段
    Object.keys(params).forEach((k) => {
      if (params[k] === undefined) delete params[k]
    })
    const res = await adminApi.getRoles(params)
    roleList.value = (res.data?.list || res.data || []).map((item) => ({
      ...item,
      _switchLoading: false,
    }))
    total.value = res.data?.total ?? res.total ?? 0
  } catch (e) {
    ElMessage.error('获取角色列表失败')
  } finally {
    loading.value = false
  }
}

// ─── 搜索 / 重置 ───
const handleSearch = () => {
  queryParams.page = 1
  getRoleList()
}

const handleReset = () => {
  queryParams.name = undefined
  queryParams.status = undefined
  queryParams.page = 1
  getRoleList()
}

// ─── 状态切换 ───
const handleStatusChange = async (row, val) => {
  row._switchLoading = true
  try {
    await adminApi.updateRole(row.id, { status: val ? 1 : 0 })
    row.status = val ? 1 : 0
    ElMessage.success('状态更新成功')
  } catch {
    ElMessage.error('状态更新失败')
  } finally {
    row._switchLoading = false
  }
}

// ─── 新增/编辑 弹窗 ───
const dialogVisible = ref(false)
const isEdit = ref(false)
const editId = ref(null)
const submitLoading = ref(false)
const formRef = ref(null)

const formData = reactive({
  name: '',
  description: '',
  status: 1,
})

const formRules = {
  name: [{ required: true, message: '请输入角色名称', trigger: 'blur' }],
}

const handleAdd = () => {
  isEdit.value = false
  editId.value = null
  dialogVisible.value = true
}

const handleEdit = async (row) => {
  isEdit.value = true
  editId.value = row.id
  formData.name = row.name
  formData.description = row.description || ''
  formData.status = row.status
  dialogVisible.value = true
}

const resetForm = () => {
  formData.name = ''
  formData.description = ''
  formData.status = 1
  formRef.value?.resetFields()
  editId.value = null
}

const handleSubmit = async () => {
  const valid = await formRef.value.validate().catch(() => false)
  if (!valid) return

  submitLoading.value = true
  try {
    if (isEdit.value) {
      await adminApi.updateRole(editId.value, {
        name: formData.name,
        description: formData.description,
        status: formData.status,
      })
      ElMessage.success('角色更新成功')
    } else {
      await adminApi.createRole({
        name: formData.name,
        description: formData.description,
        permission_ids: [],
      })
      ElMessage.success('角色创建成功')
    }
    dialogVisible.value = false
    getRoleList()
  } catch {
    ElMessage.error(isEdit.value ? '角色更新失败' : '角色创建失败')
  } finally {
    submitLoading.value = false
  }
}

// ─── 删除 ───
const handleDelete = (row) => {
  ElMessageBox.confirm(
    `确定删除角色「${row.name}」吗？此操作不可恢复。`,
    '删除确认',
    { confirmButtonText: '确定', cancelButtonText: '取消', type: 'warning' }
  )
    .then(async () => {
      try {
        await adminApi.deleteRole(row.id)
        ElMessage.success('删除成功')
        getRoleList()
      } catch {
        ElMessage.error('删除失败')
      }
    })
    .catch(() => {
      // 取消删除，不做处理
    })
}

// ─── 权限分配 ───
const permDialogVisible = ref(false)
const permLoading = ref(false)
const permSubmitLoading = ref(false)
const currentRole = ref(null)
const permissionTree = ref([])
const permTreeRef = ref(null)

const treeProps = {
  children: 'children',
  label: 'name',
  disabled: (data) => data.type === 1 && (!data.children || data.children.length === 0),
}

// 递归收集 el-tree 中所有 type=2（按钮节点）的 id
const collectButtonIds = (nodes) => {
  const ids = []
  const walk = (list) => {
    list.forEach((node) => {
      if (node.type === 2) {
        ids.push(node.id)
      }
      if (node.children && node.children.length) {
        walk(node.children)
      }
    })
  }
  walk(nodes)
  return ids
}

const handleAssignPermissions = async (row) => {
  currentRole.value = row
  permDialogVisible.value = true
  permLoading.value = true
  permissionTree.value = []

  try {
    // 并行获取权限树和角色详情（含已有权限）
    const [permRes, detailRes] = await Promise.all([
      adminApi.getPermissions(),
      adminApi.getRoleDetail(row.id),
    ])

    const treeData = permRes.data || permRes || []
    permissionTree.value = treeData

    // 角色已有的权限 id 列表
    const rolePerms = detailRes.data?.permissions || detailRes.data?.permission_ids || []

    // 确保权限树渲染后再设置勾选
    await nextTick()
    if (permTreeRef.value) {
      // 只勾选 type=2（按钮节点）的权限
      const checkedIds = rolePerms
        .map((p) => (typeof p === 'object' ? p.id : p))
        .filter((id) => {
          const allButtonIds = collectButtonIds(treeData)
          return allButtonIds.includes(id)
        })
      permTreeRef.value.setCheckedKeys(checkedIds)
    }
  } catch {
    ElMessage.error('获取权限数据失败')
  } finally {
    permLoading.value = false
  }
}

const handlePermSubmit = async () => {
  if (!permTreeRef.value) return

  permSubmitLoading.value = true
  try {
    const checkedKeys = permTreeRef.value.getCheckedKeys()
    await adminApi.assignRolePermissions(currentRole.value.id, {
      permission_ids: checkedKeys,
    })
    ElMessage.success('权限分配成功')
    permDialogVisible.value = false
  } catch {
    ElMessage.error('权限分配失败')
  } finally {
    permSubmitLoading.value = false
  }
}

const resetPermDialog = () => {
  currentRole.value = null
  permissionTree.value = []
  permTreeRef.value = null
}

// ─── 初始加载 ───
onMounted(() => {
  getRoleList()
})
</script>

<style scoped>
.role-list-container {
  padding: 20px;
}

.search-card {
  margin-bottom: 16px;
}

.table-card {
  min-height: 400px;
}

.table-header {
  margin-bottom: 16px;
}

.pagination-wrapper {
  display: flex;
  justify-content: flex-end;
  margin-top: 20px;
}

.perm-dialog-body {
  min-height: 200px;
}

.perm-loading {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 60px 0;
  color: #909399;
  font-size: 14px;
}

.perm-role-info {
  margin-bottom: 12px;
  font-size: 14px;
  color: #606266;
}

.perm-tree {
  border: 1px solid #dcdfe6;
  border-radius: 4px;
  padding: 12px;
  max-height: 420px;
  overflow-y: auto;
}
</style>
