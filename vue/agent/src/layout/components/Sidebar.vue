<template>
  <el-menu
    :default-active="activeMenu"
    class="sidebar-menu"
    :collapse="isCollapse"
    :unique-opened="true"
    router
    background-color="#001529"
    text-color="rgba(255, 255, 255, 0.65)"
    active-text-color="#1890ff"
  >
    <template v-for="item in menuList" :key="item.path">
      <!-- 单级菜单 -->
      <el-menu-item 
        v-if="!item.children" 
        :index="item.path"
        class="menu-item"
      >
        <el-icon><component :is="item.icon" /></el-icon>
        <template #title>{{ item.title }}</template>
      </el-menu-item>
      
      <!-- 多级菜单 -->
      <el-sub-menu v-else :index="item.path" class="sub-menu">
        <template #title>
          <el-icon><component :is="item.icon" /></el-icon>
          <span>{{ item.title }}</span>
        </template>
        <el-menu-item
          v-for="child in item.children"
          :key="child.path"
          :index="child.path"
          class="menu-item"
        >
          <el-icon><component :is="child.icon" /></el-icon>
          <template #title>{{ child.title }}</template>
        </el-menu-item>
      </el-sub-menu>
    </template>
  </el-menu>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { menuList } from '@/config/menu'

defineProps({
  isCollapse: {
    type: Boolean,
    default: false
  }
})

const route = useRoute()

const activeMenu = computed(() => {
  return route.path
})
</script>

<style lang="scss" scoped>
.sidebar-menu {
  border-right: none;
  height: calc(100vh - 60px);
  
  :deep(.el-menu-item),
  :deep(.el-sub-menu__title) {
    &:hover {
      background-color: rgba(255, 255, 255, 0.08) !important;
    }
  }
  
  :deep(.el-menu-item.is-active) {
    background-color: #1890ff !important;
    color: #fff !important;
  }
}
</style>
