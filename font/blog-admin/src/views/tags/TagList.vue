<template>
  <div>
    <div class="page-header">
      <div>
        <h2 class="page-title">标签管理</h2>
        <p class="page-subtitle">管理文章标签分类</p>
      </div>
      <el-button type="primary" @click="openDialog(null)">
        <el-icon><Plus /></el-icon> 新增标签
      </el-button>
    </div>

    <div class="admin-card">
      <el-table :data="list" v-loading="loading" style="width:100%" empty-text="暂无标签">
        <el-table-column type="index" label="#" width="60" align="center" />
        <el-table-column prop="name" label="名称" min-width="140">
          <template #default="{ row }">
            <el-tag :color="row.color || '#4f6ef7'" style="color:#fff;border:none">{{ row.name }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="slug" label="Slug" width="140">
          <template #default="{ row }"><span class="text-muted">/{{ row.slug }}</span></template>
        </el-table-column>
        <el-table-column prop="article_count" label="文章数" width="80" align="center" />
        <el-table-column label="操作" width="160" align="center" fixed="right">
          <template #default="{ row }">
            <el-button link type="primary" size="small" @click="openDialog(row)">编辑</el-button>
            <el-button link type="danger" size="small" @click="handleDelete(row)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
    </div>

    <el-dialog v-model="dialogVisible" :title="editId ? '编辑标签' : '新增标签'" width="460px" :close-on-click-modal="false">
      <el-form ref="formRef" :model="form" :rules="rules" label-width="80px">
        <el-form-item label="名称" prop="name">
          <el-input v-model="form.name" placeholder="标签名称" />
        </el-form-item>
        <el-form-item label="Slug" prop="slug">
          <el-input v-model="form.slug" placeholder="英文标识">
            <template #prefix>/</template>
          </el-input>
        </el-form-item>
        <el-form-item label="颜色">
          <el-color-picker v-model="form.color" :predefine="preColors" />
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
const dialogVisible = ref(false)
const editId = ref(null)
const submitLoading = ref(false)
const formRef = ref(null)

const preColors = ['#4f6ef7','#06b6d4','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#64748b']

const form = reactive({ name: '', slug: '', color: '#4f6ef7' })
const rules = {
  name: [{ required: true, message: '请输入名称', trigger: 'blur' }],
  slug: [{ required: true, message: '请输入Slug', trigger: 'blur' }],
}

onMounted(() => fetchList())

async function fetchList() {
  loading.value = true
  try {
    const res = await adminApi.getTags()
    if (res.code === 0) list.value = res.data.list || res.data
  } catch (e) { ElMessage.error('获取标签失败') }
  finally { loading.value = false }
}

function openDialog(row) {
  editId.value = row?.id || null
  if (row) {
    form.name = row.name; form.slug = row.slug; form.color = row.color || '#4f6ef7'
  } else {
    form.name = ''; form.slug = ''; form.color = '#4f6ef7'
  }
  dialogVisible.value = true
}

async function handleSubmit() {
  const valid = await formRef.value.validate().catch(() => false)
  if (!valid) return
  submitLoading.value = true
  try {
    const payload = { ...form }
    let res
    if (editId.value) res = await adminApi.updateTag(editId.value, payload)
    else res = await adminApi.createTag(payload)
    if (res.code === 0) { ElMessage.success('操作成功'); dialogVisible.value = false; fetchList() }
    else { ElMessage.error(res.message || '操作失败') }
  } catch (e) { ElMessage.error('操作失败') }
  finally { submitLoading.value = false }
}

function handleDelete(row) {
  ElMessageBox.confirm(`确定删除标签「${row.name}」吗？`, '确认', {
    type: 'warning', confirmButtonText: '确定', cancelButtonText: '取消'
  }).then(async () => {
    try {
      const res = await adminApi.deleteTag(row.id)
      if (res.code === 0) { ElMessage.success('已删除'); fetchList() }
    } catch (e) { ElMessage.error('删除失败') }
  }).catch(() => {})
}
</script>

<style scoped lang="scss">
.page-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 20px; }
.page-title { font-size: 20px; font-weight: 700; margin-bottom: 4px; }
.page-subtitle { font-size: 13px; color: var(--color-text-muted); }
.text-muted { color: var(--color-text-muted); font-size: 13px; }
</style>
