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
import { ref, reactive, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { getDrawList } from '@/api/lottery'

const loading = ref(false)

const searchForm = reactive({
  lotteryId: '',
  issue: '',
  date: ''
})

const pagination = reactive({
  page: 1,
  size: 10,
  total: 0
})

const tableData = ref([])

// 获取开奖记录
const fetchData = async () => {
  loading.value = true
  try {
    const data = await getDrawList({
      lotteryName: searchForm.lotteryId,
      issue: searchForm.issue,
      startDate: searchForm.date || '',
      endDate: searchForm.date || '',
      page: pagination.page,
      pageSize: pagination.size
    })

    tableData.value = (data.list || []).map(item => ({
      id: item.id,
      lotteryCode: item.cpname,
      lotteryName: item.cptitle || item.title,
      lotteryType: getLotteryType(item.cpname),
      color: getColorByName(item.cpname),
      issue: item.expect,
      drawNumber: item.opencode,
      drawTime: item.opentime ? new Date(item.opentime * 1000).toLocaleString('zh-CN') : '-',
      betCount: 0,
      betAmount: '0.00',
      winAmount: '0.00'
    }))
    
    pagination.total = data.total || 0
  } catch (error) {
    console.error('获取开奖记录失败:', error)
    ElMessage.error(error.message || '获取数据失败')
  } finally {
    loading.value = false
  }
}

const getLotteryType = (cpname) => {
  if (cpname.includes('ssc')) return 'ssc'
  if (cpname.includes('pk10')) return 'pk10'
  if (cpname.includes('k3')) return 'k3'
  if (cpname.includes('x5')) return '11x5'
  return 'ssc'
}

const getColorByName = (cpname) => {
  const colors = {
    'cqssc': '#ff6b6b',
    'xjssc': '#4ecdc4',
    'tjssc': '#f9ca24',
    'bjpk10': '#6c5ce7',
    'k3': '#00b894',
    'x5': '#45b7d1'
  }
  return colors[cpname] || '#409eff'
}

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
  pagination.page = 1
  fetchData()
}

const handleReset = () => {
  searchForm.lotteryId = ''
  searchForm.issue = ''
  searchForm.date = ''
  handleSearch()
}


const handleDetail = (row) => {
  ElMessage.info(`查看期号 ${row.issue} 详情...`)
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
