import { createRouter, createWebHistory } from 'vue-router'
import { useUserStore } from '@/stores/user'

const routes = [
  {
    path: '/',
    redirect: '/home'
  },
  {
    path: '/home',
    name: 'Home',
    component: () => import('@/views/home/Index.vue'),
    meta: { title: '首页', keepAlive: true }
  },
  {
    path: '/hall',
    name: 'Hall',
    component: () => import('@/views/home/Hall.vue'),
    meta: { title: '下单大厅', keepAlive: true }
  },
  {
    path: '/login',
    name: 'Login',
    component: () => import('@/views/user/Login.vue'),
    meta: { title: '登录' }
  },
  {
    path: '/register',
    name: 'Register',
    component: () => import('@/views/user/Register.vue'),
    meta: { title: '注册' }
  },
  {
    path: '/user',
    name: 'User',
    component: () => import('@/views/user/Index.vue'),
    meta: { title: '个人中心', requiresAuth: true }
  },
  {
    path: '/user/profile',
    name: 'UserProfile',
    component: () => import('@/views/user/Profile.vue'),
    meta: { title: '个人资料', requiresAuth: true }
  },
  {
    path: '/user/password',
    name: 'Password',
    component: () => import('@/views/user/Password.vue'),
    meta: { title: '修改密码', requiresAuth: true }
  },
  // 账户相关路由
  {
    path: '/account/recharge',
    name: 'Recharge',
    component: () => import('@/views/account/Recharge.vue'),
    meta: { title: '账户充值', requiresAuth: true }
  },
  {
    path: '/account/withdraw',
    name: 'Withdraw',
    component: () => import('@/views/account/Withdraw.vue'),
    meta: { title: '账户提现', requiresAuth: true }
  },
  {
    path: '/account/bank',
    name: 'Bank',
    component: () => import('@/views/account/Bank.vue'),
    meta: { title: '银行卡管理', requiresAuth: true }
  },
  {
    path: '/account/add-bank',
    name: 'AddBank',
    component: () => import('@/views/account/AddBank.vue'),
    meta: { title: '绑定银行卡', requiresAuth: true }
  },
  // 游戏路由
  {
    path: '/game/k3/:name',
    name: 'K3Game',
    component: () => import('@/views/game/K3.vue'),
    meta: { title: 'K3游戏', requiresAuth: true }
  },
  {
    path: '/game/:type',
    name: 'Game',
    component: () => import('@/views/game/Index.vue'),
    meta: { title: '下注', requiresAuth: true }
  },
  {
    path: '/account/record',
    name: 'Record',
    component: () => import('@/views/account/Record.vue'),
    meta: { title: '交易记录', requiresAuth: true }
  },
  {
    path: '/bet/list',
    name: 'BetList',
    component: () => import('@/views/user/BetList.vue'),
    meta: { title: '投注记录', requiresAuth: true }
  },
  {
    path: '/records/bet',
    name: 'BetRecords',
    component: () => import('@/views/records/BetRecords.vue'),
    meta: { title: '投注记录', requiresAuth: true }
  },
  {
    path: '/records/account',
    name: 'AccountRecords',
    component: () => import('@/views/records/AccountRecords.vue'),
    meta: { title: '账变记录', requiresAuth: true }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// 路由守卫
router.beforeEach((to, from, next) => {
  document.title = to.meta.title || '彩票系统'
  
  if (to.meta.requiresAuth) {
    const userStore = useUserStore()
    if (!userStore.token) {
      next('/login')
    } else {
      next()
    }
  } else {
    next()
  }
})

export default router

