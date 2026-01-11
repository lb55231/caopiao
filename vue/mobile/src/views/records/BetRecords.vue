<template>
  <div class="bet-records-page">
    <!-- 头部 -->
    <van-nav-bar
      title="投注记录"
      left-arrow
      @click-left="onClickLeft"
    />

    <!-- 今日概况 -->
    <div class="summary-box">
      <div class="summary-title">今日概况</div>
      <div class="summary-content">
        <div class="summary-item">
          <span class="label">下单金额：</span>
          <span class="value">{{ summary.total_bet }}元</span>
        </div>
        <div class="summary-item">
          <span class="label">收益金额：</span>
          <span class="value">{{ summary.total_win }}元</span>
        </div>
        <div class="summary-item">
          <span class="label">盈利：</span>
          <span class="value" :class="{'profit': parseFloat(summary.profit) > 0, 'loss': parseFloat(summary.profit) < 0}">
            {{ summary.profit }}元
          </span>
        </div>
      </div>
    </div>

    <!-- 筛选条件 -->
    <div class="filter-box">
      <!-- 彩票筛选 -->
      <div class="filter-item">
        <span class="filter-label">商品：</span>
        <van-dropdown-menu>
          <van-dropdown-item v-model="filters.cpname" :options="cpnameOptions" @change="loadRecords" />
        </van-dropdown-menu>
      </div>

      <!-- 时间筛选 -->
      <div class="filter-item">
        <span class="filter-label">时间：</span>
        <div class="filter-buttons">
          <span 
            v-for="item in timeOptions" 
            :key="item.value"
            :class="['filter-btn', { 'active': filters.atime === item.value }]"
            @click="changeTime(item.value)"
          >
            {{ item.text }}
          </span>
        </div>
      </div>

      <!-- 状态筛选 -->
      <div class="filter-item">
        <span class="filter-label">状态：</span>
        <div class="filter-buttons">
          <span 
            v-for="item in statusOptions" 
            :key="item.value"
            :class="['filter-btn', { 'active': filters.a_item === item.value }]"
            @click="changeStatus(item.value)"
          >
            {{ item.text }}
          </span>
        </div>
      </div>
    </div>

    <!-- 记录列表 -->
    <van-pull-refresh v-model="refreshing" @refresh="onRefresh">
      <van-list
        v-model:loading="loading"
        :finished="finished"
        finished-text="没有更多了"
        @load="onLoad"
      >
        <div v-if="records.length > 0" class="records-list">
          <div v-for="record in records" :key="record.id" class="record-item">
            <div class="record-header">
              <span class="lottery-name">{{ record.cptitle }}</span>
              <span 
                class="status" 
                :class="{
                  'status-pending': record.isdraw === 0,
                  'status-win': record.isdraw === 1,
                  'status-lose': record.isdraw === -1,
                  'status-cancel': record.isdraw === -2
                }"
              >
                {{ record.status_text }}
              </span>
            </div>
            <div class="record-row">
              <span class="label">期号：</span>
              <span class="value">{{ record.expect }}</span>
            </div>
            <div class="record-row">
              <span class="label">下单内容：</span>
              <span class="value">{{ record.playtitle }}--{{ record.tzcode }}</span>
            </div>
            <div class="record-row">
              <span class="label">下单金额：</span>
              <span class="value amount">{{ record.amount }}元</span>
            </div>
            <div class="record-row" v-if="record.opencode">
              <span class="label">匹配单号：</span>
              <span class="value">{{ record.opencode }}</span>
            </div>
            <div class="record-row" v-if="record.isdraw === 1">
              <span class="label">返佣：</span>
              <span class="value win-amount">{{ record.okamount }}元</span>
            </div>
            <div class="record-footer">
              <span class="time">{{ record.time_formatted }}</span>
            </div>
          </div>
        </div>
        <van-empty v-else description="暂无记录" />
      </van-list>
    </van-pull-refresh>

    <!-- 温馨提示 -->
    <div class="tip-box">
      <van-icon name="info-o" />
      温馨提示：下单记录最多只保留7天。
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { showToast } from 'vant'
import { getBetRecords } from '@/api/user'

const router = useRouter()

// 筛选条件
const filters = reactive({
  cpname: '0',
  atime: '1',
  a_item: '1',
  page: 1,
  pageSize: 10
})

// 下拉选项
const cpnameOptions = ref([
  { text: '全部商户', value: '0' }
])

const timeOptions = [
  { text: '今天', value: '1' },
  { text: '昨天', value: '2' },
  { text: '七天', value: '3' }
]

const statusOptions = [
  { text: '全部', value: '1' },
  { text: '已匹配订单', value: '2' },
  { text: '未匹配订单', value: '3' },
  { text: '待匹配', value: '4' }
]

