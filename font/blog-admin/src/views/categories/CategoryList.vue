<template>
  <div>
    <div class="page-header">
      <div>
        <h2 class="page-title">栏目管理</h2>
        <p class="page-subtitle">管理博客栏目分组与子栏目</p>
      </div>
      <div class="header-actions">
        <el-button @click="openDialog(null, 0)">
          <el-icon><FolderOpened /></el-icon> 新增分组
        </el-button>
        <el-button type="primary" @click="openDialog(null)">
          <el-icon><Plus /></el-icon> 新增子栏目
        </el-button>
      </div>
    </div>

    <div class="admin-card">
      <el-table
        :data="treeList"
        row-key="id"
        :tree-props="{ children: 'children', hasChildren: 'hasChildren' }"
        default-expand-all
        style="width: 100%"
        v-loading="loading"
        empty-text="暂无栏目数据"
      >
        <el-table-column label="名称" min-width="200">
          <template #default="{ row }">
            <span class="cate-name" :class="{ 'is-group': row.parent_id === 0 || !row.parent_id }">
              <el-icon v-if="row.parent_id === 0 || !row.parent_id" :size="16" style="margin-right:4px"><FolderOpened /></el-icon>
              <el-icon v-else :size="14" style="margin-right:4px;color:var(--color-text-muted)"><Document /></el-icon>
              {{ row.name }}
            </span>
          </template>
        </el-table-column>
        <el-table-column prop="slug" label="Slug" width="140">
          <template #default="{ row }">
            <span class="text-muted">/{{ row.slug }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="article_count" label="文章" width="60" align="center" />
        <el-table-column prop="sort" label="排序" width="70" align="center" />
        <el-table-column label="状态" width="80" align="center">
          <template #default="{ row }">
            <el-switch
              :model-value="row.status === 1"
              :loading="row._loading"
              @change="(val) => toggleStatus(row, val)"
              size="small"
            />
          </template>
        </el-table-column>
        <el-table-column label="操作" width="200" align="center" fixed="right">
          <template #default="{ row }">
            <el-button link type="primary" size="small" @click="openDialog(row, row.parent_id || 0)">编辑</el-button>
            <el-button v-if="row.parent_id !== 0 && row.parent_id" link type="primary" size="small" @click="openDialog(null, row.id)">
              添加子级
            </el-button>
            <el-button link type="danger" size="small" @click="handleDelete(row)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
    </div>

    <!-- 新增/编辑弹窗 -->
    <el-dialog v-model="dialogVisible" :title="dialogTitle" width="520px" :close-on-click-modal="false">
      <el-form ref="formRef" :model="form" :rules="rules" label-width="90px">
        <el-form-item label="栏目类型">
          <el-radio-group v-model="form.parent_id">
            <el-radio :value="0">顶级分组</el-radio>
            <el-radio :value="parentIdForChild" v-if="parentIdForChild">子栏目（归属当前分组）</el-radio>
          </el-radio-group>
          <div v-if="form.parent_id !== 0" class="form-tip">将作为子栏目添加到所选分组下</div>
        </el-form-item>
        <el-form-item label="名称" prop="name">
          <el-input v-model="form.name" placeholder="请输入名称" />
        </el-form-item>
        <el-form-item label="Slug" prop="slug">
          <el-input v-model="form.slug" placeholder="英文标识">
            <template #prefix>/</template>
          </el-input>
        </el-form-item>
        <el-form-item label="描述">
          <el-input v-model="form.description" type="textarea" :rows="3" placeholder="描述（可选）" />
        </el-form-item>
        <el-form-item label="排序">
          <el-input-number v-model="form.sort" :min="0" :max="999" />
        </el-form-item>
        <el-form-item label="状态">
          <el-radio-group v-model="form.status">
            <el-radio :value="1">显示</el-radio>
            <el-radio :value="0">隐藏</el-radio>
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
import { ref, reactive, computed, onMounted } from 'vue'
import { adminApi } from '../../api'
import { Plus, FolderOpened, Document } from '@element-plus/icons-vue'
import { ElMessage, ElMessageBox } from 'element-plus'

const loading = ref(false)
const list = ref([])
const dialogVisible = ref(false)
const editId = ref(null)
const submitLoading = ref(false)
const formRef = ref(null)
const parentIdForChild = ref(0)

// 构建树形结构
const treeList = computed(() => {
  const flat = list.value
  const map = {}
  const roots = []
  flat.forEach(i => { map[i.id] = { ...i, children: [] } })
  flat.forEach(i => {
    const node = map[i.id]
    if (i.parent_id && map[i.parent_id]) {
      map[i.parent_id].children.push(node)
    } else {
      roots.push(node)
    }
  })
  return roots
})

const dialogTitle = computed(() => {
  if (editId.value) return '编辑栏目'
  return form.parent_id === 0 ? '新增顶级分组' : '新增子栏目'
})

const form = reactive({
  name: '', slug: '', description: '', sort: 0, status: 1, parent_id: 0
})
const rules = {
  name: [{ required: true, message: '请输入名称', trigger: 'blur' }],
  slug: [{ required: true, message: '请输入Slug', trigger: 'blur' }]
}

onMounted(() => fetchList())

async function fetchList() {
  loading.value = true
  try {
    const res = await adminApi.getAllCategories()
    if (res.code === 0) {
      list.value = res.data.list || res.data
    }
  } catch (e) {
    ElMessage.error('获取栏目列表失败')
  } finally {
    loading.value = false
  }
}

function openDialog(row, parentId) {
  editId.value = row?.id || null
  parentIdForChild.value = parentId || 0
  if (row) {
    form.name = row.name
    form.slug = row.slug
    form.description = row.description || ''
    form.sort = row.sort
    form.status = row.status
    form.parent_id = row.parent_id || 0
  } else {
    form.name = ''
    form.slug = ''
    form.description = ''
    form.sort = 0
    form.status = 1
    form.parent_id = parentId || 0
  }
  dialogVisible.value = true
}

async function handleSubmit() {
  const valid = await formRef.value.validate().catch(() => false)
  if (!valid) return
  submitLoading.value = true
  try {
    const payload = { ...form }
    if (!payload.parent_id) payload.parent_id = 0
    let res
    if (editId.value) {
      res = await adminApi.updateCategory(editId.value, payload)
    } else {
      res = await adminApi.createCategory(payload)
    }
    if (res.code === 0) {
      ElMessage.success('操作成功')
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

async function toggleStatus(row, val) {
  row._loading = true
  try {
    const res = await adminApi.updateCategory(row.id, { status: val ? 1 : 0 })
    if (res.code === 0) {
      row.status = val ? 1 : 0
    }
  } catch (e) {
    ElMessage.error('更新失败')
  } finally {
    row._loading = false
  }
}

function handleDelete(row) {
  ElMessageBox.confirm(`确定删除「${row.name}」吗？${row.children?.length ? ' 该分组下的子栏目也会一起删除。' : ''}`, '确认', {
    type: 'warning', confirmButtonText: '确定', cancelButtonText: '取消'
  }).then(async () => {
    try {
      const res = await adminApi.deleteCategory(row.id)
      if (res.code === 0) {
        ElMessage.success('已删除')
        fetchList()
      }
    } catch (e) {
      ElMessage.error('删除失败')
    }
  }).catch(() => {})
}
</script>

<style scoped lang="scss">
.page-header {
  display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 20px;
  .page-title { font-size: 20px; font-weight: 700; margin-bottom: 4px; }
  .page-subtitle { font-size: 13px; color: var(--color-text-muted); }
}
.header-actions { display: flex; gap: 8px; }

.cate-name { font-weight: 500; display: flex; align-items: center; &.is-group { font-weight: 600; } }
.text-muted { color: var(--color-text-muted); font-size: 13px; }
.form-tip { font-size: 12px; color: var(--color-text-muted); margin-top: 4px; }
</style>
