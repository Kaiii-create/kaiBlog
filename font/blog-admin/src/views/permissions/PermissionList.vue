<template>
  <div class="permission-list-container">
    <el-card class="table-card" shadow="never">
      <div class="table-header">
        <el-button type="primary" @click="handleAdd">
          <el-icon><Plus /></el-icon>新增权限
        </el-button>
      </div>

      <el-table
        v-loading="loading"
        :data="permissionTree"
        row-key="id"
        default-expand-all
        :tree-props="{ children: 'children', hasChildren: 'hasChildren' }"
        border
        stripe
        style="width: 100%"
      >
        <el-table-column prop="name" label="名称" min-width="160" show-overflow-tooltip />
        <el-table-column prop="slug" label="Slug" min-width="120" show-overflow-tooltip />
        <el-table-column label="类型" width="80" align="center">
          <template #default="{ row }">
            <el-tag :type="row.type === 1 ? 'primary' : 'success'" size="small" effect="plain">
              {{ row.type === 1 ? '菜单' : '按钮' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="icon" label="图标" width="80" align="center">
          <template #default="{ row }">
            <span v-if="row.icon" class="icon-display">{{ row.icon }}</span>
            <span v-else class="icon-empty">-</span>
          </template>
        </el-table-column>
        <el-table-column prop="route_path" label="路由路径" min-width="160" show-overflow-tooltip />
        <el-table-column prop="sort" label="排序" width="70" align="center" />
        <el-table-column label="操作" width="200" align="center" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" link size="small" @click="handleEdit(row)">
              编辑
            </el-button>
            <el-button type="danger" link size="small" @click="handleDelete(row)">
              删除
            </el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <!-- 新增 / 编辑权限弹窗 -->
    <el-dialog
      v-model="dialogVisible"
      :title="isEdit ? '编辑权限' : '新增权限'"
      width="600px"
      :close-on-click-modal="false"
      @close="resetForm"
    >
      <el-form
        ref="formRef"
        :model="formData"
        :rules="formRules"
        label-width="100px"
      >
        <el-form-item label="权限名称" prop="name">
          <el-input
            v-model="formData.name"
            placeholder="请输入权限名称"
            maxlength="50"
            show-word-limit
          />
        </el-form-item>
        <el-form-item label="Slug" prop="slug">
          <el-input
            v-model="formData.slug"
            placeholder="权限标识符，如 user.create"
            maxlength="100"
            show-word-limit
          />
        </el-form-item>
        <el-form-item label="类型" prop="type">
          <el-radio-group v-model="formData.type">
            <el-radio :value="1">菜单</el-radio>
            <el-radio :value="2">按钮</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="父级权限" prop="parent_id">
          <el-tree-select
            v-model="formData.parent_id"
            :data="parentTree"
            :props="parentTreeProps"
            placeholder="请选择父级权限（可选）"
            clearable
            filterable
            check-strictly
            style="width: 100%"
          />
        </el-form-item>
        <el-form-item v-if="formData.type === 1" label="图标" prop="icon">
          <el-input
            v-model="formData.icon"
            placeholder="图标名称，如 UserFilled"
            maxlength="50"
          />
        </el-form-item>
        <el-form-item v-if="formData.type === 1" label="路由路径" prop="route_path">
          <el-input
            v-model="formData.route_path"
            placeholder="如 /admin/users"
            maxlength="200"
          />
        </el-form-item>
        <el-form-item label="接口路径" prop="api_path">
          <el-input
            v-model="formData.api_path"
            placeholder="如 /api/admin/users"
            maxlength="200"
          />
        </el-form-item>
        <el-form-item label="请求方法" prop="method">
          <el-select
            v-model="formData.method"
            placeholder="请选择请求方法"
            clearable
            style="width: 140px"
          >
            <el-option label="GET" value="GET" />
            <el-option label="POST" value="POST" />
            <el-option label="PUT" value="PUT" />
            <el-option label="DELETE" value="DELETE" />
          </el-select>
        </el-form-item>
        <el-form-item label="排序" prop="sort">
          <el-input-number
            v-model="formData.sort"
            :min="0"
            :max="9999"
            controls-position="right"
          />
          <span class="sort-tip">数字越小越靠前</span>
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="submitLoading" @click="handleSubmit">
          确认
        </el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, nextTick } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus } from '@element-plus/icons-vue'
import { adminApi } from '../../api'

// ─── 权限树数据 ───
const loading = ref(false)
const permissionTree = ref([])

const getPermissionTree = async () => {
  loading.value = true
  try {
    const res = await adminApi.getPermissions()
    permissionTree.value = res.data || res || []
  } catch {
    ElMessage.error('获取权限树失败')
  } finally {
    loading.value = false
  }
}

