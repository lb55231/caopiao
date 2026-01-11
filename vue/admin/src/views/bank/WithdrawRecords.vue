<template>
  <div class="withdraw-container">
    <h2 class="page-title">提现记录</h2>
    
    <!-- 搜索表单 -->
    <el-card class="search-card">
      <el-form :inline="true" :model="searchForm">
        <el-form-item label="状态">
          <el-select v-model="searchForm.state" placeholder="全部" clearable style="width: 120px">
            <el-option label="未审核" :value="0" />
            <el-option label="已完成" :value="1" />
            <el-option label="退回" :value="2" />
          </el-select>
        </el-form-item>
        <el-form-item label="用户名">
          <el-input v-model="searchForm.username" placeholder="请输入用户名" clearable style="width: 150px" />
        </el-form-item>
        <el-form-item label="单号">
          <el-input v-model="searchForm.trano" placeholder="请输入单号" clearable style="width: 200px" />
        </el-form-item>
        <el-form-item label="时间范围">
          <el-date-picker
            v-model="searchForm.dateRange"
            type="daterange"
            range-separator="至"
            start-placeholder="开始日期"
            end-placeholder="结束日期"
            value-format="YYYYMMDD"
            style="width: 260px"
          />
        </el-form-item>
        <el-form-item label="金额范围">
          <el-input-number v-model="searchForm.sAmout" :min="0" placeholder="最小" style="width: 120px" />
          <span style="margin: 0 10px">至</span>
          <el-input-number v-model="searchForm.eAmout" :min="0" placeholder="最大" style="width: 120px" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleSearch">查询</el-button>
          <el-button @click="handleReset">重置</el-button>
          <el-button type="success" @click="loadList">
            <el-icon><Refresh /></el-icon>
            刷新
          </el-button>
        </el-form-item>
      </el-form>
    </el-card>
    
    <!-- 统计信息 -->
    <el-card class="stats-card" v-if="stats">
      <div class="stats-content">
        <div class="stat-item">
          <span class="stat-label">总提款：</span>
          <span class="stat-value success">{{ stats.total_success_amount || 0 }}</span>
          <span class="stat-count">({{ stats.total_success_count || 0 }}笔)</span>
        </div>
      </div>
    </el-card>
    
    <!-- 列表 -->
    <el-table
      :data="list"
      border
      style="width: 100%"
      v-loading="loading"
    >
      <el-table-column prop="trano" label="单号" width="180" />
      <el-table-column prop="username" label="用户名" width="100" />
      <el-table-column prop="accountname" label="姓名" width="100" />
      <el-table-column prop="bankname" label="收款平台" width="120" />
      <el-table-column prop="banknumber" label="收款账号" width="150" />
      <el-table-column prop="amount" label="金额" width="100" align="right">
        <template #default="{ row }">
          <span style="color: #909399">{{ row.amount }}</span>
        </template>
      </el-table-column>
      <el-table-column prop="actualamount" label="实到金额" width="100" align="right">
        <template #default="{ row }">
          <span style="color: #67c23a; font-weight: bold">{{ row.actualamount }}</span>
        </template>
      </el-table-column>
      <el-table-column prop="fee" label="手续费" width="100" align="right" />
      <el-table-column prop="oldaccountmoney" label="变更前金额" width="110" align="right" />
      <el-table-column prop="newaccountmoney" label="变更后金额" width="110" align="right" />
      <el-table-column label="时间" width="140" align="center">
        <template #default="{ row }">
          {{ formatTime(row.oddtime) }}
        </template>
      </el-table-column>
      <el-table-column label="状态" width="100" align="center" fixed="right">
        <template #default="{ row }">
          <el-tag v-if="row.state == 0" type="danger">未审核</el-tag>
          <el-tag v-else-if="row.state == 1" type="success">已完成</el-tag>
          <el-tag v-else-if="row.state == 2" type="warning">退回</el-tag>
          <el-tag v-else type="info">未知</el-tag>
        </template>
      </el-table-column>
      <el-table-column label="操作" width="150" align="center" fixed="right">
        <template #default="{ row }">
          <el-link v-if="row.state == 0" type="primary" @click="handleAudit(row, 1)">通过</el-link>
          <el-divider v-if="row.state == 0" direction="vertical" />
          <el-link v-if="row.state == 0" type="warning" @click="handleAudit(row, 2)">退回</el-link>
          <el-divider v-if="row.state == 0" direction="vertical" />
          <el-link type="danger" @click="handleDelete(row)">删除</el-link>
        </template>
      </el-table-column>
    </el-table>
    
    <!-- 分页 -->
    <div class="pagination-container">
      <el-pagination
        v-model:current-page="currentPage"
        v-model:page-size="pageSize"
        :page-sizes="[10, 20, 50, 100]"
        :total="total"
        layout="total, sizes, prev, pager, next, jumper"
        @size-change="loadList"
        @current-change="loadList"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Refresh } from '@element-plus/icons-vue'
