<template>
  <div class="bet-check-container">
    <h2 class="page-title">注单异常检查</h2>
    
    <el-card>
      <!-- 搜索表单 -->
      <el-form :inline="true" :model="searchForm" class="search-form">
        <el-form-item label="彩种名称">
          <el-input 
            v-model="searchForm.cpname" 
            placeholder="请输入彩种名称"
            clearable
            style="width: 200px"
          />
        </el-form-item>
        <el-form-item label="会员账号">
          <el-input 
            v-model="searchForm.username" 
            placeholder="请输入会员账号"
            clearable
            style="width: 200px"
          />
        </el-form-item>
        <el-form-item label="时间差（秒）">
          <el-input-number 
            v-model="searchForm.shijiancha" 
            :min="0"
            :max="300"
            :step="10"
            style="width: 150px"
          />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="loadData">查询</el-button>
          <el-button @click="resetSearch">重置</el-button>
        </el-form-item>
      </el-form>

      <!-- 说明信息 -->
      <el-alert
        type="info"
        :closable="false"
        style="margin-bottom: 15px"
      >
        <template #title>
          <div style="line-height: 1.8">
            <strong>异常检测说明：</strong>检查投注时间和开奖时间差值较小的注单（可能存在作弊风险）<br/>
            <span style="color: #E6A23C;">⚠️ 风险等级：</span>
            <el-tag type="danger" size="small">高风险（≤30秒）</el-tag>
            <el-tag type="warning" size="small">中风险（≤60秒）</el-tag>
            <el-tag type="info" size="small">低风险（>60秒）</el-tag>
          </div>
        </template>
      </el-alert>

      <!-- 表格 -->
      <el-table
        v-loading="loading"
        :data="tableData"
        border
        stripe
        style="width: 100%"
      >
        <el-table-column type="index" label="序号" width="60" align="center" />
        <el-table-column prop="id" label="注单ID" width="80" align="center" />
        <el-table-column prop="trano" label="订单号" width="180" align="center" />
        <el-table-column prop="username" label="会员账号" width="120" align="center" />
        <el-table-column prop="cpname" label="彩种" width="100" align="center" />
        <el-table-column prop="expect" label="期号" width="120" align="center" />
        <el-table-column prop="amount" label="投注金额" width="100" align="center">
          <template #default="{ row }">
            <span style="color: #F56C6C; font-weight: bold">{{ row.amount }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="time_diff" label="时间差" width="100" align="center">
          <template #default="{ row }">
            <el-tag :type="getRiskTagType(row.risk_level)" size="small">
              {{ row.time_diff }}秒
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="risk_level" label="风险等级" width="100" align="center">
          <template #default="{ row }">
            <el-tag :type="getRiskTagType(row.risk_level)" size="small">
              {{ getRiskText(row.risk_level) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="oddtime" label="投注时间" width="150" align="center">
          <template #default="{ row }">
            {{ formatTime(row.oddtime) }}
          </template>
        </el-table-column>
        <el-table-column prop="kj_opentime" label="开奖时间" width="150" align="center">
          <template #default="{ row }">
            {{ formatTime(row.kj_opentime) }}
          </template>
        </el-table-column>
        <el-table-column prop="kj_opencode" label="开奖号码" width="120" align="center" />
        <el-table-column prop="isdraw" label="状态" width="100" align="center">
          <template #default="{ row }">
            <el-tag :type="getStatusType(row.isdraw)" size="small">
              {{ getStatusText(row.isdraw) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="200" align="center" fixed="right">
          <template #default="{ row }">
            <el-button 
              type="danger" 
              size="small"
              :disabled="![0, -1].includes(parseInt(row.isdraw))"
              @click="handleCancel(row)"
            >
              撤单
            </el-button>
            <el-button 
              type="primary" 
              size="small"
              @click="handleEdit(row)"
            >
              修改
            </el-button>
          </template>
        </el-table-column>
      </el-table>

      <!-- 分页 -->
      <el-pagination
        v-model:current-page="pagination.page"
        v-model:page-size="pagination.pageSize"
        :page-sizes="[20, 50, 100]"
        :total="pagination.total"
        layout="total, sizes, prev, pager, next, jumper"
        @size-change="loadData"
        @current-change="loadData"
        style="margin-top: 20px; text-align: right"
      />
    </el-card>

    <!-- 修改投注号码对话框 -->
    <el-dialog
      v-model="editDialogVisible"
      title="修改投注号码"
      width="500px"
    >
      <el-form :model="editForm" label-width="100px">
        <el-form-item label="订单号">
          <el-input v-model="editForm.trano" disabled />
        </el-form-item>
        <el-form-item label="原投注号码">
          <el-input v-model="editForm.oldTzcode" disabled />
        </el-form-item>
        <el-form-item label="新投注号码">
          <el-input 
            v-model="editForm.newTzcode" 
            placeholder="请输入新的投注号码"
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="editDialogVisible = false">取消</el-button>
        <el-button type="primary" @click="submitEdit">确定</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { getAbnormalBets, cancelBet, updateBetCode } from '@/api/betCheck'

const loading = ref(false)
const tableData = ref([])

const searchForm = reactive({
  cpname: '',
  username: '',
  shijiancha: 130  // 默认130秒
})

const pagination = reactive({
  page: 1,
  pageSize: 20,
  total: 0
})

const editDialogVisible = ref(false)
const editForm = reactive({
  trano: '',
  oldTzcode: '',
  newTzcode: ''
})

// 加载数据
const loadData = async () => {
  loading.value = true
  try {
    const params = {
      page: pagination.page,
      page_size: pagination.pageSize,
      ...searchForm
    }
    const res = await getAbnormalBets(params)
    if (res.code === 200) {
      tableData.value = res.data.list || []
      pagination.total = res.data.total || 0
    } else {
      ElMessage.error(res.msg || '加载失败')
    }
  } catch (error) {
    console.error('加载异常注单失败：', error)
    ElMessage.error('加载失败')
  } finally {
    loading.value = false
  }
}

// 重置搜索
const resetSearch = () => {
  searchForm.cpname = ''
  searchForm.username = ''
  searchForm.shijiancha = 130
  pagination.page = 1
  loadData()
}

// 撤单
const handleCancel = (row) => {
  ElMessageBox.confirm(
    `确定要撤销订单号为 ${row.trano} 的注单吗？撤单后将退回投注金额和洗码金额。`,
    '确认撤单',
    {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    }
  ).then(async () => {
    try {
      const res = await cancelBet(row.id)
      if (res.code === 200) {
        ElMessage.success('撤单成功')
        loadData()
      } else {
        ElMessage.error(res.msg || '撤单失败')
      }
    } catch (error) {
      console.error('撤单失败：', error)
      ElMessage.error('撤单失败')
    }
  }).catch(() => {})
}

// 打开修改对话框
const handleEdit = (row) => {
  editForm.trano = row.trano
  editForm.oldTzcode = row.tzcode
  editForm.newTzcode = row.tzcode
  editDialogVisible.value = true
}

// 提交修改
const submitEdit = async () => {
  if (!editForm.newTzcode) {
    ElMessage.warning('请输入新的投注号码')
    return
  }

  try {
    const res = await updateBetCode(editForm.trano, editForm.newTzcode)
    if (res.code === 200) {
      ElMessage.success('修改成功')
      editDialogVisible.value = false
      loadData()
    } else {
      ElMessage.error(res.msg || '修改失败')
    }
  } catch (error) {
    console.error('修改失败：', error)
    ElMessage.error('修改失败')
  }
}

// 格式化时间戳
const formatTime = (timestamp) => {
  if (!timestamp) return '-'
  const date = new Date(timestamp * 1000)
  return date.toLocaleString('zh-CN', { 
    year: 'numeric', 
    month: '2-digit', 
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
    hour12: false
  })
}

// 获取风险等级标签类型
const getRiskTagType = (level) => {
  const types = {
    high: 'danger',
    medium: 'warning',
    low: 'info'
  }
  return types[level] || 'info'
}

// 获取风险等级文本
const getRiskText = (level) => {
  const texts = {
    high: '高风险',
    medium: '中风险',
    low: '低风险'
  }
  return texts[level] || '-'
}

// 获取状态标签类型
const getStatusType = (status) => {
  const types = {
    0: 'warning',   // 未开奖
    1: 'success',   // 已中奖
    '-1': 'info',   // 未中奖
    '-2': 'danger'  // 已撤单
  }
  return types[status] || 'info'
}

// 获取状态文本
const getStatusText = (status) => {
  const texts = {
    0: '未开奖',
    1: '已中奖',
    '-1': '未中奖',
    '-2': '已撤单'
  }
  return texts[status] || '-'
}

onMounted(() => {
  loadData()
})
</script>

<style scoped lang="scss">
.bet-check-container {
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

.search-form {
  margin-bottom: 15px;
}
</style>
