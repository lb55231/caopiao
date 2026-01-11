<template>
  <div class="members-container">
    <h2 class="page-title">会员管理</h2>
    
    <!-- 搜索表单 -->
    <el-card class="search-card mb-20">
      <el-form :inline="true" :model="searchForm" class="search-form">
        <el-form-item label="用户名">
          <el-input v-model="searchForm.username" placeholder="请输入用户名" clearable />
        </el-form-item>
        <el-form-item label="邀请码">
          <el-input v-model="searchForm.invitecode" placeholder="请输入邀请码" clearable />
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="searchForm.status" placeholder="请选择状态" clearable>
            <el-option label="全部" value="" />
            <el-option label="正常" :value="0" />
            <el-option label="锁定" :value="1" />
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
    
    <!-- 会员列表 -->
    <el-card>
      <el-table
        v-loading="loading"
        :data="memberList"
        border
        stripe
        style="width: 100%"
      >
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="username" label="用户名" width="150" />
        <el-table-column prop="nickname" label="昵称" width="150" />
        <el-table-column prop="balance" label="余额" width="120">
          <template #default="{ row }">
            <span style="color: #409EFF; font-weight: bold;">¥ {{ row.balance }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="invitecode" label="邀请码" width="120" />
        <el-table-column prop="islock" label="状态" width="100">
          <template #default="{ row }">
            <el-tag :type="row.islock == 0 ? 'success' : 'danger'">
              {{ row.islock == 0 ? '正常' : '锁定' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="regtime" label="注册时间" width="180">
          <template #default="{ row }">
            {{ formatTime(row.regtime) }}
          </template>
        </el-table-column>
        <el-table-column label="操作" width="250" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" size="small" @click="handleAdjustBalance(row)">
              调整余额
            </el-button>
            <el-button 
              :type="row.islock == 0 ? 'danger' : 'success'" 
              size="small" 
              @click="handleToggleLock(row)"
            >
              {{ row.islock == 0 ? '锁定' : '解锁' }}
            </el-button>
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
    
    <!-- 调整余额对话框 -->
    <el-dialog v-model="balanceDialogVisible" title="调整余额" width="500px">
      <el-form :model="balanceForm" label-width="100px">
        <el-form-item label="用户名">
          <el-input v-model="balanceForm.username" disabled />
        </el-form-item>
        <el-form-item label="当前余额">
          <el-input v-model="balanceForm.currentBalance" disabled />
        </el-form-item>
        <el-form-item label="操作类型">
          <el-radio-group v-model="balanceForm.type">
            <el-radio :value="'add'">增加</el-radio>
            <el-radio :value="'minus'">减少</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="金额">
          <el-input-number 
            v-model="balanceForm.amount" 
            :min="0.01" 
            :precision="2" 
            :step="1"
            placeholder="请输入金额"
            style="width: 100%;"
          />
        </el-form-item>
        <el-form-item label="备注">
          <el-input 
            v-model="balanceForm.remark" 
            type="textarea" 
            :rows="3"
            placeholder="请输入备注（可选）"
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="balanceDialogVisible = false">取消</el-button>
          <el-button type="primary" @click="handleConfirmAdjust">确定</el-button>
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { getMemberList, adjustBalance } from '@/api/admin'
import { ElMessage, ElMessageBox } from 'element-plus'

const loading = ref(false)
const memberList = ref([])
const total = ref(0)
const currentPage = ref(1)
const pageSize = ref(10)

const searchForm = ref({
  username: '',
  invitecode: '',
  status: ''
})

const balanceDialogVisible = ref(false)
const balanceForm = ref({
  userId: '',
  username: '',
  currentBalance: '',
  type: 'add',
  amount: 0,
  remark: ''
})

const loadList = async () => {
  loading.value = true
  try {
    // Mock data for now
    memberList.value = [
      {
        id: 1,
        username: 'testuser04',
        nickname: '测试用户04',
        balance: '100.00',
        invitecode: 'ABC123',
        islock: 0,
        regtime: 1704067200
      }
    ]
    total.value = 1
    
    // 待后端API准备好后使用以下代码
    /*
    const res = await getMemberList({
      page: currentPage.value,
      pageSize: pageSize.value,
      ...searchForm.value
    })
    if (res.code === 200) {
      memberList.value = res.data.list || []
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
    invitecode: '',
    status: ''
  }
  handleSearch()
}

const handleAdjustBalance = (row) => {
  balanceForm.value = {
    userId: row.id,
    username: row.username,
    currentBalance: row.balance,
    type: 'add',
    amount: 0,
    remark: ''
  }
  balanceDialogVisible.value = true
}

const handleConfirmAdjust = async () => {
  if (!balanceForm.value.amount || balanceForm.value.amount <= 0) {
    ElMessage.warning('请输入有效金额')
    return
  }
  
  try {
    // Mock success
    ElMessage.success('余额调整成功')
    balanceDialogVisible.value = false
    loadList()
    
    // 待后端API准备好后使用以下代码
    /*
    const res = await adjustBalance({
      userId: balanceForm.value.userId,
      type: balanceForm.value.type,
      amount: balanceForm.value.amount,
      remark: balanceForm.value.remark
    })
    if (res.code === 200) {
      ElMessage.success('余额调整成功')
      balanceDialogVisible.value = false
      loadList()
    } else {
      ElMessage.error(res.msg || '操作失败')
    }
    */
  } catch (error) {
    ElMessage.error('操作失败: ' + error.message)
  }
}

const handleToggleLock = async (row) => {
  const action = row.islock == 0 ? '锁定' : '解锁'
  try {
    await ElMessageBox.confirm(`确定要${action}该用户吗？`, '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })
    
    // Mock success
    ElMessage.success(`${action}成功`)
    loadList()
    
    // 待后端API准备好后调用API
  } catch {
    // 取消
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
.members-container {
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

