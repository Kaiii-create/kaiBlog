<template>
  <footer class="site-footer">
    <div class="page-container footer-shell">
      <section class="footer-top">
        <div class="footer-brand">
          <p class="footer-kicker">Personal Blog</p>
          <h2>{{ site.name || 'Kaiii 博客' }}</h2>
          <p class="footer-desc">
            {{ site.description || '记录产品、开发与长期主义，把真实的学习与思考沉淀下来。' }}
          </p>
        </div>

        <nav class="footer-nav">
          <router-link to="/" class="footer-link">首页</router-link>
          <router-link to="/articles" class="footer-link">文章</router-link>
          <router-link to="/tutorials" class="footer-link">教程</router-link>
          <router-link to="/search" class="footer-link">搜索</router-link>
        </nav>
      </section>

      <section class="footer-middle">
        <div class="footer-meta">
          <a v-if="contact.email" :href="`mailto:${contact.email}`" class="footer-meta-item">{{ contact.email }}</a>
          <a v-if="contact.github" :href="contact.github" target="_blank" class="footer-meta-item">GitHub</a>
          <a v-if="contact.gitee" :href="contact.gitee" target="_blank" class="footer-meta-item">Gitee</a>
          <a v-if="contact.bilibili" :href="contact.bilibili" target="_blank" class="footer-meta-item">Bilibili</a>
          <span v-if="footer.icp" class="footer-meta-item">{{ footer.icp }}</span>
          <span v-if="footer.beian" class="footer-meta-item">{{ footer.beian }}</span>
        </div>

        <div v-if="footer.links?.length" class="footer-friends">
          <a
            v-for="link in footer.links"
            :key="link.url"
            :href="link.url"
            target="_blank"
            class="footer-friend-link"
          >
            {{ link.title }}
          </a>
        </div>
      </section>

      <section class="footer-bottom">
        <span>{{ footer.copyright || `© ${new Date().getFullYear()} ${site.name || 'Kaiii 博客'}` }}</span>
        <span>{{ footer.text || site.notice || '愿每一次发布都比上一次更完整。' }}</span>
      </section>
    </div>
  </footer>
</template>

<script setup>
import { storeToRefs } from 'pinia'
import { useAppStore } from '../stores/app'

const appStore = useAppStore()
const { site, contact, footer } = storeToRefs(appStore)
</script>

<style scoped lang="scss">
.site-footer {
  margin-top: 96px;
  padding: 0 0 28px;
  border-top: 1px solid var(--color-border);
  background:
    linear-gradient(180deg, transparent 0%, color-mix(in srgb, var(--footer-bg) 86%, transparent) 18%, var(--footer-bg) 100%),
    radial-gradient(circle at top center, rgba(15, 130, 255, 0.12), transparent 30%);
}

.footer-shell {
  padding-top: 34px;
}

.footer-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 32px;
  padding-bottom: 26px;
  border-bottom: 1px solid var(--color-border);
}

.footer-brand {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.footer-kicker {
  letter-spacing: 0.12em;
  text-transform: uppercase;
  font-size: 0.78rem;
  color: var(--color-primary-light);
}

.footer-top h2 {
  font-size: 1.42rem;
  line-height: 1.15;
}

.footer-desc {
  max-width: 620px;
  color: var(--color-text-secondary);
  font-size: 0.95rem;
}

.footer-nav {
  display: flex;
  flex-wrap: wrap;
  justify-content: flex-end;
  gap: 10px;
  padding: 6px;
  border-radius: 999px;
  background: var(--color-bg-card);
  border: 1px solid var(--color-border);
}

.footer-link {
  padding: 10px 16px;
  border-radius: 999px;
  color: var(--color-text-secondary);
  transition: 0.24s ease;

  &:hover {
    color: var(--color-text);
    background: var(--color-bg-elevated);
  }
}

.footer-middle {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 24px;
  padding: 20px 0 18px;
  border-bottom: 1px solid var(--color-border);
}

.footer-meta,
.footer-friends {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.footer-meta-item,
.footer-friend-link {
  display: inline-flex;
  align-items: center;
  min-height: 38px;
  padding: 8px 14px;
  border-radius: 999px;
  border: 1px solid var(--color-border);
  background: color-mix(in srgb, var(--color-bg-card) 92%, transparent);
  color: var(--color-text-secondary);
  font-size: 0.9rem;
  line-height: 1;
}

.footer-bottom {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  padding-top: 18px;
  color: var(--color-text-muted);
  font-size: 0.84rem;
}

@media (max-width: 900px) {
  .footer-top,
  .footer-middle,
  .footer-bottom {
    flex-direction: column;
    align-items: flex-start;
  }

  .footer-nav {
    justify-content: flex-start;
  }
}

@media (max-width: 640px) {
  .footer-shell {
    padding-top: 28px;
  }

  .footer-nav {
    width: 100%;
    justify-content: flex-start;
  }
}
</style>
