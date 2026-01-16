<template>
  <div class="page-container">
    <div class="page-header">
      <div class="title">开奖管理</div>
      <div class="subtitle">查看彩票开奖记录</div>
    </div>
    
    <el-card shadow="never">
      <!-- 搜索栏 -->
      <div class="toolbar">
        <div class="toolbar-left">
          <el-select v-model="searchForm.lotteryId" placeholder="彩种" clearable style="width: 150px;">
            <el-option label="全部" value="" />
            <el-option label="重庆时时彩" value="1" />
            <el-option label="新疆时时彩" value="2" />
            <el-option label="北京赛车PK10" value="3" />
            <el-option label="快3" value="4" />
            <el-option label="11选5" value="5" />
          </el-select>
          
          <el-input
            v-model="searchForm.issue"
            placeholder="期号"
            clearable
            style="width: 150px;"
          />
          
          <el-date-picker
            v-model="searchForm.date"
            type="date"
            placeholder="选择日期"
            value-format="YYYY-MM-DD"
            style="width: 150px;"
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
        <el-table-column prop="lotteryName" label="彩种" width="150">
          <template #default="{ row }">
            <el-tag :color="row.color" style="color: #fff; border: none;">{{ row.lotteryCode }}</el-tag>
            <span style="margin-left: 8px;">{{ row.lotteryName }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="issue" label="期号" width="120" align="center" />
        <el-table-column prop="drawNumber" label="开奖号码" min-width="250">
          <template #default="{ row }">
            <div class="draw-numbers">
              <span
                v-for="(num, index) in row.drawNumber.split(',')"
                :key="index"
                class="number-ball"
                :style="{ background: getBallColor(row.lotteryType, num) }"
              >
                {{ num }}
              </span>
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="betCount" label="投注人数" width="100" align="center" />
        <el-table-column prop="betAmount" label="投注金额" width="120" align="right">
          <template #default="{ row }">
            ¥{{ row.betAmount }}
          </template>
        </el-table-column>
        <el-table-column prop="winAmount" label="中奖金额" width="120" align="right">
          <template #default="{ row }">
            ¥{{ row.winAmount }}
          </template>
        </el-table-column>
        <el-table-column prop="drawTime" label="开奖时间" width="180" />
        <el-table-column label="操作" width="100" align="center" fixed="right">
          <template #default="{ row }">
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
  lotteryId: '',
  issue: '',
  date: ''
})

const pagination = reactive({
  page: 1,
  size: 10,
  total: 200
})

const tableData = ref([
  {
    id: 1,
    lotteryCode: 'CQSSC',
    lotteryName: '重庆时时彩',
    lotteryType: 'ssc',
    color: '#ff6b6b',
    issue: '20240320072',
    drawNumber: '3,5,8,1,9',
    betCount: 156,
    betAmount: '23,560.00',
    winAmount: '18,900.00',
    drawTime: '2024-03-20 18:20:00'
  },
  {
    id: 2,
    lotteryCode: 'XJSSC',
    lotteryName: '新疆时时彩',
    lotteryType: 'ssc',
    color: '#4ecdc4',
    issue: '20240320071',
    drawNumber: '1,4,6,2,8',
    betCount: 98,
    betAmount: '15,680.00',
    winAmount: '12,340.00',
    drawTime: '2024-03-20 18:10:00'
  },
  {
    id: 3,
    lotteryCode: 'PK10',
    lotteryName: '北京赛车PK10',
    lotteryType: 'pk10',
    color: '#f9ca24',
    issue: '20240320144',
    drawNumber: '03,07,01,10,05,02,09,04,08,06',
    betCount: 234,
    betAmount: '35,890.00',
    winAmount: '28,760.00',
    drawTime: '2024-03-20 18:05:00'
  },
  {
    id: 4,
    lotteryCode: 'K3',
    lotteryName: '快3',
    lotteryType: 'k3',
    color: '#6c5ce7',
    issue: '20240320240',
    drawNumber: '3,4,6',
    betCount: 178,
    betAmount: '28,450.00',
    winAmount: '22,130.00',
    drawTime: '2024-03-20 18:00:00'
  },
  {
    id: 5,
    lotteryCode: '11X5',
    lotteryName: '11选5',
    lotteryType: '11x5',
    color: '#00b894',
    issue: '20240320072',
    drawNumber: '01,03,05,08,11',
    betCount: 145,
    betAmount: '21,670.00',
    winAmount: '17,890.00',
    drawTime: '2024-03-20 17:50:00'
  }
])

const getBallColor = (type, num) => {
  const colors = {
    ssc: ['#ff6b6b', '#4ecdc4', '#45b7d1', '#f9ca24', '#6c5ce7'],
    pk10: '#1890ff',
    k3: '#67c23a',
    '11x5': '#e6a23c'
  }
  
  if (type === 'ssc') {
    return colors.ssc[parseInt(num) % 5]
  }
  return colors[type] || '#409eff'
}

const handleSearch = () => {
  loading.value = true
  setTimeout(() => {
    loading.value = false
    ElMessage.success('查询成功')
  }, 500)
}

const handleReset = () => {
  searchForm.lotteryId = ''
  searchForm.issue = ''
  searchForm.date = ''
  handleSearch()
}

const handleExport = () => {
  ElMessage.success('导出功能开发中...')
}

const handleDetail = (row) => {
  ElMessage.info(`查看期号 ${row.issue} 详情...`)
}

const handleSizeChange = () => {
  handleSearch()
}

const handleCurrentChange = () => {
  handleSearch()
}
</script>

<style lang="scss" scoped>
.draw-numbers {
  display: flex;
  gap: 8px;
  align-items: center;
  flex-wrap: wrap;
}

.number-ball {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  color: #fff;
  font-weight: 600;
  font-size: 14px;
}
</style>
