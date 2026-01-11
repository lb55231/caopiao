<template>
  <div class="lottery-container">
    <h2 class="page-title">开奖管理</h2>
    
    <!-- 筛选表单 -->
    <el-card class="filter-card mb-20">
      <el-form :inline="true" :model="searchForm" class="search-form">
        <el-form-item label="彩种">
          <el-select v-model="searchForm.cpname" placeholder="请选择彩种" clearable style="width: 150px;">
            <el-option label="全部" value="" />
            <el-option 
              v-for="lottery in lotteryList" 
              :key="lottery.name"
              :label="lottery.title"
              :value="lottery.name"
            />
          </el-select>
        </el-form-item>
        
        <el-form-item label="期号">
          <el-input v-model="searchForm.expect" placeholder="请输入期号" clearable style="width: 150px;" />
        </el-form-item>
        
        <el-form-item label="开奖日期">
          <el-date-picker
            v-model="searchForm.dateRange"
            type="daterange"
            range-separator="-"
            start-placeholder="开始日期"
            end-placeholder="结束日期"
            format="YYYY-MM-DD"
            value-format="YYYY-MM-DD"
            style="width: 240px;"
          />
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
    
    <!-- 操作栏和列表 -->
    <el-card>
      <div class="toolbar">
        <el-button type="primary" @click="handleAdd">
          <el-icon><Plus /></el-icon>
          手动开奖
        </el-button>
        <el-button type="success" @click="handleRefresh">
          <el-icon><Refresh /></el-icon>
          刷新
        </el-button>
      </div>
      
      <el-table 
        v-loading="loading"
        :data="tableData" 
        border 
        stripe 
        style="width: 100%; margin-top: 20px;"
      >
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="cptitle" label="彩种" width="120" />
        <el-table-column prop="expect" label="期号" width="150" />
        <el-table-column prop="opencode" label="开奖号码" width="200">
          <template #default="{ row }">
            <el-tag v-for="(num, index) in row.opencode.split(',')" :key="index" class="number-tag">
              {{ num }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="opentime_str" label="开奖时间" width="180" />
        <el-table-column prop="source" label="来源" width="100" />
        <el-table-column prop="isdraw" label="结算状态" width="100">
          <template #default="{ row }">
            <el-tag :type="row.isdraw == 1 ? 'success' : (row.isdraw == -1 ? 'info' : 'warning')">
              {{ row.isdraw == 1 ? '已结算' : (row.isdraw == -1 ? '无投注' : '待结算') }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="150" fixed="right">
          <template #default="{ row }">
            <el-button type="danger" size="small" @click="handleDelete(row)" link v-if="row.isdraw == 0">
              <el-icon><Delete /></el-icon>
              删除
            </el-button>
            <el-text v-else type="info" size="small">已结算</el-text>
          </template>
        </el-table-column>
      </el-table>
      
      <div class="pagination-container">
        <el-pagination
          background
          layout="total, sizes, prev, pager, next, jumper"
          :total="total"
          :page-size="pageSize"
          :current-page="currentPage"
          :page-sizes="[10, 20, 50, 100]"
          @size-change="handleSizeChange"
          @current-change="handlePageChange"
        />
      </div>
    </el-card>
    
    <!-- 手动开奖对话框 -->
    <el-dialog 
      v-model="dialogVisible" 
      title="手动开奖"
      width="600px"
    >
      <el-form 
        ref="formRef"
        :model="form" 
        :rules="rules"
        label-width="100px"
      >
        <el-form-item label="彩种" prop="cpname">
          <el-select v-model="form.cpname" placeholder="请选择彩种" @change="handleLotteryChange">
            <el-option 
              v-for="lottery in lotteryList" 
              :key="lottery.name"
              :label="lottery.title"
              :value="lottery.name"
            />
          </el-select>
        </el-form-item>
        
        <el-form-item label="期号" prop="expect">
          <el-input v-model="form.expect" placeholder="如: 20260109001" />
        </el-form-item>
        
        <el-form-item label="开奖号码" prop="opencode">
          <el-input v-model="form.opencode" placeholder="如: 1,2,3 或 1,2,3,4,5 (逗号分隔)" />
          <div class="tip">{{ opencodeTip }}</div>
        </el-form-item>
        
        <el-form-item label="开奖时间">
          <el-date-picker
            v-model="form.opentime"
            type="datetime"
            placeholder="选择开奖时间"
            format="YYYY-MM-DD HH:mm:ss"
            value-format="YYYY-MM-DD HH:mm:ss"
            style="width: 100%;"
          />
        </el-form-item>
        
        <el-form-item label="备注">
          <el-input v-model="form.remarks" type="textarea" :rows="3" placeholder="请输入备注" />
        </el-form-item>
      </el-form>
      
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleSubmit" :loading="submitting">
          确定开奖
        </el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus, Refresh, Delete, Search } from '@element-plus/icons-vue'
import { getLotteryResults, addLotteryResult, deleteLotteryResult, getLotteryTypes } from '@/api/lottery'

const loading = ref(false)
const submitting = ref(false)
const dialogVisible = ref(false)
const formRef = ref(null)

// 彩票列表
const lotteryList = ref([])

// 搜索表单
const searchForm = reactive({
  cpname: '',
  expect: '',
  dateRange: []
})

// 表格数据
const tableData = ref([])
const total = ref(0)
const currentPage = ref(1)
const pageSize = ref(20)

// 表单数据
const form = reactive({
  cpname: '',
  expect: '',
  opencode: '',
  opentime: '',
  remarks: ''
})

// 开奖号码提示
const opencodeTip = computed(() => {
  const lottery = lotteryList.value.find(item => item.name === form.cpname)
  if (!lottery) return '请先选择彩种'
  
  const tips = {
    'k3': 'K3类：3个号码，范围1-6，如: 1,2,3',
    'ssc': '时时彩类：5个号码，范围0-9，如: 1,2,3,4,5',
    'fc3d': '3D类：3个号码，范围0-9，如: 1,2,3',
    'pk10': 'PK10类：10个号码，范围1-10，如: 3,7,1,9,2,5,8,4,10,6',
    '11x5': '11选5类：5个号码，范围1-11，如: 1,3,5,7,9'
  }
  
  return tips[lottery.typeid] || '请输入开奖号码，逗号分隔'
})

// 表单验证规则
const rules = {
  cpname: [{ required: true, message: '请选择彩种', trigger: 'change' }],
  expect: [{ required: true, message: '请输入期号', trigger: 'blur' }],
  opencode: [{ required: true, message: '请输入开奖号码', trigger: 'blur' }]
}

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
      cpname: searchForm.cpname,
      expect: searchForm.expect,
      page: currentPage.value,
      page_size: pageSize.value
    }
    
    if (searchForm.dateRange && searchForm.dateRange.length === 2) {
      params.start_date = searchForm.dateRange[0]
      params.end_date = searchForm.dateRange[1]
    }
    
    const res = await getLotteryResults(params)
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

