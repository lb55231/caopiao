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
import { ref, reactive, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { getAccountChangeList } from '@/api/finance'

const loading = ref(false)

const searchForm = reactive({
  type: '',
  dateRange: []
})

const pagination = reactive({
  page: 1,
  size: 10,
  total: 0
})

const tableData = ref([])

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

// 获取账变记录
const fetchData = async () => {
  loading.value = true
  try {
    const startTime = searchForm.dateRange && searchForm.dateRange[0] 
      ? new Date(searchForm.dateRange[0]).getTime() / 1000 
      : 0
    const endTime = searchForm.dateRange && searchForm.dateRange[1] 
      ? new Date(searchForm.dateRange[1] + ' 23:59:59').getTime() / 1000 
      : 0

    const data = await getAccountChangeList({
      page: pagination.page,
      pageSize: pagination.size,
      type: searchForm.type,
      startTime: startTime,
      endTime: endTime
    })

    tableData.value = (data.list || []).map(item => ({
      id: item.id,
      orderNo: item.trano || '-',
      type: item.type,
      typeName: item.typename || item.type,
      amount: parseFloat(item.amount || 0),
      beforeBalance: parseFloat(item.amountbefor || 0),
      afterBalance: parseFloat(item.amountafter || 0),
      remark: item.remark || '-',
      createTime: item.oddtime ? new Date(item.oddtime * 1000).toLocaleString('zh-CN') : '-'
    }))
    
    pagination.total = data.total || 0
  } catch (error) {
    console.error('获取账变记录失败:', error)
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
  searchForm.type = ''
  searchForm.dateRange = []
  handleSearch()
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

.text-danger {
  color: #f56c6c;
  font-weight: 600;
}
</style>
