<template>
  <div class="page-container">
    <div class="page-header">
      <div class="title">彩票管理</div>
      <div class="subtitle">管理所有彩种信息</div>
    </div>
    
    <el-card shadow="never">
      <!-- 搜索栏 -->
      <div class="toolbar">
        <div class="toolbar-left">
          <el-input
            v-model="searchForm.keyword"
            placeholder="彩种名称"
            clearable
            style="width: 200px;"
          >
            <template #prefix>
              <el-icon><Search /></el-icon>
            </template>
          </el-input>
          
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
        :data="filteredData"
        v-loading="loading"
        border
        stripe
      >
        <el-table-column type="index" label="序号" width="60" align="center" />
        <el-table-column prop="title" label="彩种名称" width="180">
          <template #default="{ row }">
            <div style="display: flex; align-items: center; gap: 8px;">
              <el-tag :color="getColorByType(row.typeid)" style="color: #fff; border: none;">
                {{ row.name }}
              </el-tag>
              <span>{{ row.title }}</span>
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="typeid" label="彩种类型" width="120" align="center">
          <template #default="{ row }">
            <el-tag>{{ getTypeName(row.typeid) }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="expecttime" label="开奖周期" width="120" align="center">
          <template #default="{ row }">
            {{ row.expecttime }} 分钟
          </template>
        </el-table-column>
        <el-table-column prop="qishu" label="今日期数" width="100" align="center" />
        <el-table-column prop="issys" label="系统彩" width="100" align="center">
          <template #default="{ row }">
            <el-tag :type="row.issys == 1 ? 'success' : 'info'">
              {{ row.issys == 1 ? '是' : '否' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="isopen" label="开启状态" width="100" align="center">
          <template #default="{ row }">
            <el-switch
              v-model="row.isopen"
              :active-value="1"
              :inactive-value="0"
              @change="handleStatusChange(row, 'isopen')"
            />
          </template>
        </el-table-column>
        <el-table-column prop="iswh" label="维护状态" width="100" align="center">
          <template #default="{ row }">
            <el-switch
              v-model="row.iswh"
              :active-value="1"
              :inactive-value="0"
              active-text="维护"
              inactive-text="正常"
              @change="handleStatusChange(row, 'iswh')"
            />
          </template>
        </el-table-column>
        <el-table-column label="操作" width="120" align="center" fixed="right">
          <template #default="{ row }">
            <el-button
              type="primary"
              link
              size="small"
              @click="handleEdit(row)"
            >
              编辑
            </el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { getLotteryList, updateLotteryStatus } from '@/api/lottery'

const loading = ref(false)

const searchForm = reactive({
  keyword: ''
})

const tableData = ref([])

const filteredData = computed(() => {
  if (!searchForm.keyword) {
    return tableData.value
  }
  return tableData.value.filter(item => 
    item.title.includes(searchForm.keyword) || item.name.includes(searchForm.keyword)
  )
})

const getColorByType = (typeid) => {
  const colors = {
    'ssc': '#ff6b6b',
    'x5': '#4ecdc4',
    'pk10': '#f9ca24',
    'k3': '#6c5ce7',
    'keno': '#00b894',
    'dpc': '#45b7d1',
    'lhc': '#e17055'
  }
  return colors[typeid] || '#409eff'
}

const getTypeName = (typeid) => {
  const names = {
    'ssc': '时时彩',
    'x5': '11选5',
    'pk10': 'PK10',
    'k3': '快3',
    'keno': 'KENO',
    'dpc': '低频彩',
    'lhc': '六合彩'
  }
  return names[typeid] || typeid
}

const fetchData = async () => {
  loading.value = true
  try {
    const data = await getLotteryList({})
    tableData.value = data.list || []
  } catch (error) {
    console.error('获取彩票列表失败:', error)
    ElMessage.error(error.message || '获取数据失败')
  } finally {
    loading.value = false
  }
}

const handleSearch = () => {
  // 前端过滤，不需要重新请求
}

const handleReset = () => {
  searchForm.keyword = ''
}

const handleStatusChange = async (row, field) => {
  ElMessage.warning('代理端不支持修改彩种状态')
  // 恢复原状态
  row[field] = row[field] === 1 ? 0 : 1
}

const handleEdit = (row) => {
  ElMessage.info(`编辑 ${row.title}...`)
}

onMounted(() => {
  fetchData()
})
</script>

<style lang="scss" scoped>
</style>
