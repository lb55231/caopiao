<template>
  <div class="game-records-container">
    <nav class="filter-nav">
      <el-form :inline="true" :model="searchForm" size="small">
        <el-form-item label="内部">
          <el-select v-model="searchForm.isnb" style="width: 80px;">
            <el-option label="全部" :value="999" />
            <el-option label="是" :value="1" />
            <el-option label="否" :value="0" />
          </el-select>
        </el-form-item>
        
        <el-form-item label="彩种">
          <el-select v-model="searchForm.cpname" style="width: 100px;">
            <el-option label="全部" value="" />
            <el-option 
              v-for="cp in lotteryList" 
              :key="cp.name"
              :label="cp.title"
              :value="cp.name"
            />
          </el-select>
        </el-form-item>
        
        <el-form-item label="期号">
          <el-input v-model="searchForm.qihao" style="width: 100px;" />
        </el-form-item>
        
        <el-form-item label="单号">
          <el-input v-model="searchForm.trano" style="width: 120px;" />
        </el-form-item>
        
        <el-form-item label="时间">
          <el-date-picker
            v-model="searchForm.sDate"
            type="datetime"
            placeholder="开始时间"
            format="YYYYMMDD HH:mm"
            value-format="YYYYMMDD HH:mm"
            style="width: 150px;"
          />
          -
          <el-date-picker
            v-model="searchForm.eDate"
            type="datetime"
            placeholder="结束时间"
            format="YYYYMMDD HH:mm"
            value-format="YYYYMMDD HH:mm"
            style="width: 150px;"
          />
        </el-form-item>
        
        <el-form-item label="用户名">
          <el-input v-model="searchForm.username" style="width: 100px;" />
        </el-form-item>
        
        <el-form-item label="状态">
          <el-select v-model="searchForm.status" style="width: 100px;">
            <el-option label="全部" :value="999" />
            <el-option label="未开奖" :value="0" />
            <el-option label="中奖" :value="1" />
            <el-option label="未中奖" :value="-1" />
            <el-option label="撤单" :value="-2" />
          </el-select>
        </el-form-item>
        
        <el-form-item label="排序">
          <el-select v-model="searchForm.listorder" style="width: 120px;">
            <el-option label="默认" value="" />
            <el-option label="时间大到小" value="1" />
            <el-option label="时间小到大" value="2" />
            <el-option label="金额大到小" value="3" />
            <el-option label="金额小到大" value="4" />
          </el-select>
        </el-form-item>
        
        <el-form-item>
          <el-button type="primary" @click="handleSearch">查询</el-button>
        </el-form-item>
      </el-form>
      
      <div class="nav-right">
        自动刷新：
        <el-select v-model="refert" @change="handleRefertChange" style="width: 80px;" size="small">
          <el-option label="不刷新" :value="0" />
          <el-option label="5秒" :value="5" />
          <el-option label="10秒" :value="10" />
          <el-option label="20秒" :value="20" />
          <el-option label="30秒" :value="30" />
          <el-option label="60秒" :value="60" />
        </el-select>
      </div>
    </nav>

    <div class="page-content">
      <el-table 
        v-loading="loading"
        :data="tableData" 
        border 
        size="small"
        class="records-table"
      >
        <el-table-column type="selection" width="40" />
        <el-table-column label="单号" width="180">
          <template #default="{ row }">
            {{ row.trano }}
          </template>
        </el-table-column>
        <el-table-column label="用户名" width="100">
          <template #default="{ row }">
            {{ row.username }}
          </template>
        </el-table-column>
        <el-table-column label="彩票名称" width="100">
          <template #default="{ row }">
            {{ row.cptitle }}
          </template>
        </el-table-column>
        <el-table-column label="期号" width="120">
          <template #default="{ row }">
            {{ row.expect }}
          </template>
        </el-table-column>
        <el-table-column label="玩法" width="100">
          <template #default="{ row }">
            {{ row.playtitle }}
          </template>
        </el-table-column>
        <el-table-column label="注数" width="60">
          <template #default="{ row }">
            {{ row.itemcount }}
          </template>
        </el-table-column>
        <el-table-column label="奖金/赔率" width="80">
          <template #default="{ row }">
            {{ row.mode }}
          </template>
        </el-table-column>
        <el-table-column label="金额" width="80">
          <template #default="{ row }">
            {{ row.amount }}
          </template>
        </el-table-column>
        <el-table-column label="投注后金额" width="100">
          <template #default="{ row }">
            {{ row.amountafter }}
          </template>
        </el-table-column>
        <el-table-column label="中奖金额" width="80">
          <template #default="{ row }">
            {{ row.okamount }}
          </template>
        </el-table-column>
        <el-table-column label="中奖注数" width="80">
          <template #default="{ row }">
            {{ row.okcount }}
          </template>
        </el-table-column>
        <el-table-column label="中奖倍数" width="80">
          <template #default="{ row }">
            {{ row.beishu }}
          </template>
        </el-table-column>
        <el-table-column label="元/角/分" width="80">
          <template #default="{ row }">
            {{ row.yjf }}
          </template>
        </el-table-column>
        <el-table-column label="号码" width="100">
          <template #default="{ row }">
            <el-button 
              v-if="row.tzcode && row.tzcode.length > 20"
              type="primary" 
              link 
              size="small"
              @click="showTzcode(row)"
            >
              查看
            </el-button>
            <span v-else class="tzcode-text">{{ row.tzcode }}</span>
          </template>
        </el-table-column>
        <el-table-column label="开奖号" width="100">
          <template #default="{ row }">
            {{ row.opencode }}
          </template>
        </el-table-column>
        <el-table-column label="IP" width="135">
          <template #default="{ row }">
            {{ row.ip }}
          </template>
        </el-table-column>
        <el-table-column label="状态" width="80">
          <template #default="{ row }">
            <el-tag v-if="row.isdraw === 1" type="success">中</el-tag>
            <el-tag v-else-if="row.isdraw === 0" type="info">未开奖</el-tag>
            <el-tag v-else-if="row.isdraw === -1" type="danger">未中</el-tag>
            <el-tag v-else-if="row.isdraw === -2" type="warning">撤</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="投注时间" width="140">
          <template #default="{ row }">
            {{ formatTime(row.oddtime) }}
          </template>
        </el-table-column>
        <el-table-column label="操作" width="100" fixed="right">
          <template #default="{ row }">
            <el-button 
              v-if="row.isdraw === 0"
              type="primary" 
              link 
              size="small"
              @click="handleChedan(row)"
            >
              撤单
            </el-button>
            <span v-else style="color: #999;">---</span>
          </template>
        </el-table-column>
      </el-table>
      
      <div class="pagination-footer">
        <div class="footer-left">
          <!-- <el-button type="danger" size="small">删除</el-button> -->
        </div>
        <div class="footer-right">
          <el-pagination
            background
            layout="total, prev, pager, next"
            :total="total"
            :page-size="pageSize"
            :current-page="currentPage"
            @current-change="handlePageChange"
          />
          <span class="total-count">共有数据：<strong>{{ total }}</strong> 条</span>
        </div>
      </div>
    </div>
    
    <!-- 查看投注内容对话框 -->
    <el-dialog 
      v-model="tzcodeDialogVisible" 
      title="投注内容查看"
      width="500px"
    >
      <el-input 
        v-model="currentTzcode"
        type="textarea"
        :rows="10"
        placeholder="投注内容..."
      />
      <template #footer>
        <el-button @click="tzcodeDialogVisible = false">关闭</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { getBetRecords, getLotteryTypes } from '@/api/lottery'

