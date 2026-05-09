<template>
  <div class="login-page">
    <div class="page-container">
      <div class="login-container">
        <div class="login-card glass-card">
          <div class="login-header">
            <div class="login-logo">
              <span class="logo-icon">K</span>
            </div>
            <h2 class="gradient-text">欢迎回来</h2>
            <p>登录你的账号，继续阅读与创作。</p>
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
                placeholder="用户名 / 邮箱"
                :prefix-icon="User"
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

            <el-form-item prop="captcha">
              <div class="captcha-row">
                <el-input
                  v-model="form.captcha"
                  placeholder="验证码"
                  :prefix-icon="Key"
                  size="large"
                  maxlength="6"
                />
                <button
                  class="captcha-image"
                  type="button"
                  :disabled="captchaLoading"
                  @click="fetchCaptcha"
                >
                  <img v-if="captchaImage" :src="captchaImage" alt="验证码" />
                  <span v-else>{{ captchaLoading ? '加载中...' : '点击刷新' }}</span>
                </button>
              </div>
            </el-form-item>

            <el-form-item>
              <el-button
                class="gradient-btn"
                style="width:100%;padding:14px 0;font-size:15px;"
                :loading="loading"
                @click="handleLogin"
              >登录</el-button>
            </el-form-item>
          </el-form>

          <div class="login-footer">
            还没有账号？
            <router-link to="/register" class="register-link">立即注册</router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { Key, Lock, User } from '@element-plus/icons-vue'
import { ElMessage } from 'element-plus'
import { userApi, webApi } from '../../api'
import { useUserStore } from '../../stores/user'

const router = useRouter()
const route = useRoute()
const userStore = useUserStore()
const formRef = ref(null)
const loading = ref(false)
const captchaLoading = ref(false)
const captchaImage = ref('')
const captchaKey = ref('')

const form = reactive({
  username: '',
  password: '',
  captcha: '',
})

const rules = {
  username: [{ required: true, message: '请输入用户名或邮箱', trigger: 'blur' }],
  password: [
    { required: true, message: '请输入密码', trigger: 'blur' },
    { min: 3, message: '密码至少 3 位', trigger: 'blur' },
  ],
  captcha: [
    { required: true, message: '请输入验证码', trigger: 'blur' },
    { min: 4, message: '验证码格式不正确', trigger: 'blur' },
  ],
}

async function fetchCaptcha() {
  captchaLoading.value = true
  try {
    const res = await webApi.getCaptcha()
    captchaKey.value = res.data?.key || ''
    captchaImage.value = res.data?.image || ''
    form.captcha = ''
  } catch (e) {
    captchaKey.value = ''
    captchaImage.value = ''
    ElMessage.error(e?.message || '验证码加载失败')
  } finally {
    captchaLoading.value = false
  }
}

async function handleLogin() {
  if (!formRef.value) return
  try {
    await formRef.value.validate()
  } catch {
    return
  }

  if (!captchaKey.value) {
    ElMessage.error('验证码已失效，请刷新后重试')
    fetchCaptcha()
    return
  }

  loading.value = true
  try {
    const res = await userApi.login({
      username: form.username,
      password: form.password,
      captcha_key: captchaKey.value,
      captcha: form.captcha,
    })

    if (res.code === 0 && res.data) {
      const user = res.data.user || res.data
      userStore.setToken(res.data.token || '')
      userStore.setUserInfo({
        id: user.id,
        username: user.username,
        nickname: user.nickname,
        email: user.email,
        avatar: user.avatar || '',
        bio: user.bio || '',
      })
      const redirect = route.query.redirect || '/'
      router.push(redirect)
      return
    }

    ElMessage.error(res.message || '登录失败')
  } catch (e) {
    console.error('登录失败:', e)
    ElMessage.error(e?.message || '登录失败，请稍后重试')
    await fetchCaptcha()
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchCaptcha()
})
</script>

<style scoped lang="scss">
.login-page {
  min-height: calc(100vh - 200px);
  display: flex;
  align-items: center;
  padding: 40px 0;
}

.login-container {
  max-width: 420px;
  margin: 0 auto;
  width: 100%;
}

.login-card {
  padding: 40px;
}

.login-header {
  text-align: center;
  margin-bottom: 32px;
}

.login-logo {
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

.login-header h2 {
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 8px;
}

.login-header p {
  font-size: 14px;
  color: var(--color-text-secondary);
}

.login-form {
  width: 100%;

  :deep(.el-input) {
    width: 100%;
  }

  :deep(.el-input__wrapper) {
    background: var(--input-bg);
    border: 1px solid var(--color-border);
    border-radius: 10px;
    box-shadow: none !important;
    padding: 0 14px 0 16px;
    height: 48px;
    box-sizing: border-box;
    width: 100%;

    &:hover {
      border-color: var(--color-border-hover);
    }

    &.is-focus {
      border-color: var(--color-primary);
      box-shadow: none !important;
    }
  }

  :deep(.el-input__inner) {
    color: var(--color-text);
    height: 46px;
    min-width: 0;

    &::placeholder {
      color: var(--color-text-muted);
    }
  }

  :deep(.el-input__suffix) {
    flex: 0 0 24px;
    width: 24px;
    min-width: 24px;
    display: inline-flex;
    justify-content: center;
    align-items: center;
  }

  :deep(.el-input__suffix-inner) {
    width: 24px;
    min-width: 24px;
    display: inline-flex;
    justify-content: center;
    align-items: center;
  }

  :deep(.el-input__prefix-inner) .el-icon,
  :deep(.el-input__suffix-inner) .el-icon {
    color: var(--color-text-muted);
  }

  :deep(.el-form-item) {
    margin-bottom: 22px;
    width: 100%;
  }

  :deep(.el-form-item__content) {
    width: 100%;
  }
}

.captcha-row {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 132px;
  gap: 12px;
  width: 100%;
}

.captcha-image {
  width: 132px;
  height: 48px;
  padding: 0;
  border: 1px solid var(--color-border);
  border-radius: 10px;
  background: var(--color-bg-card);
  overflow: hidden;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: var(--color-text-secondary);
  flex-shrink: 0;

  img {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  &:disabled {
    opacity: 0.72;
  }
}

.login-footer {
  text-align: center;
  font-size: 14px;
  color: var(--color-text-secondary);
  margin-top: 8px;
}

.register-link {
  color: var(--color-primary-light);
  font-weight: 500;

  &:hover {
    color: var(--color-primary);
  }
}

@media (max-width: 768px) {
  .login-page {
    padding: 24px 0;
  }

  .login-card {
    padding: 28px 20px;
  }

  .login-header h2 {
    font-size: 1.3rem;
  }

  .captcha-row {
    grid-template-columns: 1fr;
  }

  .captcha-image {
    width: 100%;
  }
}
</style>
