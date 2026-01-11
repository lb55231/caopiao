<template>
  <div class="recharge-page">
    <van-nav-bar 
      title="在线充值" 
      left-arrow 
      @click-left="$router.back()"
      fixed
    />
    
    <div class="recharge-content">
      <!-- 充值方式选择 -->
      <div class="payment-methods">
        <div class="methods-title">选择充值方式</div>
        <div class="methods-grid">
          <div
            v-for="item in paymentList"
            :key="item.id"
            class="method-card"
            :class="{ active: selectedPayType === item.paytype }"
            @click="selectedPayType = item.paytype"
          >
            <div class="card-icon">
              <van-icon name="balance-pay" size="20" />
            </div>
            <div class="card-title">{{ item.paytypetitle }}</div>
            <div class="card-tag">
              <span v-if="item.isonline === 1" class="online-tag">在线</span>
              <span v-else class="offline-tag">线下</span>
            </div>
            <div class="card-check" v-if="selectedPayType === item.paytype">
              <van-icon name="success" size="14" />
            </div>
          </div>
        </div>
      </div>
      
      <!-- 线下转账表单 -->
      <van-form v-if="currentPaySet && currentPaySet.isonline !== 1" @submit="handleOfflineSubmit">
        <van-cell-group title="转账信息">
          <!-- 如果是二维码支付 -->
          <template v-if="bankConfig.isewm === '1' && bankConfig.ewmurl">
            <div class="qrcode-box">
              <div class="qrcode-title">{{ currentPaySet?.paytypetitle }}</div>
              <img :src="getImageUrl(bankConfig.ewmurl)" class="qrcode-image" @error="handleImageError" />
              <p class="qrcode-tip">请使用{{ currentPaySet?.paytypetitle }}扫描二维码支付</p>
            </div>
          </template>
          
          <!-- 如果是银行转账 -->
          <template v-else>
            <!-- 收款银行 -->
            <van-field
              label="收款银行"
              :model-value="currentPaySet?.paytypetitle"
              readonly
            />
            
            <!-- 收款户名 -->
            <van-field
              label="收款户名"
              :model-value="bankConfig.bankname"
              readonly
              right-icon="copy"
              @click-right-icon="copyText(bankConfig.bankname)"
            />
            
            <!-- 收款账号 -->
            <van-field
              label="收款账号"
              :model-value="bankConfig.bankcode"
              readonly
              right-icon="copy"
              @click-right-icon="copyText(bankConfig.bankcode)"
            />
            
            <!-- 开户支行 -->
            <van-field
              v-if="currentPaySet?.ftitle"
              label="开户支行"
              :model-value="currentPaySet?.ftitle"
              readonly
              right-icon="copy"
              @click-right-icon="copyText(currentPaySet?.ftitle)"
            />
          </template>
          
          <!-- 充值金额 -->
          <van-field
            v-model="form.amount"
            type="number"
            label="充值金额"
            :placeholder="`${currentPaySet?.minmoney}-${currentPaySet?.maxmoney}元`"
            required
          />
          
          <!-- 转账户名 -->
          <van-field
            v-model="form.userpayname"
            label="转账户名"
            placeholder="请输入付款人的银行卡姓名"
            required
          />
        </van-cell-group>
        
        <div class="submit-box">
          <van-button type="danger" round block native-type="submit" :loading="submitting">
            确定
          </van-button>
        </div>
        
        <!-- 温馨提示 -->
        <div class="tips">
          <p>1、请转账到以上收款银行账户。</p>
          <p>2、请正确填写转账银行卡的持卡人姓名和充值金额，以便及时核对。</p>
          <p>3、转账1笔提交1次，请勿重复提交订单。</p>
          <p>4、请务必转账后再提交订单,否则无法及时查到您的款项！</p>
        </div>
      </van-form>
      
      <!-- 在线支付表单 -->
      <van-form v-else-if="currentPaySet && currentPaySet.isonline === 1" @submit="handleOnlineSubmit">
        <van-cell-group title="充值信息">
          <!-- 充值金额 -->
          <van-field
            v-model="form.amount"
            type="number"
            label="充值金额"
            :placeholder="`${currentPaySet?.minmoney}-${currentPaySet?.maxmoney}元`"
            required
          />
        </van-cell-group>
        
        <div class="submit-box">
          <van-button type="danger" round block native-type="submit" :loading="submitting">
            立即支付
          </van-button>
        </div>
      </van-form>
      
      <!-- 备注说明 -->
      <div v-if="currentPaySet?.remark" class="remark-box">
        <div class="remark-title">备注说明</div>
        <div class="remark-content" v-html="currentPaySet.remark"></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { getPaySets, submitRecharge } from '@/api/user'
