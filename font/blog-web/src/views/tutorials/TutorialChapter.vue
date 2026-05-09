<template>
  <div class="tutorial-chapter-page">
    <div class="page-container">
      <div class="chapter-layout">
        <!-- 左侧章节目录 -->
        <aside class="chapter-sidebar glass-card">
          <div class="sidebar-header">
            <h4>{{ tutorial?.title || '教程目录' }}</h4>
          </div>
          <div class="sidebar-chapters">
            <div
              v-for="(ch, idx) in allChapters"
              :key="ch.id"
              class="sidebar-chapter"
              :class="{ active: ch.id == currentChapter?.id }"
              @click="switchChapter(ch.id)"
            >
              <span class="ch-index">{{ idx + 1 }}</span>
              <span class="ch-title">{{ ch.title }}</span>
              <el-icon v-if="ch.id == currentChapter?.id" class="ch-active-icon"><ArrowRight /></el-icon>
            </div>
          </div>
        </aside>

        <!-- 右侧正文 -->
        <main class="chapter-main">
          <div class="chapter-content glass-card">
            <div class="chapter-nav-top">
              <el-button
                v-if="prevChapter"
                class="nav-btn"
                @click="switchChapter(prevChapter.id)"
              >
                <el-icon><ArrowLeft /></el-icon> 上一章
              </el-button>
              <span class="nav-title" v-if="currentChapter">{{ currentChapter.title }}</span>
              <el-button
                v-if="nextChapter"
                class="nav-btn"
                @click="switchChapter(nextChapter.id)"
              >
                下一章 <el-icon><ArrowRight /></el-icon>
              </el-button>
            </div>

            <div class="chapter-body" v-html="currentChapter?.content"></div>

            <div class="chapter-nav-bottom">
              <el-button
                v-if="prevChapter"
                class="nav-btn"
                @click="switchChapter(prevChapter.id)"
              >
                <el-icon><ArrowLeft /></el-icon> {{ prevChapter.title }}
              </el-button>
              <el-button
                v-if="nextChapter"
                class="nav-btn"
                @click="switchChapter(nextChapter.id)"
              >
                {{ nextChapter.title }} <el-icon><ArrowRight /></el-icon>
              </el-button>
            </div>
          </div>
        </main>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ArrowRight, ArrowLeft } from '@element-plus/icons-vue'
import { webApi } from '../../api'

const route = useRoute()
const router = useRouter()

const tutorial = ref(null)
const currentChapter = ref(null)
const prevChapter = ref(null)
const nextChapter = ref(null)
const allChapters = ref([])

async function fetchChapter() {
  const tutorialId = route.params.id
  const chapterId = route.params.chapterId
  if (!tutorialId || !chapterId) return

  try {
    const res = await webApi.getTutorialChapter(tutorialId, chapterId)
    if (res.code === 0 && res.data) {
      currentChapter.value = res.data.chapter
      prevChapter.value = res.data.prev
      nextChapter.value = res.data.next
      tutorial.value = res.data.tutorial
      allChapters.value = res.data.chapters || []
    }
  } catch (e) {
    console.error('获取章节内容失败:', e)
  }
}

function switchChapter(chapterId) {
  router.push(`/tutorials/${route.params.id}/chapter/${chapterId}`)
}

onMounted(fetchChapter)
</script>

<style scoped lang="scss">
.tutorial-chapter-page {
  padding: 24px 0 60px;
}
.chapter-layout {
  display: flex;
  gap: 24px;
  align-items: flex-start;
}

/* 左侧目录 */
.chapter-sidebar {
  width: 280px;
  flex-shrink: 0;
  padding: 20px 0;
  position: sticky;
  top: 90px;
  max-height: calc(100vh - 120px);
  overflow-y: auto;
}
.sidebar-header {
  padding: 0 20px 16px;
  border-bottom: 1px solid var(--color-border);
  h4 { font-size: 14px; font-weight: 600; }
}
.sidebar-chapters {
  padding: 8px 0;
}
.sidebar-chapter {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 20px;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 13px;
  color: var(--color-text-secondary);
  border-left: 3px solid transparent;
  &.active {
    color: var(--color-primary-light);
    background: rgba(59, 130, 246, 0.08);
    border-left-color: var(--color-primary-light);
  }
  &:hover:not(.active) {
    background: rgba(255, 255, 255, 0.02);
    color: var(--color-text);
  }
}
.ch-index {
  width: 24px;
  height: 24px;
  border-radius: 6px;
  background: rgba(59, 130, 246, 0.1);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 11px;
  font-weight: 600;
  color: var(--color-primary-light);
  flex-shrink: 0;
}
.sidebar-chapter.active .ch-index {
  background: var(--gradient-primary);
  color: #fff;
}
.ch-title {
  flex: 1;
  line-height: 1.4;
}
.ch-active-icon {
  font-size: 14px;
  color: var(--color-primary-light);
}

/* 右侧内容 */
.chapter-main {
  flex: 1;
  min-width: 0;
}
.chapter-content {
  padding: 32px;
}
.chapter-nav-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 28px;
  padding-bottom: 16px;
  border-bottom: 1px solid var(--color-border);
  gap: 16px;
}
.nav-title {
  font-size: 14px;
  font-weight: 600;
  color: var(--color-text-muted);
  text-align: center;
  flex: 1;
}
.nav-btn {
  background: transparent;
  border-color: var(--color-border);
  color: var(--color-text-secondary);
  gap: 4px;
  &:hover { border-color: var(--color-primary); color: var(--color-primary-light); }
}
.chapter-body {
  line-height: 1.8;
  font-size: 15px;
  color: var(--color-text);
  min-height: 300px;
  :deep(h2) { font-size: 1.5rem; font-weight: 700; margin: 28px 0 16px; }
  :deep(h3) { font-size: 1.2rem; font-weight: 600; margin: 24px 0 12px; }
  :deep(p) { margin-bottom: 16px; }
  :deep(ul), :deep(ol) { padding-left: 24px; margin-bottom: 16px; }
  :deep(li) { margin-bottom: 8px; }
  :deep(code) { background: rgba(59,130,246,0.1); padding: 2px 8px; border-radius: 4px; font-size: 13px; color: var(--color-primary-light); }
  :deep(pre) { background: rgba(0,0,0,0.3); padding: 20px; border-radius: 12px; overflow-x: auto; margin-bottom: 16px; }
  :deep(blockquote) { border-left: 3px solid var(--color-primary); padding-left: 16px; margin: 16px 0; color: var(--color-text-secondary); }
}
.chapter-nav-bottom {
  display: flex;
  justify-content: space-between;
  margin-top: 32px;
  padding-top: 20px;
  border-top: 1px solid var(--color-border);
  gap: 16px;
}

@media (max-width: 900px) {
  .chapter-layout { flex-direction: column; }
  .chapter-sidebar {
    width: 100%;
    position: static;
    max-height: none;
  }
  .sidebar-chapters { display: flex; flex-wrap: wrap; gap: 4px; }
  .sidebar-chapter { border-left: none; border-bottom: 2px solid transparent; padding: 8px 12px; &.active { border-left: none; border-bottom-color: var(--color-primary-light); } }
}
</style>
