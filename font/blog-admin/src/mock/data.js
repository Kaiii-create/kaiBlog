// blog-admin Mock 数据（独立副本，不引用外部路径）
export const mockArticles = [
  { id: 1, category_id: 1, admin_id: 1, title: 'Vue3 Composition API 实战指南', slug: 'vue3-composition-api-guide', summary: '深入浅出讲解 Vue3 Composition API 的核心概念和实际应用场景。', cover: '', status: 1, is_top: 1, is_recommend: 1, view_count: 1280, like_count: 56, comment_count: 12, tags: [1, 2], published_at: '2026-04-28 09:00:00', created_at: '2026-04-28 08:00:00', author: '站长', content: '<h2>什么是 Composition API</h2><p>Composition API 是 Vue3 引入的全新 API 集合。</p>' },
  { id: 2, category_id: 2, admin_id: 1, title: 'ThinkPHP 8 新特性详解', slug: 'thinkphp8-new-features', summary: 'ThinkPHP 8 带来了诸多改进和新特性。', cover: '', status: 1, is_top: 0, is_recommend: 1, view_count: 856, like_count: 34, comment_count: 8, tags: [4], published_at: '2026-04-25 10:30:00', created_at: '2026-04-25 09:00:00', author: '站长' },
  { id: 3, category_id: 3, admin_id: 1, title: '2026 年前端趋势展望', slug: 'frontend-trends-2026', summary: '盘点 2026 年前端领域最值得关注的技术趋势。', cover: '', status: 1, is_top: 0, is_recommend: 1, view_count: 2100, like_count: 89, comment_count: 24, tags: [2, 6], published_at: '2026-04-20 14:00:00', created_at: '2026-04-20 12:00:00', author: '站长' },
  { id: 4, category_id: 4, admin_id: 1, title: '程序员的自我成长之路', slug: 'programmer-growth', summary: '作为一名程序员，技术成长之外，如何构建自己的知识体系和职业规划。', cover: '', status: 1, is_top: 0, is_recommend: 0, view_count: 654, like_count: 28, comment_count: 6, tags: [], published_at: '2026-04-15 11:00:00', created_at: '2026-04-15 10:00:00', author: '站长' },
  { id: 5, category_id: 1, admin_id: 1, title: 'Vue3 + Vite 项目最佳实践', slug: 'vue3-vite-best-practice', summary: '从项目初始化到部署上线，分享 Vue3 + Vite 项目架构的最佳实践。', cover: '', status: 1, is_top: 0, is_recommend: 0, view_count: 1560, like_count: 67, comment_count: 15, tags: [1, 2, 3], published_at: '2026-04-10 08:30:00', created_at: '2026-04-10 07:00:00', author: '站长' },
  { id: 6, category_id: 3, admin_id: 1, title: '优雅的错误处理：前端异常监控实践', slug: 'frontend-error-monitoring', summary: '前端异常监控是保障用户体验的重要环节。', cover: '', status: 1, is_top: 0, is_recommend: 0, view_count: 920, like_count: 42, comment_count: 9, tags: [2, 6], published_at: '2026-04-05 16:00:00', created_at: '2026-04-05 14:00:00', author: '站长' },
  { id: 7, category_id: 2, admin_id: 1, title: 'MySQL 索引优化实战', slug: 'mysql-index-optimization', summary: '索引是数据库性能优化的核心手段。', cover: '', status: 1, is_top: 0, is_recommend: 0, view_count: 723, like_count: 31, comment_count: 5, tags: [5], published_at: '2026-04-01 10:00:00', created_at: '2026-04-01 09:00:00', author: '站长' },
  { id: 8, category_id: 3, admin_id: 1, title: '从零搭建个人博客：全栈技术选型与架构设计', slug: 'build-personal-blog', summary: '记录从零搭建博客系统的完整过程。', cover: '', status: 1, is_top: 1, is_recommend: 1, view_count: 3200, like_count: 128, comment_count: 35, tags: [2, 6], published_at: '2026-03-28 09:00:00', created_at: '2026-03-28 08:00:00', author: '站长' },
  { id: 9, category_id: 4, admin_id: 1, title: '烟台旅行记：长岛与九丈崖', slug: 'yantai-travel', summary: '五一假期去了烟台，记录在长岛和九丈崖的旅行见闻。', cover: '', status: 1, is_top: 0, is_recommend: 0, view_count: 445, like_count: 18, comment_count: 3, tags: [], published_at: '2026-05-06 20:00:00', created_at: '2026-05-06 19:00:00', author: '站长' },
  { id: 10, category_id: 1, admin_id: 1, title: 'Element Plus 主题定制完全指南', slug: 'element-plus-theming', summary: 'Element Plus 提供了强大的主题定制能力。', cover: '', status: 1, is_top: 0, is_recommend: 0, view_count: 580, like_count: 25, comment_count: 4, tags: [1, 2], published_at: '2026-03-25 15:00:00', created_at: '2026-03-25 14:00:00', author: '站长' },
]

