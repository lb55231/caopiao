import { createRouter, createWebHistory } from 'vue-router'
import { useAdminStore } from '@/stores/admin'

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: () => import('@/views/Login.vue'),
    meta: { title: '管理员登录' }
  },
  {
    path: '/',
    component: () => import('@/layout/Index.vue'),
    redirect: '/member/list',
    meta: { requiresAuth: true },
    children: [
      // 会员管理 - 一级菜单
      {
        path: 'member',
        name: 'MemberManage',
        meta: { 
          title: '会员管理', 
          icon: 'User',
          alwaysShow: true
        },
        redirect: '/member/groups',
        children: [
          {
            path: 'groups',
            name: 'MemberGroups',
            component: () => import('@/views/member/Groups.vue'),
            meta: { title: '会员组', icon: 'UserFilled' }
          },
          {
            path: 'list',
            name: 'MemberList',
            component: () => import('@/views/member/List.vue'),
            meta: { title: '会员列表', icon: 'User' }
          },
          {
            path: 'same-ip',
            name: 'SameIpCheck',
            component: () => import('@/views/member/SameIp.vue'),
            meta: { title: '同IP会员检测', icon: 'Position' }
          },
          {
            path: 'fund-records',
            name: 'FundRecords',
            component: () => import('@/views/member/FundRecords.vue'),
            meta: { title: '账变记录', icon: 'List' }
          },
          {
            path: 'bank-info',
            name: 'BankInfo',
            component: () => import('@/views/member/BankInfo.vue'),
            meta: { title: '银行信息', icon: 'CreditCard' }
          },
          {
            path: 'agent-links',
            name: 'AgentLinks',
            component: () => import('@/views/member/AgentLinks.vue'),
            meta: { title: '代理注册链接', icon: 'Link' }
          },
          {
            path: 'login-logs',
            name: 'LoginLogs',
            component: () => import('@/views/member/LoginLogs.vue'),
            meta: { title: '登录日志', icon: 'Tickets' }
          }
        ]
      },
      // 系统管理 - 一级菜单
      {
        path: 'system',
        name: 'SystemManage',
        meta: { 
          title: '系统管理', 
          icon: 'Setting',
          alwaysShow: true // 始终显示父菜单
        },
        redirect: '/system/settings',
        children: [
          {
            path: 'settings',
            name: 'SystemSettings',
            component: () => import('@/views/system/Settings.vue'),
            meta: { title: '系统设置', icon: 'Tools' }
          },
          {
            path: 'lottery-types',
            name: 'LotteryTypes',
            component: () => import('@/views/system/LotteryTypes.vue'),
            meta: { title: '彩种管理', icon: 'Menu' }
          },
          {
            path: 'plays',
            name: 'Plays',
            component: () => import('@/views/system/Plays.vue'),
            meta: { title: '玩法管理', icon: 'Operation' }
          },
          {
            path: 'activities',
            name: 'Activities',
            component: () => import('@/views/system/Activities.vue'),
            meta: { title: '活动管理', icon: 'Present' }
          },
          {
            path: 'lottery',
            name: 'Lottery',
            component: () => import('@/views/system/Lottery.vue'),
            meta: { title: '开奖管理', icon: 'Trophy' }
          },
          {
            path: 'set-lottery',
            name: 'SetLottery',
            component: () => import('@/views/system/SetLottery.vue'),
            meta: { title: '设置开奖', icon: 'EditPen' }
          },
          {
            path: 'game-records',
            name: 'GameRecords',
            component: () => import('@/views/system/GameRecords.vue'),
            meta: { title: '游戏记录', icon: 'Histogram' }
          },
          {
            path: 'bet-check',
            name: 'BetCheck',
            component: () => import('@/views/system/BetCheck.vue'),
            meta: { title: '注单异常检查', icon: 'Warning' }
          },
          {
            path: 'admins',
            name: 'Admins',
            component: () => import('@/views/system/Admins.vue'),
            meta: { title: '管理员管理', icon: 'UserFilled' }
          },
          {
            path: 'change-password',
            name: 'ChangePassword',
            component: () => import('@/views/system/ChangePassword.vue'),
            meta: { title: '修改密码', icon: 'Lock' }
          }
        ]
      },
      // 电子银行 - 一级菜单
      {
        path: 'bank',
        name: 'BankManage',
        meta: { 
          title: '电子银行', 
          icon: 'CreditCard',
          alwaysShow: true
        },
        redirect: '/bank/sysbank',
        children: [
          {
            path: 'sysbank',
            name: 'SysBank',
            component: () => import('@/views/bank/SysBank.vue'),
            meta: { title: '提款银行', icon: 'WalletFilled' }
          },
          {
            path: 'payset',
            name: 'PaySet',
            component: () => import('@/views/bank/PaySet.vue'),
            meta: { title: '存款方式配置', icon: 'Wallet' }
          },
          {
            path: 'recharge',
            name: 'RechargeRecords',
            component: () => import('@/views/bank/RechargeRecords.vue'),
            meta: { title: '充值记录', icon: 'Money' }
          },
          {
            path: 'withdraw',
            name: 'WithdrawRecords',
            component: () => import('@/views/bank/WithdrawRecords.vue'),
            meta: { title: '提现记录', icon: 'CreditCard' }
          }
        ]
      }
    ]
  }
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes
})

// 路由守卫
router.beforeEach((to, from, next) => {
  document.title = to.meta.title ? `${to.meta.title} - 管理后台` : '管理后台'
  
  const adminStore = useAdminStore()
  
  if (to.meta.requiresAuth && !adminStore.token) {
    next('/login')
  } else if (to.path === '/login' && adminStore.token) {
    next('/')
  } else {
    next()
  }
})

export default router

