import axios from 'axios'
import { ElMessage } from 'element-plus'

const BASE_URL = import.meta.env.VITE_API_BASE_URL || (import.meta.env.DEV ? '' : 'https://www.kaiii.top')

const api = axios.create({
  baseURL: BASE_URL,
  timeout: 15000,
})

function getAuthToken() {
  return localStorage.getItem('user_token') || ''
}

function clearAuthState() {
  localStorage.removeItem('user_token')
  localStorage.removeItem('user_info')
}

function redirectToLogin() {
  const currentPath = window.location.pathname + window.location.search
  if (!currentPath.startsWith('/login') && !currentPath.startsWith('/register')) {
    window.location.href = `/login?redirect=${encodeURIComponent(currentPath)}`
  }
}

function handle401(message = '登录状态已失效，请重新登录') {
  clearAuthState()
  ElMessage.error(message)
  redirectToLogin()
}

api.interceptors.request.use((config) => {
  const token = getAuthToken()
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

api.interceptors.response.use(
  (response) => {
    const body = response.data
    if (body && typeof body.code !== 'undefined') {
      if (body.code === 401) {
        handle401(body.message)
        return Promise.reject(body)
      }
      if (body.code !== 0) {
        return Promise.reject(body)
      }
      return body
    }
    return {
      code: 0,
      message: 'success',
      data: body,
    }
  },
  (error) => {
    const status = error.response?.status
    const payload = error.response?.data || {
      code: status || 500,
      message: '网络连接失败，请稍后重试',
      data: null,
    }

    if (status === 401) {
      handle401(payload.message)
    } else if (status === 404) {
      payload.message = payload.message || '请求的资源不存在'
    } else if (status === 500) {
      payload.message = payload.message || '服务器开小差了，请稍后再试'
    }

    return Promise.reject(payload)
  }
)

function buildParams(params = {}) {
  const next = {}
  Object.entries(params).forEach(([key, value]) => {
    if (value !== '' && value !== null && typeof value !== 'undefined') {
      next[key] = value
    }
  })
  return next
}

function normalizeListData(result) {
  return {
    list: result?.data?.list || [],
    pagination: result?.data?.pagination || {
      page: 1,
      page_size: 10,
      total: 0,
      total_page: 0,
    },
  }
}

function extractTagsFromArticles(articles = []) {
  const tagMap = new Map()

  articles.forEach((article) => {
    const tags = Array.isArray(article.tags) ? article.tags : []
    tags.forEach((tag) => {
      const normalized = typeof tag === 'object'
        ? {
            id: tag.id,
            name: tag.name,
            color: tag.color,
          }
        : {
            id: tag,
            name: String(tag),
          }

      if (!normalized.id) return

      const current = tagMap.get(normalized.id) || { ...normalized, article_count: 0 }
      current.article_count += 1
      tagMap.set(normalized.id, current)
    })
  })

  return Array.from(tagMap.values()).sort((a, b) => b.article_count - a.article_count)
}

export const webApi = {
  getCaptcha() {
    return api.get('/api/captcha')
  },

  getConfig() {
    return api.get('/api/config')
  },

  getSeo(type, id) {
    const path = id ? `/api/seo/${type}/${id}` : `/api/seo/${type}`
    return api.get(path)
  },

  async getHomeData() {
    const [articlesRes, categoriesRes, tutorialsRes] = await Promise.all([
      api.get('/api/articles', { params: { page: 1, page_size: 12 } }),
      api.get('/api/categories'),
      api.get('/api/tutorials', { params: { page: 1, page_size: 6 } }),
    ])

    const articleData = normalizeListData(articlesRes)
    const tutorialData = normalizeListData(tutorialsRes)
    const articles = articleData.list
    const categories = Array.isArray(categoriesRes.data) ? categoriesRes.data : []

    const topArticles = articles.filter((item) => Number(item.is_top) === 1).slice(0, 3)
    const recommendArticles = articles.filter((item) => Number(item.is_recommend) === 1).slice(0, 4)
    const rootCategories = categories.filter((item) => !item.parent_id)

    return {
      code: 0,
      message: 'success',
      data: {
        top_articles: topArticles.length ? topArticles : articles.slice(0, 3),
        latest_articles: articles.slice(0, 6),
        recommend_articles: recommendArticles.length ? recommendArticles : articles.slice(0, 4),
        categories: rootCategories,
        hot_tags: extractTagsFromArticles(articles).slice(0, 12),
        tutorials: tutorialData.list,
      },
    }
  },

  getArticles(params = {}) {
    return api.get('/api/articles', { params: buildParams(params) })
  },

  getArticleDetail(id) {
    return api.get(`/api/articles/${id}`)
  },

  getCategories() {
    return api.get('/api/categories')
  },

  getCategoryDetail(id) {
    return api.get(`/api/categories/${id}`)
  },

  getCategoryArticles(categoryId, params = {}) {
    return api.get('/api/articles', {
      params: buildParams({
        ...params,
        category_id: categoryId,
      }),
    })
  },

  async getTags() {
    const res = await api.get('/api/articles', {
      params: {
        page: 1,
        page_size: 50,
      },
    })

    return {
      code: 0,
      message: 'success',
      data: extractTagsFromArticles(normalizeListData(res).list),
    }
  },

  getTutorials(params = {}) {
    return api.get('/api/tutorials', { params: buildParams(params) })
  },

  getTutorialDetail(id) {
    return api.get(`/api/tutorials/${id}`)
  },

  getTutorialChapters(id) {
    return api.get(`/api/tutorials/${id}/chapters`)
  },

  getChapterDetail(id) {
    return api.get(`/api/chapters/${id}`)
  },

  async getTutorialChapter(tutorialId, chapterId) {
    const [tutorialRes, chapterRes] = await Promise.all([
      api.get(`/api/tutorials/${tutorialId}`),
      api.get(`/api/chapters/${chapterId}`),
    ])

    const tutorialPayload = tutorialRes.data || {}
    const tutorial = tutorialPayload.tutorial || tutorialPayload
    const chapters = tutorialPayload.chapters || []
    const chapter = chapterRes.data
    const currentIndex = chapters.findIndex((item) => String(item.id) === String(chapterId))

    return {
      code: 0,
      message: 'success',
      data: {
        tutorial,
        chapter,
        chapters,
        prev: currentIndex > 0 ? chapters[currentIndex - 1] : null,
        next: currentIndex >= 0 && currentIndex < chapters.length - 1 ? chapters[currentIndex + 1] : null,
      },
    }
  },

  search(keyword, params = {}) {
    return api.get('/api/articles', {
      params: buildParams({
        ...params,
        keyword,
      }),
    })
  },

  getComments(targetType, targetId, params = {}) {
    return api.get('/api/comments', {
      params: buildParams({
        ...params,
        target_type: targetType,
        target_id: targetId,
      }),
    })
  },
}

export const userApi = {
  login(payload) {
    return api.post('/api/auth/login', payload)
  },

  register(payload) {
    return api.post('/api/auth/register', payload)
  },

  getUserInfo() {
    return api.get('/api/user/profile')
  },

  updateProfile(payload) {
    return api.put('/api/user/profile', payload)
  },

  getFavorites(params = {}) {
    return api.get('/api/user/favorites', { params: buildParams(params) })
  },

  async getMyComments() {
    try {
      return await api.get('/api/user/comments')
    } catch {
      return {
        code: 0,
        message: 'success',
        data: [],
      }
    }
  },

  toggleLike(targetType, targetId) {
    if (targetType !== 'article') {
      return Promise.reject({ code: 400, message: '暂不支持该类型点赞', data: null })
    }
    return api.post(`/api/user/articles/${targetId}/like`)
  },

  toggleFavorite(targetType, targetId) {
    if (targetType !== 'article') {
      return Promise.reject({ code: 400, message: '暂不支持该类型收藏', data: null })
    }
    return api.post(`/api/user/articles/${targetId}/favorite`)
  },

  postComment(payload) {
    return api.post('/api/user/comments', payload)
  },

  uploadFile(file) {
    const formData = new FormData()
    formData.append('file', file)
    return api.post('/api/user/upload', formData)
  },
}

export default {
  webApi,
  userApi,
}
