<template>
  <div class="tabbar-layout">
    <!-- 页面内容区域 -->
    <div class="content-wrapper">
      <router-view v-slot="{ Component }">
        <keep-alive>
          <component :is="Component" v-if="$route.meta.keepAlive" />
        </keep-alive>
        <component :is="Component" v-if="!$route.meta.keepAlive" />
      </router-view>
    </div>

    <!-- 底部导航栏 -->
    <van-tabbar
      v-model="active"
      route
      active-color="#ff6034"
      inactive-color="#7d7e80"
      class="custom-tabbar"
    >
      <van-tabbar-item to="/home" icon="wap-home-o">
        首页
      </van-tabbar-item>
      
      <van-tabbar-item to="/orders" icon="orders-o">
        订单
      </van-tabbar-item>
      
      <van-tabbar-item to="/merchants" icon="shop-o">
        商家列表
      </van-tabbar-item>
      
      <van-tabbar-item to="/activity" icon="gift-o">
        活动
      </van-tabbar-item>
      
      <van-tabbar-item to="/user" icon="user-o">
        我的
      </van-tabbar-item>
    </van-tabbar>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const active = ref(0)

// 根据路由设置激活的 tab
const tabRoutes = ['/home', '/orders', '/merchants', '/activity', '/user']

watch(() => route.path, (newPath) => {
  const index = tabRoutes.findIndex(r => newPath.startsWith(r))
  if (index !== -1) {
    active.value = index
  }
}, { immediate: true })
</script>

<style scoped lang="scss">
.tabbar-layout {
  width: 100%;
  height: 100vh;
  display: flex;
  flex-direction: column;
}

.content-wrapper {
  flex: 1;
  overflow-y: auto;
  -webkit-overflow-scrolling: touch;
}

.custom-tabbar {
  box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.1);
  
  :deep(.van-tabbar-item) {
    font-size: 12px;
  }
  
  :deep(.van-tabbar-item__icon) {
    font-size: 22px;
    margin-bottom: 4px;
  }
}
</style>
