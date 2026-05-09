<template>
  <div class="member-profile-page">
    <div class="profile-card glass-card">
      <h2 class="page-section-title gradient-text">个人资料</h2>

      <!-- 个人资料加载骨架 -->
      <template v-if="loadingProfile">
        <el-skeleton animated :count="4" style="margin-top:20px">
          <div style="display:flex;gap:20px;margin-bottom:30px">
            <el-skeleton-item variant="circle" style="width:80px;height:80px" />
            <div style="flex:1">
              <el-skeleton-item variant="p" style="width:40%" />
              <el-skeleton-item variant="text" style="margin-top:10px" />
            </div>
          </div>
          <el-skeleton-item variant="p" style="width:60%;margin-bottom:12px" v-for="n in 3" :key="n" />
        </el-skeleton>
      </template>

      <template v-else>
        <div class="avatar-section">
          <div class="avatar-wrapper" @click="triggerFileInput">
            <el-avatar :size="80" :icon="UserFilled" :src="form.avatar" />
            <div class="avatar-overlay">
              <el-icon><Camera /></el-icon>
              <span>更换头像</span>
            </div>
          </div>
          <input
            ref="fileInputRef"
            type="file"
            accept="image/*"
            style="display:none"
            @change="handleFileChange"
          />
          <div class="avatar-info">
            <h3>{{ userStore.nickname || '用户' }}</h3>
            <p>{{ form.email || '' }}</p>
          </div>
        </div>

        <el-form
          ref="formRef"
          :model="form"
          :rules="rules"
          label-position="top"
          class="profile-form"
        >
          <el-form-item label="昵称" prop="nickname">
            <el-input v-model="form.nickname" placeholder="请输入昵称" size="large" />
          </el-form-item>

          <el-form-item label="邮箱" prop="email">
            <el-input v-model="form.email" placeholder="请输入邮箱" size="large" />
          </el-form-item>

          <el-form-item label="个人简介" prop="bio">
            <el-input
              v-model="form.bio"
              type="textarea"
              :rows="4"
              placeholder="介绍一下自己..."
              maxlength="200"
              show-word-limit
            />
          </el-form-item>

          <el-form-item>
            <el-button
              class="gradient-btn"
              :loading="saving"
              @click="handleSave"
            >保存修改</el-button>
          </el-form-item>
        </el-form>

        <el-divider style="border-color:var(--color-border)" />

        <div class="security-section">
          <h3 class="gradient-text">修改密码</h3>
          <p class="security-desc">修改密码后需要重新登录</p>
          <el-form ref="pwdFormRef" :model="pwdForm" label-position="top" class="profile-form">
            <el-form-item label="新密码" prop="password">
              <el-input
                v-model="pwdForm.password"
                type="password"
                placeholder="如需修改密码请填写"
                size="large"
                show-password
              />
            </el-form-item>
            <el-form-item>
              <el-button
                class="gradient-btn"
                :loading="saving"
                @click="handleSaveWithPwd"
              >保存密码</el-button>
            </el-form-item>
          </el-form>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { UserFilled, Camera } from '@element-plus/icons-vue'
import { ElMessage } from 'element-plus'
import { userApi } from '../../api'
import { useUserStore } from '../../stores/user'

const userStore = useUserStore()
const formRef = ref(null)
const pwdFormRef = ref(null)
const fileInputRef = ref(null)
const saving = ref(false)
const loadingProfile = ref(true)

const form = reactive({
  nickname: '',
  email: '',
  bio: '',
  avatar: ''
})

const pwdForm = reactive({
  password: ''
})

const rules = {
  nickname: [{ required: true, message: '请输入昵称', trigger: 'blur' }],
  email: [
    { required: true, message: '请输入邮箱', trigger: 'blur' },
    { type: 'email', message: '请输入正确的邮箱格式', trigger: 'blur' }
  ]
}

onMounted(async () => {
  // 先从服务端获取最新用户信息
  try {
    const res = await userApi.getUserInfo()
    if (res.code === 0 && res.data) {
      const info = res.data
      userStore.setUserInfo(info)
      form.nickname = info.nickname || ''
      form.email = info.email || ''
      form.bio = info.bio || ''
      form.avatar = info.avatar || ''
    }
  } catch (e) {
    // 如果 API 失败，从本地 store 读取
    if (userStore.userInfo) {
      form.nickname = userStore.userInfo.nickname || ''
      form.email = userStore.userInfo.email || ''
      form.bio = userStore.userInfo.bio || ''
      form.avatar = userStore.userInfo.avatar || ''
    }
  } finally {
    loadingProfile.value = false
  }
})

