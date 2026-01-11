# 彩票系统移动端

基于 Vue 3 + Vite + Vant 4 开发的彩票系统移动端。

## 技术栈

- Vue 3 - 渐进式 JavaScript 框架
- Vite 4 - 下一代前端构建工具
- Vue Router 4 - 路由管理
- Pinia - 状态管理
- Vant 4 - 移动端 UI 组件库
- Axios - HTTP 客户端
- Sass - CSS 预处理器

## 项目结构

```
src/
├── api/              # API 接口
│   ├── request.js    # Axios 封装
│   ├── user.js       # 用户相关接口
│   └── lottery.js    # 彩票相关接口
├── assets/           # 静态资源
│   ├── images/       # 图片
│   └── styles/       # 全局样式
├── components/       # 公共组件
├── router/           # 路由配置
│   └── index.js
├── stores/           # 状态管理
│   └── user.js       # 用户状态
├── views/            # 页面组件
│   ├── home/         # 首页
│   ├── game/         # 游戏页
│   ├── user/         # 用户中心
│   └── account/      # 账户相关
├── App.vue           # 根组件
└── main.js           # 入口文件
```

## 快速开始

### 安装依赖

```bash
npm install
```

### 启动开发服务器

```bash
npm run dev
```

访问 http://localhost:3000

### 构建生产版本

```bash
npm run build
```

### 预览生产版本

```bash
npm run preview
```

## 页面说明

### 首页 (/home)
- 轮播图展示
- 系统公告
- 热门彩种列表
- 收益榜单

### 下单大厅 (/hall)
- 彩种分类
- 当前期号
- 开奖倒计时
- 历史开奖

### 游戏下注 (/game/:type)
- 玩法选择
- 号码选择
- 金额设置
- 投注提交

### 用户中心 (/user)
- 个人信息
- 余额管理
- 功能菜单

### 投注记录 (/bet/list)
- 投注历史
- 状态筛选
- 详情查看

### 充值提现 (/account/recharge, /account/withdraw)
- 充值申请
- 提现申请
- 银行卡管理

### 账变记录 (/account/record)
- 资金变动记录
- 类型筛选

## 开发规范

### 组件命名
- 使用 PascalCase 命名组件
- 文件名与组件名保持一致

### API 调用
```javascript
import { getLotteryTypes } from '@/api/lottery'

const loadData = async () => {
  try {
    const res = await getLotteryTypes()
    if (res.code === 200) {
      // 处理数据
    }
  } catch (error) {
    console.error(error)
  }
}
```

### 状态管理
```javascript
import { useUserStore } from '@/stores/user'

const userStore = useUserStore()
const userInfo = computed(() => userStore.userInfo)
```

## API 配置

后端 API 地址配置在 `vite.config.js` 中：

```javascript
server: {
proxy: {
  '/api': {
      target: 'http://localhost:8080',
    changeOrigin: true
    }
  }
}
```

## 环境要求

- Node.js >= 14.0.0
- npm >= 6.0.0

## 注意事项

1. 本项目为移动端页面，推荐使用移动设备或浏览器移动模式访问
2. 开发时后端服务需同时运行
3. 生产环境需要配置反向代理

## License

MIT
