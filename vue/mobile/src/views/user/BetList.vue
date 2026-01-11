<template>
  <div class="bet-list-page">
    <van-nav-bar 
      title="投注记录" 
      left-arrow 
      @click-left="$router.back()"
      fixed
    />

    <div class="bet-content">
      <van-tabs v-model:active="activeTab" @change="onTabChange">
        <van-tab title="全部" name="all"></van-tab>
        <van-tab title="待开奖" name="0"></van-tab>
        <van-tab title="已中奖" name="1"></van-tab>
        <van-tab title="未中奖" name="2"></van-tab>
      </van-tabs>

      <van-pull-refresh v-model="refreshing" @refresh="onRefresh">
        <van-list
          v-model:loading="loading"
          :finished="finished"
          finished-text="没有更多了"
          @load="onLoad"
        >
          <div class="bet-list">
            <div 
              v-for="item in list" 
              :key="item.id"
              class="bet-item"
              @click="viewDetail(item)"
            >
              <div class="bet-header">
                <span class="lottery-name">{{ item.lottery_name }}</span>
                <span 
                  class="bet-status" 
                  :class="`status-${item.status}`"
                >
                  {{ getStatusText(item.status) }}
                </span>
              </div>
              
              <div class="bet-info">
                <div class="info-row">
                  <span class="label">期号：</span>
                  <span class="value">{{ item.period }}</span>
                </div>
                <div class="info-row">
                  <span class="label">玩法：</span>
                  <span class="value">{{ item.play_name }}</span>
                </div>
                <div class="info-row">
                  <span class="label">投注内容：</span>
                  <span class="value">{{ item.bet_content }}</span>
                </div>
                <div class="info-row">
                  <span class="label">投注金额：</span>
                  <span class="value amount">{{ item.bet_amount }} 元</span>
                </div>
                <div class="info-row" v-if="item.status === 1">
                  <span class="label">中奖金额：</span>
                  <span class="value win-amount">{{ item.win_amount }} 元</span>
                </div>
              </div>
              
              <div class="bet-footer">
                <span class="order-no">订单号：{{ item.order_no }}</span>
                <span class="bet-time">{{ formatTime(item.create_time) }}</span>
              </div>
            </div>
          </div>

          <van-empty 
            v-if="!loading && list.length === 0" 
            description="暂无投注记录" 
          />
        </van-list>
      </van-pull-refresh>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { getBetList } from '@/api/lottery'
import { showToast } from 'vant'

export default {
  name: 'BetList',
  setup() {
    const router = useRouter()
    const activeTab = ref('all')
    const list = ref([])
    const loading = ref(false)
    const refreshing = ref(false)
    const finished = ref(false)
    const page = ref(1)
    const limit = 20

    // 状态文本
    const getStatusText = (status) => {
      const statusMap = {
        0: '待开奖',
        1: '已中奖',
        2: '未中奖',
        3: '已撤单'
      }
      return statusMap[status] || '未知'
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
          params.status = activeTab.value
        }
        
        const res = await getBetList(params)
        
        if (res.code === 200) {
          if (page.value === 1) {
            list.value = res.data.list
          } else {
            list.value.push(...res.data.list)
          }
          
          // 判断是否还有更多数据
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

    // 查看详情
    const viewDetail = (item) => {
      router.push(`/bet/detail?order_no=${item.order_no}`)
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
      getStatusText,
      formatTime,
      onTabChange,
      onRefresh,
      onLoad,
      viewDetail
    }
  }
}
</script>

<style lang="scss" scoped>
.bet-list-page {
  min-height: 100vh;
  background: #f5f5f5;
  padding-top: 46px;
}

.bet-content {
  padding-top: 44px;
}

.bet-list {
  padding: 10px;
}

.bet-item {
  background: white;
  border-radius: 8px;
  padding: 15px;
  margin-bottom: 10px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.bet-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
  padding-bottom: 10px;
  border-bottom: 1px solid #f0f0f0;
  
  .lottery-name {
    font-size: 16px;
    font-weight: bold;
  }
  
  .bet-status {
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 12px;
    
    &.status-0 {
      background: #fff7e6;
      color: #f39c12;
    }
    
    &.status-1 {
      background: #e8f5e9;
      color: #27ae60;
    }
    
    &.status-2 {
      background: #f5f5f5;
      color: #999;
    }
    
    &.status-3 {
      background: #ffebee;
      color: #e74c3c;
    }
  }
}

.bet-info {
  margin-bottom: 10px;
  
  .info-row {
    display: flex;
    margin-bottom: 5px;
    font-size: 14px;
    
    .label {
      color: #999;
      min-width: 80px;
    }
    
    .value {
      flex: 1;
      color: #333;
      
      &.amount {
        color: #ff6b6b;
        font-weight: bold;
      }
      
      &.win-amount {
        color: #27ae60;
        font-weight: bold;
      }
    }
  }
}

.bet-footer {
  display: flex;
  justify-content: space-between;
  font-size: 12px;
  color: #999;
  padding-top: 10px;
  border-top: 1px solid #f0f0f0;
}
</style>

