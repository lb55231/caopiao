<template>
  <div class="page-container">
    <div class="page-header">
      <div class="title">注单管理</div>
      <div class="subtitle">查看和管理用户投注记录</div>
    </div>
    
    <el-card shadow="never">
      <!-- 搜索栏 -->
      <div class="toolbar">
        <div class="toolbar-left">
          <el-input
            v-model="searchForm.orderNo"
            placeholder="注单号"
            clearable
            style="width: 180px;"
          >
            <template #prefix>
              <el-icon><Search /></el-icon>
            </template>
          </el-input>
          
          <el-input
            v-model="searchForm.username"
            placeholder="用户名"
            clearable
            style="width: 150px;"
          />
          
          <el-select v-model="searchForm.lotteryId" placeholder="彩种" clearable style="width: 120px;">
            <el-option label="全部" value="" />
            <el-option label="重庆时时彩" value="1" />
            <el-option label="新疆时时彩" value="2" />
            <el-option label="PK10" value="3" />
            <el-option label="快3" value="4" />
            <el-option label="11选5" value="5" />
          </el-select>
          
          <el-select v-model="searchForm.status" placeholder="状态" clearable style="width: 120px;">
            <el-option label="全部" value="" />
            <el-option label="待开奖" value="0" />
            <el-option label="未中奖" value="1" />
            <el-option label="已中奖" value="2" />
            <el-option label="已撤单" value="3" />
          </el-select>
          
          <el-date-picker
            v-model="searchForm.dateRange"
            type="datetimerange"
            range-separator="至"
            start-placeholder="开始时间"
            end-placeholder="结束时间"
            value-format="YYYY-MM-DD HH:mm:ss"
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
            导出
          </el-button>
        </div>
      </div>
      
      <!-- 统计信息 -->
      <el-row :gutter="20" style="margin-bottom: 20px;">
        <el-col :span="6">
          <div class="stat-box">
            <div class="stat-label">投注总额</div>
            <div class="stat-value">¥{{ statistics.totalBet }}</div>
          </div>
        </el-col>
        <el-col :span="6">
          <div class="stat-box">
            <div class="stat-label">中奖总额</div>
            <div class="stat-value" style="color: #67c23a;">¥{{ statistics.totalWin }}</div>
          </div>
        </el-col>
        <el-col :span="6">
          <div class="stat-box">
            <div class="stat-label">注单总数</div>
            <div class="stat-value">{{ statistics.totalOrders }}</div>
          </div>
        </el-col>
        <el-col :span="6">
          <div class="stat-box">
            <div class="stat-label">投注人数</div>
            <div class="stat-value">{{ statistics.totalUsers }}</div>
          </div>
        </el-col>
      </el-row>
      
      <!-- 表格 -->
      <el-table
        :data="tableData"
        v-loading="loading"
        border
        stripe
      >
        <el-table-column type="index" label="序号" width="60" align="center" />
        <el-table-column prop="orderNo" label="注单号" width="180" show-overflow-tooltip />
        <el-table-column prop="username" label="用户名" width="120" />
        <el-table-column prop="lotteryName" label="彩种" width="120" />
        <el-table-column prop="issue" label="期号" width="120" align="center" />
        <el-table-column prop="playName" label="玩法" width="120" />
        <el-table-column prop="betContent" label="投注内容" min-width="150" show-overflow-tooltip />
        <el-table-column prop="betAmount" label="投注金额" width="100" align="right">
          <template #default="{ row }">
            ¥{{ row.betAmount }}
          </template>
        </el-table-column>
        <el-table-column prop="winAmount" label="中奖金额" width="100" align="right">
          <template #default="{ row }">
            <span v-if="row.winAmount > 0" class="text-success">¥{{ row.winAmount }}</span>
            <span v-else>-</span>
          </template>
        </el-table-column>
        <el-table-column prop="status" label="状态" width="100" align="center">
          <template #default="{ row }">
            <el-tag :type="getStatusTag(row.status)">{{ row.statusName }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="betTime" label="投注时间" width="180" />
        <el-table-column label="操作" width="120" align="center" fixed="right">
          <template #default="{ row }">
            <el-button
              type="primary"
              link
              size="small"
              @click="handleDetail(row)"
            >
              详情
            </el-button>
            <el-button
              v-if="row.status === 0"
              type="danger"
              link
              size="small"
              @click="handleCancel(row)"
            >
              撤单
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
import { ElMessage, ElMessageBox } from 'element-plus'

const loading = ref(false)

const searchForm = reactive({
  orderNo: '',
  username: '',
  lotteryId: '',
  status: '',
  dateRange: []
})

const pagination = reactive({
  page: 1,
  size: 10,
  total: 500
})

const statistics = ref({
  totalBet: '156,890.50',
  totalWin: '128,560.00',
  totalOrders: 1568,
  totalUsers: 256
})

const tableData = ref([
  {
    id: 1,
    orderNo: 'BET202403201234567890',
    username: 'user001',
    lotteryName: '重庆时时彩',
    issue: '20240320072',
    playName: '五星直选',
    betContent: '12345',
    betAmount: '100.00',
    winAmount: '0.00',
    status: 1,
    statusName: '未中奖',
    betTime: '2024-03-20 18:15:30'
  },
  {
    id: 2,
    orderNo: 'BET202403201234567891',
    username: 'user002',
    lotteryName: '新疆时时彩',
    issue: '20240320071',
    playName: '三星组选',
    betContent: '168',
    betAmount: '50.00',
    winAmount: '320.00',
    status: 2,
    statusName: '已中奖',
    betTime: '2024-03-20 18:05:15'
  },
  {
    id: 3,
    orderNo: 'BET202403201234567892',
    username: 'user003',
    lotteryName: 'PK10',
    issue: '20240320144',
    playName: '冠军',
    betContent: '3号车',
    betAmount: '200.00',
    winAmount: '0.00',
    status: 0,
    statusName: '待开奖',
    betTime: '2024-03-20 18:00:20'
  },
  {
    id: 4,
    orderNo: 'BET202403201234567893',
    username: 'user004',
    lotteryName: '快3',
    issue: '20240320240',
    playName: '三同号',
    betContent: '111',
    betAmount: '30.00',
    winAmount: '0.00',
    status: 1,
    statusName: '未中奖',
    betTime: '2024-03-20 17:58:45'
  },
  {
    id: 5,
    orderNo: 'BET202403201234567894',
    username: 'user005',
    lotteryName: '11选5',
    issue: '20240320072',
    playName: '任选五',
    betContent: '01,03,05,08,11',
    betAmount: '80.00',
    winAmount: '560.00',
    status: 2,
    statusName: '已中奖',
    betTime: '2024-03-20 17:45:30'
  }
])

const getStatusTag = (status) => {
  const tags = {
    0: 'warning',
    1: 'info',
    2: 'success',
    3: 'danger'
  }
  return tags[status] || ''
}

const handleSearch = () => {
  loading.value = true
  setTimeout(() => {
    loading.value = false
    ElMessage.success('查询成功')
  }, 500)
}

const handleReset = () => {
  searchForm.orderNo = ''
  searchForm.username = ''
  searchForm.lotteryId = ''
  searchForm.status = ''
  searchForm.dateRange = []
  handleSearch()
}

const handleExport = () => {
  ElMessage.success('导出功能开发中...')
}

const handleDetail = (row) => {
  ElMessage.info(`查看注单 ${row.orderNo} 详情...`)
}

const handleCancel = (row) => {
  ElMessageBox.confirm('确定要撤销该注单吗？撤单后不可恢复！', '提示', {
    confirmButtonText: '确定',
    cancelButtonText: '取消',
    type: 'warning'
  }).then(() => {
    ElMessage.success('撤单成功')
  }).catch(() => {})
}

const handleSizeChange = () => {
  handleSearch()
}

const handleCurrentChange = () => {
  handleSearch()
}
</script>

<style lang="scss" scoped>
.stat-box {
  padding: 16px;
  background: #f5f7fa;
  border-radius: 4px;
  text-align: center;
  
  .stat-label {
    font-size: 14px;
    color: #909399;
    margin-bottom: 8px;
  }
  
  .stat-value {
    font-size: 24px;
    font-weight: 600;
    color: #303133;
  }
}

.text-success {
  color: #67c23a;
  font-weight: 600;
}
</style>
