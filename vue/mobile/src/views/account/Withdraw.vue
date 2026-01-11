<template>
  <div class="withdraw-page">
    <van-nav-bar 
      title="提款" 
      left-arrow 
      @click-left="$router.back()"
      fixed
    />
    
    <div class="withdraw-content">
      <van-form @submit="handleSubmit">
        <van-cell-group>
          <!-- 银行选择 -->
          <van-field
            v-if="bankList.length > 0"
            label="银行"
            readonly
            is-link
            :model-value="selectedBank ? `${selectedBank.bankname}(${selectedBank.banknumber})` : '请选择银行'"
            @click="showPicker = true"
          />
          <van-field v-else label="银行" readonly>
            <template #input>
              <span>请先<router-link to="/account/add-bank" style="color: #ee0a24; padding: 0 5px">绑定银行账户</router-link></span>
            </template>
          </van-field>
          
          <!-- 提款金额 -->
          <van-field
            v-model="form.amount"
            type="number"
            label="提款金额"
            placeholder="提款金额"
          />
          
          <!-- 账户可用金额 -->
          <van-field
            label="账户可用金额"
            :model-value="userStore.userInfo?.balance || 0"
            readonly
            disabled
          />
          
          <!-- 洗码余额 -->
          <van-field
            label="洗码余额"
            :model-value="userStore.userInfo?.xima || 0"
            readonly
            disabled
          >
            <template #extra>
              <span v-if="parseFloat(userStore.userInfo?.xima || 0) > 0" style="color: #ee0a24; font-size: 12px;">
                需为0才可提款
              </span>
              <span v-else style="color: #07c160; font-size: 12px;">✓</span>
            </template>
          </van-field>
          
          <!-- 资金密码 -->
          <van-field
            v-if="hasTradePassword"
            v-model="form.password"
            type="password"
            label="资金密码"
            placeholder="请填写资金密码"
          />
          <van-field v-else label="资金密码" readonly>
            <template #input>
              <span>请先设置<router-link to="/user/safepass" style="color: #ee0a24; padding: 0 5px">资金密码</router-link></span>
            </template>
          </van-field>
        </van-cell-group>
        
        <div class="submit-box">
          <van-button 
            v-if="bankList.length > 0 && hasTradePassword"
            type="danger" 
            round 
            block 
            native-type="submit"
            :loading="submitting"
          >
            确认提款
          </van-button>
        </div>
      </van-form>
    </div>
    
    <!-- 银行卡选择弹窗 -->
    <van-popup v-model:show="showPicker" position="bottom">
      <van-picker
        :columns="bankColumns"
        @confirm="onBankConfirm"
        @cancel="showPicker = false"
      />
    </van-popup>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '@/stores/user'
import { getBankList, submitWithdraw } from '@/api/user'
import { showToast, showSuccessToast } from 'vant'

const router = useRouter()
const userStore = useUserStore()
const submitting = ref(false)
const showPicker = ref(false)
const bankList = ref([])
const selectedBank = ref(null)
const hasTradePassword = ref(true) // TODO: 从用户信息获取

const form = reactive({
  amount: '100',
  password: ''
})

// 银行卡选项
const bankColumns = computed(() => {
  return bankList.value.map(bank => ({
    text: `${bank.bankname}(${bank.banknumber})`,
    value: bank.id,
    bank: bank
  }))
})

// 加载银行卡列表
const loadBankList = async () => {
  try {
    const res = await getBankList()
    if (res.code === 200) {
      bankList.value = res.data.list
      if (bankList.value.length > 0) {
        selectedBank.value = bankList.value.find(b => b.isdefault == 1) || bankList.value[0]
      }
    }
  } catch (error) {
    showToast('加载失败')
  }
}

// 选择银行卡
const onBankConfirm = ({ selectedOptions }) => {
  selectedBank.value = selectedOptions[0].bank
  showPicker.value = false
}

// 提交提现
const handleSubmit = async () => {
  const amount = parseFloat(form.amount)
  const minWithdraw = 100
  
  // 检查洗码余额
  const xima = parseFloat(userStore.userInfo?.xima || 0)
  if (xima > 0) {
    showToast('打码不足，洗码余额为0时可以提款')
    return
  }
  
  if (amount < minWithdraw) {
    showToast(`最低提款金额为${minWithdraw}元`)
    return
  }
  
  if (!selectedBank.value) {
    showToast('请选择银行卡')
    return
  }
  
  if (!form.password) {
    showToast('请输入资金密码')
    return
  }
  
  try {
    submitting.value = true
    const res = await submitWithdraw({
      amount: amount,
      bank_id: selectedBank.value.id,
      password: form.password
    })
    
    if (res.code === 200) {
      showSuccessToast('提款申请已提交')
      setTimeout(() => {
        router.back()
      }, 1500)
    } else {
      showToast(res.msg || '提交失败')
    }
  } catch (error) {
    showToast(error.message || '网络错误，请稍后重试')
  } finally {
    submitting.value = false
  }
}

onMounted(() => {
  loadBankList()
})
</script>

<style scoped lang="scss">
.withdraw-page {
  min-height: 100vh;
  background: #f5f5f5;
  padding-top: 46px;
}

.withdraw-content {
  padding: 12px;
}

.submit-box {
  padding: 24px 12px;
}
</style>
