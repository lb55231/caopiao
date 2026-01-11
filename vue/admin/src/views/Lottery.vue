<template>
  <div class="lottery-container">
    <h2 class="page-title">开奖管理</h2>
    
    <!-- 操作按钮 -->
    <el-card class="mb-20">
      <el-button type="primary" @click="handleOpenDialog">
        <el-icon><Plus /></el-icon>
        添加开奖记录
      </el-button>
    </el-card>
    
    <!-- 开奖列表 -->
    <el-card>
      <el-table
        v-loading="loading"
        :data="lotteryList"
        border
        stripe
        style="width: 100%"
      >
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="title" label="彩种" width="120" />
        <el-table-column prop="expect" label="期号" width="150" />
        <el-table-column prop="opencode" label="开奖号码" width="150">
          <template #default="{ row }">
            <el-tag v-for="(num, index) in row.opencode.split(',')" :key="index" type="primary" class="mr-5">
              {{ num }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="opentime" label="开奖时间" width="180">
          <template #default="{ row }">
            {{ formatTime(row.opentime) }}
          </template>
        </el-table-column>
        <el-table-column prop="remarks" label="备注" />
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
    
    <!-- 添加开奖对话框 -->
    <el-dialog v-model="dialogVisible" title="添加开奖记录" width="600px">
      <el-form :model="lotteryForm" label-width="100px">
        <el-form-item label="彩种">
          <el-select v-model="lotteryForm.cpname" placeholder="请选择彩种" style="width: 100%;">
            <el-option label="河北快3" value="hebk3" />
            <el-option label="快3" value="k3" />
          </el-select>
        </el-form-item>
        <el-form-item label="期号">
          <el-input v-model="lotteryForm.expect" placeholder="请输入期号" />
        </el-form-item>
        <el-form-item label="开奖号码">
          <el-input v-model="lotteryForm.opencode" placeholder="例如: 1,2,3" />
        </el-form-item>
        <el-form-item label="备注">
          <el-input 
            v-model="lotteryForm.remarks" 
            type="textarea" 
            :rows="3"
            placeholder="请输入备注（可选）"
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="dialogVisible = false">取消</el-button>
          <el-button type="primary" @click="handleConfirmAdd">确定</el-button>
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { getLotteryList, addLottery } from '@/api/admin'
import { ElMessage } from 'element-plus'

const loading = ref(false)
const lotteryList = ref([])
const total = ref(0)
const currentPage = ref(1)
const pageSize = ref(10)

const dialogVisible = ref(false)
const lotteryForm = ref({
  cpname: '',
  expect: '',
  opencode: '',
  remarks: ''
})

const loadList = async () => {
  loading.value = true
  try {
    // Mock data for now
    lotteryList.value = []
    total.value = 0
    
    // 待后端API准备好后使用以下代码
    /*
    const res = await getLotteryList({
      page: currentPage.value,
      pageSize: pageSize.value
    })
    if (res.code === 200) {
      lotteryList.value = res.data.list || []
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

const handleOpenDialog = () => {
  lotteryForm.value = {
    cpname: '',
    expect: '',
    opencode: '',
    remarks: ''
  }
  dialogVisible.value = true
}

const handleConfirmAdd = async () => {
  if (!lotteryForm.value.cpname || !lotteryForm.value.expect || !lotteryForm.value.opencode) {
    ElMessage.warning('请填写完整信息')
    return
  }
  
  try {
    // Mock success
    ElMessage.success('添加成功')
    dialogVisible.value = false
    loadList()
    
    // 待后端API准备好后使用以下代码
    /*
    const res = await addLottery(lotteryForm.value)
    if (res.code === 200) {
      ElMessage.success('添加成功')
      dialogVisible.value = false
      loadList()
    } else {
      ElMessage.error(res.msg || '添加失败')
    }
    */
  } catch (error) {
    ElMessage.error('添加失败: ' + error.message)
  }
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

.mr-5 {
  margin-right: 5px;
}
</style>

