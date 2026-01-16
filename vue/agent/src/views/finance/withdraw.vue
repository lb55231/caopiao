<template>
  <div class="page-container">
    <div class="page-header">
      <div class="title">提现记录</div>
      <div class="subtitle">查看账户提现记录明细</div>
    </div>
    
    <el-card shadow="never">
      <!-- 搜索栏 -->
      <div class="toolbar">
        <div class="toolbar-left">
          <el-input
            v-model="searchForm.orderNo"
            placeholder="订单号"
            clearable
            style="width: 200px;"
          >
            <template #prefix>
              <el-icon><Search /></el-icon>
            </template>
          </el-input>
          
          <el-select v-model="searchForm.status" placeholder="状态" clearable style="width: 120px;">
            <el-option label="全部" value="" />
            <el-option label="审核中" value="0" />
            <el-option label="已通过" value="1" />
            <el-option label="已拒绝" value="2" />
            <el-option label="已完成" value="3" />
          </el-select>
          
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
          <el-button type="primary" @click="handleWithdraw">
            <el-icon><Plus /></el-icon>
            申请提现
          </el-button>
        </div>
      </div>
      
      <!-- 表格 -->
      <el-table
        :data="tableData"
        v-loading="loading"
        border
        stripe
      >
        <el-table-column type="index" label="序号" width="60" align="center" />
        <el-table-column prop="orderNo" label="订单号" width="180" />
        <el-table-column prop="amount" label="提现金额" width="120" align="right">
          <template #default="{ row }">
            <span class="text-danger">¥{{ row.amount }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="fee" label="手续费" width="100" align="right">
          <template #default="{ row }">
            ¥{{ row.fee }}
          </template>
        </el-table-column>
        <el-table-column prop="actualAmount" label="实际到账" width="120" align="right">
          <template #default="{ row }">
            <span class="text-success">¥{{ row.actualAmount }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="bankName" label="收款银行" width="150" />
        <el-table-column prop="bankAccount" label="收款账号" width="180" />
        <el-table-column prop="status" label="状态" width="100" align="center">
          <template #default="{ row }">
            <el-tag :type="getStatusTag(row.status)">{{ row.statusName }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="createTime" label="申请时间" width="180" />
        <el-table-column prop="auditTime" label="审核时间" width="180">
          <template #default="{ row }">
            {{ row.auditTime || '-' }}
          </template>
        </el-table-column>
        <el-table-column label="操作" width="100" align="center" fixed="right">
          <template #default="{ row }">
            <el-button
              v-if="row.status === 0"
              type="danger"
              link
              size="small"
              @click="handleCancel(row)"
            >
              取消
            </el-button>
            <el-button
              type="primary"
              link
              size="small"
              @click="handleDetail(row)"
            >
              详情
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
  status: '',
  dateRange: []
})

const pagination = reactive({
  page: 1,
  size: 10,
  total: 30
})

const tableData = ref([
  {
    id: 1,
    orderNo: 'WD202403201234567890',
    amount: '10,000.00',
    fee: '50.00',
    actualAmount: '9,950.00',
    bankName: '中国工商银行',
    bankAccount: '6222 **** **** 1234',
    status: 3,
    statusName: '已完成',
    createTime: '2024-03-20 10:25:30',
    auditTime: '2024-03-20 10:30:25'
  },
  {
    id: 2,
    orderNo: 'WD202403201234567891',
    amount: '5,000.00',
    fee: '25.00',
    actualAmount: '4,975.00',
    bankName: '中国建设银行',
    bankAccount: '6217 **** **** 5678',
    status: 0,
    statusName: '审核中',
    createTime: '2024-03-20 14:00:15',
    auditTime: null
  },
  {
    id: 3,
    orderNo: 'WD202403201234567892',
    amount: '8,000.00',
    fee: '40.00',
    actualAmount: '7,960.00',
    bankName: '中国农业银行',
    bankAccount: '6228 **** **** 9012',
    status: 1,
    statusName: '已通过',
    createTime: '2024-03-20 15:20:40',
    auditTime: '2024-03-20 15:25:50'
  },
  {
    id: 4,
    orderNo: 'WD202403201234567893',
    amount: '3,000.00',
    fee: '15.00',
    actualAmount: '2,985.00',
    bankName: '中国银行',
    bankAccount: '6216 **** **** 3456',
    status: 2,
    statusName: '已拒绝',
    createTime: '2024-03-20 16:10:20',
    auditTime: '2024-03-20 16:15:30'
  }
])

const getStatusTag = (status) => {
  const tags = {
    0: 'warning',
    1: 'primary',
    2: 'danger',
    3: 'success'
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
  searchForm.status = ''
  searchForm.dateRange = []
  handleSearch()
}

const handleWithdraw = () => {
  ElMessage.info('提现功能开发中...')
}

const handleCancel = (row) => {
  ElMessageBox.confirm('确定要取消该提现申请吗？', '提示', {
    confirmButtonText: '确定',
    cancelButtonText: '取消',
    type: 'warning'
  }).then(() => {
    ElMessage.success('取消成功')
  }).catch(() => {})
}

const handleDetail = (row) => {
  ElMessage.info(`查看订单 ${row.orderNo} 详情...`)
}

const handleSizeChange = () => {
  handleSearch()
}

const handleCurrentChange = () => {
  handleSearch()
}
</script>

<style lang="scss" scoped>
.text-success {
  color: #67c23a;
  font-weight: 600;
}

.text-danger {
  color: #f56c6c;
  font-weight: 600;
}
</style>
