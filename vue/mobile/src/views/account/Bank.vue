<template>
  <div class="bank-page">
    <van-nav-bar 
      title="银行卡管理" 
      left-arrow 
      @click-left="$router.back()"
      fixed
    />
    
    <div class="bank-content">
      <!-- 银行卡列表 -->
      <div v-if="bankList.length > 0" class="bank-list">
        <div
          v-for="bank in bankList"
          :key="bank.id"
          class="bank-card"
        >
          <div class="card-header">
            <div class="bank-name">{{ bank.bankname }}</div>
            <van-tag v-if="bank.isdefault == 1" type="success">默认</van-tag>
          </div>
          <div class="card-body">
            <div class="card-number">{{ formatCardNumber(bank.banknumber) }}</div>
            <div class="card-owner">{{ bank.accountname }}</div>
          </div>
          <div class="card-footer">
            <van-button 
              v-if="bank.isdefault != 1" 
              size="small"
              plain
              type="danger"
              @click="handleSetDefault(bank)"
            >
              设为默认
            </van-button>
            <van-button 
              size="small" 
              type="danger" 
              @click="handleDelete(bank)"
            >
              删除
            </van-button>
          </div>
        </div>
      </div>
      
      <!-- 空状态 -->
      <van-empty v-else description="暂无银行卡" />
      
      <!-- 添加按钮 -->
      <div class="add-box">
        <van-button 
          type="danger" 
          block 
          round 
          @click="$router.push('/account/add-bank')"
          :disabled="bankList.length >= 3"
        >
          {{ bankList.length >= 3 ? '最多绑定3张银行卡' : '添加银行卡' }}
        </van-button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { getBankList, deleteBank, setDefaultBank } from '@/api/user'
import { showToast, showConfirmDialog } from 'vant'

const router = useRouter()

const bankList = ref([])

// 格式化卡号
const formatCardNumber = (number) => {
  if (!number) return ''
  const len = number.length
  if (len <= 8) return number
  return number.substring(0, 4) + ' **** **** ' + number.substring(len - 4)
}

// 加载银行卡列表
const loadList = async () => {
  try {
    const res = await getBankList()
    if (res.code === 200) {
      bankList.value = res.data.list
    }
  } catch (error) {
    showToast('加载失败：' + error.message)
  }
}

// 设为默认
const handleSetDefault = async (bank) => {
  try {
    const res = await setDefaultBank(bank.id)
    if (res.code === 200) {
      showToast('设置成功')
      loadList()
    } else {
      showToast(res.msg || '设置失败')
    }
  } catch (error) {
    showToast('设置失败：' + error.message)
  }
}

// 删除银行卡
const handleDelete = async (bank) => {
  showConfirmDialog({
    title: '提示',
    message: '确定要删除这张银行卡吗？'
  }).then(async () => {
    try {
      const res = await deleteBank(bank.id)
      if (res.code === 200) {
        showToast('删除成功')
        loadList()
      } else {
        showToast(res.msg || '删除失败')
      }
    } catch (error) {
      showToast('删除失败：' + error.message)
    }
  }).catch(() => {})
}

onMounted(() => {
  loadList()
})
</script>

<style scoped lang="scss">
.bank-page {
  min-height: 100vh;
  background: #f5f5f5;
  padding-top: 46px;
}

.bank-content {
  padding: 16px;
}

.bank-list {
  margin-bottom: 16px;
}

.bank-card {
  background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
  border-radius: 12px;
  padding: 20px;
  color: white;
  margin-bottom: 16px;
  
  .card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    
    .bank-name {
      font-size: 16px;
      font-weight: bold;
    }
  }
  
  .card-body {
    margin-bottom: 16px;
    
    .card-number {
      font-size: 18px;
      font-family: 'Courier New', monospace;
      margin-bottom: 8px;
    }
    
    .card-owner {
      font-size: 14px;
      opacity: 0.9;
    }
  }
  
  .card-footer {
    display: flex;
    gap: 8px;
    justify-content: flex-end;
  }
}

.add-box {
  padding: 16px 0;
}
</style>