function triggerFileInput() {
  fileInputRef.value?.click()
}

async function handleFileChange(e) {
  const file = e.target.files?.[0]
  if (!file) return

  try {
    const res = await userApi.uploadFile(file)
    if (res.code === 0 && res.data) {
      const fileUrl = res.data.file_url || res.data.url || ''
      form.avatar = fileUrl
      ElMessage.success('头像上传成功')
    } else {
      ElMessage.error(res.message || '上传失败')
    }
  } catch (e) {
    ElMessage.error(e.message || '上传失败，请重试')
  }
}

async function handleSave() {
  if (!formRef.value) return
  try {
    await formRef.value.validate()
  } catch {
    return
  }

  saving.value = true
  try {
    const res = await userApi.updateProfile({
      nickname: form.nickname,
      email: form.email,
      bio: form.bio,
      avatar: form.avatar
    })
    if (res.code === 0) {
      userStore.setUserInfo({ ...userStore.userInfo, ...res.data })
      ElMessage.success('资料更新成功')
    } else {
      ElMessage.error(res.message || '更新失败')
    }
  } catch (e) {
    ElMessage.error(e.message || '更新失败，请重试')
  } finally {
    saving.value = false
  }
}

async function handleSaveWithPwd() {
  if (!pwdForm.value.password) {
    ElMessage.info('请输入新密码')
    return
  }

  saving.value = true
  try {
    const res = await userApi.updateProfile({
      password: pwdForm.value.password
    })
    if (res.code === 0) {
      ElMessage.success('密码修改成功，请重新登录')
      pwdForm.value.password = ''
      userStore.logout()
      // 跳转到登录
      window.location.href = '/login'
    } else {
      ElMessage.error(res.message || '修改失败')
    }
  } catch (e) {
    ElMessage.error(e.message || '修改失败，请重试')
  } finally {
    saving.value = false
  }
}
</script>

<style scoped lang="scss">
.member-profile-page {
  min-height: 400px;
}
.profile-card {
  padding: 32px;
}
.page-section-title {
  font-size: 1.3rem;
  font-weight: 700;
  margin-bottom: 24px;
}
.avatar-section {
  display: flex;
  align-items: center;
  gap: 24px;
  margin-bottom: 32px;
  padding-bottom: 24px;
  border-bottom: 1px solid var(--color-border);
}
.avatar-wrapper {
  position: relative;
  cursor: pointer;
  &:hover .avatar-overlay { opacity: 1; }
}
.avatar-overlay {
  position: absolute;
  inset: 0;
  border-radius: 50%;
  background: rgba(0,0,0,0.5);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: 11px;
  gap: 4px;
  opacity: 0;
  transition: opacity 0.3s;
  .el-icon { font-size: 20px; }
}
.avatar-info {
  h3 { font-size: 16px; font-weight: 600; margin-bottom: 4px; }
  p { font-size: 13px; color: var(--color-text-muted); }
}
.profile-form {
  max-width: 500px;
  :deep(.el-input__wrapper) {
    background: var(--input-bg);
    border: 1px solid var(--color-border);
    border-radius: 10px;
    box-shadow: none !important;
    padding: 0 16px;
    height: 48px;
    box-sizing: border-box;
    &:hover { border-color: var(--color-border-hover); }
    &.is-focus {
      border-color: var(--color-primary);
      box-shadow: none !important;
    }
  }
  :deep(.el-input__inner) {
    color: var(--color-text);
    height: 46px;
    &::placeholder { color: var(--color-text-muted); }
  }
  :deep(.el-textarea__inner) {
    background: rgba(255,255,255,0.04);
    border: 1px solid var(--color-border);
    border-radius: 10px;
    box-shadow: none;
    color: var(--color-text);
    &:hover { border-color: var(--color-border-hover); }
    &:focus { border-color: var(--color-primary); }
  }
  :deep(.el-form-item__label) { color: var(--color-text-secondary); font-size: 13px; font-weight: 500; }
}
@media (max-width: 768px) {
  .profile-card { padding: 20px; }
  .avatar-section { flex-direction: column; text-align: center; gap: 12px; }
  .profile-form { max-width: 100%; }
  .security-section { margin-top: 8px; }
}
.security-section {
  margin-top: 16px;
  h3 { font-size: 1.1rem; font-weight: 700; margin-bottom: 4px; }
}
.security-desc {
  font-size: 13px;
  color: var(--color-text-muted);
  margin-bottom: 20px;
}
</style>
