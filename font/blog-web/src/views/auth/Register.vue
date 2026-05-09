<template>
  <div class="register-page">
    <div class="page-container">
      <div class="register-container">
        <div class="register-card glass-card">
          <div class="register-header">
            <div class="register-logo">
              <span class="logo-icon">K</span>
            </div>
            <h2 class="gradient-text">创建账号</h2>
            <p>加入社区，开启技术探索之旅</p>
          </div>

          <el-form
            ref="formRef"
            :model="form"
            :rules="rules"
            class="register-form"
            @keyup.enter="handleRegister"
          >
            <el-form-item prop="username">
              <el-input
                v-model="form.username"
                placeholder="用户名"
                :prefix-icon="User"
                size="large"
              />
            </el-form-item>

            <el-form-item prop="email">
              <el-input
                v-model="form.email"
                placeholder="邮箱"
                :prefix-icon="Message"
                size="large"
              />
            </el-form-item>

            <el-form-item prop="password">
              <el-input
                v-model="form.password"
                type="password"
                placeholder="密码"
                :prefix-icon="Lock"
                size="large"
                show-password
              />
            </el-form-item>

            <el-form-item prop="confirmPassword">
              <el-input
                v-model="form.confirmPassword"
                type="password"
                placeholder="确认密码"
                :prefix-icon="Lock"
                size="large"
                show-password
              />
            </el-form-item>

            <el-form-item>
              <el-button
                class="gradient-btn"
                style="width:100%;padding:14px 0;font-size:15px;"
                :loading="loading"
                @click="handleRegister"
              >注 册</el-button>
            </el-form-item>
          </el-form>

          <div class="register-footer">
            已有账号？
            <router-link to="/login" class="login-link">立即登录</router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { User, Message, Lock } from '@element-plus/icons-vue'
import { ElMessage } from 'element-plus'
import { userApi } from '../../api'

const router = useRouter()
const formRef = ref(null)
const loading = ref(false)

const form = reactive({
  username: '',
  email: '',
  password: '',
  confirmPassword: ''
})

const validatePass = (rule, value, callback) => {
  if (value !== form.password) {
    callback(new Error('两次输入的密码不一致'))
  } else {
    callback()
  }
}

const rules = {
  username: [
    { required: true, message: '请输入用户名', trigger: 'blur' },
    { min: 3, max: 20, message: '用户名长度在 3 到 20 个字符', trigger: 'blur' }
  ],
  email: [
    { required: true, message: '请输入邮箱地址', trigger: 'blur' },
    { type: 'email', message: '请输入正确的邮箱格式', trigger: 'blur' }
  ],
  password: [
    { required: true, message: '请输入密码', trigger: 'blur' },
    { min: 6, message: '密码至少6位', trigger: 'blur' }
  ],
  confirmPassword: [
    { required: true, message: '请确认密码', trigger: 'blur' },
    { validator: validatePass, trigger: 'blur' }
  ]
}

async function handleRegister() {
  if (!formRef.value) return
  try {
    await formRef.value.validate()
  } catch {
    return
  }

  loading.value = true
  try {
    const res = await userApi.register({
      username: form.username,
      email: form.email,
      password: form.password,
      nickname: form.username
    })
    if (res.code === 0) {
      ElMessage.success('注册成功，请登录')
      router.push('/login')
    } else {
      ElMessage.error(res.message || '注册失败')
    }
  } catch (e) {
    console.error('注册失败:', e)
    ElMessage.error('注册失败，请重试')
  } finally {
    loading.value = false
  }
}
</script>

<style scoped lang="scss">
.register-page {
  min-height: calc(100vh - 70px - 200px);
  display: flex;
  align-items: center;
  padding: 40px 0;
}
.register-container {
  max-width: 420px;
  margin: 0 auto;
  width: 100%;
}
.register-card {
  padding: 40px;
}
.register-header {
  text-align: center;
  margin-bottom: 32px;
}
.register-logo {
  margin-bottom: 16px;
  .logo-icon {
    display: inline-flex;
    width: 48px;
    height: 48px;
    background: var(--gradient-primary);
    border-radius: 12px;
    color: #fff;
    font-size: 22px;
    font-weight: 800;
    align-items: center;
    justify-content: center;
  }
}
.register-header h2 {
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 8px;
}
.register-header p {
  font-size: 14px;
  color: var(--color-text-secondary);
}
.register-form {
  width: 100%;
  :deep(.el-input__wrapper) {
    background: var(--input-bg);
    border: 1px solid var(--color-border);
    border-radius: 10px;
    box-shadow: none !important;
    padding: 0 38px 0 16px;
    height: 48px;
    box-sizing: border-box;
    width: 100%;
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
  :deep(.el-input__prefix-inner) .el-icon { color: var(--color-text-muted); }
  :deep(.el-input__suffix-inner) .el-icon { color: var(--color-text-muted); }
  :deep(.el-form-item) {
    margin-bottom: 22px;
    width: 100%;
  }
  :deep(.el-form-item__content) { width: 100%; }
}
.register-footer {
  text-align: center;
  font-size: 14px;
  color: var(--color-text-secondary);
  margin-top: 8px;
}
.login-link {
  color: var(--color-primary-light);
  font-weight: 500;
  &:hover { color: var(--color-primary); }
}
@media (max-width: 768px) {
  .register-page { padding: 24px 0; }
  .register-card { padding: 28px 20px; }
  .register-header h2 { font-size: 1.3rem; }
}
</style>
