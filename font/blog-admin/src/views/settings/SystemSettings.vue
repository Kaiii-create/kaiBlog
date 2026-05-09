<template>
  <div>
    <div class="page-header">
      <h2 class="page-title">系统设置</h2>
      <p class="page-subtitle">管理网站所有配置项</p>
    </div>

    <div class="admin-card">
      <el-tabs v-model="activeGroup" class="config-tabs">
        <el-tab-pane v-for="(items, group) in groupedConfig" :key="group" :label="groupLabels[group] || group" :name="group">
          <template #label>
            <span class="tab-label">
              <el-icon :size="16"><component :is="groupIcons[group] || 'Setting'" /></el-icon>
              {{ groupLabels[group] || group }}
            </span>
          </template>
        </el-tab-pane>
      </el-tabs>

      <el-divider />

      <div v-loading="loading" class="config-list">
        <div v-for="item in currentItems" :key="item.key" class="config-row">
          <div class="config-left">
            <div class="config-title">{{ item.description || item.key }}</div>
            <div class="config-key">key: {{ item.key }}</div>
          </div>
          <div class="config-right">
            <!-- 开关 -->
            <template v-if="item.type === 'switch'">
              <el-switch :model-value="item.value === true || item.value === '1' || item.value === 1"
                @change="(v) => changeItem(item, v ? '1' : '0')" />
            </template>
            <!-- 数字 -->
            <template v-else-if="item.type === 'number'">
              <el-input-number :model-value="Number(item.value) || 0"
                @change="(v) => changeItem(item, String(v))" />
            </template>
            <!-- 多行文本 -->
            <template v-else-if="item.type === 'textarea'">
              <el-input :model-value="item.value" type="textarea" :rows="3" style="width:360px"
                @blur="(e) => changeItem(item, e.target.value)" />
            </template>
            <!-- 单选 -->
            <template v-else-if="item.type === 'radio'">
              <el-radio-group :model-value="item.value" @change="(v) => changeItem(item, v)">
                <el-radio v-for="opt in parseOptions(item)" :key="opt.value" :value="opt.value">
                  {{ opt.label }}
                </el-radio>
              </el-radio-group>
            </template>
            <!-- 多选 (value存逗号分隔字符串) -->
            <template v-else-if="item.type === 'checkbox'">
              <el-checkbox-group :model-value="splitVal(item.value)" @change="(v) => changeItem(item, v.join(','))">
                <el-checkbox v-for="opt in parseOptions(item)" :key="opt.value" :label="opt.value">
                  {{ opt.label }}
                </el-checkbox>
              </el-checkbox-group>
            </template>
            <!-- 图片上传 -->
            <template v-else-if="item.type === 'image'">
              <div class="img-upload-wrap">
                <img v-if="item.value" :src="item.value" class="img-preview" @click="openUpload(item)" />
                <div v-else class="img-placeholder" @click="openUpload(item)">
                  <el-icon :size="20"><Plus /></el-icon>
                  <span>上传图片</span>
                </div>
              </div>
            </template>
            <!-- 默认：单行文本 -->
            <template v-else>
              <el-input :model-value="item.value" style="width:360px"
                @blur="(e) => changeItem(item, e.target.value)" />
            </template>

            <el-button text type="danger" size="small" class="del-btn" @click="deleteItem(item)">
              <el-icon><Delete /></el-icon>
            </el-button>
          </div>
        </div>

        <el-empty v-if="!currentItems?.length" description="暂无配置项" :image-size="60" />
      </div>

      <div class="config-footer">
        <el-button plain @click="openAddDialog"><el-icon><Plus /></el-icon> 新增配置</el-button>
        <el-button type="primary" :loading="saving" @click="batchSave">保存修改</el-button>
      </div>
    </div>

    <!-- 隐藏的文件输入 -->
    <input ref="fileInput" type="file" accept="image/*" style="display:none" @change="onFileSelected" />

    <!-- 新增弹窗 -->
    <el-dialog v-model="addDialog" title="新增配置项" width="520px" :close-on-click-modal="false">
      <el-form ref="addFormRef" :model="addForm" :rules="addRules" label-width="90px">
        <el-form-item label="Key" prop="key">
          <el-input v-model="addForm.key" placeholder="唯一标识, 如 site_name" />
        </el-form-item>
        <el-form-item label="名称" prop="description">
          <el-input v-model="addForm.description" placeholder="配置项说明" />
        </el-form-item>
        <el-form-item label="类型" prop="type">
          <el-select v-model="addForm.type" style="width:100%">
            <el-option label="单行文本" value="string" />
            <el-option label="多行文本" value="textarea" />
            <el-option label="开关" value="switch" />
            <el-option label="数字" value="number" />
            <el-option label="图片" value="image" />
            <el-option label="单选" value="radio" />
            <el-option label="多选" value="checkbox" />
          </el-select>
        </el-form-item>
        <el-form-item label="选项" v-if="addForm.type === 'radio' || addForm.type === 'checkbox'">
          <el-input v-model="addForm.extra" type="textarea" :rows="3"
            placeholder='[{"label":"是","value":"1"},{"label":"否","value":"0"}]' />
          <div class="form-tip">JSON 数组格式，每个对象包含 label(显示名) 和 value(值)</div>
        </el-form-item>
        <el-form-item label="默认值">
          <el-input v-model="addForm.value" placeholder="默认值" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="addDialog = false">取消</el-button>
        <el-button type="primary" :loading="addLoading" @click="confirmAdd">确认</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { adminApi } from '../../api'
import { Plus, Delete, Tools, UserFilled, UploadFilled, Search, ChatDotSquare, Picture } from '@element-plus/icons-vue'
import { ElMessage, ElMessageBox } from 'element-plus'

