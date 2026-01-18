<template>
  <el-container class="layout-container">
    <el-aside :width="isCollapse ? '64px' : '220px'" class="layout-aside">
      <div class="logo-container">
        <span v-if="!isCollapse" class="logo-text">代理系统</span>
      </div>
      <Sidebar :is-collapse="isCollapse" />
    </el-aside>
    
    <el-container class="main-container">
      <el-header class="layout-header">
        <Header @toggle-collapse="toggleCollapse" :is-collapse="isCollapse" />
      </el-header>
      
      <el-main class="layout-main">
        <router-view v-slot="{ Component }">
          <transition name="fade-transform" mode="out-in">
            <component :is="Component" />
          </transition>
        </router-view>
      </el-main>
    </el-container>
  </el-container>
</template>

<script setup>
import { ref } from 'vue'
import Sidebar from './components/Sidebar.vue'
import Header from './components/Header.vue'

const isCollapse = ref(false)

const toggleCollapse = () => {
  isCollapse.value = !isCollapse.value
}
</script>

<style lang="scss" scoped>
.layout-container {
  width: 100%;
  height: 100%;
}

.layout-aside {
  background: #001529;
  transition: width 0.3s;
  overflow-x: hidden;
  
  .logo-container {
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    
    img {
      width: 32px;
      height: 32px;
    }
    
    .logo-text {
      color: #fff;
      font-size: 18px;
      font-weight: 600;
    }
  }
}

.main-container {
  background: #f0f2f5;
}

.layout-header {
  background: #fff;
  padding: 0 20px;
  box-shadow: 0 1px 4px rgba(0, 21, 41, 0.08);
}

.layout-main {
  padding: 20px;
  overflow: auto;
}

/* 页面切换动画 */
.fade-transform-leave-active,
.fade-transform-enter-active {
  transition: all 0.3s;
}

.fade-transform-enter-from {
  opacity: 0;
  transform: translateX(-30px);
}

.fade-transform-leave-to {
  opacity: 0;
  transform: translateX(30px);
}
</style>
