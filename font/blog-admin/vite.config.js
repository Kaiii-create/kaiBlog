import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { fileURLToPath, URL } from 'node:url'

export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    }
  },
  css: {
    preprocessorOptions: {
      scss: {
        api: 'modern-compiler',
      }
    }
  },
  base: '/admin/',
  server: {
    port: 5174,
    host: true,
    proxy: {
      '/admin': {
        target: 'https://www.kaiii.top',
        changeOrigin: true,
        bypass(req) {
          // POST/PUT/DELETE = API 调用，直接代理
          if (req.method !== 'GET') return null
          // GET 请求：只有 Accept 要 JSON 的才走代理（API 调用）
          const accept = req.headers.accept || ''
          if (accept.includes('application/json')) return null
          // 其他 GET 请求（页面导航、静态资源）走本地 SPA
          return req.url
        }
      },
      '/api': {
        target: 'https://www.kaiii.top',
        changeOrigin: true,
      }
    }
  },
  build: {
    outDir: 'dist',
    assetsDir: 'assets',
  }
})
