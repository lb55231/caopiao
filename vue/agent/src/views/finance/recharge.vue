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
import { ref, reactive, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { getRechargeList } from '@/api/finance'

const loading = ref(false)

const searchForm = reactive({
  orderNo: '',
  status: '',
  dateRange: []
})

const pagination = reactive({
  page: 1,
  size: 10,
  total: 0
})

const tableData = ref([])

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
    2: 'danger',
    '-1': 'danger'
  }
  return tags[status] || ''
}

const getStatusName = (status) => {
  const names = {
    0: '待审核',
    1: '成功',
    2: '失败',
    '-1': '拒绝'
  }
  return names[status] || '未知'
}

// 获取充值记录
const fetchData = async () => {
  loading.value = true
  try {
    const data = await getRechargeList({
      page: pagination.page,
      pageSize: pagination.size,
      state: searchForm.status
    })

    tableData.value = (data.list || []).map(item => ({
      id: item.id,
      orderNo: item.trano || '-',
      amount: parseFloat(item.amount || 0).toFixed(2),
      payType: item.isauto || 1,
      payTypeName: item.isauto == 1 ? '自动' : '人工',
      status: item.state,
      statusName: getStatusName(item.state),
      remark: item.remark || '-',
      createTime: item.oddtime ? new Date(item.oddtime * 1000).toLocaleString('zh-CN') : '-',
      payTime: item.state == 1 && item.oddtime ? new Date(item.oddtime * 1000).toLocaleString('zh-CN') : null
    }))
    
    pagination.total = data.total || 0
  } catch (error) {
    console.error('获取充值记录失败:', error)
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
.text-success {
  color: #67c23a;
  font-weight: 600;
}
</style>
