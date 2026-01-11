<template>
  <div class="bets-container">
    <h2 class="page-title">投注记录</h2>
    
    <!-- 搜索表单 -->
    <el-card class="search-card mb-20">
      <el-form :inline="true" :model="searchForm" class="search-form">
        <el-form-item label="用户名">
          <el-input v-model="searchForm.username" placeholder="请输入用户名" clearable />
        </el-form-item>
        <el-form-item label="期号">
          <el-input v-model="searchForm.expect" placeholder="请输入期号" clearable />
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="searchForm.status" placeholder="请选择状态" clearable>
            <el-option label="全部" value="" />
            <el-option label="待开奖" :value="0" />
            <el-option label="已中奖" :value="1" />
            <el-option label="未中奖" :value="-1" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleSearch">
            <el-icon><Search /></el-icon>
            搜索
          </el-button>
          <el-button @click="handleReset">重置</el-button>
        </el-form-item>
      </el-form>
    </el-card>
    
    <!-- 投注列表 -->
    <el-card>
      <el-table
        v-loading="loading"
        :data="betList"
        border
        stripe
        style="width: 100%"
      >
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="username" label="用户名" width="120" />
        <el-table-column prop="cptitle" label="彩种" width="100" />
        <el-table-column prop="expect" label="期号" width="150" />
        <el-table-column prop="playtitle" label="玩法" width="120" />
        <el-table-column prop="tzcode" label="投注内容" width="100" />
        <el-table-column prop="amount" label="投注金额" width="100">
          <template #default="{ row }">
            <span style="color: #E6A23C; font-weight: bold;">¥ {{ row.amount }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="opencode" label="开奖号码" width="120" />
        <el-table-column prop="okamount" label="中奖金额" width="100">
          <template #default="{ row }">
            <span v-if="row.okamount > 0" style="color: #67C23A; font-weight: bold;">
              ¥ {{ row.okamount }}
            </span>
            <span v-else>---</span>
          </template>
        </el-table-column>
        <el-table-column prop="isdraw" label="状态" width="100">
          <template #default="{ row }">
            <el-tag v-if="row.isdraw == 0" type="warning">待开奖</el-tag>
            <el-tag v-else-if="row.isdraw == 1" type="success">已中奖</el-tag>
            <el-tag v-else type="info">未中奖</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="oddtime" label="投注时间" width="180">
          <template #default="{ row }">
            {{ formatTime(row.oddtime) }}
          </template>
        </el-table-column>
      </el-table>
      
      <!-- 分页 -->
      <el-pagination
        v-model:current-page="currentPage"
        v-model:page-size="pageSize"
        :page-sizes="[10, 20, 50, 100]"
        :total="total"
        layout="total, sizes, prev, pager, next, jumper"
        @size-change="loadList"
        @current-change="loadList"
      />
    </el-card>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { getBetList } from '@/api/admin'
import { ElMessage } from 'element-plus'

const loading = ref(false)
const betList = ref([])
const total = ref(0)
const currentPage = ref(1)
const pageSize = ref(10)

const searchForm = ref({
  username: '',
  expect: '',
  status: ''
})

const loadList = async () => {
  loading.value = true
  try {
    // Mock data for now
    betList.value = []
    total.value = 0
    
    // 待后端API准备好后使用以下代码
    /*
    const res = await getBetList({
      page: currentPage.value,
      pageSize: pageSize.value,
      ...searchForm.value
    })
    if (res.code === 200) {
      betList.value = res.data.list || []
      total.value = res.data.total || 0
    } else {
      ElMessage.error(res.msg || '加载失败')
    }
    */
  } catch (error) {
    ElMessage.error('加载失败: ' + error.message)
  } finally {
    loading.value = false
  }
}

const handleSearch = () => {
  currentPage.value = 1
  loadList()
}

const handleReset = () => {
  searchForm.value = {
    username: '',
    expect: '',
    status: ''
  }
  handleSearch()
}

const formatTime = (timestamp) => {
  if (!timestamp) return '---'
  const date = new Date(timestamp * 1000)
  return date.toLocaleString('zh-CN')
}

onMounted(() => {
  loadList()
})
</script>

<style scoped lang="scss">
.bets-container {
  padding: 20px;
}

.page-title {
  font-size: 24px;
  font-weight: bold;
  margin-bottom: 20px;
  padding-bottom: 10px;
  border-bottom: 2px solid #5CB85C;
  color: #333;
}

.search-card {
  .search-form {
    :deep(.el-form-item) {
      margin-bottom: 0;
    }
  }
}

.mb-20 {
  margin-bottom: 20px;
}
</style>

