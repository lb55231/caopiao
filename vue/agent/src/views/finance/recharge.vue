<template>
  <div class="page-container">
    <div class="page-header">
      <div class="title">充值记录</div>
      <div class="subtitle">查看账户充值记录明细</div>
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
            <el-option label="待支付" value="0" />
            <el-option label="成功" value="1" />
            <el-option label="失败" value="2" />
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
          <el-button type="primary" @click="handleRecharge">
            <el-icon><Plus /></el-icon>
            申请充值
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
        <el-table-column prop="amount" label="充值金额" width="120" align="right">
          <template #default="{ row }">
            <span class="text-success">¥{{ row.amount }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="payType" label="支付方式" width="120" align="center">
          <template #default="{ row }">
            <el-tag :type="getPayTypeTag(row.payType)">{{ row.payTypeName }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="status" label="状态" width="100" align="center">
          <template #default="{ row }">
            <el-tag :type="getStatusTag(row.status)">{{ row.statusName }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="remark" label="备注" min-width="200" show-overflow-tooltip />
        <el-table-column prop="createTime" label="申请时间" width="180" />
        <el-table-column prop="payTime" label="完成时间" width="180">
          <template #default="{ row }">
            {{ row.payTime || '-' }}
          </template>
        </el-table-column>
        <el-table-column label="操作" width="100" align="center" fixed="right">
          <template #default="{ row }">
            <el-button
              v-if="row.status === 0"
              type="primary"
              link
              size="small"
              @click="handlePay(row)"
            >
              去支付
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
import { ElMessage } from 'element-plus'

const loading = ref(false)

const searchForm = reactive({
  orderNo: '',
  status: '',
  dateRange: []
})

const pagination = reactive({
  page: 1,
  size: 10,
  total: 50
})

const tableData = ref([
  {
    id: 1,
    orderNo: 'RC202403201234567890',
    amount: '10,000.00',
    payType: 1,
    payTypeName: '银行卡',
    status: 1,
    statusName: '成功',
    remark: '银行卡转账充值',
    createTime: '2024-03-20 14:25:30',
    payTime: '2024-03-20 14:30:25'
  },
  {
    id: 2,
    orderNo: 'RC202403201234567891',
    amount: '5,000.00',
    payType: 2,
    payTypeName: '支付宝',
    status: 0,
    statusName: '待支付',
    remark: '支付宝充值',
    createTime: '2024-03-20 15:00:15',
    payTime: null
  },
  {
    id: 3,
    orderNo: 'RC202403201234567892',
    amount: '8,000.00',
    payType: 3,
    payTypeName: '微信',
    status: 1,
    statusName: '成功',
    remark: '微信转账充值',
    createTime: '2024-03-20 16:20:40',
    payTime: '2024-03-20 16:25:50'
  },
  {
    id: 4,
    orderNo: 'RC202403201234567893',
    amount: '3,000.00',
    payType: 1,
    payTypeName: '银行卡',
    status: 2,
    statusName: '失败',
    remark: '银行卡转账充值',
    createTime: '2024-03-20 17:10:20',
    payTime: '2024-03-20 17:15:30'
  }
])

const getPayTypeTag = (type) => {
  const tags = {
    1: 'primary',
    2: 'success',
    3: 'warning'
  }
  return tags[type] || ''
}

const getStatusTag = (status) => {
  const tags = {
    0: 'info',
    1: 'success',
    2: 'danger'
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

const handleRecharge = () => {
  ElMessage.info('充值功能开发中...')
}

const handlePay = (row) => {
  ElMessage.info(`订单 ${row.orderNo} 去支付...`)
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
</style>