export const mockCategories = [
  { id: 1, parent_id: 0, name: '前端开发', slug: 'frontend', description: 'HTML/CSS/JavaScript 等前端技术', cover: '', sort: 1, status: 1, article_count: 5 },
  { id: 2, parent_id: 0, name: '后端开发', slug: 'backend', description: 'PHP/Python/数据库等技术', cover: '', sort: 2, status: 1, article_count: 3 },
  { id: 3, parent_id: 0, name: '技术杂谈', slug: 'tech-talk', description: '行业动态与技术思考', cover: '', sort: 3, status: 1, article_count: 4 },
  { id: 4, parent_id: 0, name: '生活随笔', slug: 'life', description: '生活感悟与记录', cover: '', sort: 4, status: 1, article_count: 2 },
  { id: 5, parent_id: 1, name: 'Vue.js', slug: 'vue', description: 'Vue 相关技术', cover: '', sort: 1, status: 1, article_count: 3 },
  { id: 6, parent_id: 1, name: 'React', slug: 'react', description: 'React 相关技术', cover: '', sort: 2, status: 1, article_count: 2 },
  { id: 7, parent_id: 2, name: 'ThinkPHP', slug: 'thinkphp', description: 'ThinkPHP 开发', cover: '', sort: 1, status: 1, article_count: 2 },
]

export const mockTags = [
  { id: 1, name: 'Vue3', slug: 'vue3', color: '#42b883', article_count: 3 },
  { id: 2, name: 'JavaScript', slug: 'javascript', color: '#f7df1e', article_count: 5 },
  { id: 3, name: 'TypeScript', slug: 'typescript', color: '#3178c6', article_count: 2 },
  { id: 4, name: 'PHP', slug: 'php', color: '#777bb4', article_count: 2 },
  { id: 5, name: 'MySQL', slug: 'mysql', color: '#4479a1', article_count: 1 },
  { id: 6, name: '性能优化', slug: 'performance', color: '#e74c3c', article_count: 2 },
]

export const mockTutorials = [
  { id: 1, admin_id: 1, title: 'Vue3 从入门到精通', slug: 'vue3-beginner-to-pro', description: '一套完整的 Vue3 学习教程，从基础概念到高级实战。', cover: '', difficulty: 1, status: 1, is_recommend: 1, view_count: 5600, chapter_count: 8, sort: 1, published_at: '2026-01-15 09:00:00', created_at: '2026-01-15 08:00:00' },
  { id: 2, admin_id: 1, title: 'ThinkPHP 快速入门教程', slug: 'thinkphp-quick-start', description: '面向初学者的 ThinkPHP 框架入门教程。', cover: '', difficulty: 1, status: 1, is_recommend: 1, view_count: 3200, chapter_count: 6, sort: 2, published_at: '2026-02-10 10:00:00', created_at: '2026-02-10 09:00:00' },
  { id: 3, admin_id: 1, title: '前端性能优化实战', slug: 'frontend-performance', description: '深入前端性能优化的各个方面。', cover: '', difficulty: 2, status: 1, is_recommend: 0, view_count: 1800, chapter_count: 5, sort: 3, published_at: '2026-03-01 14:00:00', created_at: '2026-03-01 12:00:00' },
]

