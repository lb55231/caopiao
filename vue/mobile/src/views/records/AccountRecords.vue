<template>
  <div class="account-records-page">
    <!-- 头部 -->
    <van-nav-bar
      title="账变记录"
      left-arrow
      @click-left="onClickLeft"
    />

    <!-- 筛选条件 -->
    <div class="filter-box">
      <!-- 摘要筛选 -->
      <div class="filter-item">
        <span class="filter-label">摘要：</span>
        <van-dropdown-menu>
          <van-dropdown-item v-model="filters.type" :options="typeOptions" @change="loadRecords" />
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
              <div class="type-info">
                <div class="typename">{{ record.typename }}</div>
                <div class="time">{{ record.time_formatted }}</div>
              </div>
              <div class="amount-info">
                <div 
                  class="amount" 
                  :class="{
                    'amount-in': record.amount_type === 'in',
                    'amount-out': record.amount_type === 'out'
                  }"
                >
                  {{ record.amount_formatted }}
                </div>
                <div class="balance">余额：{{ record.amountafter }}</div>
              </div>
            </div>
            <div v-if="record.remark" class="record-remark">
              <span class="label">备注：</span>
              <span class="value">{{ record.remark }}</span>
            </div>
          </div>
        </div>
        <van-empty v-else description="暂无记录" />
      </van-list>
    </van-pull-refresh>

    <!-- 温馨提示 -->
    <div class="tip-box">
      <van-icon name="info-o" />
      温馨提示：交易记录最多只保留7天。
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { showToast } from 'vant'
import { getAccountRecords } from '@/api/user'

const router = useRouter()

// 筛选条件
const filters = reactive({
  type: '0',
  atime: '3',
  page: 1,
  pageSize: 10
})

// 下拉选项
const typeOptions = ref([
  { text: '全部摘要', value: '0' },
  { text: '充值', value: 'recharge' },
  { text: '提现', value: 'withdraw' },
  { text: '投注', value: 'order' },
  { text: '中奖', value: 'prize' },
  { text: '撤单', value: 'cancel' }
])

const timeOptions = [
  { text: '今天', value: '1' },
  { text: '昨天', value: '2' },
  { text: '七天', value: '3' }
]

// 数据
const records = ref([])

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
    const res = await getAccountRecords(filters)
    
    if (res.code === 200) {
      const newRecords = res.data.records || []
      
      if (filters.page === 1) {
        records.value = newRecords
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
.account-records-page {
  min-height: 100vh;
  background: #f5f5f5;
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
  align-items: flex-start;
}

.type-info {
  flex: 1;
  
  .typename {
    font-size: 15px;
    font-weight: bold;
    color: #333;
    margin-bottom: 5px;
  }
  
  .time {
    font-size: 12px;
    color: #999;
  }
}

.amount-info {
  text-align: right;
  
  .amount {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 5px;
    
    &.amount-in {
      color: #ff4444;
    }
    
    &.amount-out {
      color: #07c160;
    }
  }
  
  .balance {
    font-size: 12px;
    color: #999;
  }
}

.record-remark {
  margin-top: 10px;
  padding-top: 10px;
  border-top: 1px solid #f0f0f0;
  font-size: 13px;
  
  .label {
    color: #999;
  }
  
  .value {
    color: #666;
  }
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