// 数据
const records = ref([])
const summary = ref({
  total_bet: '0.00',
  total_win: '0.00',
  profit: '0.00'
})

// 加载状态
const loading = ref(false)
const finished = ref(false)
const refreshing = ref(false)

// 返回
const onClickLeft = () => {
  router.back()
}

// 改变时间
const changeTime = (value) => {
  filters.atime = value
  filters.page = 1
  records.value = []
  finished.value = false
  loadRecords()
}

// 改变状态
const changeStatus = (value) => {
  filters.a_item = value
  filters.page = 1
  records.value = []
  finished.value = false
  loadRecords()
}

// 下拉刷新
const onRefresh = () => {
  filters.page = 1
  records.value = []
  finished.value = false
  refreshing.value = true
  loadRecords()
}

// 上拉加载
const onLoad = () => {
  loadRecords()
}

// 加载记录
const loadRecords = async () => {
  try {
    loading.value = true
    const res = await getBetRecords(filters)
    
    if (res.code === 200) {
      const newRecords = res.data.records || []
      
      if (filters.page === 1) {
        records.value = newRecords
        summary.value = res.data.summary
      } else {
        records.value = [...records.value, ...newRecords]
      }
      
      // 判断是否还有更多数据
      const pagination = res.data.pagination
      if (pagination.page >= pagination.totalPages || newRecords.length === 0) {
        finished.value = true
      } else {
        filters.page++
      }
    } else {
      showToast(res.msg || '加载失败')
      finished.value = true
    }
  } catch (error) {
    showToast(error.message || '加载失败')
    finished.value = true
  } finally {
    loading.value = false
    refreshing.value = false
  }
}

onMounted(() => {
  loadRecords()
})
</script>

<style scoped lang="scss">
.bet-records-page {
  min-height: 100vh;
  background: #f5f5f5;
}

.summary-box {
  background: #fff;
  padding: 15px;
  margin-bottom: 10px;
}

.summary-title {
  font-size: 16px;
  font-weight: bold;
  margin-bottom: 10px;
  color: #333;
}

.summary-content {
  display: flex;
  justify-content: space-between;
}

.summary-item {
  flex: 1;
  text-align: center;
  
  .label {
    font-size: 12px;
    color: #666;
  }
  
  .value {
    display: block;
    font-size: 14px;
    font-weight: bold;
    color: #333;
    margin-top: 5px;
    
    &.profit {
      color: #ff4444;
    }
    
    &.loss {
      color: #07c160;
    }
  }
}

.filter-box {
  background: #fff;
  padding: 10px 15px;
  margin-bottom: 10px;
}

.filter-item {
  display: flex;
  align-items: center;
  margin-bottom: 10px;
  
  &:last-child {
    margin-bottom: 0;
  }
}

.filter-label {
  font-size: 14px;
  color: #666;
  margin-right: 10px;
  white-space: nowrap;
}

.filter-buttons {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.filter-btn {
  padding: 4px 12px;
  font-size: 13px;
  color: #666;
  background: #f5f5f5;
  border-radius: 4px;
  cursor: pointer;
  
  &.active {
    color: #fff;
    background: #1989fa;
  }
}

.records-list {
  padding: 10px 15px;
}

.record-item {
  background: #fff;
  border-radius: 8px;
  padding: 15px;
  margin-bottom: 10px;
}

.record-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
  padding-bottom: 10px;
  border-bottom: 1px solid #f0f0f0;
}

.lottery-name {
  font-size: 15px;
  font-weight: bold;
  color: #333;
}

.status {
  font-size: 13px;
  padding: 2px 8px;
  border-radius: 4px;
  
  &.status-pending {
    color: #ff9800;
    background: #fff3e0;
  }
  
  &.status-win {
    color: #ff4444;
    background: #ffebee;
  }
  
  &.status-lose {
    color: #07c160;
    background: #e8f5e9;
  }
  
  &.status-cancel {
    color: #999;
    background: #f5f5f5;
  }
}

.record-row {
  display: flex;
  align-items: center;
  margin-bottom: 8px;
  font-size: 13px;
  
  .label {
    color: #999;
    margin-right: 5px;
  }
  
  .value {
    color: #333;
    flex: 1;
    
    &.amount {
      color: #ff4444;
      font-weight: bold;
    }
    
    &.win-amount {
      color: #07c160;
      font-weight: bold;
    }
  }
}

.record-footer {
  margin-top: 10px;
  padding-top: 10px;
  border-top: 1px solid #f0f0f0;
}

.time {
  font-size: 12px;
  color: #999;
}

.tip-box {
  padding: 15px;
  text-align: center;
  font-size: 12px;
  color: #999;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 5px;
}
</style>

