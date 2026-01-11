<template>
  <div class="register-page">
    <!-- 顶部导航栏 -->
    <van-nav-bar
      title="用户注册"
      left-arrow
      @click-left="$router.back()"
      fixed
      placeholder
    />

    <!-- 注册表单 -->
    <div class="bank_recharge">
      <van-form @submit="onSubmit">
        <ul class="bank_form_list">
          <!-- 邀请码 -->
          <li class="am-cf">
            <span class="bank_form_left">邀请码</span>
            <div class="bank_right_input">
              <input 
                type="text" 
                v-model="form.reccode" 
                class="input_txt" 
                placeholder="请输入邀请码"
                maxlength="16"
              />
            </div>
          </li>

          <!-- 账号 -->
          <li class="am-cf">
            <span class="bank_form_left">账号</span>
            <div class="bank_right_input">
              <input 
                type="text" 
                v-model="form.username" 
                class="input_txt" 
                placeholder="请输入账号"
              />
            </div>
          </li>

          <!-- 设置密码 -->
          <li class="am-cf">
            <span class="bank_form_left">设置密码</span>
            <div class="bank_right_input">
              <input 
                type="password" 
                v-model="form.password" 
                class="input_txt" 
                placeholder="请输入密码"
              />
            </div>
          </li>

          <!-- 确认密码 -->
          <li class="am-cf">
            <span class="bank_form_left">确认密码</span>
            <div class="bank_right_input">
              <input 
                type="password" 
                v-model="form.cpassword" 
                class="input_txt" 
                placeholder="请再次输入密码"
              />
            </div>
          </li>

          <!-- 提款密码 -->
          <li class="am-cf">
            <span class="bank_form_left">提款密码</span>
            <div class="bank_right_input">
              <input 
                type="password" 
                v-model="form.tradepassword" 
                class="input_txt" 
                placeholder="请输入提款密码"
              />
            </div>
          </li>
        </ul>

        <p class="bank_pass">
          <router-link to="/login">已有账号? 立即登录</router-link>
        </p>

        <van-button 
          class="am-btn am-btn-danger am-radius am-btn-block" 
          native-type="submit"
          :loading="loading"
        >
          立即注册
        </van-button>
      </van-form>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { showToast } from 'vant'
import { register } from '@/api/user'
import { useUserStore } from '@/stores/user'

export default {
  name: 'Register',
  setup() {
    const router = useRouter()
    const userStore = useUserStore()
    const loading = ref(false)
    
    const form = ref({
      reccode: '',
      username: '',
      password: '',
      cpassword: '',
      tradepassword: ''
    })

    const onSubmit = async () => {
      // 验证
      if (!form.value.username) {
        showToast('请输入账号')
        return
      }

      if (!form.value.password) {
        showToast('请输入密码')
        return
      }

      if (!form.value.cpassword) {
        showToast('请再次输入密码')
        return
      }

      if (form.value.password !== form.value.cpassword) {
        showToast('两次密码输入不一致')
        return
      }

      if (!form.value.tradepassword) {
        showToast('请输入提款密码')
        return
      }

      loading.value = true
      try {
        const res = await register(form.value)
        if (res.data.sign === true || res.code === 200) {
          showToast('注册成功')
          
          // 保存token和用户信息（自动登录）
          if (res.data.token) {
            userStore.setToken(res.data.token)
            userStore.setUserInfo({
              id: res.data.id,
              username: res.data.username
            })
          }
          
          // 延迟跳转
          setTimeout(() => {
            router.push('/')
          }, 1000)
        }
      } catch (error) {
        console.error('注册失败:', error)
      } finally {
        loading.value = false
      }
    }

    return {
      form,
      loading,
      onSubmit
    }
  }
}
</script>

<style scoped lang="scss">
.register-page {
  min-height: 100vh;
  background-color: white;
}

.bank_recharge {
  padding: 20px;

  .bank_form_list {
    list-style: none;
    padding: 0;
    margin: 0;

    li {
      width: 90%;
      height: 42px;
      margin: 0 auto 30px;
      background: #fff;
      padding: 0 0.1rem;
      border-radius: 2030px;
      overflow: hidden;
      font-size: 14px;
      border: 1px solid #ddd;
      display: flex;
      align-items: center;

      .bank_form_left {
        flex-shrink: 0;
        width: 80px;
        padding-left: 10px;
        color: #333;
      }

      .bank_right_input {
        flex: 1;
        
        .input_txt {
          width: 100%;
          border: none;
          outline: none;
          background: transparent;
          font-size: 14px;
          color: #333;

          &::placeholder {
            color: #999;
          }
        }
      }
    }
  }

  .bank_pass {
    text-align: center;
    margin: 10px 0 20px;

    a {
      color: #666;
      font-size: 14px;
      text-decoration: none;

      &:hover {
        color: #333;
      }
    }
  }

  .am-btn {
    width: 90%;
    margin: 0.1rem auto 0;
    display: block;
    height: 44px;
    line-height: 44px;
    padding: 0;
    border: none;
    font-size: 16px;
    cursor: pointer;

    &.am-btn-danger {
      background-color: #d9534f;
      color: #fff;
    }

    &.am-radius {
      border-radius: 22px;
    }

    &.am-btn-block {
      display: block;
      width: 90%;
    }
  }
}
</style>
