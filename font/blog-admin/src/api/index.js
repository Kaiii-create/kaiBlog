// ==========================================
// blog-admin 真实 API 层
// ==========================================
import axios from 'axios'
import router from '../router/index.js'

const api = axios.create({
  baseURL: '',
  timeout: 15000,
})

api.interceptors.request.use(config => {
  const token = localStorage.getItem('admin_token')
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

api.interceptors.response.use(
  res => res.data,
  err => {
    // 兼容各种返回格式
    let data = err.response?.data
    if (typeof data === 'string') {
      try { data = JSON.parse(data) } catch (e) { data = { code: 500, message: data } }
    }
    if (!data) data = { code: 500, message: '网络错误' }

    // 401 → Token 失效/未登录/被踢下线，跳转登录页
    if (data.code === 401 || data.code === '401' || err.response?.status === 401) {
      localStorage.removeItem('admin_token')
      localStorage.removeItem('admin_info')
      localStorage.removeItem('admin_super')
      localStorage.removeItem('admin_menus')
      localStorage.removeItem('admin_btns')
      // 延时跳转登录（确保清理完成后再跳）
      setTimeout(() => {
        window.location.replace('/admin/login')
      }, 50)
    }
    return Promise.reject(data)
  }
)

function p(obj) {
  const r = {}
  Object.keys(obj).forEach(k => { if (obj[k] !== undefined && obj[k] !== null && obj[k] !== '') r[k] = obj[k] })
  return r
}

export const adminApi = {
  // ---- 认证 ----
  async login(data) { return api.post('/admin/auth/login', data) },
  async getProfile() { return api.get('/admin/auth/profile') },

  // ---- 控制台 ----
  async getDashboard() { return api.get('/admin/dashboard') },

  // ---- 管理员管理 ----
  async getAdmins(params) { return api.get('/admin/admins', { params: p(params) }) },
  async getAdminDetail(id) { return api.get(`/admin/admins/${id}`) },
  async createAdmin(data) { return api.post('/admin/admins', data) },
  async updateAdmin(id, data) { return api.put(`/admin/admins/${id}`, data) },
  async deleteAdmin(id) { return api.delete(`/admin/admins/${id}`) },

  // ---- 角色管理 ----
  async getRoles(params) { return api.get('/admin/roles', { params: p(params) }) },
  async getRoleDetail(id) { return api.get(`/admin/roles/${id}`) },
  async createRole(data) { return api.post('/admin/roles', data) },
  async updateRole(id, data) { return api.put(`/admin/roles/${id}`, data) },
  async deleteRole(id) { return api.delete(`/admin/roles/${id}`) },
  async assignRolePermissions(id, data) { return api.put(`/admin/roles/${id}/permissions`, data) },

  // ---- 权限管理 ----
  async getPermissions() { return api.get('/admin/permissions') },
  async getPermissionDetail(id) { return api.get(`/admin/permissions/${id}`) },
  async createPermission(data) { return api.post('/admin/permissions', data) },
  async updatePermission(id, data) { return api.put(`/admin/permissions/${id}`, data) },
  async deletePermission(id) { return api.delete(`/admin/permissions/${id}`) },

  // ---- 栏目管理 ----
  async getCategories(params) { return api.get('/admin/categories', { params: p(params) }) },
  async getAllCategories() { return api.get('/admin/categories') },
  async createCategory(data) { return api.post('/admin/categories', data) },
  async updateCategory(id, data) { return api.put(`/admin/categories/${id}`, data) },
  async deleteCategory(id) { return api.delete(`/admin/categories/${id}`) },

  // ---- 文章管理 ----
  async getArticles(params) { return api.get('/admin/articles', { params: p(params) }) },
  async getArticleDetail(id) { return api.get(`/admin/articles/${id}`) },
  async createArticle(data) { return api.post('/admin/articles', data) },
  async updateArticle(id, data) { return api.put(`/admin/articles/${id}`, data) },
  async deleteArticle(id) { return api.delete(`/admin/articles/${id}`) },

  // ---- 标签管理 ----
  async getTags() { return api.get('/admin/tags') },
  async createTag(data) { return api.post('/admin/tags', data) },
  async updateTag(id, data) { return api.put(`/admin/tags/${id}`, data) },
  async deleteTag(id) { return api.delete(`/admin/tags/${id}`) },

  // ---- 教程管理 ----
  async getTutorials(params) { return api.get('/admin/tutorials', { params: p(params) }) },
  async getTutorialDetail(id) { return api.get(`/admin/tutorials/${id}`) },
  async createTutorial(data) { return api.post('/admin/tutorials', data) },
  async updateTutorial(id, data) { return api.put(`/admin/tutorials/${id}`, data) },
  async deleteTutorial(id) { return api.delete(`/admin/tutorials/${id}`) },

  // ---- 章节管理 ----
  async getChapters(tutorialId) { return api.get('/admin/chapters', { params: { tutorial_id: tutorialId } }) },
  async createChapter(data) { return api.post('/admin/chapters', data) },
  async updateChapter(id, data) { return api.put(`/admin/chapters/${id}`, data) },
  async deleteChapter(id) { return api.delete(`/admin/chapters/${id}`) },
  async sortChapters(data) { return api.put('/admin/chapters/sort', data) },

  // ---- 评论管理 ----
  async getComments(params) { return api.get('/admin/comments', { params: p(params) }) },
  async auditComment(id, status) { return api.put(`/admin/comments/${id}/audit`, { status }) },
  async deleteComment(id) { return api.delete(`/admin/comments/${id}`) },

  // ---- 消息通知 ----
  async getMessages(params) { return api.get('/admin/messages', { params: p(params) }) },
  async getUnreadCount() { return api.get('/admin/messages/unread-count') },
  async markMessageRead(id) { return api.put(`/admin/messages/${id}/read`) },
  async markAllRead() { return api.put('/admin/messages/read-all') },
  async deleteMessage(id) { return api.delete(`/admin/messages/${id}`) },

  // ---- 用户管理 ----
  async getUsers(params) { return api.get('/admin/users', { params: p(params) }) },
  async updateUserStatus(id, status) { return api.put(`/admin/users/${id}/status`, { status }) },

  // ---- 文件管理 ----
  async getUploads(params) { return api.get('/admin/upload', { params: p(params) }) },
  async uploadFile(file) {
    const form = new FormData()
    form.append('file', file)
    return api.post('/admin/upload', form)
  },
  async deleteUpload(id) { return api.delete(`/admin/upload/${id}`) },

  // ---- 系统配置 ----
  async getConfig() { return api.get('/admin/config') },
  async updateConfig(data) { return api.put('/admin/config', data) },
  async createConfig(data) { return api.post('/admin/config', data) },
  async deleteConfig(id) { return api.delete(`/admin/config/${id}`) },
}

export default { adminApi }
