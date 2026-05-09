import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  {
    path: '/',
    component: () => import('../layouts/DefaultLayout.vue'),
    children: [
      { path: '', name: 'Home', component: () => import('../views/home/HomePage.vue'), meta: { title: '首页' } },
      { path: 'articles', name: 'Articles', component: () => import('../views/articles/ArticleList.vue'), meta: { title: '文章列表' } },
      { path: 'articles/:id', name: 'ArticleDetail', component: () => import('../views/articles/ArticleDetail.vue'), meta: { title: '文章详情' } },
      { path: 'categories/:id', name: 'CategoryArticles', component: () => import('../views/articles/CategoryArticles.vue'), meta: { title: '栏目文章' } },
      { path: 'tutorials', name: 'Tutorials', component: () => import('../views/tutorials/TutorialList.vue'), meta: { title: '教程中心' } },
      { path: 'tutorials/:id', name: 'TutorialDetail', component: () => import('../views/tutorials/TutorialDetail.vue'), meta: { title: '教程详情' } },
      { path: 'tutorials/:id/chapter/:chapterId', name: 'TutorialChapter', component: () => import('../views/tutorials/TutorialChapter.vue'), meta: { title: '教程阅读' } },
      { path: 'search', name: 'Search', component: () => import('../views/search/SearchResult.vue'), meta: { title: '搜索' } },
      { path: 'login', name: 'Login', component: () => import('../views/auth/Login.vue'), meta: { title: '登录' } },
      { path: 'register', name: 'Register', component: () => import('../views/auth/Register.vue'), meta: { title: '注册' } },
      {
        path: 'member',
        redirect: '/member/profile',
        component: () => import('../layouts/MemberLayout.vue'),
        children: [
          { path: 'profile', name: 'MemberProfile', component: () => import('../views/member/MemberProfile.vue'), meta: { title: '个人资料', requiresAuth: true } },
          { path: 'favorites', name: 'MemberFavorites', component: () => import('../views/member/MemberFavorites.vue'), meta: { title: '我的收藏', requiresAuth: true } },
          { path: 'comments', name: 'MemberComments', component: () => import('../views/member/MemberComments.vue'), meta: { title: '我的评论', requiresAuth: true } },
        ]
      },
    ]
  },
  { path: '/:pathMatch(.*)*', name: 'NotFound', component: () => import('../views/NotFound.vue'), meta: { title: '404' } },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior() { return { top: 0 } }
})

router.beforeEach((to, from, next) => {
  document.title = to.meta.title ? `${to.meta.title} - Kaiii 技术博客` : 'Kaiii 技术博客'
  if (to.meta.requiresAuth) {
    const token = localStorage.getItem('user_token')
    if (!token) return next({ name: 'Login', query: { redirect: to.fullPath } })
  }
  next()
})

export default router
