<template>
  <div class="user-page">
    <van-nav-bar title="个人中心" fixed />

    <div class="user-content">
      <!-- 用户信息 -->
      <div class="user-header">
        <div class="avatar-box">
          <img :src="userInfo.avatar || 'https://api.dicebear.com/7.x/avataaars/svg?seed=' + userInfo.username" class="avatar" />
        </div>
        <div class="user-info">
          <div class="username">{{ userInfo.username }}</div>
          <div class="user-id">ID: {{ userInfo.id }}</div>
        </div>
      </div>

      <!-- 余额信息 -->
      <div class="balance-box">
        <div class="balance-item">
          <div class="balance-value">{{ userInfo.balance || 0 }}</div>
          <div class="balance-label">账户余额(元)</div>
        </div>
        <div class="balance-actions">
          <van-button type="danger" size="small" @click="$router.push('/account/recharge')">
            充值
          </van-button>
          <van-button plain type="danger" size="small" @click="$router.push('/account/withdraw')">
            提现
          </van-button>
        </div>
      </div>

      <!-- 功能菜单 -->
      <van-cell-group class="menu-group">
        <van-cell 
          title="投注记录" 
          icon="orders-o" 
          is-link 
          @click="$router.push('/records/bet')"
        />
        <van-cell 
          title="账变记录" 
          icon="balance-list-o" 
          is-link 
          @click="$router.push('/records/account')"
        />
        <van-cell 
          title="银行卡管理" 
          icon="credit-pay" 
          is-link 
          @click="$router.push('/account/bank')"
        />
      </van-cell-group>

      <van-cell-group class="menu-group">
        <van-cell 
          title="修改密码" 
          icon="lock" 
          is-link 
          @click="$router.push('/user/password')"
        />
        <van-cell 
          title="个人资料" 
          icon="contact" 
          is-link 
          @click="$router.push('/user/profile')"
        />
      </van-cell-group>

      <van-cell-group class="menu-group">
        <van-cell 
          title="联系客服" 
          icon="service-o" 
          is-link 
          @click="handleContactService"
        />
      </van-cell-group>

      <!-- 退出登录 -->
      <div class="logout-box">
        <van-button 
          block 
          round 
          type="danger" 
          @click="handleLogout"
        >
          退出登录
        </van-button>
      </div>
    </div>

    <!-- 底部导航 -->
    <Tabbar />
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '@/stores/user'
import { showDialog, showToast } from 'vant'
import request from '@/api/request'
import Tabbar from '@/components/Tabbar.vue'

export default {
  name: 'UserCenter',
  components: {
    Tabbar
  },
  setup() {
    const router = useRouter()
    const userStore = useUserStore()
    const siteConfig = ref({})

    const userInfo = computed(() => userStore.userInfo || {})

    // 加载网站配置
    const loadSiteConfig = async () => {
      try {
        const res = await request.get('/system/config')
        if (res.code === 200) {
          siteConfig.value = res.data
        }
      } catch (error) {
        console.error('加载配置失败:', error)
      }
    }

    // 联系客服
    const handleContactService = () => {
      if (siteConfig.value.servicecode) {
        // 如果有第三方客服链接代码，跳转
        window.location.href = siteConfig.value.servicecode
      } else if (siteConfig.value.serviceqq) {
        // 否则显示客服QQ
        showDialog({
          title: '联系客服',
          message: `客服QQ：${siteConfig.value.serviceqq}`,
          confirmButtonText: '复制QQ',
          confirmButtonColor: '#ee0a24'
        }).then(() => {
          // 复制QQ号
          const input = document.createElement('input')
          document.body.appendChild(input)
          input.setAttribute('value', siteConfig.value.serviceqq)
          input.select()
          if (document.execCommand('copy')) {
            showToast('QQ号已复制')
          }
          document.body.removeChild(input)
        })
      } else {
        showToast('暂无客服信息')
      }
    }

    // 退出登录
    const handleLogout = () => {
      showDialog({
        title: '提示',
        message: '确定要退出登录吗？',
      }).then(() => {
        userStore.logout()
        showToast('已退出登录')
        router.push('/login')
      }).catch(() => {
        // 取消
      })
    }

    // 加载用户信息
    const loadUserInfo = async () => {
      try {
        await userStore.getUserInfo()
      } catch (error) {
        console.error('加载用户信息失败:', error)
      }
    }

    onMounted(() => {
      if (userStore.token) {
        loadUserInfo()
      }
      loadSiteConfig()
    })

    return {
      userInfo,
      handleContactService,
      handleLogout
    }
  }
}
</script>

<style lang="scss" scoped>
.user-page {
  min-height: 100vh;
  background: #f5f5f5;
  padding-top: 46px;
  padding-bottom: 50px;
}

.user-content {
  padding-bottom: 20px;
}

.user-header {
  background: #ee0a24;
  padding: 30px 20px;
  display: flex;
  align-items: center;
  
  .avatar-box {
    margin-right: 15px;
    
    .avatar {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      border: 3px solid rgba(255, 255, 255, 0.3);
    }
  }
  
  .user-info {
    flex: 1;
    color: white;
    
    .username {
      font-size: 20px;
      font-weight: bold;
      margin-bottom: 5px;
    }
    
    .user-id {
      font-size: 14px;
      opacity: 0.8;
    }
  }
}

.balance-box {
  background: white;
  margin: -20px 15px 15px;
  border-radius: 10px;
  padding: 20px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  position: relative;
  
  .balance-item {
    text-align: center;
    margin-bottom: 15px;
    
    .balance-value {
      font-size: 32px;
      font-weight: bold;
      color: #ff6b6b;
      margin-bottom: 5px;
    }
    
    .balance-label {
      font-size: 14px;
      color: #999;
    }
  }
  
  .balance-actions {
    display: flex;
    gap: 10px;
    
    .van-button {
      flex: 1;
    }
  }
}

.menu-group {
  margin-bottom: 15px;
}

.logout-box {
  padding: 0 15px;
  margin-top: 30px;
}
</style>

