<template>
  <div class="page-container">
    <div class="page-header">
      <div>
        <h2 class="page-title">教程管理</h2>
        <p class="page-subtitle">管理系列教程</p>
      </div>
      <el-button type="primary" @click="$router.push('/admin/tutorials/create')">
        <el-icon><Plus /></el-icon> 新建教程
      </el-button>
    </div>

    <div class="admin-card">
      <el-table :data="list" stripe style="width: 100%" v-loading="loading" empty-text="暂无教程数据">
        <el-table-column type="index" label="#" width="55" align="center" />
        <el-table-column prop="title" label="教程名称" min-width="200">
          <template #default="{ row }">
            <span class="tutorial-title">{{ row.title }}</span>
          </template>
        </el-table-column>
        <el-table-column label="难度" width="90" align="center">
          <template #default="{ row }">
            <el-tag :type="difficultyType(row.difficulty)" size="small" effect="dark">
              {{ difficultyLabel(row.difficulty) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="状态" width="80" align="center">
          <template #default="{ row }">
            <el-tag :type="row.status === 1 ? 'success' : 'danger'" size="small" effect="dark">
              {{ row.status === 1 ? '发布' : '隐藏' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="chapter_count" label="章节数" width="80" align="center" />
        <el-table-column prop="view_count" label="浏览量" width="80" align="center" />
        <el-table-column label="操作" width="260" align="center" fixed="right">
          <template #default="{ row }">
            <el-button link type="primary" size="small" @click="$router.push(`/admin/tutorials/${row.id}/chapters`)">
              章节管理
            </el-button>
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

    <!-- 编辑弹窗 -->
    <el-dialog
      v-model="dialogVisible"
      title="编辑教程"
      width="560px"
      :close-on-click-modal="false"
      class="admin-dialog"
    >
      <el-form ref="editFormRef" :model="editForm" :rules="editRules" label-width="80px">
        <el-form-item label="标题" prop="title">
          <el-input v-model="editForm.title" placeholder="教程标题" />
        </el-form-item>
        <el-form-item label="Slug" prop="slug">
          <el-input v-model="editForm.slug" placeholder="URL标识" />
        </el-form-item>
        <el-form-item label="简介" prop="description">
          <el-input v-model="editForm.description" type="textarea" :rows="3" placeholder="教程简介" />
        </el-form-item>
        <el-form-item label="难度" prop="difficulty">
          <el-select v-model="editForm.difficulty" style="width: 100%">
            <el-option :value="1" label="初级" />
            <el-option :value="2" label="中级" />
            <el-option :value="3" label="高级" />
          </el-select>
        </el-form-item>
        <el-form-item label="状态">
          <el-radio-group v-model="editForm.status">
            <el-radio :value="1">发布</el-radio>
            <el-radio :value="0">隐藏</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="封面上传">
          <el-upload
            action="#"
            :auto-upload="false"
            :on-change="handleEditCover"
            :show-file-list="false"
            accept="image/*"
          >
            <img v-if="editForm.cover" :src="editForm.cover" class="edit-cover" />
            <el-button v-else size="small">选择封面</el-button>
          </el-upload>
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="submitLoading" @click="handleEditSubmit">确认</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { adminApi } from '../../api'
import { Plus } from '@element-plus/icons-vue'
import { ElMessage, ElMessageBox } from 'element-plus'

const router = useRouter()
const loading = ref(false)
const list = ref([])
const pagination = reactive({
  page: 1,
  page_size: 10,
  total: 0
})

const dialogVisible = ref(false)
const editId = ref(null)
const submitLoading = ref(false)
const editFormRef = ref(null)
const editForm = reactive({
  title: '',
  slug: '',
  description: '',
  difficulty: 1,
  status: 1,
  cover: ''
})
const editRules = {
  title: [{ required: true, message: '请输入标题', trigger: 'blur' }],
  slug: [{ required: true, message: '请输入Slug', trigger: 'blur' }]
}

onMounted(() => {
  fetchList()
})

async function fetchList() {
  loading.value = true
  try {
    const res = await adminApi.getTutorials({ page: pagination.page })
    if (res.code === 0) {
      list.value = res.data.list
      pagination.total = res.data.pagination.total
      pagination.page = res.data.pagination.page
    }
  } catch (e) {
    ElMessage.error('获取教程列表失败')
  } finally {
    loading.value = false
  }
}

function difficultyType(d) {
  if (d === 1) return 'success'
  if (d === 2) return 'warning'
  return 'danger'
}

function difficultyLabel(d) {
  if (d === 1) return '初级'
  if (d === 2) return '中级'
  return '高级'
}

function openEditDialog(row) {
  editId.value = row.id
  editForm.title = row.title
  editForm.slug = row.slug
  editForm.description = row.description || ''
  editForm.difficulty = row.difficulty
  editForm.status = row.status
  editForm.cover = row.cover || ''
  dialogVisible.value = true
}

function handleEditCover(file) {
  const reader = new FileReader()
  reader.onload = (e) => { editForm.cover = e.target.result }
  reader.readAsDataURL(file.raw)
}

async function handleEditSubmit() {
  const valid = await editFormRef.value.validate().catch(() => false)
  if (!valid) return
  submitLoading.value = true
  try {
    const res = await adminApi.updateTutorial(editId.value, editForm)
    if (res.code === 0) {
      ElMessage.success('更新成功')
      dialogVisible.value = false
      fetchList()
    }
  } catch (e) {
    ElMessage.error('更新失败')
  } finally {
    submitLoading.value = false
  }
}

function handleDelete(row) {
  ElMessageBox.confirm(`确定要删除教程「${row.title}」吗？`, '删除确认', {
    confirmButtonText: '确定',
    cancelButtonText: '取消',
    type: 'warning'
  }).then(async () => {
    try {
      const res = await adminApi.deleteTutorial(row.id)
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
.tutorial-title {
  font-weight: 500;
}
.pagination-wrap {
  margin-top: 20px;
}
.edit-cover {
  width: 120px;
  height: 68px;
  object-fit: cover;
  border-radius: var(--radius-sm);
  border: 1px solid var(--color-border);
}
.admin-dialog {
  :deep(.el-dialog) {
    background: #1a1a2e;
    border: 1px solid rgba(99, 102, 241, 0.15);
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