const loading = ref(false)
const saving = ref(false)
const configData = ref({})
const activeGroup = ref('basic')
const changedKeys = reactive(new Set())
const fileInput = ref(null)
const uploadingItem = ref(null)

const groupLabels = {
  basic: '基础设置', user: '用户设置', upload: '上传设置',
  seo: 'SEO 设置', mail: '邮件设置', comment: '评论设置', api: 'API 设置',
}
const groupIcons = {
  basic: 'Tools', user: 'UserFilled', upload: 'UploadFilled',
  seo: 'Search', mail: 'Message', comment: 'ChatDotSquare', api: 'Connection',
}

const groups = ref([])

const groupedConfig = computed(() => configData.value)
const currentItems = computed(() => configData.value[activeGroup.value] || [])

onMounted(() => fetchConfig())

async function fetchConfig() {
  loading.value = true
  try {
    const res = await adminApi.getConfig()
    if (res.code === 0) {
      // 新格式：data.configs + data.groups
      const d = res.data
      if (d.configs) {
        configData.value = d.configs
        groups.value = d.groups || []
        // 用 groups 信息建立 groupIcons/labels 映射
        d.groups?.forEach(g => {
          groupLabels[g.name] = g.label
          groupIcons[g.name] = g.icon
        })
      } else {
        configData.value = d
      }
      const keys = Object.keys(configData.value)
      if (keys.length && !keys.includes(activeGroup.value)) activeGroup.value = keys[0]
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

// 解析 extra 选项
function parseOptions(item) {
  if (!item.extra) return []
  if (typeof item.extra === 'string') {
    try { return JSON.parse(item.extra)?.options || JSON.parse(item.extra) }
    catch (e) { return [] }
  }
  return item.extra.options || item.extra
}

// checkbox 多选拆分
function splitVal(v) {
  if (!v) return []
  return String(v).split(',').filter(Boolean)
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
      const url = res.data.url || res.data
      changeItem(uploadingItem.value, url)
      ElMessage.success('上传成功')
    } else {
      ElMessage.error(res.message || '上传失败')
    }
  } catch (e) {
    ElMessage.error('上传失败')
  }
  uploadingItem.value = null
  e.target.value = ''
}

async function batchSave() {
  if (changedKeys.size === 0) { ElMessage.info('没有修改需要保存'); return }
  saving.value = true
  try {
    const configs = []
    for (const group of Object.keys(configData.value)) {
      for (const item of configData.value[group]) {
        if (changedKeys.has(item.key)) configs.push({ key: item.key, value: item.value })
      }
    }
    const res = await adminApi.updateConfig({ configs })
    if (res.code === 0) { ElMessage.success(`已保存 ${configs.length} 项`); changedKeys.clear() }
    else { ElMessage.error(res.message || '保存失败') }
  } catch (e) { ElMessage.error('保存失败') }
  finally { saving.value = false }
}

// ---- 新增 ----
const addDialog = ref(false)
const addLoading = ref(false)
const addFormRef = ref(null)
const addForm = reactive({ key: '', description: '', type: 'string', value: '', extra: '' })
const addRules = {
  key: [{ required: true, message: '请输入 Key', trigger: 'blur' }],
  description: [{ required: true, message: '请输入名称', trigger: 'blur' }],
}

function openAddDialog() {
  addForm.key = ''; addForm.description = ''; addForm.type = 'string'; addForm.value = ''; addForm.extra = ''
  addDialog.value = true
}

async function confirmAdd() {
  const valid = await addFormRef.value.validate().catch(() => false)
  if (!valid) return
  addLoading.value = true
  try {
    let extra = null
    if (addForm.type === 'radio' || addForm.type === 'checkbox') {
      if (addForm.extra) {
        try { extra = JSON.parse(addForm.extra) } catch (e) { ElMessage.warning('选项 JSON 格式错误'); return }
      }
    }
    const res = await adminApi.createConfig({
      key: addForm.key, description: addForm.description,
      type: addForm.type, value: addForm.value, group: activeGroup.value, extra
    })
    if (res.code === 0) { ElMessage.success('新增成功'); addDialog.value = false; fetchConfig() }
    else { ElMessage.error(res.message || '新增失败') }
  } catch (e) { ElMessage.error('新增失败') }
  finally { addLoading.value = false }
}

// ---- 删除 ----
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
.form-tip { font-size: 12px; color: var(--color-text-muted); margin-top: 4px; }

.config-tabs {
  :deep(.el-tabs__header) { margin-bottom: 0; }
  :deep(.el-tabs__nav-wrap) { padding-left: 4px; }
}
.tab-label { display: flex; align-items: center; gap: 6px; font-size: 14px; }
:deep(.el-divider) { margin: 16px 0; }
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

.config-footer {
  display: flex; align-items: center; justify-content: space-between;
  padding-top: 16px; border-top: 1px solid var(--color-border-light); margin-top: 4px;
}

// 图片上传控件
.img-upload-wrap {
  display: flex; align-items: center; gap: 8px;
}
.img-preview {
  width: 80px; height: 80px; object-fit: cover; border-radius: 8px;
  border: 1px solid var(--color-border); cursor: pointer;
  transition: 0.15s; &:hover { border-color: var(--color-primary); }
}
.img-placeholder {
  width: 80px; height: 80px; display: flex; flex-direction: column; align-items: center; justify-content: center;
  border: 2px dashed var(--color-border); border-radius: 8px; cursor: pointer;
  color: var(--color-text-muted); font-size: 12px; gap: 4px; transition: 0.15s;
  &:hover { border-color: var(--color-primary); color: var(--color-primary); }
}
</style>
