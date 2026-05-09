<template>
  <div class="login-page">
    <div class="login-bg">
      <div class="orb orb-1" />
      <div class="orb orb-2" />
      <div class="orb orb-3" />
    </div>
    <div class="login-container">
      <div class="login-card">
        <div class="login-brand">
          <div class="brand-icon">K</div>
          <h1 class="brand-title">Kaiii 管理后台</h1>
          <p class="brand-desc">博客内容管理系统</p>
        </div>
        <el-form
          ref="formRef"
          :model="form"
          :rules="rules"
          class="login-form"
          @keyup.enter="handleLogin"
        >
          <el-form-item prop="username">
            <el-input
              v-model="form.username"
              placeholder="请输入账号"
              size="large"
              :prefix-icon="User"
              clearable
            />
          </el-form-item>
          <el-form-item prop="password">
            <el-input
              v-model="form.password"
              type="password"
              placeholder="请输入密码"
              size="large"
              :prefix-icon="Lock"
              show-password
            />
          </el-form-item>
          <el-form-item>
            <el-button
              type="primary"
              size="large"
              class="login-btn"
              :loading="loading"
              @click="handleLogin"
            >
              {{ loading ? '登录中...' : '登 录' }}
            </el-button>
          </el-form-item>
        </el-form>
        <div class="login-footer">
          <span>测试账号：admin / admin123</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAdminStore } from '../../stores/admin'
import { useThemeStore } from '../../stores/theme'
import { adminApi } from '../../api'
import { User, Lock } from '@element-plus/icons-vue'
import { ElMessage } from 'element-plus'

const router = useRouter()
const adminStore = useAdminStore()
const themeStore = useThemeStore()

const formRef = ref(null)
const loading = ref(false)
const form = reactive({ username: '', password: '' })
const rules = {
  username: [{ required: true, message: '请输入账号', trigger: 'blur' }],
  password: [{ required: true, message: '请输入密码', trigger: 'blur' }]
}

async function handleLogin() {
  const valid = await formRef.value.validate().catch(() => false)
  if (!valid) return
  loading.value = true
  try {
    const res = await adminApi.login({ username: form.username, password: form.password })
    if (res.code === 0) {
      adminStore.setToken(res.data.token)
      adminStore.setAdminInfo(res.data.admin || res.data)
      // 获取权限和菜单
      const profileRes = await adminApi.getProfile()
      if (profileRes.code === 0) {
        adminStore.setProfile(profileRes.data)
      }
      ElMessage.success('登录成功')
      router.push('/admin/dashboard')
    } else {
      ElMessage.error(res.message || '登录失败')
    }
  } catch (e) {
    ElMessage.error(e?.message || '登录失败，请重试')
  } finally {
    loading.value = false
  }
}
</script>

<style scoped lang="scss">
.login-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--color-bg);
  position: relative;
  overflow: hidden;
  transition: background 0.3s;
}
.login-bg {
  position: absolute;
  inset: 0;
  pointer-events: none;
}
.orb {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  transition: all 0.5s;
  &-1 {
    width: 400px; height: 400px;
    top: -10%; left: -5%;
    background: radial-gradient(circle, rgba(99, 102, 241, 0.15), transparent);
  }
  &-2 {
    width: 350px; height: 350px;
    bottom: -10%; right: -5%;
    background: radial-gradient(circle, rgba(6, 182, 212, 0.12), transparent);
  }
  &-3 {
    width: 250px; height: 250px;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    background: radial-gradient(circle, rgba(99, 102, 241, 0.06), transparent);
  }
}
.login-container {
  position: relative;
  z-index: 1;
  width: 100%;
  max-width: 420px;
  padding: 24px;
}
.login-card {
  background: var(--color-bg-card);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-lg);
  padding: 40px 32px;
  box-shadow: var(--shadow-lg);
  transition: all 0.3s;
}
.login-brand {
  text-align: center;
  margin-bottom: 32px;
  .brand-icon {
    width: 56px; height: 56px;
    margin: 0 auto 16px;
    display: flex; align-items: center; justify-content: center;
    background: var(--gradient-primary);
    border-radius: 16px;
    color: #fff;
    font-size: 24px;
    font-weight: 800;
  }
  .brand-title {
    font-size: 22px;
    font-weight: 700;
    margin-bottom: 6px;
  }
  .brand-desc {
    font-size: 14px;
    color: var(--color-text-muted);
  }
}
.login-form {
  :deep(.el-input__wrapper) {
    background: transparent;
    border: 1px solid var(--color-border);
    box-shadow: none;
    border-radius: var(--radius-md);
    padding: 4px 12px;
    transition: all 0.3s;
    &:hover { border-color: var(--color-border-hover); }
    &.is-focus { border-color: var(--color-primary); }
  }
  :deep(.el-input__inner) {
    &::placeholder { color: var(--color-text-muted); }
  }
  :deep(.el-input__prefix-inner) {
    color: var(--color-text-muted);
  }
  .el-form-item { margin-bottom: 22px; }
}
.login-btn {
  width: 100%;
  height: 48px;
  font-size: 16px;
  border-radius: var(--radius-md);
  background: var(--gradient-primary);
  border: none;
  transition: all 0.3s;
  &:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);
  }
}
.login-footer {
  text-align: center;
  margin-top: 16px;
  font-size: 12px;
  color: var(--color-text-muted);
}
</style>