import { getWithdrawList, auditWithdraw, deleteWithdraw } from '@/api/bank'

const loading = ref(false)
const list = ref([])
const total = ref(0)
const currentPage = ref(1)
const pageSize = ref(10)
const stats = ref(null)

const searchForm = reactive({
  state: '',
  username: '',
  trano: '',
  dateRange: null,
  sAmout: null,
  eAmout: null
})

// 加载列表
const loadList = async () => {
  try {
    loading.value = true
    
    const params = {
      page: currentPage.value,
      page_size: pageSize.value,
      state: searchForm.state,
      username: searchForm.username,
      trano: searchForm.trano,
      sAmout: searchForm.sAmout,
      eAmout: searchForm.eAmout
    }
    
    if (searchForm.dateRange && searchForm.dateRange.length === 2) {
      params.sDate = searchForm.dateRange[0]
      params.eDate = searchForm.dateRange[1]
    }
    
    const res = await getWithdrawList(params)
    if (res.code === 200) {
      list.value = res.data.list
      total.value = res.data.total
      stats.value = res.data.stats
    } else {
      ElMessage.error(res.msg || '加载失败')
    }
  } catch (error) {
    ElMessage.error('加载失败：' + error.message)
  } finally {
    loading.value = false
  }
}

// 搜索
const handleSearch = () => {
  currentPage.value = 1
  loadList()
}

// 重置
const handleReset = () => {
  searchForm.state = ''
  searchForm.username = ''
  searchForm.trano = ''
  searchForm.dateRange = null
  searchForm.sAmout = null
  searchForm.eAmout = null
  handleSearch()
}

// 审核
const handleAudit = (row, state) => {
  const action = state === 1 ? '通过' : '退回'
  ElMessageBox.confirm(`确定要${action}这条提现记录吗？${state === 2 ? '退回将返还用户余额。' : ''}`, '提示', {
    confirmButtonText: '确定',
    cancelButtonText: '取消',
    type: 'warning'
  }).then(async () => {
    try {
      const res = await auditWithdraw({ id: row.id, state })
      if (res.code === 200) {
        ElMessage.success('操作成功')
        loadList()
      } else {
        ElMessage.error(res.msg || '操作失败')
      }
    } catch (error) {
      ElMessage.error('操作失败：' + error.message)
    }
  }).catch(() => {})
}

// 删除
const handleDelete = (row) => {
  ElMessageBox.confirm('确定要删除这条记录吗？', '提示', {
    confirmButtonText: '确定',
    cancelButtonText: '取消',
    type: 'warning'
  }).then(async () => {
    try {
      const res = await deleteWithdraw(row.id)
      if (res.code === 200) {
        ElMessage.success('删除成功')
        loadList()
      } else {
        ElMessage.error(res.msg || '删除失败')
      }
    } catch (error) {
      ElMessage.error('删除失败：' + error.message)
    }
  }).catch(() => {})
}

// 格式化时间
const formatTime = (timestamp) => {
  if (!timestamp) return ''
  const date = new Date(timestamp * 1000)
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  const hour = String(date.getHours()).padStart(2, '0')
  const minute = String(date.getMinutes()).padStart(2, '0')
  return `${month}-${day} ${hour}:${minute}`
}

// 页面加载
onMounted(() => {
  loadList()
})
</script>

<style scoped lang="scss">
.withdraw-container {
  padding: 20px;
}

.page-title {
  font-size: 24px;
  font-weight: 500;
  margin-bottom: 20px;
  color: #303133;
}

.search-card {
  margin-bottom: 20px;
}

.stats-card {
  margin-bottom: 20px;
  
  .stats-content {
    display: flex;
    justify-content: center;
    
    .stat-item {
      text-align: center;
      
      .stat-label {
        font-size: 14px;
        color: #606266;
      }
      
      .stat-value {
        font-size: 20px;
        font-weight: bold;
        color: #409EFF;
        margin: 0 5px;
        
        &.success {
          color: #f56c6c;
        }
      }
      
      .stat-count {
        font-size: 12px;
        color: #909399;
      }
    }
  }
}

.pagination-container {
  margin-top: 20px;
  display: flex;
  justify-content: flex-end;
}
</style>

