<template>
  <div class="fund-records-container">
    <el-card>
      <template #header>
        <span>账变记录</span>
      </template>

      <!-- 搜索筛选 -->
      <el-form :inline="true" class="search-form">
        <el-form-item label="用户名">
          <el-input v-model="searchForm.username" placeholder="请输入用户名" clearable />
        </el-form-item>
        <el-form-item label="变动类型">
          <el-select v-model="searchForm.type" placeholder="请选择类型" clearable>
            <el-option label="全部" value="" />
            <el-option label="充值" value="recharge" />
            <el-option label="提现" value="withdraw" />
            <el-option label="投注" value="bet" />
            <el-option label="中奖" value="win" />
            <el-option label="返点" value="rebate" />
            <el-option label="人工加款" value="admin_add" />
            <el-option label="人工减款" value="admin_sub" />
          </el-select>
        </el-form-item>
        <el-form-item label="时间范围">
          <el-date-picker
            v-model="dateRange"
            type="datetimerange"
            range-separator="至"
            start-placeholder="开始时间"
            end-placeholder="结束时间"
            value-format="X"
          />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleSearch">搜索</el-button>
          <el-button @click="handleReset">重置</el-button>
        </el-form-item>
      </el-form>

      <!-- 表格 -->
      <el-table :data="dataList" border stripe>
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="trano" label="订单号" width="180" />
        <el-table-column prop="username" label="用户名" width="120" />
        <el-table-column prop="typename" label="类型" width="120">
          <template #default="{ row }">
            <el-tag :type="getTypeColor(row.type)">{{ row.typename }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="amount" label="变动金额" width="120">
          <template #default="{ row }">
            <span :style="{ color: parseFloat(row.amount) >= 0 ? '#67c23a' : '#f56c6c' }">
              {{ parseFloat(row.amount) >= 0 ? '+' : '' }}{{ row.amount }}
            </span>
          </template>
        </el-table-column>
        <el-table-column prop="amountbefor" label="变动前" width="120" />
        <el-table-column prop="amountafter" label="变动后" width="120" />
        <el-table-column prop="remark" label="备注" min-width="200" />
        <el-table-column label="时间" width="180">
          <template #default="{ row }">
            {{ formatTime(row.oddtime) }}
          </template>
        </el-table-column>
      </el-table>

      <!-- 分页 -->
      <el-pagination
        v-model:current-page="pagination.page"
        v-model:page-size="pagination.page_size"
        :total="pagination.total"
        :page-sizes="[10, 20, 50, 100]"
        layout="total, sizes, prev, pager, next, jumper"
        @size-change="loadData"
        @current-change="loadData"
      />
    </el-card>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { getFundRecords } from '@/api/member'

const dataList = ref([])
const dateRange = ref([])

const searchForm = ref({
  username: '',
  type: ''
})

const pagination = ref({
  page: 1,
  page_size: 20,
  total: 0
})

const loadData = async () => {
  try {
    const params = {
      page: pagination.value.page,
      page_size: pagination.value.page_size,
      ...searchForm.value
    }
    
    if (dateRange.value && dateRange.value.length === 2) {
      params.start_time = dateRange.value[0]
      params.end_time = dateRange.value[1]
    }
    
    const res = await getFundRecords(params)
    if (res.code === 200) {
      dataList.value = res.data.list
      pagination.value.total = res.data.total
    }
  } catch (error) {
    ElMessage.error('加载数据失败')
  }
}

const getTypeColor = (type) => {
  const colorMap = {
    'recharge': 'success',
    'withdraw': 'warning',
    'bet': 'info',
    'win': 'success',
    'rebate': 'primary',
    'admin_add': 'success',
    'admin_sub': 'danger'
  }
  return colorMap[type] || 'info'
}

const formatTime = (timestamp) => {
  if (!timestamp) return '-'
  const date = new Date(timestamp * 1000)
  return date.toLocaleString('zh-CN')
}

const handleSearch = () => {
  pagination.value.page = 1
  loadData()
}

const handleReset = () => {
  searchForm.value = {
    username: '',
    type: ''
  }
  dateRange.value = []
  handleSearch()
}

onMounted(() => {
  loadData()
})
</script>

<style scoped>
.fund-records-container {
  padding: 20px;
}

.search-form {
  margin-bottom: 20px;
}

.el-pagination {
  margin-top: 20px;
  justify-content: flex-end;
}
</style>

