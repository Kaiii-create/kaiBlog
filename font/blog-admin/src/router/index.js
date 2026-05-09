import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  {
    path: '/admin/login',
    name: 'AdminLogin',
    component: () => import('../views/auth/AdminLogin.vue'),
    meta: { title: '后台登录' }
  },
  {
    path: '/admin',
    component: () => import('../layouts/AdminLayout.vue'),
    redirect: '/admin/dashboard',
    children: [
      { path: 'dashboard', name: 'Dashboard', component: () => import('../views/dashboard/Dashboard.vue'), meta: { title: '控制台' } },
      { path: 'categories', name: 'AdminCategories', component: () => import('../views/categories/CategoryList.vue'), meta: { title: '栏目管理' } },
      { path: 'tags', name: 'AdminTags', component: () => import('../views/tags/TagList.vue'), meta: { title: '标签管理' } },
      { path: 'articles', name: 'AdminArticles', component: () => import('../views/articles/ArticleList.vue'), meta: { title: '文章管理' } },
      { path: 'articles/create', name: 'ArticleCreate', component: () => import('../views/articles/ArticleForm.vue'), meta: { title: '发布文章' } },
      { path: 'articles/edit/:id', name: 'ArticleEdit', component: () => import('../views/articles/ArticleForm.vue'), meta: { title: '编辑文章' } },
      { path: 'tutorials', name: 'AdminTutorials', component: () => import('../views/tutorials/TutorialList.vue'), meta: { title: '教程管理' } },
      { path: 'tutorials/create', name: 'TutorialCreate', component: () => import('../views/tutorials/TutorialForm.vue'), meta: { title: '新建教程' } },
      { path: 'tutorials/:id/chapters', name: 'TutorialChapters', component: () => import('../views/tutorials/TutorialChapters.vue'), meta: { title: '章节管理' } },
      { path: 'users', name: 'AdminUsers', component: () => import('../views/users/UserList.vue'), meta: { title: '用户管理' } },
      { path: 'comments', name: 'AdminComments', component: () => import('../views/comments/CommentList.vue'), meta: { title: '评论管理' } },
      { path: 'messages', name: 'AdminMessages', component: () => import('../views/messages/MessageList.vue'), meta: { title: '消息管理' } },
      { path: 'seo', name: 'AdminSeo', component: () => import('../views/settings/SeoSettings.vue'), meta: { title: 'SEO管理' } },
      { path: 'uploads', name: 'AdminUploads', component: () => import('../views/uploads/UploadList.vue'), meta: { title: '文件管理' } },
      { path: 'settings', name: 'AdminSettings', component: () => import('../views/settings/SystemSettings.vue'), meta: { title: '系统设置' } },
      { path: 'admins', name: 'AdminAdmins', component: () => import('../views/admins/AdminList.vue'), meta: { title: '管理员管理' } },
      { path: 'roles', name: 'AdminRoles', component: () => import('../views/roles/RoleList.vue'), meta: { title: '角色管理' } },
      { path: 'permissions', name: 'AdminPermissions', component: () => import('../views/permissions/PermissionList.vue'), meta: { title: '权限管理' } },
    ]
  },
  { path: '/:pathMatch(.*)*', redirect: '/admin/dashboard' },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to, from, next) => {
  document.title = to.meta.title ? `${to.meta.title} - 后台管理` : '后台管理'
  if (to.path.startsWith('/admin') && to.name !== 'AdminLogin') {
    const token = localStorage.getItem('admin_token')
    if (!token) return next('/admin/login')
  }
  next()
})

export default router
