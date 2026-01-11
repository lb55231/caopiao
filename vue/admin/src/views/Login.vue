<template>
  <div class="loginWraper">
    <div id="loginform" class="loginBox">
      <!-- 网站Logo和名称 -->
      <div class="login-header">
        <img v-if="siteConfig.weblogo" :src="getImageUrl(siteConfig.weblogo)" alt="Logo" class="site-logo" />
        <h1 class="site-title">{{ siteConfig.webtitle || '管理后台' }}</h1>
      </div>
      
      <form class="form form-horizontal" @submit.prevent="handleLogin">
        <div class="row cl">
          <label class="form-label col-xs-3">
            <i class="Hui-iconfont">&#xe60d;</i>
          </label>
          <div class="formControls col-xs-8">
            <input 
              id="name" 
              v-model="loginForm.username"
              type="text" 
              placeholder="账户" 
              class="input-text size-L"
              autocomplete="off"
            />
          </div>
        </div>
        
        <div class="row cl">
          <label class="form-label col-xs-3">
            <i class="Hui-iconfont">&#xe60e;</i>
          </label>
          <div class="formControls col-xs-8">
            <input 
              id="pass" 
              v-model="loginForm.password"
              type="password" 
              placeholder="密码" 
              class="input-text size-L"
              autocomplete="off"
            />
          </div>
        </div>
        
        <div class="row cl">
          <label class="form-label col-xs-3">
            <i class="Hui-iconfont">&#xe60e;</i>
          </label>
          <div class="formControls col-xs-8">
            <input 
              id="rzm" 
              v-model="loginForm.safecode"
              type="password" 
              placeholder="安全码" 
              class="input-text size-L"
              autocomplete="off"
              @keyup.enter="handleLogin"
            />
          </div>
        </div>
        
        <div class="row cl">
          <div class="formControls col-xs-8 col-xs-offset-3">
            <input 
              type="submit" 
              class="btn btn-success radius size-L ylogin" 
              :value="loading ? '登录中...' : '登录'"
              :disabled="loading"
            />
            <input 
              type="button" 
              class="btn btn-default radius size-L yres" 
              value="取消"
              @click="handleReset"
            />
          </div>
        </div>
      </form>
    </div>
  </div>
  <div class="footer">Copyright MKdinzhi 2025</div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import { useAdminStore } from '@/stores/admin'
import { getImageUrl } from '@/utils/image'
import request from '@/utils/request'
import '@/assets/fonts/hui-iconfont.css'
import '@/assets/styles/login.css'

const router = useRouter()
const adminStore = useAdminStore()
const loading = ref(false)

const loginForm = reactive({
  username: '',
  password: '',
  safecode: ''
})

// 网站配置
const siteConfig = reactive({
  webtitle: '管理后台',
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

const handleLogin = async () => {
  // 表单验证
  if (!loginForm.username || loginForm.username.length < 3) {
    ElMessage.error('用户名格式错误！')
    return
  }
  
  if (!loginForm.password || loginForm.password.length < 6) {
    ElMessage.error('密码格式错误！')
    return
  }
  
  if (!loginForm.safecode) {
    ElMessage.error('请输入安全码！')
    return
  }
  
  loading.value = true
  
  try {
    const result = await adminStore.login(loginForm)
    
    if (result.success) {
      ElMessage.success('登录成功')
      router.push('/')
    } else {
      ElMessage.error(result.message || '登录失败')
    }
  } catch (error) {
    ElMessage.error(error.message || '登录失败')
  } finally {
    loading.value = false
  }
}

const handleReset = () => {
  loginForm.username = ''
  loginForm.password = ''
  loginForm.safecode = ''
}
</script>

<style scoped>
/* 所有样式已移至 @/assets/styles/login.css，避免重复和冲突 */

</style>