export const mockTutorialChapters = [
  { id: 1, tutorial_id: 1, parent_id: 0, title: 'Vue3 简介与环境搭建', slug: 'vue3-intro', summary: '了解 Vue3 的发展历程和生态环境。', sort: 1, status: 1, view_count: 1200 },
  { id: 2, tutorial_id: 1, parent_id: 0, title: 'Composition API 核心', slug: 'vue3-composition-api', summary: '学习 setup、ref、reactive 等核心 API。', sort: 2, status: 1, view_count: 980 },
  { id: 3, tutorial_id: 1, parent_id: 0, title: '组件化开发', slug: 'vue3-components', summary: '深入理解 Vue3 的组件系统。', sort: 3, status: 1, view_count: 850 },
  { id: 4, tutorial_id: 1, parent_id: 0, title: 'Vue Router 与路由守卫', slug: 'vue-router', summary: '学习 Vue Router 4 的配置和使用。', sort: 4, status: 1, view_count: 720 },
  { id: 5, tutorial_id: 1, parent_id: 0, title: 'Pinia 状态管理', slug: 'pinia', summary: '使用 Pinia 管理应用全局状态。', sort: 5, status: 1, view_count: 680 },
  { id: 6, tutorial_id: 1, parent_id: 0, title: '与后端 API 对接', slug: 'vue3-api', summary: '使用 Axios 封装 HTTP 请求。', sort: 6, status: 1, view_count: 590 },
  { id: 7, tutorial_id: 1, parent_id: 0, title: '项目实战：博客系统', slug: 'vue3-project', summary: '综合运用所学知识。', sort: 7, status: 1, view_count: 450 },
  { id: 8, tutorial_id: 1, parent_id: 0, title: '部署与发布', slug: 'vue3-deploy', summary: '学习如何将项目部署到生产环境。', sort: 8, status: 1, view_count: 380 },
  { id: 9, tutorial_id: 2, parent_id: 0, title: 'ThinkPHP 介绍与安装', slug: 'tp-intro', summary: '了解 ThinkPHP 框架的特点。', sort: 1, status: 1, view_count: 800 },
  { id: 10, tutorial_id: 2, parent_id: 0, title: '路由与控制器', slug: 'tp-routing', summary: '学习路由配置和控制器编写。', sort: 2, status: 1, view_count: 650 },
  { id: 11, tutorial_id: 2, parent_id: 0, title: '模型与数据库操作', slug: 'tp-model', summary: '使用模型操作数据库。', sort: 3, status: 1, view_count: 580 },
  { id: 12, tutorial_id: 3, parent_id: 0, title: '加载性能优化', slug: 'perf-loading', summary: '减少首屏加载时间。', sort: 1, status: 1, view_count: 420 },
  { id: 13, tutorial_id: 3, parent_id: 0, title: '渲染性能优化', slug: 'perf-render', summary: '优化 DOM 操作。', sort: 2, status: 1, view_count: 380 },
]

export const mockUsers = [
  { id: 1, username: 'demo', nickname: 'Demo用户', email: 'demo@kaiii.top', avatar: '', bio: '一个热爱技术的开发者', status: 1, created_at: '2025-01-15 10:00:00', last_login_at: '2026-05-01 08:00:00' },
  { id: 2, username: 'zhangsan', nickname: '张三', email: 'zhangsan@test.com', avatar: '', bio: '前端开发工程师', status: 1, created_at: '2025-02-20 14:30:00', last_login_at: '2026-04-20 10:00:00' },
  { id: 3, username: 'test', nickname: '测试用户', email: 'test@test.com', avatar: '', bio: '测试账号', status: 0, created_at: '2025-03-10 09:00:00', last_login_at: '2025-12-01 12:00:00' },
]

