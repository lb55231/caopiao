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
import { ref, reactive } from 'vue'
import { ElMessage } from 'element-plus'

const loading = ref(false)

const stats = ref({
  todayProfit: '3,280.50',
  weekProfit: '18,560.00',
  monthProfit: '56,890.00',
  totalProfit: '235,680.00'
})

const searchForm = reactive({
  dateRange: []
})

const pagination = reactive({
  page: 1,
  size: 10,
  total: 90
})

const tableData = ref([
  {
    date: '2024-03-20',
    rechargeAmount: '50,000.00',
    withdrawAmount: '20,000.00',
    betAmount: '35,000.00',
    winAmount: '28,000.00',
    commission: '2,800.00',
    rebate: '1,500.00',
    profit: '4,300.00',
    userCount: 85
  },
  {
    date: '2024-03-19',
    rechargeAmount: '45,000.00',
    withdrawAmount: '18,000.00',
    betAmount: '32,000.00',
    winAmount: '25,000.00',
    commission: '2,500.00',
    rebate: '1,350.00',
    profit: '3,850.00',
    userCount: 78
  },
  {
    date: '2024-03-18',
    rechargeAmount: '48,000.00',
    withdrawAmount: '22,000.00',
    betAmount: '38,000.00',
    winAmount: '30,000.00',
    commission: '3,000.00',
    rebate: '1,600.00',
    profit: '4,600.00',
    userCount: 92
  },
  {
    date: '2024-03-17',
    rechargeAmount: '42,000.00',
    withdrawAmount: '15,000.00',
    betAmount: '30,000.00',
    winAmount: '24,000.00',
    commission: '2,400.00',
    rebate: '1,250.00',
    profit: '3,650.00',
    userCount: 72
  },
  {
    date: '2024-03-16',
    rechargeAmount: '38,000.00',
    withdrawAmount: '16,000.00',
    betAmount: '28,000.00',
    winAmount: '22,000.00',
    commission: '2,200.00',
    rebate: '1,100.00',
    profit: '3,300.00',
    userCount: 68
  }
])

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

const handleSearch = () => {
  loading.value = true
  setTimeout(() => {
    loading.value = false
    ElMessage.success('查询成功')
  }, 500)
}

const handleReset = () => {
  searchForm.dateRange = []
  handleSearch()
}

const handleExport = () => {
  ElMessage.success('导出功能开发中...')
}

const handleDetail = (row) => {
  ElMessage.info(`查看 ${row.date} 详细数据...`)
}

const handleSizeChange = () => {
  handleSearch()
}

const handleCurrentChange = () => {
  handleSearch()
}
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
