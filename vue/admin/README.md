# 彩票系统管理后台

基于 Vue 3 + Element Plus 的管理后台系统

## 技术栈

- Vue 3
- Vue Router 4
- Pinia
- Element Plus
- Axios
- Vite

## 开发

```bash
# 安装依赖
npm install

# 启动开发服务器
npm run dev

# 构建生产版本
npm run build
```

## 默认登录账号

- 用户名: admin
- 密码: admin123
- 安全码: 123456

## 项目结构

```
vue/admin/
├── public/          # 静态资源
├── src/
│   ├── api/        # API接口
│   ├── assets/     # 资源文件
│   ├── components/ # 组件
│   ├── layout/     # 布局组件
│   ├── router/     # 路由配置
│   ├── stores/     # 状态管理
│   ├── utils/      # 工具函数
│   ├── views/      # 页面
│   ├── App.vue     # 根组件
│   └── main.js     # 入口文件
├── index.html
├── package.json
└── vite.config.js
```

## API接口说明

后端API基础路径: `http://127.0.0.1:8000/api`

开发环境通过 Vite proxy 代理到 `/adminapi`
