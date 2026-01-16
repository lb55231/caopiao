<template>
  <div class="activity-page">
    <!-- 顶部导航栏 -->
    <van-nav-bar
      title="活动中心"
      fixed
      placeholder
      :border="false"
      class="nav-bar"
    />

    <!-- 活动图片列表 -->
    <div class="activity-list">
      <div class="activity-banner">
        <img src="/images/banner/1.jpg" alt="活动1" />
      </div>
      <div class="activity-banner">
        <img src="/images/banner/2.jpg" alt="活动2" />
      </div>
      <div class="activity-banner">
        <img src="/images/banner/3.jpg" alt="活动3" />
      </div>
    </div>

    <!-- 底部导航 -->
    <Tabbar />
  </div>
</template>

<script setup>
import Tabbar from '@/components/Tabbar.vue'
</script>

<style scoped lang="scss">
.activity-page {
  min-height: 100vh;
  background: #f5f5f5;
  padding-bottom: 60px;
}

.nav-bar {
  :deep(.van-nav-bar__title) {
    font-weight: 600;
  }
}

.activity-list {
  padding: 0;
}

.activity-banner {
  width: 100%;
  margin-bottom: 0;
  background: white;
  
  img {
    width: 100%;
    height: 100px;
    object-fit: cover;
    display: block;
  }
}
</style>
