<template>
  <div class="floating-toolbar" :class="{ visible: show }">
    <!-- 回到顶部 -->
    <div class="toolbar-item back-top" v-show="showBackTop" @click="scrollToTop" title="回到顶部">
      <el-icon><ArrowUp /></el-icon>
    </div>

    <!-- QQ 联系 -->
    <div class="toolbar-item" v-if="contact?.qq" @click="copyQQ" title="QQ联系">
      <el-icon><ChatDotSquare /></el-icon>
      <span class="toolbar-label">QQ</span>
    </div>

    <!-- 微信公众号 -->
    <div class="toolbar-item wechat-item" v-if="contact?.wechat_qr" @click="showWechat = !showWechat" title="公众号">
      <el-icon><ChatLineRound /></el-icon>
      <span class="toolbar-label">公众号</span>
      <!-- 二维码弹出 -->
      <Transition name="pop">
        <div class="qr-popup" v-if="showWechat" @click.stop>
          <img :src="contact.wechat_qr" alt="公众号二维码" />
          <p>{{ contact.wechat || '扫描关注公众号' }}</p>
        </div>
      </Transition>
    </div>

    <!-- 打赏 -->
    <div class="toolbar-item reward-item" v-if="reward?.enabled" @click="showReward = !showReward" title="打赏">
      <el-icon><Coin /></el-icon>
      <span class="toolbar-label">打赏</span>
      <Transition name="pop">
        <div class="qr-popup reward-popup" v-if="showReward" @click.stop>
          <p class="reward-text">{{ reward.text || '请作者喝杯咖啡 ☕' }}</p>
          <div class="reward-qrs">
            <div class="reward-qr-item" v-if="reward.wechat_qr">
              <img :src="reward.wechat_qr" alt="微信收款码" />
              <span>微信</span>
            </div>
            <div class="reward-qr-item" v-if="reward.alipay_qr">
              <img :src="reward.alipay_qr" alt="支付宝收款码" />
              <span>支付宝</span>
            </div>
          </div>
        </div>
      </Transition>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { ArrowUp, ChatDotSquare, ChatLineRound, Coin } from '@element-plus/icons-vue'
import { ElMessage } from 'element-plus'
import { useAppStore } from '../stores/app'

const appStore = useAppStore()
const show = ref(false)
const showBackTop = ref(false)
const showWechat = ref(false)
const showReward = ref(false)

const contact = computed(() => appStore.siteConfig?.contact || null)
const reward = computed(() => appStore.siteConfig?.reward || null)

let scrollHandler = null

onMounted(() => {
  show.value = true
  scrollHandler = () => {
    showBackTop.value = window.scrollY > 400
  }
  window.addEventListener('scroll', scrollHandler)
  // 点击其他区域关闭弹窗
  document.addEventListener('click', closePopups)
})

onUnmounted(() => {
  if (scrollHandler) window.removeEventListener('scroll', scrollHandler)
  document.removeEventListener('click', closePopups)
})

function closePopups() {
  showWechat.value = false
  showReward.value = false
}

function scrollToTop() {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

function copyQQ() {
  const qq = contact.value?.qq
  if (qq) {
    navigator.clipboard.writeText(qq).then(() => {
      ElMessage.success('QQ号已复制: ' + qq)
    }).catch(() => {
      ElMessage.info('QQ: ' + qq)
    })
  }
}
</script>

<style scoped lang="scss">
.floating-toolbar {
  position: fixed;
  right: 20px;
  bottom: 100px;
  z-index: 99;
  display: flex;
  flex-direction: column;
  gap: 8px;
  opacity: 0;
  transform: translateX(20px);
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  &.visible {
    opacity: 1;
    transform: translateX(0);
  }
}

.toolbar-item {
  position: relative;
  width: 44px;
  height: 44px;
  border-radius: 12px;
  background: var(--color-bg-card, rgba(255,255,255,0.04));
  backdrop-filter: blur(12px);
  border: 1px solid var(--color-border);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: var(--color-text-secondary);
  font-size: 18px;
  transition: all 0.3s ease;
  gap: 1px;
  &:hover {
    border-color: var(--color-primary);
    color: var(--color-primary);
    box-shadow: var(--shadow-glow);
    transform: scale(1.08);
  }
}

.toolbar-label {
  font-size: 9px;
  line-height: 1;
  color: inherit;
}

.back-top {
  background: var(--gradient-primary);
  border: none;
  color: #fff;
  &:hover {
    color: #fff;
    box-shadow: 0 4px 16px rgba(59, 130, 246, 0.4);
  }
}

/* 二维码弹窗 */
.qr-popup {
  position: absolute;
  right: 56px;
  bottom: 0;
  width: 180px;
  background: var(--color-bg-card, #fff);
  border: 1px solid var(--color-border);
  border-radius: 12px;
  padding: 16px;
  text-align: center;
  box-shadow: var(--shadow-lg);
  img {
    width: 100%;
    border-radius: 8px;
    display: block;
  }
  p {
    margin-top: 10px;
    font-size: 13px;
    color: var(--color-text-secondary);
  }
}

.reward-popup {
  width: 220px;
  .reward-text {
    font-size: 13px;
    color: var(--color-accent);
    font-weight: 600;
    margin-bottom: 12px;
  }
}
.reward-qrs {
  display: flex;
  gap: 12px;
  justify-content: center;
}
.reward-qr-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  img {
    width: 90px;
    height: 90px;
    object-fit: cover;
    border-radius: 8px;
  }
  span {
    font-size: 11px;
    color: var(--color-text-muted);
  }
}

/* 弹窗入场动画 */
.pop-enter-active {
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}
.pop-leave-active {
  transition: all 0.15s ease;
}
.pop-enter-from,
.pop-leave-to {
  opacity: 0;
  transform: translateX(10px) scale(0.95);
}

@media (max-width: 768px) {
  .floating-toolbar {
    right: 12px;
    bottom: 80px;
    gap: 6px;
  }
  .toolbar-item {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    font-size: 16px;
  }
  .qr-popup {
    right: 48px;
    width: 150px;
    padding: 12px;
  }
  .reward-popup {
    width: 180px;
  }
  .reward-qr-item img {
    width: 72px;
    height: 72px;
  }
}
</style>
