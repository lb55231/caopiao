export const menuList = [
  {
    path: '/account',
    title: '账户管理',
    icon: 'User'
  },
  {
    path: '/finance',
    title: '财务管理',
    icon: 'Wallet',
    children: [
      {
        path: '/finance/account-change',
        title: '账变记录',
        icon: 'List'
      },
      {
        path: '/finance/recharge',
        title: '充值记录',
        icon: 'CirclePlus'
      },
      {
        path: '/finance/withdraw',
        title: '提现记录',
        icon: 'Minus'
      },
      {
        path: '/finance/profit',
        title: '收益报表',
        icon: 'TrendCharts'
      }
    ]
  },
  {
    path: '/lottery',
    title: '彩票管理',
    icon: 'Tickets',
    children: [
      {
        path: '/lottery/list',
        title: '彩票管理',
        icon: 'Tickets'
      },
      {
        path: '/lottery/draw',
        title: '开奖管理',
        icon: 'VideoPlay'
      },
      {
        path: '/lottery/set-draw',
        title: '设置开奖',
        icon: 'Setting'
      },
      {
        path: '/lottery/orders',
        title: '注单管理',
        icon: 'Document'
      }
    ]
  }
]
