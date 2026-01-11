<template>
  <div class="profile-page">
    <van-nav-bar 
      title="个人资料" 
      left-arrow 
      @click-left="$router.back()"
      fixed
    />
    
    <div class="profile-content">
      <van-form @submit="handleSubmit">
        <van-cell-group>
          <van-field
            label="用户名"
            :model-value="userInfo.username"
            readonly
            disabled
          />
          
          <van-field
            v-model="form.realname"
            label="真实姓名"
            placeholder="请输入真实姓名"
          />
          
          <van-field
            v-model="form.phone"
            label="手机号码"
            placeholder="请输入手机号码"
            type="tel"
          />
          
          <van-field
            v-model="form.email"
            label="电子邮箱"
            placeholder="请输入电子邮箱"
            type="email"
          />
          
          <van-field
            v-model="form.qq"
            label="QQ号码"
            placeholder="请输入QQ号码"
          />
          
          <!-- <van-field
            v-model="form.wechat"
            label="微信号"
            placeholder="请输入微信号"
          /> -->
        </van-cell-group>
        
        <div class="submit-box">
          <van-button type="danger" round block native-type="submit" :loading="submitting">
            保存修改
          </van-button>
        </div>
      </van-form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '@/stores/user'
import { showToast, showSuccessToast } from 'vant'
import { getUserProfile, updateUserProfile } from '@/api/user'

const router = useRouter()
const userStore = useUserStore()
const submitting = ref(false)

const userInfo = computed(() => userStore.userInfo || {})

const form = reactive({
  realname: '',
  phone: '',
  email: '',
  qq: '',
  wechat: ''
})

// 加载用户资料
const loadProfile = async () => {
  try {
    const res = await getUserProfile()
    if (res.code === 200) {
      Object.assign(form, res.data)
    }
  } catch (error) {
    console.error('加载资料失败:', error)
  }
}

// 提交修改
const handleSubmit = async () => {
  try {
    submitting.value = true
    const res = await updateUserProfile(form)
    
    if (res.code === 200) {
      showSuccessToast('保存成功')
      // 刷新用户信息
      await userStore.getUserInfo()
      setTimeout(() => {
        router.back()
      }, 1500)
    } else {
      showToast(res.msg || '保存失败')
    }
  } catch (error) {
    showToast(error.message || '网络错误，请稍后重试')
  } finally {
    submitting.value = false
  }
}

onMounted(() => {
  loadProfile()
})
</script>

<style scoped lang="scss">
.profile-page {
  min-height: 100vh;
  background: #f5f5f5;
  padding-top: 46px;
}

.profile-content {
  padding: 12px;
}

.submit-box {
  padding: 24px 12px;
}

:deep(.van-cell) {
  font-size: 14px;
}
</style>

