<template>
  <footer class="site-footer">
    <div class="page-container">
      <div class="footer-content">
        <div class="footer-brand">
          <div class="footer-logo">
            <span class="logo-icon">K</span>
            <span class="gradient-text">{{ site?.name || 'Kaiii 技术博客' }}</span>
          </div>
          <p class="footer-desc">{{ site?.description || '分享技术，记录成长。' }}</p>
          <!-- 社交图标 -->
          <div class="footer-social" v-if="contact">
            <a v-if="contact.github" :href="contact.github" target="_blank" class="social-link" title="GitHub">
              <el-icon><Promotion /></el-icon>
            </a>
            <a v-if="contact.email" :href="'mailto:' + contact.email" class="social-link" title="邮箱">
              <el-icon><Message /></el-icon>
            </a>
            <a v-if="contact.bilibili" :href="contact.bilibili" target="_blank" class="social-link" title="B站">
              <el-icon><VideoPlay /></el-icon>
            </a>
          </div>
        </div>
        <div class="footer-links">
          <div class="link-group">
            <h4>内容</h4>
            <router-link to="/articles">文章列表</router-link>
            <router-link to="/tutorials">教程中心</router-link>
            <router-link to="/search">搜索</router-link>
          </div>
          <div class="link-group">
            <h4>联系</h4>
            <span v-if="contact?.email">{{ contact.email }}</span>
            <span v-if="contact?.qq">QQ: {{ contact.qq }}</span>
            <a v-if="contact?.gitee" :href="contact.gitee" target="_blank">Gitee</a>
          </div>
          <div class="link-group" v-if="links && links.length">
            <h4>友情链接</h4>
            <a v-for="link in links" :key="link.title" :href="link.url" target="_blank">{{ link.title }}</a>
          </div>
        </div>
      </div>
      <div class="footer-bottom">
        <span>{{ footer?.copyright || footer?.text || '&copy; 2025-2026 Kaiii.top' }}</span>
        <div class="footer-bottom-right">
          <span class="icp" v-if="footer?.icp">{{ footer.icp }}</span>
          <span class="beian" v-if="footer?.beian">{{ footer.beian }}</span>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { computed } from 'vue'
import { useAppStore } from '../stores/app'
import { Promotion, Message, VideoPlay } from '@element-plus/icons-vue'

const appStore = useAppStore()

const site = computed(() => appStore.siteConfig?.site || null)
const contact = computed(() => appStore.siteConfig?.contact || null)
const footer = computed(() => appStore.siteConfig?.footer || null)
const links = computed(() => appStore.siteConfig?.footer?.links || [])
</script>

<style scoped lang="scss">
.site-footer {
  background: var(--footer-bg);
  border-top: 1px solid var(--color-border);
  padding: 48px 0 24px;
  margin-top: 80px;
}
.footer-content {
  display: flex;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 32px;
  margin-bottom: 32px;
}
.footer-brand {
  max-width: 320px;
}
.footer-logo {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 18px;
  font-weight: 700;
  margin-bottom: 12px;
  .logo-icon {
    width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;
    background: var(--gradient-primary); border-radius: 6px; color: #fff; font-weight: 800;
  }
}
.footer-desc { color: var(--color-text-muted); font-size: 14px; margin-bottom: 16px; }

.footer-social {
  display: flex;
  gap: 10px;
}
.social-link {
  width: 36px; height: 36px;
  border-radius: 50%;
  border: 1px solid var(--color-border);
  display: flex; align-items: center; justify-content: center;
  color: var(--color-text-muted);
  font-size: 16px;
  transition: all 0.3s;
  &:hover {
    border-color: var(--color-primary);
    color: var(--color-primary);
    box-shadow: var(--shadow-glow);
    transform: translateY(-2px);
  }
}

.link-group {
  h4 { font-size: 14px; font-weight: 600; margin-bottom: 12px; color: var(--color-text); }
  a, span {
    display: block; color: var(--color-text-muted); font-size: 13px; margin-bottom: 8px;
    text-decoration: none; transition: color 0.3s;
    &:hover { color: var(--color-primary); }
  }
}
.footer-bottom {
  display: flex; justify-content: space-between; align-items: center;
  padding-top: 16px; border-top: 1px solid var(--color-border);
  color: var(--color-text-muted); font-size: 12px;
}
.footer-bottom-right {
  display: flex; gap: 16px;
}

@media (max-width: 768px) {
  .site-footer { padding: 32px 0 20px; margin-top: 40px; }
  .footer-content { flex-direction: column; gap: 24px; }
  .footer-brand { max-width: 100%; }
  .link-group { width: 50%; }
  .footer-bottom { flex-direction: column; gap: 8px; text-align: center; }
  .footer-bottom-right { flex-direction: column; gap: 4px; }
}
</style>
