<template>
  <div class="password-page">
    <van-nav-bar 
      title="修改密码" 
      left-arrow 
      @click-left="$router.back()"
      fixed
    />
    
    <div class="password-content">
      <!-- 密码类型切换 -->
      <van-tabs v-model:active="activeTab" color="#ee0a24">
        <van-tab title="登录密码" name="login">
          <van-form @submit="handleLoginSubmit">
            <van-cell-group>
              <van-field
                v-model="loginForm.oldPassword"
                type="password"
                label="旧密码"
                placeholder="请输入旧登录密码"
                required
              />
              
              <van-field
                v-model="loginForm.newPassword"
                type="password"
                label="新密码"
                placeholder="请输入新登录密码(6-20位)"
                required
              />
              
              <van-field
                v-model="loginForm.confirmPassword"
                type="password"
                label="确认密码"
                placeholder="请再次输入新密码"
                required
              />
            </van-cell-group>
            
            <div class="submit-box">
              <van-button type="danger" round block native-type="submit" :loading="submitting">
                确认修改
              </van-button>
            </div>
          </van-form>
          
          <div class="tips">
            <p>登录密码修改提示：</p>
            <p>1. 密码长度为6-20位字符</p>
            <p>2. 建议使用字母+数字组合</p>
            <p>3. 修改成功后请重新登录</p>
          </div>
        </van-tab>
        
        <van-tab title="支付密码" name="trade">
          <van-form @submit="handleTradeSubmit">
            <van-cell-group>
              <van-field
                v-model="tradeForm.oldPassword"
                type="password"
                label="旧密码"
                placeholder="请输入旧支付密码"
                required
              />
              
              <van-field
                v-model="tradeForm.newPassword"
                type="password"
                label="新密码"
                placeholder="请输入新支付密码(6位数字)"
                required
                maxlength="6"
              />
              
              <van-field
                v-model="tradeForm.confirmPassword"
                type="password"
                label="确认密码"
                placeholder="请再次输入新密码"
                required
                maxlength="6"
              />
            </van-cell-group>
            
            <div class="submit-box">
              <van-button type="danger" round block native-type="submit" :loading="submitting">
                确认修改
              </van-button>
            </div>
          </van-form>
          
          <div class="tips">
            <p>支付密码修改提示：</p>
            <p>1. 支付密码为6位纯数字</p>
            <p>2. 用于提现、绑定银行卡等操作</p>
            <p>3. 请妥善保管支付密码</p>
            <p>4. 如忘记支付密码，请联系客服</p>
          </div>
        </van-tab>
      </van-tabs>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '@/stores/user'
import { showToast, showSuccessToast, showDialog } from 'vant'
import { changePassword } from '@/api/user'

const router = useRouter()
const userStore = useUserStore()
const submitting = ref(false)
const activeTab = ref('login')

const loginForm = reactive({
  oldPassword: '',
  newPassword: '',
  confirmPassword: ''
})

const tradeForm = reactive({
  oldPassword: '',
  newPassword: '',
  confirmPassword: ''
})

// 提交修改登录密码
const handleLoginSubmit = async () => {
  if (!loginForm.oldPassword) {
    showToast('请输入旧密码')
    return
  }
  
  if (!loginForm.newPassword) {
    showToast('请输入新密码')
    return
  }
  
  if (loginForm.newPassword.length < 6 || loginForm.newPassword.length > 20) {
    showToast('密码长度为6-20位')
    return
  }
  
  if (loginForm.newPassword !== loginForm.confirmPassword) {
    showToast('两次密码输入不一致')
    return
  }
  
  try {
    submitting.value = true
    const res = await changePassword({
      type: 'login',
      oldPassword: loginForm.oldPassword,
      newPassword: loginForm.newPassword
    })
    
    if (res.code === 200) {
      showDialog({
        title: '修改成功',
        message: '登录密码修改成功，请重新登录',
        confirmButtonText: '确定',
        confirmButtonColor: '#ee0a24'
      }).then(() => {
        // 退出登录
        userStore.logout()
        router.push('/login')
      })
    } else {
      showToast(res.msg || '修改失败')
    }
  } catch (error) {
    showToast(error.message || '网络错误，请稍后重试')
  } finally {
    submitting.value = false
  }
}

// 提交修改支付密码
const handleTradeSubmit = async () => {
  if (!tradeForm.oldPassword) {
    showToast('请输入旧密码')
    return
  }
  
  if (!tradeForm.newPassword) {
    showToast('请输入新密码')
    return
  }
  
  // 验证支付密码格式（6位数字）
  if (!/^\d{6}$/.test(tradeForm.newPassword)) {
    showToast('支付密码必须为6位数字')
    return
  }
  
  if (tradeForm.newPassword !== tradeForm.confirmPassword) {
    showToast('两次密码输入不一致')
    return
  }
  
  try {
    submitting.value = true
    const res = await changePassword({
      type: 'trade',
      oldPassword: tradeForm.oldPassword,
      newPassword: tradeForm.newPassword
    })
    
    if (res.code === 200) {
      showSuccessToast('支付密码修改成功')
      // 清空表单
      tradeForm.oldPassword = ''
      tradeForm.newPassword = ''
      tradeForm.confirmPassword = ''
    } else {
      showToast(res.msg || '修改失败')
    }
  } catch (error) {
    showToast(error.message || '网络错误，请稍后重试')
  } finally {
    submitting.value = false
  }
}
</script>

<style scoped lang="scss">
.password-page {
  min-height: 100vh;
  background: #f5f5f5;
  padding-top: 46px;
}

.password-content {
  padding-top: 8px;
}

:deep(.van-tabs__nav) {
  background: white;
}

:deep(.van-tab) {
  font-size: 15px;
  font-weight: 500;
}

.submit-box {
  padding: 24px 12px;
}

.tips {
  background: white;
  border-radius: 6px;
  padding: 12px;
  margin: 12px;
  
  p {
    font-size: 12px;
    color: #666;
    line-height: 20px;
    margin: 0;
    margin-bottom: 4px;
    
    &:first-child {
      font-weight: 500;
      color: #323233;
      margin-bottom: 8px;
    }
    
    &:last-child {
      margin-bottom: 0;
    }
  }
}

:deep(.van-cell) {
  font-size: 14px;
}

:deep(.van-field__label) {
  width: 70px;
}
</style>

