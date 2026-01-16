<template>
  <div class="plays-container">
    <h2 class="page-title">玩法管理</h2>
    
    <el-card>
      <!-- 搜索表单 -->
      <el-form :inline="true" :model="searchForm" class="search-form">
        <el-form-item label="彩种类型">
          <el-input 
            v-model="searchForm.typeid" 
            placeholder="请输入彩种类型ID"
            clearable
            style="width: 200px"
          />
        </el-form-item>
        <el-form-item label="玩法ID">
          <el-input 
            v-model="searchForm.playid" 
            placeholder="请输入玩法ID"
            clearable
            style="width: 200px"
          />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="loadData">查询</el-button>
          <el-button @click="resetSearch">重置</el-button>
          <el-button type="success" @click="showBatchRateDialog">批量调整赔率</el-button>
        </el-form-item>
      </el-form>

      <!-- 表格 -->
      <el-table
        v-loading="loading"
        :data="tableData"
        border
        stripe
        style="width: 100%"
      >
        <el-table-column type="index" label="序号" width="60" align="center" />
        <el-table-column prop="id" label="ID" width="60" align="center" />
        <el-table-column prop="title" label="玩法名称" width="150" align="center" />
        <el-table-column prop="typeid" label="彩种类型" width="100" align="center" />
        <el-table-column prop="playid" label="玩法ID" width="100" align="center" />
        <el-table-column prop="rate" label="赔率" width="100" align="center">
          <template #default="{ row }">
            <span style="color: #409EFF; font-weight: bold">{{ row.rate }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="maxrate" label="最大赔率" width="100" align="center" />
        <el-table-column prop="minrate" label="最小赔率" width="100" align="center" />
        <el-table-column prop="maxzs" label="最高注数" width="100" align="center" />
        <el-table-column prop="minxf" label="最低消费" width="100" align="center" />
        <el-table-column prop="maxxf" label="最大投注" width="100" align="center" />
        <el-table-column prop="maxprize" label="最高奖金" width="100" align="center" />
        <el-table-column prop="isopen" label="状态" width="100" align="center">
          <template #default="{ row }">
            <el-switch
              v-model="row.isopen"
              :active-value="1"
              :inactive-value="0"
              @change="handleToggleStatus(row)"
            />
          </template>
        </el-table-column>
        <el-table-column label="操作" width="100" align="center" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" size="small" @click="handleEdit(row)">
              编辑
            </el-button>
          </template>
        </el-table-column>
      </el-table>

      <!-- 分页 -->
      <el-pagination
        v-model:current-page="pagination.page"
        v-model:page-size="pagination.pageSize"
        :page-sizes="[20, 50, 100, 200]"
        :total="pagination.total"
        layout="total, sizes, prev, pager, next, jumper"
        @size-change="loadData"
        @current-change="loadData"
        style="margin-top: 20px; text-align: right"
      />
    </el-card>

    <!-- 编辑玩法对话框 -->
    <el-dialog
      v-model="editDialogVisible"
      title="编辑玩法"
      width="600px"
    >
      <el-form :model="editForm" label-width="120px">
        <el-form-item label="玩法名称">
          <el-input v-model="editForm.title" disabled />
        </el-form-item>
        <el-form-item label="彩种类型">
          <el-input v-model="editForm.typeid" disabled />
        </el-form-item>
        <el-form-item label="玩法ID">
          <el-input v-model="editForm.playid" disabled />
        </el-form-item>
        <el-form-item label="赔率">
          <el-input-number 
            v-model="editForm.rate" 
            :precision="2"
            :step="0.1"
            :min="0"
            style="width: 100%"
          />
        </el-form-item>
        <el-form-item label="最大赔率">
          <el-input-number 
            v-model="editForm.maxrate" 
            :precision="3"
            :step="0.1"
            :min="0"
            style="width: 100%"
          />
        </el-form-item>
        <el-form-item label="最小赔率">
          <el-input-number 
            v-model="editForm.minrate" 
            :precision="3"
            :step="0.1"
            :min="0"
            style="width: 100%"
          />
        </el-form-item>
        <el-form-item label="最高注数">
          <el-input-number 
            v-model="editForm.maxzs" 
            :min="0"
            style="width: 100%"
          />
        </el-form-item>
        <el-form-item label="最低消费">
          <el-input-number 
            v-model="editForm.minxf" 
            :precision="2"
            :step="1"
            :min="0"
            style="width: 100%"
          />
        </el-form-item>
        <el-form-item label="最大投注金额">
          <el-input-number 
            v-model="editForm.maxxf" 
            :precision="2"
            :step="100"
            :min="0"
            style="width: 100%"
          />
        </el-form-item>
        <el-form-item label="最高奖金">
          <el-input-number 
            v-model="editForm.maxprize" 
            :precision="2"
            :step="100"
            :min="0"
            style="width: 100%"
          />
        </el-form-item>
        <el-form-item label="备注">
          <el-input 
            v-model="editForm.remark" 
            type="textarea"
            :rows="3"
            placeholder="请输入备注"
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="editDialogVisible = false">取消</el-button>
        <el-button type="primary" @click="submitEdit">确定</el-button>
      </template>
    </el-dialog>

    <!-- 批量调整赔率对话框 -->
    <el-dialog
      v-model="batchRateDialogVisible"
      title="批量调整赔率"
      width="500px"
    >
      <el-form :model="batchRateForm" label-width="120px">
        <el-form-item label="彩种类型ID">
          <el-input 
            v-model="batchRateForm.typeid" 
            placeholder="请输入彩种类型ID"
          />
        </el-form-item>
        <el-form-item label="赔率调整值">
          <el-input-number 
            v-model="batchRateForm.rate_change" 
            :precision="2"
            :step="0.1"
            placeholder="输入正数增加，负数减少"
            style="width: 100%"
          />
          <div style="color: #909399; font-size: 12px; margin-top: 5px">
            例如：输入 +0.5 表示所有玩法赔率增加0.5，输入 -0.5 表示减少0.5
          </div>
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="batchRateDialogVisible = false">取消</el-button>
        <el-button type="primary" @click="submitBatchRate">确定</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { getPlayList, updatePlay, togglePlayStatus, batchUpdateRate } from '@/api/play'

const loading = ref(false)
const tableData = ref([])

const searchForm = reactive({
  typeid: '',
  playid: ''
})

const pagination = reactive({
  page: 1,
  pageSize: 50,
  total: 0
})

const editDialogVisible = ref(false)
const editForm = reactive({
  id: null,
  title: '',
  typeid: '',
  playid: '',
  rate: 0,
  maxrate: 0,
  minrate: 0,
  maxzs: 0,
  minxf: 0,
  maxxf: 0,
  maxprize: 0,
  remark: ''
})

const batchRateDialogVisible = ref(false)
const batchRateForm = reactive({
  typeid: '',
  rate_change: 0
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
    const res = await getPlayList(params)
    if (res.code === 200) {
      tableData.value = res.data.list || []
      pagination.total = res.data.total || 0
    } else {
      ElMessage.error(res.msg || '加载失败')
    }
  } catch (error) {
    console.error('加载玩法列表失败：', error)
    ElMessage.error('加载失败')
  } finally {
    loading.value = false
  }
}

// 重置搜索
const resetSearch = () => {
  searchForm.typeid = ''
  searchForm.playid = ''
  pagination.page = 1
  loadData()
}

// 切换状态
const handleToggleStatus = async (row) => {
  try {
    const res = await togglePlayStatus(row.id)
    if (res.code === 200) {
      ElMessage.success('状态切换成功')
      loadData()
    } else {
      ElMessage.error(res.msg || '操作失败')
      // 恢复状态
      row.isopen = row.isopen === 1 ? 0 : 1
    }
  } catch (error) {
    console.error('切换状态失败：', error)
    ElMessage.error('操作失败')
    // 恢复状态
    row.isopen = row.isopen === 1 ? 0 : 1
  }
}

// 打开编辑对话框
const handleEdit = (row) => {
  editForm.id = row.id
  editForm.title = row.title
  editForm.typeid = row.typeid
  editForm.playid = row.playid
  editForm.rate = parseFloat(row.rate) || 0
  editForm.maxrate = parseFloat(row.maxrate) || 0
  editForm.minrate = parseFloat(row.minrate) || 0
  editForm.maxzs = parseInt(row.maxzs) || 0
  editForm.minxf = parseFloat(row.minxf) || 0
  editForm.maxxf = parseFloat(row.maxxf) || 0
  editForm.maxprize = parseFloat(row.maxprize) || 0
  editForm.remark = row.remark || ''
  editDialogVisible.value = true
}

// 提交编辑
const submitEdit = async () => {
  if (!editForm.id) {
    ElMessage.warning('参数错误')
    return
  }

  try {
    const data = {
      rate: editForm.rate,
      maxrate: editForm.maxrate,
      minrate: editForm.minrate,
      maxzs: editForm.maxzs,
      minxf: editForm.minxf,
      maxxf: editForm.maxxf,
      maxprize: editForm.maxprize,
      remark: editForm.remark
    }
    const res = await updatePlay(editForm.id, data)
    if (res.code === 200) {
      ElMessage.success('修改成功')
      editDialogVisible.value = false
      loadData()
    } else {
      ElMessage.error(res.msg || '修改失败')
    }
  } catch (error) {
    console.error('修改玩法失败：', error)
    ElMessage.error('修改失败')
  }
}

// 显示批量调整赔率对话框
const showBatchRateDialog = () => {
  batchRateForm.typeid = searchForm.typeid || ''
  batchRateForm.rate_change = 0
  batchRateDialogVisible.value = true
}

// 提交批量调整赔率
const submitBatchRate = async () => {
  if (!batchRateForm.typeid) {
    ElMessage.warning('请输入彩种类型ID')
    return
  }

  if (batchRateForm.rate_change === 0) {
    ElMessage.warning('请输入赔率调整值')
    return
  }

  ElMessageBox.confirm(
    `确定要将 ${batchRateForm.typeid} 彩种的所有玩法赔率调整 ${batchRateForm.rate_change > 0 ? '+' : ''}${batchRateForm.rate_change} 吗？`,
    '确认批量调整',
    {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    }
  ).then(async () => {
    try {
      const res = await batchUpdateRate(batchRateForm)
      if (res.code === 200) {
        ElMessage.success('批量调整成功')
        batchRateDialogVisible.value = false
        loadData()
      } else {
        ElMessage.error(res.msg || '批量调整失败')
      }
    } catch (error) {
      console.error('批量调整失败：', error)
      ElMessage.error('批量调整失败')
    }
  }).catch(() => {})
}

onMounted(() => {
  loadData()
})
</script>

<style scoped lang="scss">
.plays-container {
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