// 重置
const handleReset = () => {
  searchForm.cpname = ''
  searchForm.expect = ''
  searchForm.dateRange = []
  currentPage.value = 1
  loadList()
}

// 刷新
const handleRefresh = () => {
  loadList()
}

// 分页
const handleSizeChange = (val) => {
  pageSize.value = val
  loadList()
}

const handlePageChange = (val) => {
  currentPage.value = val
  loadList()
}

// 添加
const handleAdd = () => {
  Object.assign(form, {
    cpname: '',
    expect: '',
    opencode: '',
    opentime: '',
    remarks: ''
  })
  dialogVisible.value = true
}

// 彩种变化
const handleLotteryChange = () => {
  form.opencode = ''
}

// 提交
const handleSubmit = async () => {
  if (!formRef.value) return
  
  await formRef.value.validate(async (valid) => {
    if (!valid) return
    
    submitting.value = true
    
    try {
      const res = await addLotteryResult(form)
      if (res.code === 200) {
        ElMessage.success('开奖成功')
        dialogVisible.value = false
        loadList()
      } else {
        ElMessage.error(res.msg || '开奖失败')
      }
    } catch (error) {
      ElMessage.error('开奖失败: ' + error.message)
    } finally {
      submitting.value = false
    }
  })
}

// 删除
const handleDelete = async (row) => {
  try {
    await ElMessageBox.confirm('确定要删除该开奖记录吗？删除后不可恢复！', '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })
    
    const res = await deleteLotteryResult({ id: row.id })
    if (res.code === 200) {
      ElMessage.success('删除成功')
      loadList()
    } else {
      ElMessage.error(res.msg || '删除失败')
    }
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('删除失败')
    }
  }
}

onMounted(() => {
  loadLotteryList()
  loadList()
})
</script>

<style scoped lang="scss">
.lottery-container {
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

.mb-20 {
  margin-bottom: 20px;
}

.filter-card {
  .search-form {
    margin-bottom: 0;
  }
}

.toolbar {
  margin-bottom: 10px;
}

.number-tag {
  margin-right: 5px;
  font-weight: bold;
  font-size: 14px;
}

.tip {
  font-size: 12px;
  color: #999;
  margin-top: 5px;
}

.pagination-container {
  margin-top: 20px;
  display: flex;
  justify-content: flex-end;
}
</style>