export const mockComments = [
  { id: 1, user_id: 1, target_type: 'article', target_id: 1, content: '写得非常好，Composition API 这部分讲解得很清晰！', status: 1, created_at: '2026-04-29 10:00:00', user: { nickname: 'Demo用户', avatar: '' } },
  { id: 2, user_id: 2, target_type: 'article', target_id: 1, content: '请问 ref 和 reactive 在性能上有区别吗？', status: 1, created_at: '2026-04-29 14:30:00', user: { nickname: '张三', avatar: '' } },
  { id: 3, user_id: 1, target_type: 'article', target_id: 3, content: 'AI 辅助开发确实是大趋势，已经在用了。', status: 1, created_at: '2026-04-21 09:00:00', user: { nickname: 'Demo用户', avatar: '' } },
  { id: 4, user_id: 1, target_type: 'article', target_id: 8, content: '这个博客系统看起来很棒，代码开源吗？', status: 1, created_at: '2026-03-29 11:00:00', user: { nickname: 'Demo用户', avatar: '' } },
  { id: 5, user_id: 2, target_type: 'tutorial_chapter', target_id: 2, content: '期待后面的章节更新！', status: 0, created_at: '2026-02-20 16:00:00', user: { nickname: '张三', avatar: '' } },
]

export const mockUploads = [
  { id: 1, original_name: 'logo.png', file_url: '/uploads/logo.png', file_size: 24500, mime_type: 'image/png', created_at: '2026-03-10 10:00:00' },
  { id: 2, original_name: 'banner.jpg', file_url: '/uploads/banner.jpg', file_size: 156000, mime_type: 'image/jpeg', created_at: '2026-03-15 14:00:00' },
  { id: 3, original_name: 'avatar_default.png', file_url: '/uploads/avatar_default.png', file_size: 12000, mime_type: 'image/png', created_at: '2026-04-01 09:00:00' },
]

export const mockAdmin = {
  id: 1, username: 'admin', nickname: '站长', avatar: '', role: 'admin', token: 'mock-admin-token-2025'
}

export const mockDashboard = {
  article_count: 10, tutorial_count: 3, user_count: 3, comment_count: 5, view_count_total: 18258,
  recent_articles: [
    { id: 1, title: 'Vue3 Composition API 实战指南', status: 1, published_at: '2026-04-28 09:00:00', view_count: 1280 },
    { id: 2, title: 'ThinkPHP 8 新特性详解', status: 1, published_at: '2026-04-25 10:30:00', view_count: 856 },
    { id: 3, title: '2026 年前端趋势展望', status: 1, published_at: '2026-04-20 14:00:00', view_count: 2100 },
    { id: 4, title: '程序员的自我成长之路', status: 1, published_at: '2026-04-15 11:00:00', view_count: 654 },
    { id: 5, title: 'Vue3 + Vite 项目最佳实践', status: 1, published_at: '2026-04-10 08:30:00', view_count: 1560 },
  ],
  recent_comments: [
    { id: 1, content: '写得非常好！', status: 1, created_at: '2026-04-29 10:00:00', user: { nickname: 'Demo用户' } },
    { id: 2, content: '请问 ref 和 reactive 在性能上有区别吗？', status: 1, created_at: '2026-04-29 14:30:00', user: { nickname: '张三' } },
    { id: 3, content: 'AI 辅助开发确实是大趋势', status: 1, created_at: '2026-04-21 09:00:00', user: { nickname: 'Demo用户' } },
    { id: 4, content: '代码开源吗？', status: 1, created_at: '2026-03-29 11:00:00', user: { nickname: 'Demo用户' } },
  ],
}

export const mockSystemConfig = {
  site_name: 'Kaiii 技术博客',
  site_description: '分享技术，记录成长',
  site_keywords: '技术博客,Vue3,ThinkPHP,前端开发,后端开发',
  site_logo: '',
  icp: '京ICP备2025XXXXXX号',
  social_github: 'https://github.com/kaiii-top',
  social_email: 'admin@kaiii.top',
  about_text: '一个热爱技术的开发者，专注于 Web 全栈开发。',
}