// ─── 弹窗状态 ───
const dialogVisible = ref(false)
const isEdit = ref(false)
const editId = ref(null)
const submitLoading = ref(false)
const formRef = ref(null)

const defaultForm = {
  name: '',
  slug: '',
  type: 1,
  parent_id: undefined,
  icon: '',
  route_path: '',
  api_path: '',
  method: '',
  sort: 0,
}

const formData = reactive({ ...defaultForm })

const formRules = {
  name: [
    { required: true, message: '请输入权限名称', trigger: 'blur' },
    { max: 50, message: '长度不超过 50 字符', trigger: 'blur' },
  ],
  slug: [
    { required: true, message: '请输入 Slug', trigger: 'blur' },
    { max: 100, message: '长度不超过 100 字符', trigger: 'blur' },
  ],
  type: [{ required: true, message: '请选择类型', trigger: 'change' }],
}

// ─── 父级权限树选择器 ───
// 克隆权限树并在顶部插入一个"顶级"节点
const deepCloneTree = (nodes) => {
  return nodes.map((node) => ({
    ...node,
    children: node.children ? deepCloneTree(node.children) : [],
  }))
}

const parentTree = computed(() => {
  const cloned = deepCloneTree(permissionTree.value)
  const root = {
    id: 0,
    name: '顶级',
    children: cloned,
  }
  return [root]
})

const parentTreeProps = {
  label: 'name',
  children: 'children',
  value: 'id',
  disabled: (data) => data.id === editId.value,
  emitPath: false,
}

// ─── 新增 ───
const handleAdd = () => {
  isEdit.value = false
  editId.value = null
  Object.assign(formData, { ...defaultForm })
  dialogVisible.value = true
}

// ─── 编辑 ───
const handleEdit = async (row) => {
  isEdit.value = true
  editId.value = row.id
  formData.name = row.name || ''
  formData.slug = row.slug || ''
  formData.type = row.type
  formData.parent_id = row.parent_id || undefined
  formData.icon = row.icon || ''
  formData.route_path = row.route_path || ''
  formData.api_path = row.api_path || ''
  formData.method = row.method || ''
  formData.sort = row.sort ?? 0
  dialogVisible.value = true
}

// ─── 提交 ───
const handleSubmit = async () => {
  const valid = await formRef.value.validate().catch(() => false)
  if (!valid) return

  submitLoading.value = true
  try {
    const payload = {
      name: formData.name,
      slug: formData.slug,
      type: formData.type,
      parent_id: formData.parent_id || 0,
      icon: formData.type === 1 ? (formData.icon || '') : '',
      route_path: formData.type === 1 ? (formData.route_path || '') : '',
      api_path: formData.api_path || '',
      method: formData.method || '',
      sort: formData.sort ?? 0,
    }

    if (isEdit.value) {
      await adminApi.updatePermission(editId.value, payload)
      ElMessage.success('权限更新成功')
    } else {
      await adminApi.createPermission(payload)
      ElMessage.success('权限创建成功')
    }
    dialogVisible.value = false
    getPermissionTree()
  } catch {
    ElMessage.error(isEdit.value ? '权限更新失败' : '权限创建失败')
  } finally {
    submitLoading.value = false
  }
}

// ─── 删除 ───
const handleDelete = (row) => {
  ElMessageBox.confirm(
    `确定删除权限「${row.name}」吗？${
      row.children && row.children.length
        ? '该权限包含子权限，删除后子权限将一并被移除。'
        : '此操作不可恢复。'
    }`,
    '删除确认',
    { confirmButtonText: '确定', cancelButtonText: '取消', type: 'warning' }
  )
    .then(async () => {
      try {
        await adminApi.deletePermission(row.id)
        ElMessage.success('删除成功')
        getPermissionTree()
      } catch {
        ElMessage.error('删除失败')
      }
    })
    .catch(() => {})
}

// ─── 重置表单 ───
const resetForm = () => {
  formRef.value?.resetFields()
  nextTick(() => {
    Object.assign(formData, { ...defaultForm })
    editId.value = null
  })
}

// ─── 初始加载 ───
onMounted(() => {
  getPermissionTree()
})
</script>

<style scoped>
.permission-list-container {
  padding: 20px;
}

.table-card {
  min-height: 400px;
}

.table-header {
  margin-bottom: 16px;
}

.icon-display {
  font-family: 'Courier New', Courier, monospace;
  font-size: 12px;
  color: #606266;
}

.icon-empty {
  color: #c0c4cc;
}

.sort-tip {
  margin-left: 8px;
  font-size: 12px;
  color: #909399;
}
</style>
