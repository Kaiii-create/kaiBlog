<template>
  <div class="not-found-page">
    <div class="page-container">
      <div class="not-found-content">
        <div class="not-found-illustration">
          <div class="error-code">
            <span class="digit">4</span>
            <span class="digit zero">
              <el-icon><CircleClose /></el-icon>
            </span>
            <span class="digit">4</span>
          </div>
        </div>
        <h1 class="gradient-text">页面未找到</h1>
        <p>抱歉，您访问的页面不存在或已被移除</p>
        <div class="not-found-actions">
          <el-button class="gradient-btn" @click="$router.push('/')">
            <el-icon><House /></el-icon>
            返回首页
          </el-button>
          <el-button class="back-btn" @click="$router.back()">
            <el-icon><ArrowLeft /></el-icon>
            返回上一页
          </el-button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { CircleClose, House, ArrowLeft } from '@element-plus/icons-vue'
</script>

<style scoped lang="scss">
.not-found-page {
  min-height: calc(100vh - 70px - 200px);
  display: flex;
  align-items: center;
  padding: 60px 0;
}
.not-found-content {
  text-align: center;
  max-width: 500px;
  margin: 0 auto;
}
.not-found-illustration {
  margin-bottom: 32px;
}
.error-code {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
}
.digit {
  font-size: 6rem;
  font-weight: 800;
  background: linear-gradient(135deg, #3b82f6, #06b6d4);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  line-height: 1;
}
.zero {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  background: rgba(59, 130, 246, 0.1);
  display: flex;
  align-items: center;
  justify-content: center;
  -webkit-text-fill-color: initial;
  .el-icon { font-size: 56px; color: var(--color-primary-light); }
}
.not-found-content h1 {
  font-size: 1.6rem;
  font-weight: 700;
  margin-bottom: 12px;
}
.not-found-content p {
  font-size: 14px;
  color: var(--color-text-secondary);
  margin-bottom: 32px;
}
.not-found-actions {
  display: flex;
  gap: 16px;
  justify-content: center;
}
.back-btn {
  background: transparent;
  border: 1px solid var(--color-border);
  color: var(--color-text);
  &:hover { border-color: var(--color-primary); color: var(--color-primary); }
}
@media (max-width: 768px) {
  .digit { font-size: 4rem; }
  .zero { width: 72px; height: 72px; .el-icon { font-size: 40px; } }
  .error-code { gap: 8px; }
  .not-found-actions { flex-direction: column; align-items: center; }
}
</style>
