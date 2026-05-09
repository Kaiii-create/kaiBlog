<template>
  <div class="default-layout">
    <SiteHeader />
    <main class="main-content">
      <router-view v-slot="{ Component }">
        <transition name="fade-slide" mode="out-in">
          <component :is="Component" />
        </transition>
      </router-view>
    </main>
    <SiteFooter />
    <FloatingToolbar />
  </div>
</template>

<script setup>
import SiteHeader from './SiteHeader.vue'
import SiteFooter from './SiteFooter.vue'
import FloatingToolbar from '../components/FloatingToolbar.vue'
</script>

<style scoped lang="scss">
.default-layout {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}
.main-content {
  flex: 1;
  padding-top: 70px;
}
.fade-slide-enter-active, .fade-slide-leave-active {
  transition: all 0.3s ease;
}
.fade-slide-enter-from {
  opacity: 0;
  transform: translateY(20px);
}
.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-20px);
}
</style>
