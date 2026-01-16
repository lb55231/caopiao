<template>
  <div class="page-container">
    <div class="page-header">
      <div class="title">账变记录</div>
      <div class="subtitle">查看账户资金变动明细</div>
    </div>
    
    <el-card shadow="never">
      <!-- 搜索栏 -->
      <div class="toolbar">
        <div class="toolbar-left">
          <el-select v-model="searchForm.type" placeholder="变动类型" clearable style="width: 150px;">
            <el-option label="全部" value="" />
            <el-option label="充值" value="1" />
            <el-option label="提现" value="2" />
            <el-option label="返佣" value="3" />
            <el-option label="返点" value="4" />
            <el-option label="消费" value="5" />
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
          <el-button type="success" @click="handleExport">
            <el-icon><Download /></el-icon>
            导出
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
        <el-table-column prop="type" label="变动类型" width="100" align="center">
          <template #default="{ row }">
            <el-tag :type="getTypeTag(row.type)">{{ row.typeName }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="amount" label="变动金额" width="120" align="right">
          <template #default="{ row }">
            <span :class="row.amount > 0 ? 'text-success' : 'text-danger'">
              {{ row.amount > 0 ? '+' : '' }}¥{{ row.amount }}
            </span>
          </template>
        </el-table-column>
        <el-table-column prop="beforeBalance" label="变动前余额" width="120" align="right">
          <template #default="{ row }">
            ¥{{ row.beforeBalance }}
          </template>
        </el-table-column>
        <el-table-column prop="afterBalance" label="变动后余额" width="120" align="right">
          <template #default="{ row }">
            ¥{{ row.afterBalance }}
          </template>
        </el-table-column>
        <el-table-column prop="remark" label="备注" min-width="200" show-overflow-tooltip />
        <el-table-column prop="createTime" label="时间" width="180" />
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

const searchForm = reactive({
  type: '',
  dateRange: []
})

const pagination = reactive({
  page: 1,
  size: 10,
  total: 100
})

const tableData = ref([
  {
    id: 1,
    orderNo: 'AC202403201234567890',
    type: 1,
    typeName: '充值',
    amount: 10000.00,
    beforeBalance: 5000.00,
    afterBalance: 15000.00,
    remark: '银行卡充值',
    createTime: '2024-03-20 14:25:30'
  },
  {
    id: 2,
    orderNo: 'AC202403201234567891',
    type: 2,
    typeName: '提现',
    amount: -2000.00,
    beforeBalance: 15000.00,
    afterBalance: 13000.00,
    remark: '提现到银行卡',
    createTime: '2024-03-20 15:30:20'
  },
  {
    id: 3,
    orderNo: 'AC202403201234567892',
    type: 3,
    typeName: '返佣',
    amount: 500.00,
    beforeBalance: 13000.00,
    afterBalance: 13500.00,
    remark: '下级用户投注返佣',
    createTime: '2024-03-20 16:00:15'
  },
  {
    id: 4,
    orderNo: 'AC202403201234567893',
    type: 4,
    typeName: '返点',
    amount: 300.00,
    beforeBalance: 13500.00,
    afterBalance: 13800.00,
    remark: '平台返点',
    createTime: '2024-03-20 17:20:45'
  },
  {
    id: 5,
    orderNo: 'AC202403201234567894',
    type: 5,
    typeName: '消费',
    amount: -800.00,
    beforeBalance: 13800.00,
    afterBalance: 13000.00,
    remark: '投注消费',
    createTime: '2024-03-20 18:15:30'
  }
])

const getTypeTag = (type) => {
  const tags = {
    1: 'success',
    2: 'warning',
    3: 'primary',
    4: 'info',
    5: 'danger'
  }
  return tags[type] || ''
}

const handleSearch = () => {
  loading.value = true
  setTimeout(() => {
    loading.value = false
    ElMessage.success('查询成功')
  }, 500)
}

const handleReset = () => {
  searchForm.type = ''
  searchForm.dateRange = []
  handleSearch()
}

const handleExport = () => {
  ElMessage.success('导出功能开发中...')
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
