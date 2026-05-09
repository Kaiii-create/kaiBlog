<template>
  <div class="page-container">
    <div class="page-header">
      <div>
        <h2 class="page-title">{{ isEdit ? '编辑文章' : '发布文章' }}</h2>
        <p class="page-subtitle">{{ isEdit ? '修改已有文章内容' : '创建一篇新的博客文章' }}</p>
      </div>
      <el-button @click="$router.push('/admin/articles')">
        <el-icon><ArrowLeft /></el-icon> 返回列表
      </el-button>
    </div>

    <div class="admin-card form-card">
      <el-form ref="formRef" :model="form" :rules="rules" label-width="80px" label-position="top">
        <el-row :gutter="24">
          <el-col :span="16">
            <el-form-item label="文章标题" prop="title">
              <el-input v-model="form.title" placeholder="请输入文章标题" size="large" />
            </el-form-item>
          </el-col>
          <el-col :span="8">
            <el-form-item label="Slug" prop="slug">
              <el-input v-model="form.slug" placeholder="URL 标识">
                <template #prefix>/</template>
              </el-input>
            </el-form-item>
          </el-col>
        </el-row>

        <el-row :gutter="24">
          <el-col :span="8">
            <el-form-item label="所属栏目" prop="category_id">
              <el-select v-model="form.category_id" placeholder="选择栏目" style="width: 100%">
                <el-option v-for="c in categories" :key="c.id" :label="c.name" :value="c.id" />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="8">
            <el-form-item label="状态" prop="status">
              <el-select v-model="form.status" placeholder="选择状态" style="width: 100%">
                <el-option label="发布" :value="1" />
                <el-option label="草稿" :value="0" />
                <el-option label="隐藏" :value="2" />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="8">
            <el-form-item label="标签">
              <el-select v-model="form.tags" multiple placeholder="选择标签" style="width: 100%">
                <el-option v-for="t in tags" :key="t.id" :label="t.name" :value="t.id" />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>

        <el-form-item label="文章摘要" prop="summary">
          <el-input v-model="form.summary" type="textarea" :rows="3" placeholder="文章摘要（可选）" />
        </el-form-item>

        <el-form-item label="文章内容" prop="content">
          <el-input
            v-model="form.content"
            type="textarea"
            :rows="16"
            placeholder="在此输入文章内容（支持 HTML）..."
            class="content-editor"
          />
        </el-form-item>

        <el-form-item label="封面图片">
          <el-upload
            class="cover-uploader"
            action="#"
            :auto-upload="false"
            :on-change="handleCoverChange"
            :show-file-list="false"
            accept="image/*"
          >
            <img v-if="form.cover" :src="form.cover" class="cover-preview" />
            <div v-else class="cover-placeholder">
              <el-icon :size="32"><Plus /></el-icon>
              <span>点击上传封面</span>
            </div>
          </el-upload>
        </el-form-item>

        <el-form-item>
          <el-button type="primary" size="large" :loading="submitLoading" @click="handleSubmit">
            {{ isEdit ? '更新文章' : '发布文章' }}
          </el-button>
          <el-button size="large" @click="$router.push('/admin/articles')">取消</el-button>
        </el-form-item>
      </el-form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { adminApi } from '../../api'
import { Plus, ArrowLeft } from '@element-plus/icons-vue'
import { ElMessage } from 'element-plus'

const route = useRoute()
const router = useRouter()

const isEdit = computed(() => !!route.params.id)

const formRef = ref(null)
const submitLoading = ref(false)
const categories = ref([])
const tags = ref([])

const form = reactive({
  title: '',
  slug: '',
  category_id: null,
  status: 0,
  tags: [],
  summary: '',
  content: '',
  cover: ''
})

const rules = {
  title: [{ required: true, message: '请输入文章标题', trigger: 'blur' }],
  category_id: [{ required: true, message: '请选择栏目', trigger: 'change' }],
  status: [{ required: true, message: '请选择状态', trigger: 'change' }]
}

onMounted(async () => {
  await Promise.all([loadCategories(), loadTags()])
  if (isEdit.value) {
    loadArticle()
  }
})

async function loadCategories() {
  try {
    const res = await adminApi.getAllCategories()
    if (res.code === 0) categories.value = res.data.list || res.data
  } catch (e) {}
}

async function loadTags() {
  try {
    const mockTags = [
      { id: 1, name: 'Vue3' }, { id: 2, name: 'JavaScript' },
      { id: 3, name: 'TypeScript' }, { id: 4, name: 'PHP' },
      { id: 5, name: 'MySQL' }, { id: 6, name: '性能优化' },
    ]
    tags.value = mockTags
  } catch (e) {}
}

async function loadArticle() {
  try {
    const res = await adminApi.getArticleDetail(route.params.id)
    if (res.code === 0) {
      const d = res.data
      form.title = d.title
      form.slug = d.slug
      form.category_id = d.category_id
      form.status = d.status
      form.tags = d.tags || []
      form.summary = d.summary || ''
      form.content = d.content || ''
      form.cover = d.cover || ''
    } else {
      ElMessage.error('文章不存在')
      router.push('/admin/articles')
    }
  } catch (e) {
    ElMessage.error('加载文章失败')
  }
}

function handleCoverChange(file) {
  const reader = new FileReader()
  reader.onload = (e) => {
    form.cover = e.target.result
  }
  reader.readAsDataURL(file.raw)
}

async function handleSubmit() {
  const valid = await formRef.value.validate().catch(() => false)
  if (!valid) return
  submitLoading.value = true
  try {
    let res
    if (isEdit.value) {
      res = await adminApi.updateArticle(route.params.id, form)
    } else {
      res = await adminApi.createArticle(form)
    }
    if (res.code === 0) {
      ElMessage.success(res.message || '操作成功')
      router.push('/admin/articles')
    } else {
      ElMessage.error(res.message || '操作失败')
    }
  } catch (e) {
    ElMessage.error('操作失败')
  } finally {
    submitLoading.value = false
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
.form-card {
  max-width: 960px;
}
.content-editor {
  font-family: 'Courier New', monospace;
  font-size: 14px;
  line-height: 1.6;
}
.cover-uploader {
  .cover-preview {
    width: 100%;
    max-width: 300px;
    border-radius: var(--radius-md);
    border: 1px solid var(--color-border);
  }
  .cover-placeholder {
    width: 200px;
    height: 120px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    border: 2px dashed var(--color-border);
    border-radius: var(--radius-md);
    color: var(--color-text-muted);
    cursor: pointer;
    transition: all 0.3s;
    font-size: 13px;
    &:hover {
      border-color: var(--color-primary);
      color: var(--color-primary);
    }
  }
}
</style>
