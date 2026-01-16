<template>
  <div class="page-container">
    <div class="page-header">
      <div class="title">收益报表</div>
      <div class="subtitle">查看账户收益统计报表</div>
    </div>
    
    <!-- 统计卡片 -->
    <el-row :gutter="20" class="stats-cards">
      <el-col :span="6">
        <div class="stat-card">
          <div class="stat-label">今日收益</div>
          <div class="stat-value">¥{{ stats.todayProfit }}</div>
          <div class="stat-trend">
            <el-icon color="#67c23a"><CaretTop /></el-icon>
            <span>+12.5%</span>
          </div>
        </div>
      </el-col>
      <el-col :span="6">
        <div class="stat-card">
          <div class="stat-label">本周收益</div>
          <div class="stat-value">¥{{ stats.weekProfit }}</div>
          <div class="stat-trend">
            <el-icon color="#67c23a"><CaretTop /></el-icon>
            <span>+8.3%</span>
          </div>
        </div>
      </el-col>
      <el-col :span="6">
        <div class="stat-card">
          <div class="stat-label">本月收益</div>
          <div class="stat-value">¥{{ stats.monthProfit }}</div>
          <div class="stat-trend">
            <el-icon color="#67c23a"><CaretTop /></el-icon>
            <span>+15.7%</span>
          </div>
        </div>
      </el-col>
      <el-col :span="6">
        <div class="stat-card">
          <div class="stat-label">累计收益</div>
          <div class="stat-value">¥{{ stats.totalProfit }}</div>
          <div class="stat-trend">
            <el-icon color="#67c23a"><CaretTop /></el-icon>
            <span>+23.4%</span>
          </div>
        </div>
      </el-col>
    </el-row>
    
    <el-card shadow="never">
      <!-- 搜索栏 -->
      <div class="toolbar">
        <div class="toolbar-left">
          <el-date-picker
            v-model="searchForm.dateRange"
            type="daterange"
            range-separator="至"
            start-placeholder="开始日期"
            end-placeholder="结束日期"
            value-format="YYYY-MM-DD"
          />
          
          <el-button type="primary" @click="handleSearch">
            <el-icon><Search /></el-icon>
            查询
          </el-button>
          <el-button @click="handleReset">
            <el-icon><Refresh /></el-icon>
            重置
          </el-button>
        </div>
        <div class="toolbar-right">
          <el-button type="success" @click="handleExport">
            <el-icon><Download /></el-icon>
            导出报表
          </el-button>
        </div>
      </div>
      
      <!-- 表格 -->
      <el-table
        :data="tableData"
        v-loading="loading"
        border
        stripe
        show-summary
        :summary-method="getSummaries"
      >
        <el-table-column prop="date" label="日期" width="120" align="center" />
        <el-table-column prop="rechargeAmount" label="充值金额" width="120" align="right">
          <template #default="{ row }">
            ¥{{ row.rechargeAmount }}
          </template>
        </el-table-column>
        <el-table-column prop="withdrawAmount" label="提现金额" width="120" align="right">
          <template #default="{ row }">
            ¥{{ row.withdrawAmount }}
          </template>
        </el-table-column>
        <el-table-column prop="betAmount" label="投注金额" width="120" align="right">
          <template #default="{ row }">
            ¥{{ row.betAmount }}
          </template>
        </el-table-column>
        <el-table-column prop="winAmount" label="中奖金额" width="120" align="right">
          <template #default="{ row }">
            ¥{{ row.winAmount }}
          </template>
        </el-table-column>
        <el-table-column prop="commission" label="返佣金额" width="120" align="right">
          <template #default="{ row }">
            <span class="text-success">¥{{ row.commission }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="rebate" label="返点金额" width="120" align="right">
          <template #default="{ row }">
            <span class="text-success">¥{{ row.rebate }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="profit" label="当日收益" width="120" align="right">
          <template #default="{ row }">
            <span :class="row.profit >= 0 ? 'text-success' : 'text-danger'">
              {{ row.profit >= 0 ? '+' : '' }}¥{{ row.profit }}
            </span>
          </template>
        </el-table-column>
        <el-table-column prop="userCount" label="活跃用户" width="100" align="center" />
        <el-table-column label="操作" width="100" align="center" fixed="right">
          <template #default="{ row }">
            <el-button
              type="primary"
              link
              size="small"
              @click="handleDetail(row)"
            >
              查看详情
            </el-button>
          </template>
        </el-table-column>
      </el-table>
      
      <!-- 分页 -->
      <el-pagination
        v-model:current-page="pagination.page"
        v-model:page-size="pagination.size"
        :total="pagination.total"
        :page-sizes="[10, 20, 50, 100]"
        layout="total, sizes, prev, pager, next, jumper"
        @size-change="handleSizeChange"
        @current-change="handleCurrentChange"
        style="margin-top: 20px; justify-content: flex-end;"
      />
    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { getProfitList } from '@/api/finance'

const loading = ref(false)

const stats = ref({
  todayProfit: '0.00',
  weekProfit: '0.00',
  monthProfit: '0.00',
  totalProfit: '0.00'
})

const searchForm = reactive({
  dateRange: []
})

const pagination = reactive({
  page: 1,
  size: 10,
  total: 0
})

const tableData = ref([])

const getSummaries = (param) => {
  const { columns, data } = param
  const sums = []
  columns.forEach((column, index) => {
    if (index === 0) {
      sums[index] = '合计'
      return
    }
    if (index === columns.length - 1) {
      sums[index] = '-'
      return
    }
    const values = data.map(item => {
      const value = item[column.property]
      return Number(value.replace(/,/g, ''))
    })
    if (!values.every(value => isNaN(value))) {
      sums[index] = '¥' + values.reduce((prev, curr) => {
        const value = Number(curr)
        if (!isNaN(value)) {
          return prev + curr
        } else {
          return prev
        }
      }, 0).toFixed(2)
    } else {
      sums[index] = '-'
    }
  })
  return sums
}

// 获取收益报表数据
const fetchData = async () => {
  loading.value = true
  try {
    const startTime = searchForm.dateRange && searchForm.dateRange[0] 
      ? new Date(searchForm.dateRange[0]).getTime() / 1000 
      : 0
    const endTime = searchForm.dateRange && searchForm.dateRange[1] 
      ? new Date(searchForm.dateRange[1] + ' 23:59:59').getTime() / 1000 
      : 0

    const data = await getProfitList({
      page: pagination.page,
      pageSize: pagination.size,
      startTime: startTime,
      endTime: endTime
    })

    tableData.value = (data.list || []).map(item => ({
      date: item.oddtime ? new Date(item.oddtime * 1000).toLocaleDateString('zh-CN') : '-',
      typeName: item.typename || item.type,
      amount: parseFloat(item.amount || 0).toFixed(2),
      remark: item.remark || '-',
      // 以下字段为显示需要，实际以 amount 为准
      rechargeAmount: '0.00',
      withdrawAmount: '0.00',
      betAmount: '0.00',
      winAmount: '0.00',
      commission: item.type === 'commission' ? parseFloat(item.amount || 0).toFixed(2) : '0.00',
      rebate: item.type === 'rebate' || item.type === 'fandian' ? parseFloat(item.amount || 0).toFixed(2) : '0.00',
      profit: parseFloat(item.amount || 0).toFixed(2),
      userCount: 0
    }))
    
    pagination.total = data.total || 0
  } catch (error) {
    console.error('获取收益报表失败:', error)
    ElMessage.error(error.message || '获取数据失败')
  } finally {
    loading.value = false
  }
}

const handleSearch = () => {
  pagination.page = 1
  fetchData()
}

const handleReset = () => {
  searchForm.dateRange = []
  handleSearch()
}

const handleExport = () => {
  ElMessage.info('导出功能开发中...')
}

const handleDetail = (row) => {
  ElMessage.info(`查看 ${row.date} 详细数据...`)
}

const handleSizeChange = () => {
  fetchData()
}

const handleCurrentChange = () => {
  fetchData()
}

onMounted(() => {
  fetchData()
})
</script>

<style lang="scss" scoped>
.stats-cards {
  margin-bottom: 20px;
}

.stat-card {
  padding: 24px;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  transition: all 0.3s;
  
  &:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
  }
  
  .stat-label {
    font-size: 14px;
    color: #909399;
    margin-bottom: 12px;
  }
  
  .stat-value {
    font-size: 28px;
    font-weight: 600;
    color: #303133;
    margin-bottom: 8px;
  }
  
  .stat-trend {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 14px;
    color: #67c23a;
  }
}

.text-success {
  color: #67c23a;
  font-weight: 600;
}

.text-danger {
  color: #f56c6c;
  font-weight: 600;
}
</style>
