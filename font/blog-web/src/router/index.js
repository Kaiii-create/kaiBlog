import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  {
    path: '/',
    component: () => import('../layouts/DefaultLayout.vue'),
    children: [
      { path: '', name: 'Home', component: () => import('../views/home/HomePage.vue'), meta: { title: '首页', seoType: 'home' } },
      { path: 'articles', name: 'Articles', component: () => import('../views/articles/ArticleList.vue'), meta: { title: '文章' } },
      { path: 'articles/:id', name: 'ArticleDetail', component: () => import('../views/articles/ArticleDetail.vue'), meta: { title: '文章详情', seoType: 'article', seoIdKey: 'id' } },
      { path: 'categories/:id', name: 'CategoryArticles', component: () => import('../views/articles/CategoryArticles.vue'), meta: { title: '栏目内容', seoType: 'category', seoIdKey: 'id' } },
      { path: 'tutorials', name: 'Tutorials', component: () => import('../views/tutorials/TutorialList.vue'), meta: { title: '教程' } },
      { path: 'tutorials/:id', name: 'TutorialDetail', component: () => import('../views/tutorials/TutorialDetail.vue'), meta: { title: '教程详情', seoType: 'tutorial', seoIdKey: 'id' } },
      { path: 'tutorials/:id/chapter/:chapterId', name: 'TutorialChapter', component: () => import('../views/tutorials/TutorialChapter.vue'), meta: { title: '教程章节', seoType: 'chapter', seoIdKey: 'chapterId' } },
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
        ],
      },
    ],
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: () => import('../views/NotFound.vue'),
    meta: { title: '页面不存在' },
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 }
  },
})

router.beforeEach((to, from, next) => {
  if (to.meta.requiresAuth && !localStorage.getItem('user_token')) {
    next({ name: 'Login', query: { redirect: to.fullPath } })
    return
  }
  next()
})

export default router
