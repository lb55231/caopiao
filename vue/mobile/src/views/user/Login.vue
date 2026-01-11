<template>
  <div class="login-page">
    <div class="logo-box">
      <img v-if="siteConfig.weblogo" :src="getImageUrl(siteConfig.weblogo)" alt="logo" />
      <!-- <img v-else src="@/assets/images/logo.png" alt="logo" /> -->
    </div>
    
    <div class="welcome-text">
      <span class="welcome-title">欢迎来到 {{ siteConfig.webtitle || '云集' }}</span>
    </div>

    <div class="login-form">
      <van-form @submit="onSubmit">
        <div class="form-item">
          <span class="form-label">账　号</span>
          <van-field
            v-model="form.username"
            placeholder="请输入账号"
            :border="false"
          />
        </div>
        
        <div class="form-item">
          <span class="form-label">密　码</span>
          <van-field
            v-model="form.password"
            type="password"
            placeholder="请输入密码"
            :border="false"
          />
        </div>

        <van-button
          round
          block
          type="danger"
          native-type="submit"
          class="login-btn"
        >
          立即登录
        </van-button>
      </van-form>

      <div class="link-box">
        <router-link to="/forget" class="link">忘记密码?</router-link>
        <router-link to="/register" class="link">立即注册</router-link>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { showToast } from 'vant'
import { useUserStore } from '@/stores/user'
import { getImageUrl } from '@/utils/image'
import request from '@/api/request'

export default {
  name: 'Login',
  setup() {
    const router = useRouter()
    const userStore = useUserStore()
    
    const form = ref({
      username: '',
      password: ''
    })
    
    // 网站配置
    const siteConfig = reactive({
      webtitle: '云集',
      weblogo: ''
    })
    
    // 加载网站配置
    const loadSiteConfig = async () => {
      try {
        const res = await request.get('/system/config')
        if (res.code === 200) {
          Object.assign(siteConfig, res.data)
          // 更新页面标题
          document.title = siteConfig.webtitle
        }
      } catch (error) {
        console.error('加载网站配置失败:', error)
      }
    }

    const onSubmit = async () => {
      if (!form.value.username) {
        showToast('请输入账号')
        return
      }
      if (!form.value.password) {
        showToast('请输入密码')
        return
      }

      const result = await userStore.login(form.value)
      
      if (result.success) {
        showToast('登录成功')
        setTimeout(() => {
          router.push('/')
        }, 1000)
      } else {
        showToast(result.message || '登录失败')
      }
    }
    
    // 页面加载时获取配置
    onMounted(() => {
      loadSiteConfig()
    })

    return {
      form,
      siteConfig,
      onSubmit,
      getImageUrl
    }
  }
}
</script>

<style lang="scss" scoped>
.login-page {
  min-height: 100vh;
  background: white;
  padding-top: 130px;
}

.logo-box {
  display: flex;
  justify-content: center;
  margin-bottom: 50px;
  
  img {
    width: 60px;
    height: 60px;
  }
}

.welcome-text {
  text-align: center;
  margin-bottom: 50px;
}

.welcome-title {
  color: red;
  font-size: 20px;
  font-weight: 600;
  background-image: linear-gradient(to right, #FFEB3B, #ff7d02 10%, #ff7d02 20%, #c3f985 30%, #CCCCFF 40%, #ff7902 50%, #f38bf0 60%, #ff7902 70%, #c3f985 80%, #ff7d02 90%, #FFEB3B 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-size: 200% 100%;
  animation: shine 4s linear infinite;
}

@keyframes shine {
  0% {
    background-position: 0 0;
  }
  100% {
    background-position: -100% 0;
  }
}

.login-form {
  padding: 0 5%;
}

.form-item {
  width: 100%;
  height: 42px;
  background: #fff;
  padding: 0 10px;
  border-radius: 30px;
  border: 1px solid #ddd;
  margin-bottom: 30px;
  display: flex;
  align-items: center;
  
  .form-label {
    min-width: 60px;
    font-size: 14px;
  }
  
  :deep(.van-field) {
    padding: 0;
    margin-left: 10px;
  }
}

.login-btn {
  margin-top: 10px;
  height: 44px;
  font-size: 16px;
}

.link-box {
  text-align: center;
  font-size: 14px;
  margin-top: 20px;
  
  .link {
    color: #666;
    margin: 0 15px;
    text-decoration: none;
    
    &:active {
      color: #999;
    }
  }
}
</style>

