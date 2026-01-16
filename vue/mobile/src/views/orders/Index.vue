<template>
  <div class="orders-page">
    <!-- 顶部导航栏 -->
    <van-nav-bar
      title="我的订单"
      fixed
      placeholder
      :border="false"
    />

    <!-- 标签页 -->
    <van-tabs
      v-model:active="activeTab"
      sticky
      :offset-top="46"
      @change="onTabChange"
    >
      <van-tab title="全部" name="all"></van-tab>
      <van-tab title="待开奖" name="pending"></van-tab>
      <van-tab title="已中奖" name="won"></van-tab>
      <van-tab title="未中奖" name="lost"></van-tab>
    </van-tabs>

    <!-- 订单列表 -->
    <van-pull-refresh v-model="refreshing" @refresh="onRefresh">
      <van-list
        v-model:loading="loading"
        :finished="finished"
        finished-text="没有更多了"
        @load="onLoad"
      >
        <div v-if="orderList.length > 0">
          <div
            v-for="order in orderList"
            :key="order.id"
            class="order-card"
            @click="goToDetail(order)"
          >
            <!-- 订单头部 -->
            <div class="order-header">
              <span class="order-no">订单号：{{ order.trano }}</span>
              <span :class="['order-status', getStatusClass(order.isdraw)]">
                {{ order.status_text || getStatusText(order.isdraw) }}
              </span>
            </div>

            <!-- 订单内容 -->
            <div class="order-content">
              <div class="lottery-info">
                <div class="lottery-icon">
                  {{ order.cptitle ? order.cptitle.charAt(0) : '彩' }}
                </div>
                <div class="lottery-details">
                  <div class="lottery-name">{{ order.cptitle }}</div>
                  <div class="lottery-period">第 {{ order.expect }} 期</div>
                  <div class="lottery-play">{{ order.playtitle }} · {{ order.tzcode }}</div>
                </div>
              </div>
              <div class="order-amount">
                <span class="label">投注金额</span>
                <span class="value">¥{{ order.amount }}</span>
              </div>
            </div>

            <!-- 订单底部 -->
            <div class="order-footer">
              <div class="order-time">{{ formatTime(order.oddtime) }}</div>
              <div class="order-actions">
                <van-button
                  v-if="order.isdraw === -1"
                  size="small"
                  round
                  color="#ff6034"
                >
                  等待开奖
                </van-button>
                <van-button
                  v-else-if="order.isdraw === 1"
                  size="small"
                  round
                  type="success"
                >
                  中奖 ¥{{ order.okamount }}
                </van-button>
                <van-button
                  v-else-if="order.isdraw === 0"
                  size="small"
                  round
                  plain
                  type="default"
                >
                  未中奖
                </van-button>
              </div>
            </div>
          </div>
        </div>

        <!-- 空状态 -->
        <van-empty
          v-else
          image="search"
          image-size="120"
          description="暂无订单记录"
        />
      </van-list>
    </van-pull-refresh>

    <!-- 底部导航 -->
    <Tabbar />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { showToast } from 'vant'
import { getImageUrl } from '@/utils/image'
import request from '@/api/request'
import Tabbar from '@/components/Tabbar.vue'

const router = useRouter()
const activeTab = ref('all')
const orderList = ref([])
const loading = ref(false)
const finished = ref(false)
const refreshing = ref(false)
const page = ref(1)
const pageSize = 20

// 加载订单列表
const loadOrders = async (isRefresh = false) => {
  try {
    if (isRefresh) {
      page.value = 1
      orderList.value = []
      finished.value = false
    }

    const params = {
      page: page.value,
      limit: pageSize
    }

    if (activeTab.value !== 'all') {
      params.status = getStatusValue(activeTab.value)
    }

    const res = await request.get('/user/bet/records', { params })
    
    if (res.code === 200) {
      const newOrders = res.data.records || []
      
      if (isRefresh) {
        orderList.value = newOrders
      } else {
        orderList.value = [...orderList.value, ...newOrders]
      }

      // 判断是否还有更多数据
      if (newOrders.length < pageSize) {
        finished.value = true
      }
      
      page.value++
    }
  } catch (error) {
    showToast('加载失败')
    console.error('加载订单失败:', error)
  } finally {
    loading.value = false
    refreshing.value = false
  }
}

