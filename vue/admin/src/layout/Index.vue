<template>
  <div class="admin-layout">
    <!-- 顶部导航栏 -->
    <el-header class="layout-header">
      <div class="header-left">
        <div class="logo">
          <img v-if="siteConfig.weblogo" :src="getImageUrl(siteConfig.weblogo)" alt="Logo" class="logo-image" />
          <i v-else class="el-icon-s-platform"></i>
          <span class="logo-text">{{ siteConfig.webtitle || '彩票管理系统' }}</span>
        </div>
      </div>
      
      <div class="header-right">
        <el-dropdown @command="handleCommand">
          <span class="user-dropdown">
            <el-icon><User /></el-icon>
            {{ adminStore.username }}
            <el-icon class="el-icon--right"><arrow-down /></el-icon>
          </span>
          <template #dropdown>
            <el-dropdown-menu>
              <el-dropdown-item command="editPass">修改密码</el-dropdown-item>
              <el-dropdown-item command="logout" divided>退出登录</el-dropdown-item>
            </el-dropdown-menu>
          </template>
        </el-dropdown>
      </div>
    </el-header>

    <el-container class="layout-container">
      <!-- 左侧菜单 -->
      <el-aside class="layout-aside" width="200px">
        <el-menu
          :default-active="activeMenu"
          :default-openeds="defaultOpeneds"
          class="side-menu"
          background-color="#2c3e50"
          text-color="#fff"
          active-text-color="#5CB85C"
          router
        >
          <template v-for="route in menuRoutes" :key="route.path">
            <!-- 有子菜单的情况 -->
            <el-sub-menu 
              v-if="route.children && route.children.length > 0" 
              :index="route.path"
            >
              <template #title>
                <el-icon><component :is="route.meta.icon" /></el-icon>
                <span>{{ route.meta.title }}</span>
              </template>
              <el-menu-item 
                v-for="child in route.children" 
                :key="child.path"
                :index="getFullPath(route.path, child.path)"
              >
                <el-icon><component :is="child.meta.icon" /></el-icon>
                <span>{{ child.meta.title }}</span>
              </el-menu-item>
            </el-sub-menu>
            
            <!-- 没有子菜单的情况 -->
            <el-menu-item 
              v-else
              :index="route.path"
            >
              <el-icon><component :is="route.meta.icon" /></el-icon>
              <span>{{ route.meta.title }}</span>
            </el-menu-item>
          </template>
        </el-menu>
      </el-aside>

      <!-- 主内容区 -->
      <el-main class="layout-main">
        <router-view />
      </el-main>
    </el-container>
  </div>
</template>

<script setup>
import { computed, reactive, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ElMessageBox } from 'element-plus'
import { useAdminStore } from '@/stores/admin'
import { getImageUrl } from '@/utils/image'
import request from '@/utils/request'

const route = useRoute()
const router = useRouter()
const adminStore = useAdminStore()

// 网站配置
const siteConfig = reactive({
  webtitle: '彩票管理系统',
  weblogo: ''
})

// 加载网站配置
const loadSiteConfig = async () => {
  try {
    const res = await request.get('/system/config')
    if (res.code === 200) {
      Object.assign(siteConfig, res.data)
      // 更新页面标题
      document.title = siteConfig.webtitle + ' - 管理后台'
    }
  } catch (error) {
    console.error('加载网站配置失败:', error)
  }
}

// 页面加载时获取配置
onMounted(() => {
  loadSiteConfig()
})

// 当前激活的菜单
const activeMenu = computed(() => {
  return route.path
})

// 默认展开的子菜单
const defaultOpeneds = computed(() => {
  return ['/system']
})

// 菜单路由（过滤掉hidden的）
const menuRoutes = computed(() => {
  const routes = router.getRoutes()
  const parentRoute = routes.find(r => r.path === '/')
  if (!parentRoute || !parentRoute.children) return []
  
  return parentRoute.children.filter(r => !r.meta?.hidden).map(route => {
    // 如果有子路由，提取出来
    if (route.children && route.children.length > 0) {
      return {
        ...route,
        children: route.children.filter(child => !child.meta?.hidden)
      }
    }
    return route
  })
})

// 获取完整路径
const getFullPath = (parentPath, childPath) => {
  // 确保返回的路径始终以 / 开头
  if (childPath.startsWith('/')) {
    return childPath
  }
  // 如果父路径不以 / 开头，添加 /
  const fullParentPath = parentPath.startsWith('/') ? parentPath : `/${parentPath}`
  return `${fullParentPath}/${childPath}`
}

// 处理下拉菜单命令
const handleCommand = async (command) => {
  if (command === 'logout') {
    try {
      await ElMessageBox.confirm('确定要退出登录吗？', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      })
      adminStore.logout()
      router.push('/login')
    } catch {
      // 取消
    }
  } else if (command === 'editPass') {
    ElMessageBox.alert('修改密码功能开发中', '提示')
  }
}
</script>

<style scoped lang="scss">
.admin-layout {
  height: 100vh;
  display: flex;
  flex-direction: column;
}

.layout-header {
  background: #2c3e50;
  color: #fff;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 20px;
  height: 60px !important;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.header-left {
  display: flex;
  align-items: center;
}

.logo {
  display: flex;
  align-items: center;
  font-size: 20px;
  font-weight: bold;
  
  i {
    font-size: 28px;
    margin-right: 10px;
  }
  
  .logo-image {
    max-width: 40px;
    max-height: 40px;
    object-fit: contain;
    margin-right: 10px;
  }
}

.logo-text {
  color: #fff;
}

.header-right {
  .user-dropdown {
    display: flex;
    align-items: center;
    gap: 5px;
    cursor: pointer;
    color: #fff;
    
    &:hover {
      color: #5CB85C;
    }
  }
}

.layout-container {
  flex: 1;
  overflow: hidden;
}

.layout-aside {
  background: #2c3e50;
  overflow-y: auto;
  
  &::-webkit-scrollbar {
    width: 6px;
  }
  
  &::-webkit-scrollbar-thumb {
    background: #34495e;
    border-radius: 3px;
  }
}

.side-menu {
  border: none;
  
  :deep(.el-menu-item) {
    &:hover {
      background-color: #34495e !important;
    }
    
    &.is-active {
      background-color: #34495e !important;
    }
  }
  
  :deep(.el-sub-menu__title) {
    &:hover {
      background-color: #34495e !important;
    }
  }
  
  :deep(.el-sub-menu.is-active > .el-sub-menu__title) {
    color: #5CB85C !important;
  }
}

.layout-main {
  background: #f5f5f5;
  overflow-y: auto;
  padding: 0;
}
</style>

