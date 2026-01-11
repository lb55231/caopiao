<template>
  <div class="login-logs-container">
    <el-card>
      <template #header>
        <span>登录日志</span>
      </template>

      <!-- 搜索筛选 -->
      <el-form :inline="true" class="search-form">
        <el-form-item label="用户名">
          <el-input v-model="searchForm.username" placeholder="请输入用户名" clearable />
        </el-form-item>
        <el-form-item label="IP地址">
          <el-input v-model="searchForm.ip" placeholder="请输入IP地址" clearable />
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
        <el-table-column prop="username" label="用户名" width="150" />
        <el-table-column prop="loginip" label="登录IP" width="150" />
        <el-table-column prop="iparea" label="IP归属地" width="120" />
        <el-table-column prop="loginsource" label="登录来源" width="120">
          <template #default="{ row }">
            <el-tag>{{ row.loginsource || 'Web' }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="device" label="设备信息" min-width="200" />
        <el-table-column label="登录时间" width="180">
          <template #default="{ row }">
            {{ formatTime(row.logintime) }}
          </template>
        </el-table-column>
        <el-table-column label="登录状态" width="100">
          <template #default="{ row }">
            <el-tag :type="row.status === 1 ? 'success' : 'danger'">
              {{ row.status === 1 ? '成功' : '失败' }}
            </el-tag>
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
import { getLoginLogs } from '@/api/member'

const dataList = ref([])
const dateRange = ref([])

const searchForm = ref({
  username: '',
  ip: ''
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
    
    const res = await getLoginLogs(params)
    if (res.code === 200) {
      dataList.value = res.data.list
      pagination.value.total = res.data.total
    }
  } catch (error) {
    ElMessage.error('加载数据失败')
  }
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
    ip: ''
  }
  dateRange.value = []
  handleSearch()
}

onMounted(() => {
  loadData()
})
</script>

<style scoped>
.login-logs-container {
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

