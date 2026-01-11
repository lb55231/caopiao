<template>
  <div class="record-page">
    <van-nav-bar 
      title="账变记录" 
      left-arrow 
      @click-left="$router.back()"
      fixed
    />

    <div class="record-content">
      <van-tabs v-model:active="activeTab" @change="onTabChange">
        <van-tab title="全部" name="all"></van-tab>
        <van-tab title="充值" name="recharge"></van-tab>
        <van-tab title="提现" name="withdraw"></van-tab>
        <van-tab title="投注" name="bet"></van-tab>
        <van-tab title="中奖" name="win"></van-tab>
      </van-tabs>

      <van-pull-refresh v-model="refreshing" @refresh="onRefresh">
        <van-list
          v-model:loading="loading"
          :finished="finished"
          finished-text="没有更多了"
          @load="onLoad"
        >
          <div class="record-list">
            <div 
              v-for="item in list" 
              :key="item.id"
              class="record-item"
            >
              <div class="record-icon" :class="`type-${item.type}`">
                <van-icon :name="getTypeIcon(item.type)" />
              </div>
              
              <div class="record-info">
                <div class="record-title">{{ getTypeText(item.type) }}</div>
                <div class="record-time">{{ formatTime(item.create_time) }}</div>
                <div class="record-remark" v-if="item.remark">{{ item.remark }}</div>
              </div>
              
              <div class="record-amount" :class="getAmountClass(item.amount)">
                {{ formatAmount(item.amount) }}
              </div>
            </div>
          </div>

          <van-empty 
            v-if="!loading && list.length === 0" 
            description="暂无记录" 
          />
        </van-list>
      </van-pull-refresh>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { getAccountLogs } from '@/api/user'
import { showToast } from 'vant'

export default {
  name: 'Record',
  setup() {
    const activeTab = ref('all')
    const list = ref([])
    const loading = ref(false)
    const refreshing = ref(false)
    const finished = ref(false)
    const page = ref(1)
    const limit = 20

    // 获取类型图标
    const getTypeIcon = (type) => {
      const iconMap = {
        recharge: 'cashier-o',
        withdraw: 'balance-o',
        bet: 'shopping-cart-o',
        win: 'smile-o',
        refund: 'replay',
        commission: 'gold-coin-o'
      }
      return iconMap[type] || 'orders-o'
    }

    // 获取类型文本
    const getTypeText = (type) => {
      const textMap = {
        recharge: '充值',
        withdraw: '提现',
        bet: '投注',
        win: '中奖',
        refund: '退款',
        commission: '佣金'
      }
      return textMap[type] || '其他'
    }

    // 获取金额样式类
    const getAmountClass = (amount) => {
      return parseFloat(amount) >= 0 ? 'amount-plus' : 'amount-minus'
    }

    // 格式化金额
    const formatAmount = (amount) => {
      const num = parseFloat(amount)
      return (num >= 0 ? '+' : '') + num.toFixed(2)
    }

    // 格式化时间
    const formatTime = (timestamp) => {
      const date = new Date(timestamp * 1000)
      return date.toLocaleString('zh-CN', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
      })
    }

    // 加载列表
    const loadList = async () => {
      try {
        const params = {
          page: page.value,
          limit: limit
        }
        
        if (activeTab.value !== 'all') {
          params.type = activeTab.value
        }
        
        const res = await getAccountLogs(params)
        
        if (res.code === 200) {
          if (page.value === 1) {
            list.value = res.data.list
          } else {
            list.value.push(...res.data.list)
          }
          
          if (list.value.length >= res.data.total) {
            finished.value = true
          }
        }
      } catch (error) {
        console.error('加载失败:', error)
        showToast('加载失败')
      } finally {
        loading.value = false
        refreshing.value = false
      }
    }

    // 切换tab
    const onTabChange = () => {
      page.value = 1
      finished.value = false
      list.value = []
      loadList()
    }

    // 下拉刷新
    const onRefresh = () => {
      page.value = 1
      finished.value = false
      loadList()
    }

    // 加载更多
    const onLoad = () => {
      if (!finished.value) {
        page.value++
        loadList()
      }
    }

    onMounted(() => {
      loadList()
    })

    return {
      activeTab,
      list,
      loading,
      refreshing,
      finished,
      getTypeIcon,
      getTypeText,
      getAmountClass,
      formatAmount,
      formatTime,
      onTabChange,
      onRefresh,
      onLoad
    }
  }
}
</script>

<style lang="scss" scoped>
.record-page {
  min-height: 100vh;
  background: #f5f5f5;
  padding-top: 46px;
}

.record-content {
  padding-top: 44px;
}

.record-list {
  padding: 10px;
}

.record-item {
  background: white;
  border-radius: 8px;
  padding: 15px;
  margin-bottom: 10px;
  display: flex;
  align-items: center;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.record-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 15px;
  font-size: 20px;
  color: white;
  
  &.type-recharge {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
  }
  
  &.type-withdraw {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  }
  
  &.type-bet {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
  }
  
  &.type-win {
    background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);
  }
  
  &.type-refund,
  &.type-commission {
    background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
  }
}

.record-info {
  flex: 1;
  
  .record-title {
    font-size: 16px;
    font-weight: bold;
    margin-bottom: 5px;
  }
  
  .record-time {
    font-size: 12px;
    color: #999;
    margin-bottom: 3px;
  }
  
  .record-remark {
    font-size: 12px;
    color: #666;
  }
}

.record-amount {
  font-size: 18px;
  font-weight: bold;
  
  &.amount-plus {
    color: #27ae60;
  }
  
  &.amount-minus {
    color: #e74c3c;
  }
}
</style>

