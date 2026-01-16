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
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { getOrderList } from '@/api/lottery'

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
  total: 0
})

const statistics = ref({
  totalBet: '0.00',
  totalWin: '0.00',
  totalOrders: 0,
  totalUsers: 0
})

const tableData = ref([])

// 获取注单记录
const fetchData = async () => {
  loading.value = true
  try {
    const data = await getOrderList({
      lotteryName: searchForm.lotteryId,
      username: searchForm.username,
      issue: '',
      orderNo: searchForm.orderNo,
      status: searchForm.status,
      sDate: searchForm.dateRange && searchForm.dateRange[0] ? searchForm.dateRange[0].split(' ')[0] : '',
      eDate: searchForm.dateRange && searchForm.dateRange[1] ? searchForm.dateRange[1].split(' ')[0] : '',
      page: pagination.page,
      pageSize: pagination.size
    })

    tableData.value = (data.list || []).map(item => ({
      id: item.id,
      orderNo: item.trano || '-',
      username: item.username,
      lotteryName: item.cptitle || item.cpname,
      issue: item.expect,
      playName: item.playtitle || '-',
      betContent: item.tzcode || '-',
      betAmount: parseFloat(item.amount || 0).toFixed(2),
      winAmount: parseFloat(item.okamount || 0).toFixed(2),
      status: item.isdraw,
      statusName: getStatusName(item.isdraw),
      betTime: item.oddtime ? new Date(item.oddtime * 1000).toLocaleString('zh-CN') : '-',
      drawNumber: item.opencode || '-'
    }))
    
    pagination.total = data.total || 0

    // 更新统计信息
    if (data.stats) {
      statistics.value = {
        totalBet: parseFloat(data.stats.total_amount || 0).toFixed(2),
        totalWin: parseFloat(data.stats.total_award || 0).toFixed(2),
        totalOrders: data.stats.total_count || 0,
        totalUsers: data.stats.win_count || 0
      }
    }
  } catch (error) {
    console.error('获取注单记录失败:', error)
    ElMessage.error(error.message || '获取数据失败')
  } finally {
    loading.value = false
  }
}

const getStatusName = (status) => {
  const names = {
    0: '待开奖',
    1: '已中奖',
    2: '未中奖'
  }
  return names[status] || '未知'
}

const getStatusTag = (status) => {
  const tags = {
    0: 'warning',
    1: 'success',
    2: 'info',
    3: 'danger'
  }
  return tags[status] || ''
}

// Delete old fake data


const handleSearch = () => {
  pagination.page = 1
  fetchData()
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
  ElMessage.info('导出功能开发中...')
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
