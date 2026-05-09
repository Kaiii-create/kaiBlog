// ==========================================
// blog-web 真实 API 层（已对接接口文档）
// ==========================================
import axios from 'axios'
import { ElMessage } from 'element-plus'

// 根据环境决定 baseURL：开发环境走 Vite proxy，生产环境使用真实域名
const BASE_URL = import.meta.env.VITE_API_BASE_URL || ''

// --- Axios 实例 ---
const api = axios.create({
  baseURL: BASE_URL,
  timeout: 15000,
})

/**
 * 401 处理：清除 Token 并跳转登录页
 */
function handle401() {
  localStorage.removeItem('user_token')
  localStorage.removeItem('user_info')
  const currentPath = window.location.hash ? window.location.hash.slice(1) : window.location.pathname
  if (currentPath !== '/login' && currentPath !== '/register') {
    ElMessage.error('登录已过期，请重新登录')
    // 使用 router push 的方式，需要确保 router 可用
    window.location.href = '/login'
  }
}

// 请求拦截器：注入用户 Token
api.interceptors.request.use(config => {
  const token = localStorage.getItem('user_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// 响应拦截器：统一处理返回 { code, message, data }
api.interceptors.response.use(
  res => {
    const body = res.data
    // 后端返回标准格式 { code, message, data }
    if (body && body.code !== undefined) {
      if (body.code === 401) {
        handle401()
        return Promise.reject(body)
      }
      if (body.code !== 0) {
        return Promise.reject(body)
      }
    }
    return body
  },
  err => {
    const status = err.response?.status
    const data = err.response?.data || { code: 500, message: '网络错误', data: null }

    if (status === 401) {
      handle401()
    } else if (status === 500) {
      data.message = data.message || '服务器内部错误'
    } else if (status === 404) {
      data.message = '请求的资源不存在'
    } else if (!err.response) {
      data.message = '网络连接失败，请检查网络'
    }

    return Promise.reject(data)
  }
)

// --- 工具函数 ---
function buildParams(obj) {
  const p = {}
  Object.keys(obj).forEach(k => {
    if (obj[k] !== undefined && obj[k] !== null && obj[k] !== '') {
      p[k] = obj[k]
    }
  })
  return p
}

// ==========================================
// 前台公开接口
// ==========================================
export const webApi = {
  // 获取网站配置
  async getConfig() {
    return api.get('/api/config')
  },

  // 首页 - 组合多个接口
  async getHomeData() {
    const [articlesRes, categoriesRes, tutorialsRes] = await Promise.all([
      api.get('/api/articles', { params: { page: 1, page_size: 9 } }),
      api.get('/api/categories'),
      api.get('/api/tutorials', { params: { page: 1, page_size: 3 } }),
    ])

    const articleList = articlesRes.data?.list || []
    const catList = categoriesRes.data || []

    return {
      code: 0,
      message: 'success',
      data: {
        // 置顶文章（后端返回的 is_top 为 1 的）
        top_articles: articleList.filter(a => a.is_top == 1).slice(0, 3) ||
                       articleList.slice(0, 3),
        // 最新文章
        latest_articles: articleList.slice(0, 6),
        // 推荐置顶（is_recommend 为 1 的）
        recommend_articles: articleList.filter(a => a.is_recommend == 1).slice(0, 4) ||
                            articleList.slice(0, 4),
        // 顶级栏目（parent_id === 0 或 parent_id === null）
        categories: (catList.filter ? catList.filter(c => c.parent_id == 0) : catList) || [],
        // 热门标签（文章接口中提取的标签数据）
        hot_tags: extractTagsFromArticles(articleList),
        // 教程
        tutorials: tutorialsRes.data?.list || [],
      }
    }
  },

  // 文章列表
  async getArticles(params = {}) {
    return api.get('/api/articles', { params: buildParams(params) })
  },

  // 文章详情
  async getArticleDetail(id) {
    return api.get(`/api/articles/${id}`)
  },

  // 栏目列表
  async getCategories() {
    return api.get('/api/categories')
  },

  // 栏目详情
  async getCategoryDetail(id) {
    return api.get(`/api/categories/${id}`)
  },

  // 栏目文章
  async getCategoryArticles(categoryId, params = {}) {
    return api.get('/api/articles', { params: buildParams({ ...params, category_id: categoryId }) })
  },

  // 标签列表（从文章数据中动态提取）
  async getTags() {
    try {
      const res = await api.get('/api/articles', { params: { page: 1, page_size: 50 } })
      const list = res.data?.list || []
      return { code: 0, message: 'success', data: extractTagsFromArticles(list) }
    } catch {
      return { code: 0, message: 'success', data: [] }
    }
  },

  // 教程列表
  async getTutorials(params = {}) {
    return api.get('/api/tutorials', { params: buildParams(params) })
  },

  // 教程详情
  async getTutorialDetail(id) {
    return api.get(`/api/tutorials/${id}`)
  },

  // 教程章节树
  async getTutorialChapters(id) {
    return api.get(`/api/tutorials/${id}/chapters`)
  },

  // 章节详情
  async getChapterDetail(id) {
    return api.get(`/api/chapters/${id}`)
  },

  // 教程章节（含导航）
  async getTutorialChapter(tutorialId, chapterId) {
    const detailRes = await api.get(`/api/tutorials/${tutorialId}`)
    const chapterRes = await api.get(`/api/chapters/${chapterId}`)
    const tutorial = detailRes.data?.tutorial || detailRes.data
    const chapters = detailRes.data?.chapters || []
    const chapter = chapterRes.data

    // 计算前后章节
    const currentIdx = chapters.findIndex(c => c.id == chapterId)
    const prev = currentIdx > 0 ? chapters[currentIdx - 1] : null
    const next = currentIdx < chapters.length - 1 ? chapters[currentIdx + 1] : null

    return {
      code: 0,
      message: 'success',
      data: {
        chapter,
        prev,
        next,
        tutorial,
        chapters,
      }
    }
  },

  // 搜索（复用文章列表接口）
  async search(keyword, params = {}) {
    return api.get('/api/articles', { params: buildParams({ ...params, keyword }) })
  },

  // 公共评论列表
  async getComments(targetType, targetId) {
    return api.get('/api/comments', { params: { target_type: targetType, id: targetId } })
  },
}

// ==========================================
// 前台用户接口（需要 Token）
// ==========================================
export const userApi = {
  // 登录
  async login(data) {
    return api.post('/api/auth/login', data)
  },

  // 注册
  async register(data) {
    return api.post('/api/auth/register', data)
  },

  // 获取用户信息
  async getUserInfo() {
    return api.get('/api/user/profile')
  },

  // 更新资料
  async updateProfile(data) {
    return api.put('/api/user/profile', data)
  },

  // 我的收藏列表
  async getFavorites() {
    return api.get('/api/user/favorites')
  },

  // 我的评论（文档中没有独立接口，从公共评论接口按用户筛选，预留）
  async getMyComments() {
    // 尝试调用，如果后端没有实现则降级
    return api.get('/api/user/comments').catch(() => ({ code: 0, message: 'success', data: [] }))
  },

  // 点赞/取消点赞
  async toggleLike(targetType, targetId) {
    return api.post(`/api/user/articles/${targetId}/like`)
  },

  // 收藏/取消收藏
  async toggleFavorite(targetType, targetId) {
    return api.post(`/api/user/articles/${targetId}/favorite`)
  },

  // 发表评论
  async postComment(data) {
    return api.post('/api/user/comments', data)
  },

  // 上传文件
  async uploadFile(file) {
    const form = new FormData()
    form.append('file', file)
    return api.post('/api/user/upload', form)
  },
}

// ==========================================
// 辅助：从文章中提取标签数据
// ==========================================
function extractTagsFromArticles(articles) {
  const tagMap = {}
  if (!articles || !articles.length) return []
  articles.forEach(a => {
    const tags = a.tags
    if (!tags || !tags.length) return
    tags.forEach(t => {
      if (typeof t === 'object' && t.id) {
        if (!tagMap[t.id]) {
          tagMap[t.id] = { ...t, article_count: 1 }
        } else {
          tagMap[t.id].article_count++
        }
      } else if (typeof t === 'number' || typeof t === 'string') {
        if (!tagMap[t]) {
          tagMap[t] = { id: t, name: `标签${t}`, article_count: 1 }
        } else {
          tagMap[t].article_count++
        }
      }
    })
  })
  return Object.values(tagMap)
}

export default { webApi, userApi }