// 下拉刷新
const onRefresh = () => {
  loadOrders(true)
}

// 加载更多
const onLoad = () => {
  if (!finished.value) {
    loadOrders()
  }
}

// 切换标签
const onTabChange = () => {
  orderList.value = []
  page.value = 1
  finished.value = false
  loadOrders(true)
}

// 获取状态文本 (isdraw: -1=待开奖, 0=未中奖, 1=已中奖)
const getStatusText = (isdraw) => {
  const statusMap = {
    '-1': '待开奖',
    '0': '未中奖',
    '1': '已中奖'
  }
  return statusMap[String(isdraw)] || '未知'
}

// 获取状态样式类
const getStatusClass = (isdraw) => {
  const classMap = {
    '-1': 'pending',
    '0': 'lost',
    '1': 'won'
  }
  return classMap[String(isdraw)] || ''
}

// 获取状态值
const getStatusValue = (tab) => {
  const statusMap = {
    'pending': -1,
    'won': 1,
    'lost': 0
  }
  return statusMap[tab]
}

// 格式化时间
const formatTime = (timestamp) => {
  const date = new Date(timestamp * 1000)
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  const hour = String(date.getHours()).padStart(2, '0')
  const minute = String(date.getMinutes()).padStart(2, '0')
  return `${year}-${month}-${day} ${hour}:${minute}`
}

// 查看详情
const goToDetail = (order) => {
  router.push(`/orders/${order.id}`)
}

onMounted(() => {
  loadOrders(true)
})
</script>

<style scoped lang="scss">
.orders-page {
  min-height: 100vh;
  background: #f7f8fa;
  padding-bottom: 60px;
}

.order-card {
  background: white;
  margin: 10px;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);

  .order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 15px;
    border-bottom: 1px solid #f0f0f0;

    .order-no {
      font-size: 13px;
      color: #666;
    }

    .order-status {
      font-size: 13px;
      font-weight: 500;

      &.pending {
        color: #ff9800;
      }

      &.won {
        color: #07c160;
      }

      &.lost {
        color: #999;
      }

      &.cancelled {
        color: #999;
      }
    }
  }

  .order-content {
    padding: 15px;

    .lottery-info {
      display: flex;
      align-items: center;
      margin-bottom: 12px;

      .lottery-icon {
        width: 50px;
        height: 50px;
        border-radius: 8px;
        margin-right: 12px;
        background: linear-gradient(135deg, #ff6034, #ee0a24);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        font-weight: bold;
        color: white;
      }

      .lottery-details {
        flex: 1;

        .lottery-name {
          font-size: 16px;
          font-weight: 500;
          color: #323233;
          margin-bottom: 4px;
        }

        .lottery-period {
          font-size: 13px;
          color: #969799;
        }

        .lottery-play {
          font-size: 12px;
          color: #ff6034;
          margin-top: 2px;
        }
      }
    }

    .order-amount {
      display: flex;
      justify-content: space-between;
      align-items: center;

      .label {
        font-size: 14px;
        color: #646566;
      }

      .value {
        font-size: 18px;
        font-weight: 600;
        color: #ff6034;
      }
    }
  }

  .order-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 15px;
    border-top: 1px solid #f0f0f0;
    background: #fafafa;

    .order-time {
      font-size: 12px;
      color: #969799;
    }

    .order-actions {
      :deep(.van-button) {
        padding: 0 16px;
      }
    }
  }
}

:deep(.van-empty) {
  padding: 80px 0;
}
</style>
