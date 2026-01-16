# 底部导航检查清单

## 路由配置检查

| 路径 | 路由名称 | 组件文件 | 状态 |
|------|---------|---------|------|
| `/home` | Home | views/home/Index.vue | ✅ |
| `/orders` | Orders | views/orders/Index.vue | ✅ |
| `/merchants` | Merchants | views/merchants/Index.vue | ✅ |
| `/activity` | Activity | views/activity/Index.vue | ✅ |
| `/user` | User | views/user/Index.vue | ✅ |
| `/hall` | Hall | views/home/Hall.vue | ✅ |

## 各页面底部导航配置

### 首页 (Home - Index.vue)
- **路径**: `/home`
- **tabbarActive**: `ref(0)` ✅
- **底部导航**: 5个标签 ✅
- **图标**: wap-home-o, orders-o, shop-o, gift-o, user-o ✅

### 订单页 (Orders - orders/Index.vue)
- **路径**: `/orders`
- **tabbarActive**: `ref(1)` ✅
- **底部导航**: 5个标签 ✅
- **图标**: wap-home-o, orders-o, shop-o, gift-o, user-o ✅

### 商家列表 (Merchants - merchants/Index.vue)
- **路径**: `/merchants`
- **tabbarActive**: `ref(2)` ✅
- **底部导航**: 5个标签 ✅
- **图标**: wap-home-o, orders-o, shop-o, gift-o, user-o ✅

### 活动中心 (Activity - activity/Index.vue)
- **路径**: `/activity`
- **tabbarActive**: `ref(3)` ✅
- **底部导航**: 5个标签 ✅
- **图标**: wap-home-o, orders-o, shop-o, gift-o, user-o ✅

### 我的 (User - user/Index.vue)
- **路径**: `/user`
- **active**: `ref(4)` ✅
- **底部导航**: 5个标签 ✅
- **图标**: wap-home-o, orders-o, shop-o, gift-o, user-o ✅

### 下单大厅 (Hall - home/Hall.vue)
- **路径**: `/hall`
- **tabbarActive**: `ref(1)` ⚠️ 注意：这个页面不在主导航中
- **底部导航**: 已更新为5个标签 ✅

## 底部导航标准模板

```vue
<van-tabbar v-model="tabbarActive" route>
  <van-tabbar-item icon="wap-home-o" to="/home">首页</van-tabbar-item>
  <van-tabbar-item icon="orders-o" to="/orders">订单</van-tabbar-item>
  <van-tabbar-item icon="shop-o" to="/merchants">商家列表</van-tabbar-item>
  <van-tabbar-item icon="gift-o" to="/activity">活动</van-tabbar-item>
  <van-tabbar-item icon="user-o" to="/user">我的</van-tabbar-item>
</van-tabbar>
```

## tabbarActive 索引对应

| 索引 | 页面 | 路径 |
|-----|------|------|
| 0 | 首页 | /home |
| 1 | 订单 | /orders |
| 2 | 商家列表 | /merchants |
| 3 | 活动 | /activity |
| 4 | 我的 | /user |

## 检查步骤

1. ✅ 所有主页面都已添加底部导航
2. ✅ 所有底部导航都使用统一的5个标签
3. ✅ 所有路径都使用绝对路径（以 `/` 开头）
4. ✅ tabbarActive 值与索引对应正确
5. ✅ 使用 `route` 属性启用路由模式
6. ✅ App.vue 已恢复 keep-alive 逻辑

## 可能的问题

### 如果点击无响应
1. 检查浏览器控制台是否有错误
2. 确认路由配置中的路径与 `to` 属性一致
3. 检查是否需要登录（订单页需要登录）
4. 重启开发服务器

### 如果高亮不正确
1. 检查 `tabbarActive` 的 ref 值
2. 确保每个页面的值唯一且正确
3. 确认使用了 `v-model` 绑定

## 测试清单

- [ ] 点击"首页"能跳转到首页
- [ ] 点击"订单"能跳转到订单页
- [ ] 点击"商家列表"能跳转到商家列表
- [ ] 点击"活动"能跳转到活动中心
- [ ] 点击"我的"能跳转到个人中心
- [ ] 各页面高亮显示正确
- [ ] 来回切换流畅无卡顿
