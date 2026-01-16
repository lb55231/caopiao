import { createRouter, createWebHistory } from 'vue-router'
import Layout from '@/layout/index.vue'

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: () => import('@/views/login/index.vue'),
    meta: { title: '登录' }
  },
  {
    path: '/',
    component: Layout,
    redirect: '/account',
    children: [
      {
        path: '/account',
        name: 'Account',
        component: () => import('@/views/account/index.vue'),
        meta: { title: '账户管理', icon: 'User' }
      }
    ]
  },
  {
    path: '/finance',
    component: Layout,
    redirect: '/finance/account-change',
    meta: { title: '财务管理', icon: 'Wallet' },
    children: [
      {
        path: 'account-change',
        name: 'AccountChange',
        component: () => import('@/views/finance/account-change.vue'),
        meta: { title: '账变记录', icon: 'List' }
      },
      {
        path: 'recharge',
        name: 'Recharge',
        component: () => import('@/views/finance/recharge.vue'),
        meta: { title: '充值记录', icon: 'CirclePlus' }
      },
      {
        path: 'withdraw',
        name: 'Withdraw',
        component: () => import('@/views/finance/withdraw.vue'),
        meta: { title: '提现记录', icon: 'Minus' }
      },
      {
        path: 'profit',
        name: 'Profit',
        component: () => import('@/views/finance/profit.vue'),
        meta: { title: '收益报表', icon: 'TrendCharts' }
      }
    ]
  },
  {
    path: '/lottery',
    component: Layout,
    redirect: '/lottery/list',
    meta: { title: '彩票管理', icon: 'Tickets' },
    children: [
      {
        path: 'list',
        name: 'LotteryList',
        component: () => import('@/views/lottery/list.vue'),
        meta: { title: '彩票管理', icon: 'Tickets' }
      },
      {
        path: 'draw',
        name: 'LotteryDraw',
        component: () => import('@/views/lottery/draw.vue'),
        meta: { title: '开奖管理', icon: 'VideoPlay' }
      },
      {
        path: 'set-draw',
        name: 'SetDraw',
        component: () => import('@/views/lottery/set-draw.vue'),
        meta: { title: '设置开奖', icon: 'Setting' }
      },
      {
        path: 'orders',
        name: 'LotteryOrders',
        component: () => import('@/views/lottery/orders.vue'),
        meta: { title: '注单管理', icon: 'Document' }
      }
    ]
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// 路由守卫
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')
  
  if (to.path === '/login') {
    next()
  } else {
    if (token) {
      next()
    } else {
      next('/login')
    }
  }
})

export default router
