<template>
  <div class="page-container">
    <div class="page-header">
      <div>
        <h2 class="page-title">{{ isEdit ? '编辑教程' : '新建教程' }}</h2>
        <p class="page-subtitle">{{ isEdit ? '修改教程信息' : '创建一个新的系列教程' }}</p>
      </div>
      <el-button @click="$router.push('/admin/tutorials')">
        <el-icon><ArrowLeft /></el-icon> 返回列表
      </el-button>
    </div>

    <div class="admin-card form-card">
      <el-form ref="formRef" :model="form" :rules="rules" label-width="100px">
        <el-row :gutter="24">
          <el-col :span="12">
            <el-form-item label="教程标题" prop="title">
              <el-input v-model="form.title" placeholder="请输入教程标题" size="large" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="Slug" prop="slug">
              <el-input v-model="form.slug" placeholder="URL 标识，如 vue3-beginner-to-pro">
                <template #prefix>/</template>
              </el-input>
            </el-form-item>
          </el-col>
        </el-row>

        <el-form-item label="简介" prop="description">
          <el-input v-model="form.description" type="textarea" :rows="4" placeholder="教程简介/描述" />
        </el-form-item>

        <el-row :gutter="24">
          <el-col :span="8">
            <el-form-item label="难度" prop="difficulty">
              <el-select v-model="form.difficulty" style="width: 100%">
                <el-option :value="1" label="初级" />
                <el-option :value="2" label="中级" />
                <el-option :value="3" label="高级" />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="8">
            <el-form-item label="状态" prop="status">
              <el-select v-model="form.status" style="width: 100%">
                <el-option :value="1" label="发布" />
                <el-option :value="0" label="隐藏" />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="8">
            <el-form-item label="排序" prop="sort">
              <el-input-number v-model="form.sort" :min="0" :max="999" />
            </el-form-item>
          </el-col>
        </el-row>

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
              <el-icon :size="28"><Plus /></el-icon>
              <span>上传封面</span>
            </div>
          </el-upload>
        </el-form-item>

        <el-form-item>
          <el-button type="primary" size="large" :loading="submitLoading" @click="handleSubmit">
            {{ isEdit ? '更新教程' : '创建教程' }}
          </el-button>
          <el-button size="large" @click="$router.push('/admin/tutorials')">取消</el-button>
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

const form = reactive({
  title: '',
  slug: '',
  description: '',
  difficulty: 1,
  status: 1,
  sort: 0,
  cover: ''
})

const rules = {
  title: [{ required: true, message: '请输入教程标题', trigger: 'blur' }],
  slug: [{ required: true, message: '请输入Slug', trigger: 'blur' }]
}

onMounted(() => {
  if (isEdit.value) {
    loadTutorial()
  }
})

async function loadTutorial() {
  try {
    const res = await adminApi.getTutorialDetail(route.params.id)
    if (res.code === 0) {
      const d = res.data.tutorial || res.data
      form.title = d.title
      form.slug = d.slug
      form.description = d.description || ''
      form.difficulty = d.difficulty
      form.status = d.status
      form.sort = d.sort || 0
      form.cover = d.cover || ''
    } else {
      ElMessage.error('教程不存在')
      router.push('/admin/tutorials')
    }
  } catch (e) {
    ElMessage.error('加载教程失败')
  }
}

const coverUploading = ref(false)

function handleCoverChange(file) {
  coverUploading.value = true
  adminApi.uploadFile(file.raw).then(res => {
    if (res.code === 0) {
      form.cover = res.data.url || res.data.path || res.data
      ElMessage.success('封面上传成功')
    } else {
      ElMessage.error(res.message || '封面上传失败')
    }
  }).catch(() => {
    ElMessage.error('封面上传失败')
  }).finally(() => {
    coverUploading.value = false
  })
}

async function handleSubmit() {
  const valid = await formRef.value.validate().catch(() => false)
  if (!valid) return
  submitLoading.value = true
  try {
    let res
    if (isEdit.value) {
      res = await adminApi.updateTutorial(route.params.id, form)
    } else {
      res = await adminApi.createTutorial(form)
    }
    if (res.code === 0) {
      ElMessage.success(isEdit.value ? '更新成功' : '创建成功')
      router.push('/admin/tutorials')
    } else {
      ElMessage.error(res.message || '操作失败')
    }
  } catch (e) {
    ElMessage.error(isEdit.value ? '更新失败' : '创建失败')
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
  max-width: 800px;
}
.cover-uploader {
  .cover-preview {
    width: 100%;
    max-width: 280px;
    border-radius: var(--radius-md);
    border: 1px solid var(--color-border);
  }
  .cover-placeholder {
    width: 200px;
    height: 112px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 6px;
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
