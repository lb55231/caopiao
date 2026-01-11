<template>
  <div class="add-bank-page">
    <van-nav-bar 
      title="绑定提现账号" 
      left-arrow 
      @click-left="$router.back()"
      fixed
    />
    
    <div class="add-bank-content">
      <!-- 类型切换 -->
      <div class="type-tabs">
        <button 
          :class="['type-btn', { active: formType === 'alipay' }]"
          @click="formType = 'alipay'"
        >
          支付宝
        </button>
        <button 
          :class="['type-btn', { active: formType === 'bank' }]"
          @click="formType = 'bank'"
        >
          银行卡
        </button>
      </div>
      
      <!-- 支付宝表单 -->
      <van-form v-if="formType === 'alipay'" @submit="handleSubmit">
        <van-cell-group>
          <van-field
            v-model="alipayForm.accountname"
            label="本人姓名"
            placeholder="请输入您的真实姓名"
            :disabled="!!userBankName"
            required
          />
          <van-field
            v-model="alipayForm.account"
            label="支付宝账号"
            placeholder="请输入支付宝账号"
            required
          />
          <van-field
            v-model="alipayForm.password"
            type="password"
            label="资金密码"
            placeholder="请输入资金密码"
            required
          />
        </van-cell-group>
        
        <div class="submit-box">
          <van-button type="danger" round block native-type="submit" :loading="submitting">
            提交
          </van-button>
        </div>
      </van-form>
      
      <!-- 银行卡表单 -->
      <van-form v-else @submit="handleSubmit">
        <van-cell-group>
          <van-field
            v-model="bankForm.accountname"
            label="持卡人姓名"
            placeholder="请输入您的真实姓名"
            :disabled="!!userBankName"
            required
          />
          <van-field
            v-model="bankForm.bankname"
            label="银行"
            placeholder="请输入银行"
            required
          />
          <van-field
            v-model="bankForm.banknumber"
            label="银行卡号"
            type="digit"
            placeholder="请输入银行卡的卡号"
            required
          />
          <van-field
            v-model="bankForm.confirmNumber"
            label="确认卡号"
            type="digit"
            placeholder="请再次输入银行卡号"
            required
          />
          <van-field
            v-model="bankForm.password"
            type="password"
            label="资金密码"
            placeholder="请输入您的资金密码"
            required
          />
        </van-cell-group>
        
        <div class="submit-box">
          <van-button type="danger" round block native-type="submit" :loading="submitting">
            确定
          </van-button>
        </div>
      </van-form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '@/stores/user'
import { addBank } from '@/api/user'
import { showToast, showSuccessToast } from 'vant'

const router = useRouter()
const userStore = useUserStore()
const submitting = ref(false)
const formType = ref('alipay')
const userBankName = ref('')

const alipayForm = reactive({
  accountname: '',
  account: '',
  password: ''
})

const bankForm = reactive({
  accountname: '',
  bankname: '',
  banknumber: '',
  confirmNumber: '',
  password: ''
})

// 提交表单
const handleSubmit = async () => {
  if (formType.value === 'alipay') {
    if (!alipayForm.accountname || !alipayForm.account || !alipayForm.password) {
      showToast('请填写完整信息')
      return
    }
    
    try {
      submitting.value = true
      const res = await addBank({
        bankname: '支付宝',
        bankcode: '支付宝',
        accountname: alipayForm.accountname,
        banknumber: alipayForm.account,
        password: alipayForm.password
      })
      
      if (res.code === 200) {
        showSuccessToast('绑定成功')
        setTimeout(() => {
          router.push('/account/bank')
        }, 1500)
      } else {
        showToast(res.msg || '绑定失败')
      }
    } catch (error) {
      showToast(error.message || '网络错误，请稍后重试')
    } finally {
      submitting.value = false
    }
  } else {
    if (!bankForm.accountname || !bankForm.bankname || !bankForm.banknumber || !bankForm.confirmNumber || !bankForm.password) {
      showToast('请填写完整信息')
      return
    }
    
    if (bankForm.accountname.length < 2) {
      showToast('姓名至少两位')
      return
    }
    
    if (bankForm.banknumber !== bankForm.confirmNumber) {
      showToast('两次卡号输入的不一致，请重新输入')
      return
    }
    
    try {
      submitting.value = true
      const res = await addBank({
        bankname: bankForm.bankname,
        bankcode: bankForm.bankname, // bankcode 与 bankname 相同
        accountname: bankForm.accountname,
        banknumber: bankForm.banknumber,
        password: bankForm.password
      })
      
      if (res.code === 200) {
        showSuccessToast('银行绑定成功')
        setTimeout(() => {
          router.push('/account/bank')
        }, 1500)
      } else {
        showToast(res.msg || '绑定失败')
      }
    } catch (error) {
      showToast(error.message || '网络错误，请稍后重试')
    } finally {
      submitting.value = false
    }
  }
}

onMounted(() => {
  // 从用户信息获取真实姓名
  userBankName.value = userStore.userInfo?.userbankname || ''
  if (userBankName.value) {
    alipayForm.accountname = userBankName.value
    bankForm.accountname = userBankName.value
  }
})
</script>

<style scoped lang="scss">
.add-bank-page {
  min-height: 100vh;
  background: #f5f5f5;
  padding-top: 46px;
}

.add-bank-content {
  padding: 12px;
}

.type-tabs {
  display: flex;
  justify-content: center;
  margin: 20px 0;
  
  .type-btn {
    padding: 8px 30px;
    outline: none;
    background: white;
    border: 1px solid #ee0a24;
    font-size: 14px;
    color: #333;
    
    &:first-child {
      border-right: none;
      border-top-left-radius: 4px;
      border-bottom-left-radius: 4px;
    }
    
    &:last-child {
      border-top-right-radius: 4px;
      border-bottom-right-radius: 4px;
    }
    
    &.active {
      background: #ee0a24;
      color: white;
    }
  }
}

.submit-box {
  padding: 24px 0;
}
</style>