import { showToast, showSuccessToast, showDialog } from 'vant'
import { getImageUrl } from '@/utils/image'

const router = useRouter()
const submitting = ref(false)
const paymentList = ref([])
const selectedPayType = ref('')

const form = reactive({
  amount: '',
  userpayname: ''
})

// 当前选中的支付方式
const currentPaySet = computed(() => {
  return paymentList.value.find(item => item.paytype === selectedPayType.value)
})

// 银行配置（解析configs字段）
const bankConfig = computed(() => {
  return currentPaySet.value?.configs_array || {}
})

// 加载支付方式列表
const loadPaymentList = async () => {
  try {
    const res = await getPaySets()
    if (res.code === 200 && res.data.list.length > 0) {
      paymentList.value = res.data.list
      // 默认选择第一个
      if (paymentList.value.length > 0) {
        selectedPayType.value = paymentList.value[0].paytype
      }
    } else {
      showToast('暂无可用的充值方式')
    }
  } catch (error) {
    showToast('加载失败')
  }
}

// 监听支付方式变化，重置表单
watch(selectedPayType, () => {
  form.amount = ''
  form.userpayname = ''
})

// 复制文本
const copyText = (text) => {
  if (!text) return
  const input = document.createElement('input')
  document.body.appendChild(input)
  input.setAttribute('value', text)
  input.select()
  if (document.execCommand('copy')) {
    showSuccessToast('复制成功')
  }
  document.body.removeChild(input)
}

// 图片加载失败
const handleImageError = (e) => {
  e.target.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjMwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMzAwIiBoZWlnaHQ9IjMwMCIgZmlsbD0iI2Y1ZjVmNSIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTQiIGZpbGw9IiM5OTkiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj7kuoznpIHnoIHliqDovb3lpLHotKU8L3RleHQ+PC9zdmc+'
}

// 提交线下充值
const handleOfflineSubmit = async () => {
  const minMoney = parseFloat(currentPaySet.value?.minmoney || 0)
  const maxMoney = parseFloat(currentPaySet.value?.maxmoney || 999999)
  const amount = parseFloat(form.amount)
  
  if (!form.amount || amount < minMoney) {
    showToast(`充值金额最低为${minMoney}元`)
    return
  }
  
  if (amount > maxMoney) {
    showToast(`充值金额最高为${maxMoney}元`)
    return
  }
  
  if (!form.userpayname) {
    showToast('请输入您的银行卡姓名')
    return
  }
  
  try {
    submitting.value = true
    const res = await submitRecharge({
      amount: amount,
      paytype: currentPaySet.value.paytype,
      userpayname: form.userpayname
    })
    
    if (res.code === 200) {
      // 构建提示信息
      let messageHtml = '<div style="text-align: left; padding: 10px 0;">'
      messageHtml += '<p style="margin-bottom: 12px; color: #07c160; font-weight: 500;">✓ 充值申请已提交</p>'
      
      if (res.data.bankname && res.data.bankcode) {
        messageHtml += '<p style="margin: 8px 0; color: #666;"><span style="color: #323233; font-weight: 500;">收款户名：</span>' + res.data.bankname + '</p>'
        messageHtml += '<p style="margin: 8px 0; color: #666;"><span style="color: #323233; font-weight: 500;">收款账号：</span>' + res.data.bankcode + '</p>'
      }
      
      messageHtml += '<p style="margin: 8px 0; color: #666;"><span style="color: #323233; font-weight: 500;">充值金额：</span>' + amount + '元</p>'
      messageHtml += '<p style="margin: 8px 0; color: #666;"><span style="color: #323233; font-weight: 500;">订单号：</span>' + res.data.trano + '</p>'
      messageHtml += '<p style="margin-top: 12px; font-size: 12px; color: #969799;">请等待管理员审核</p>'
      messageHtml += '</div>'
      
      showDialog({
        title: '充值提示',
        message: messageHtml,
        confirmButtonText: '我知道了',
        confirmButtonColor: '#ee0a24',
        allowHtml: true
      }).then(() => {
        router.push('/user')
      })
    } else {
      showToast(res.msg || '提交失败')
    }
  } catch (error) {
    showToast(error.message || '网络错误，请稍后重试')
  } finally {
    submitting.value = false
  }
}

