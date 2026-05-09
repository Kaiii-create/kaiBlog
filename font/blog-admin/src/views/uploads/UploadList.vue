<template>
  <div class="page-container">
    <div class="page-header">
      <div>
        <h2 class="page-title">文件管理</h2>
        <p class="page-subtitle">管理上传的图片和文件</p>
      </div>
    </div>

    <div class="admin-card">
      <el-table :data="list" stripe style="width: 100%" v-loading="loading" empty-text="暂无上传文件">
        <el-table-column type="index" label="#" width="55" align="center" />
        <el-table-column label="文件预览" width="80" align="center">
          <template #default="{ row }">
            <template v-if="row.mime_type?.startsWith('image/')">
              <el-image
                :src="row.file_url"
                :preview-src-list="[row.file_url]"
                fit="cover"
                class="file-thumb"
              >
                <template #error>
                  <el-icon :size="24"><Picture /></el-icon>
                </template>
              </el-image>
            </template>
            <el-icon v-else :size="24"><Document /></el-icon>
          </template>
        </el-table-column>
        <el-table-column prop="original_name" label="文件名" min-width="200">
          <template #default="{ row }">
            <div class="file-name">
              <span>{{ row.original_name }}</span>
              <el-tag :type="fileTypeTag(row.mime_type)" size="small" effect="dark" class="file-type-tag">
                {{ fileTypeLabel(row.mime_type) }}
              </el-tag>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="文件大小" width="110" align="center">
          <template #default="{ row }">
            <span class="text-muted">{{ formatSize(row.file_size) }}</span>
          </template>
        </el-table-column>
        <el-table-column label="MIME 类型" width="150" align="center">
          <template #default="{ row }">
            <span class="text-muted">{{ row.mime_type || '-' }}</span>
          </template>
        </el-table-column>
        <el-table-column label="上传时间" width="170" align="center">
          <template #default="{ row }">
            <span class="text-muted">{{ row.created_at }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="100" align="center" fixed="right">
          <template #default="{ row }">
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
import { Picture, Document } from '@element-plus/icons-vue'
import { ElMessage, ElMessageBox } from 'element-plus'

const loading = ref(false)
const list = ref([])
const pagination = reactive({
  page: 1,
  page_size: 10,
  total: 0
})

onMounted(() => {
  fetchList()
})

async function fetchList() {
  loading.value = true
  try {
    const res = await adminApi.getUploads({ page: pagination.page })
    if (res.code === 0) {
      list.value = res.data.list
      pagination.total = res.data.pagination.total
      pagination.page = res.data.pagination.page
    }
  } catch (e) {
    ElMessage.error('获取文件列表失败')
  } finally {
    loading.value = false
  }
}

function formatSize(bytes) {
  if (!bytes) return '-'
  if (bytes < 1024) return bytes + ' B'
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB'
  return (bytes / (1024 * 1024)).toFixed(1) + ' MB'
}

function fileTypeTag(mime) {
  if (!mime) return 'info'
  if (mime.startsWith('image/')) return 'success'
  if (mime.startsWith('video/')) return 'warning'
  if (mime.startsWith('text/')) return 'primary'
  return 'info'
}

function fileTypeLabel(mime) {
  if (!mime) return '未知'
  if (mime.startsWith('image/')) return '图片'
  if (mime.startsWith('video/')) return '视频'
  if (mime.startsWith('text/')) return '文档'
  return '文件'
}

function handleDelete(row) {
  ElMessageBox.confirm(`确定要删除文件「${row.original_name}」吗？`, '删除确认', {
    confirmButtonText: '确定',
    cancelButtonText: '取消',
    type: 'warning'
  }).then(async () => {
    try {
      const res = await adminApi.deleteUpload(row.id)
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
.file-thumb {
  width: 40px;
  height: 40px;
  border-radius: var(--radius-sm);
  object-fit: cover;
  border: 1px solid var(--color-border);
}
.file-name {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  .file-type-tag {
    flex-shrink: 0;
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
