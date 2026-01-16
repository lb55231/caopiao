<template>
  <van-tabbar :model-value="active" route>
    <van-tabbar-item icon="wap-home-o" to="/home">首页</van-tabbar-item>
    <van-tabbar-item icon="orders-o" to="/orders">订单</van-tabbar-item>
    <van-tabbar-item icon="shop-o" to="/merchants">商家列表</van-tabbar-item>
    <van-tabbar-item icon="gift-o" to="/activity">活动</van-tabbar-item>
    <van-tabbar-item icon="user-o" to="/user">我的</van-tabbar-item>
  </van-tabbar>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()

// 根据当前路由自动设置激活项
const active = computed(() => {
  const path = route.path
  if (path.startsWith('/home')) return 0
  if (path.startsWith('/orders')) return 1
  if (path.startsWith('/merchants')) return 2
  if (path.startsWith('/activity')) return 3
  if (path.startsWith('/user')) return 4
  return 0
})
</script>

<style scoped>
:deep(.van-tabbar) {
  box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.1);
}
</style>
