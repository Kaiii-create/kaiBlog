<template>
  <div>
    <div class="page-header">
      <div>
        <h2 class="page-title">SEO 管理</h2>
        <p class="page-subtitle">搜索引擎优化与元数据配置</p>
      </div>
    </div>

    <div v-loading="loading" class="admin-card">
      <div class="config-list">
        <div v-for="item in items" :key="item.key" class="config-row">
          <div class="config-left">
            <div class="config-title">{{ item.description || item.key }}</div>
            <div class="config-key">key: {{ item.key }}</div>
          </div>
          <div class="config-right">
            <template v-if="item.type === 'switch'">
              <el-switch :model-value="item.value === true || item.value === '1' || item.value === 1"
                @change="(v) => changeItem(item, v ? '1' : '0')" />
            </template>
            <template v-else-if="item.type === 'number'">
              <el-input-number :model-value="Number(item.value) || 0" @change="(v) => changeItem(item, String(v))" />
            </template>
            <template v-else-if="item.type === 'textarea'">
              <el-input :model-value="item.value" type="textarea" :rows="3" style="width:360px"
                @blur="(e) => changeItem(item, e.target.value)" />
            </template>
            <template v-else-if="item.type === 'image'">
              <div class="img-wrap">
                <img v-if="item.value" :src="item.value" class="img-preview" @click="openUpload(item)" />
                <div v-else class="img-placeholder" @click="openUpload(item)">
                  <el-icon :size="18"><Plus /></el-icon><span>上传</span>
                </div>
              </div>
            </template>
            <template v-else>
              <el-input :model-value="item.value" style="width:360px"
                @blur="(e) => changeItem(item, e.target.value)" />
            </template>
            <el-button text type="danger" size="small" class="del-btn" @click="deleteItem(item)">
              <el-icon><Delete /></el-icon>
            </el-button>
          </div>
        </div>
        <el-empty v-if="!items.length" description="暂无 SEO 配置项" :image-size="60" />
      </div>
      <div class="config-footer">
        <el-button type="primary" :loading="saving" @click="batchSave">保存修改</el-button>
      </div>
    </div>

    <input ref="fileInput" type="file" accept="image/*" style="display:none" @change="onFileSelected" />
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { adminApi } from '../../api'
import { Plus, Delete } from '@element-plus/icons-vue'
import { ElMessage, ElMessageBox } from 'element-plus'

const loading = ref(false)
const saving = ref(false)
const allConfigs = ref({})
const changedKeys = reactive(new Set())
const fileInput = ref(null)
const uploadingItem = ref(null)

const items = computed(() => allConfigs.value['seo'] || [])

onMounted(() => fetchConfig())

async function fetchConfig() {
  loading.value = true
  try {
    const res = await adminApi.getConfig()
    if (res.code === 0) {
      allConfigs.value = res.data.configs || res.data
      changedKeys.clear()
    }
  } catch (e) {
    ElMessage.error('加载配置失败')
  } finally {
    loading.value = false
  }
}

function changeItem(item, val) {
  item.value = val
  changedKeys.add(item.key)
}

// ---- 图片上传 ----
function openUpload(item) {
  uploadingItem.value = item
  fileInput.value?.click()
}
async function onFileSelected(e) {
  const file = e.target.files?.[0]
  if (!file || !uploadingItem.value) return
  try {
    const res = await adminApi.uploadFile(file)
    if (res.code === 0) {
      changeItem(uploadingItem.value, res.data.url || res.data)
      ElMessage.success('上传成功')
    }
  } catch (e) { ElMessage.error('上传失败') }
  uploadingItem.value = null
  e.target.value = ''
}

async function batchSave() {
  if (changedKeys.size === 0) { ElMessage.info('没有修改需要保存'); return }
  saving.value = true
  try {
    const configs = []
    for (const group of Object.keys(allConfigs.value)) {
      for (const item of allConfigs.value[group]) {
        if (changedKeys.has(item.key)) configs.push({ key: item.key, value: item.value })
      }
    }
    const res = await adminApi.updateConfig({ configs })
    if (res.code === 0) { ElMessage.success(`已保存 ${configs.length} 项`); changedKeys.clear() }
    else { ElMessage.error(res.message || '保存失败') }
  } catch (e) { ElMessage.error('保存失败') }
  finally { saving.value = false }
}

function deleteItem(item) {
  ElMessageBox.confirm(`确定删除「${item.key}」吗？`, '确认', {
    type: 'warning', confirmButtonText: '确定', cancelButtonText: '取消'
  }).then(async () => {
    try {
      const res = await adminApi.deleteConfig(item.id)
      if (res.code === 0) { ElMessage.success('已删除'); fetchConfig() }
    } catch (e) { ElMessage.error('删除失败') }
  }).catch(() => {})
}
</script>

<style scoped lang="scss">
.page-header { margin-bottom: 20px; }
.page-title { font-size: 20px; font-weight: 700; margin-bottom: 4px; }
.page-subtitle { font-size: 13px; color: var(--color-text-muted); }
.config-list { min-height: 120px; }
.config-row {
  display: flex; align-items: center; justify-content: space-between;
  padding: 14px 0; border-bottom: 1px solid var(--color-border-light); gap: 16px;
  &:last-of-type { border-bottom: none; }
}
.config-left { flex-shrink: 0; min-width: 160px; }
.config-title { font-size: 14px; font-weight: 500; margin-bottom: 2px; }
.config-key { font-size: 12px; color: var(--color-text-muted); font-family: 'SF Mono', 'Fira Code', monospace; }
.config-right { display: flex; align-items: center; gap: 10px; }
.del-btn { opacity: 0.35; &:hover { opacity: 1; } }
.config-footer { display: flex; justify-content: flex-end; padding-top: 16px; border-top: 1px solid var(--color-border-light); margin-top: 4px; }

.img-wrap { display: flex; align-items: center; gap: 8px; }
.img-preview { width: 60px; height: 60px; object-fit: cover; border-radius: 6px; border: 1px solid var(--color-border); cursor: pointer; &:hover { border-color: var(--color-primary); } }
.img-placeholder { width: 60px; height: 60px; display: flex; flex-direction: column; align-items: center; justify-content: center; border: 2px dashed var(--color-border); border-radius: 6px; cursor: pointer; color: var(--color-text-muted); font-size: 11px; gap: 2px; &:hover { border-color: var(--color-primary); color: var(--color-primary); } }
</style>