// 提交在线充值
const handleOnlineSubmit = async () => {
  const minMoney = parseFloat(currentPaySet.value?.minmoney || 0)
  const maxMoney = parseFloat(currentPaySet.value?.maxmoney || 999999)
  const amount = parseFloat(form.amount)
  
  if (!form.amount || amount < minMoney) {
    showToast(`充值金额最低为${minMoney}元`)
    return
  }
  
  if (amount > maxMoney) {
    showToast(`充值金额最高为${maxMoney}元`)
    return
  }
  
  try {
    submitting.value = true
    const res = await submitRecharge({
      amount: amount,
      paytype: currentPaySet.value.paytype,
      userpayname: '' // 在线支付不需要转账户名
    })
    
    if (res.code === 200) {
      // 在线支付跳转到支付页面或显示支付二维码
      if (res.data.pay_url) {
        window.location.href = res.data.pay_url
      } else {
        showSuccessToast('充值订单已创建')
        setTimeout(() => {
          router.push('/records/account')
        }, 1500)
      }
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
  loadPaymentList()
})
</script>

<style scoped lang="scss">
.recharge-page {
  min-height: 100vh;
  background: #f5f5f5;
  padding-top: 46px;
}

.recharge-content {
  padding: 8px;
}

.payment-methods {
  margin-bottom: 12px;
  
  .methods-title {
    font-size: 14px;
    font-weight: 500;
    color: #323233;
    padding: 12px 8px 8px;
  }
  
  .methods-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
    padding: 0 8px;
  }
  
  .method-card {
    position: relative;
    background: white;
    border-radius: 6px;
    padding: 8px 6px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
    border: 2px solid transparent;
    
    &.active {
      border-color: #ee0a24;
      background: linear-gradient(135deg, #fff5f5 0%, #ffe8e8 100%);
      
      .card-icon {
        color: #ee0a24;
      }
      
      .card-title {
        color: #ee0a24;
        font-weight: 500;
      }
    }
    
    .card-icon {
      color: #969799;
      margin-bottom: 4px;
      transition: all 0.3s;
    }
    
    .card-title {
      font-size: 12px;
      color: #323233;
      margin-bottom: 4px;
      line-height: 1.2;
      transition: all 0.3s;
    }
    
    .card-tag {
      .online-tag,
      .offline-tag {
        display: inline-block;
        padding: 1px 6px;
        border-radius: 8px;
        font-size: 9px;
        font-weight: 500;
      }
      
      .online-tag {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
      }
      
      .offline-tag {
        background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
        color: #d35400;
      }
    }
    
    .card-check {
      position: absolute;
      top: 3px;
      right: 3px;
      width: 18px;
      height: 18px;
      background: #ee0a24;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
    }
  }
}

.submit-box {
  padding: 16px 8px;
}

.qrcode-box {
  background: white;
  padding: 16px;
  text-align: center;
  
  .qrcode-title {
    font-size: 15px;
    font-weight: 500;
    color: #333;
    margin-bottom: 12px;
  }
  
  .qrcode-image {
    width: 200px;
    height: 200px;
    border: 1px solid #eee;
    border-radius: 6px;
    padding: 8px;
    background: white;
    display: block;
    margin: 0 auto;
  }
  
  .qrcode-tip {
    font-size: 12px;
    color: #666;
    margin-top: 10px;
    margin-bottom: 0;
  }
}

.tips {
  background: white;
  border-radius: 6px;
  padding: 12px;
  margin-top: 8px;
  
  p {
    font-size: 11px;
    color: #666;
    line-height: 18px;
    margin: 0;
    margin-bottom: 4px;
    
    &:last-child {
      margin-bottom: 0;
    }
  }
}

.remark-box {
  background: white;
  border-radius: 6px;
  padding: 12px;
  margin-top: 8px;
  
  .remark-title {
    font-size: 13px;
    font-weight: 500;
    color: #333;
    margin-bottom: 6px;
  }
  
  .remark-content {
    font-size: 11px;
    color: #666;
    line-height: 18px;
  }
}

:deep(.van-cell-group__title) {
  padding: 12px 12px 8px;
  font-size: 13px;
}

:deep(.van-cell) {
  padding: 10px 12px;
  font-size: 13px;
}

:deep(.van-field__label) {
  width: 70px;
  font-size: 13px;
}

:deep(.van-field__control) {
  font-size: 13px;
}
</style>