const loading = ref(false)
const lotteryList = ref([])
const tableData = ref([])
const total = ref(0)
const currentPage = ref(1)
const pageSize = ref(10)
const refert = ref(0)
let refreshTimer = null

const tzcodeDialogVisible = ref(false)
const currentTzcode = ref('')

const searchForm = reactive({
  isnb: 999,
  cpname: '',
  qihao: '',
  trano: '',
  sDate: '',
  eDate: '',
  username: '',
  status: 999,
  listorder: ''
})

// 加载彩票列表
const loadLotteryList = async () => {
  try {
    const res = await getLotteryTypes({})
    if (res.code === 200) {
      lotteryList.value = res.data.list || []
    }
  } catch (error) {
    console.error('加载彩票列表失败:', error)
  }
}

// 加载列表
const loadList = async () => {
  loading.value = true
  try {
    const params = {
      ...searchForm,
      page: currentPage.value,
      page_size: pageSize.value
    }
    
    const res = await getBetRecords(params)
    if (res.code === 200) {
      tableData.value = res.data.list || []
      total.value = res.data.total || 0
    } else {
      ElMessage.error(res.msg || '加载失败')
    }
  } catch (error) {
    ElMessage.error('加载失败: ' + error.message)
  } finally {
    loading.value = false
  }
}

// 搜索
const handleSearch = () => {
  currentPage.value = 1
  loadList()
}

// 分页
const handlePageChange = (val) => {
  currentPage.value = val
  loadList()
}

// 格式化时间
const formatTime = (timestamp) => {
  if (!timestamp) return ''
  const date = new Date(timestamp * 1000)
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  const hour = String(date.getHours()).padStart(2, '0')
  const minute = String(date.getMinutes()).padStart(2, '0')
  const second = String(date.getSeconds()).padStart(2, '0')
  return `${month}-${day} ${hour}:${minute}:${second}`
}

// 查看投注内容
const showTzcode = (row) => {
  currentTzcode.value = row.tzcode
  tzcodeDialogVisible.value = true
}

// 撤单
const handleChedan = async (row) => {
  try {
    await ElMessageBox.confirm('您确认撤单吗？', '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })
    
    // TODO: 调用撤单API
    ElMessage.success('撤单成功')
    loadList()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('撤单失败')
    }
  }
}

// 自动刷新
const handleRefertChange = () => {
  if (refreshTimer) {
    clearInterval(refreshTimer)
    refreshTimer = null
  }
  
  if (refert.value > 0) {
    refreshTimer = setInterval(() => {
      loadList()
    }, refert.value * 1000)
  }
}

onMounted(() => {
  loadLotteryList()
  loadList()
})

onUnmounted(() => {
  if (refreshTimer) {
    clearInterval(refreshTimer)
  }
})
</script>

<style scoped lang="scss">
.game-records-container {
  padding: 10px;
}

.filter-nav {
  background: #fff;
  padding: 15px;
  border-radius: 4px;
  margin-bottom: 10px;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  
  .el-form {
    flex: 1;
  }
  
  .nav-right {
    display: flex;
    align-items: center;
    white-space: nowrap;
    margin-left: 20px;
  }
}

.page-content {
  background: #fff;
  padding: 15px;
  border-radius: 4px;
}

.records-table {
  font-size: 12px;
  
  .tzcode-text {
    color: #409EFF;
    cursor: pointer;
    display: inline-block;
    max-width: 85px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
}

.pagination-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 20px;
  padding: 10px;
  background: #f5f7fa;
  
  .footer-right {
    display: flex;
    align-items: center;
    gap: 15px;
    
    .total-count {
      font-size: 14px;
      color: #606266;
      
      strong {
        color: #409EFF;
      }
    }
  }
}
</style>
