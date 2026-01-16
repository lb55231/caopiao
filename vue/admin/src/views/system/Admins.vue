<template>
  <div class="admins-container">
    <el-card class="box-card">
      <template #header>
        <div class="card-header">
          <span>管理员管理</span>
          <el-button type="primary" @click="handleAdd">
            <el-icon><Plus /></el-icon>
            新增管理员
          </el-button>
        </div>
      </template>

      <!-- 数据表格 -->
      <el-table :data="tableData" style="width: 100%" v-loading="loading">
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="username" label="用户名" width="150" />
        <el-table-column prop="groupname" label="所属组" width="120" />
        <el-table-column prop="email" label="邮箱" min-width="180" />
        <el-table-column prop="safecode" label="安全码" width="100" />
        <el-table-column label="状态" width="100">
          <template #default="{ row }">
            <el-tag :type="row.islock == 0 ? 'success' : 'danger'">
              {{ row.islock == 0 ? '正常' : '锁定' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="最后登录" width="180">
          <template #default="{ row }">
            {{ formatTime(row.logintime) }}
          </template>
        </el-table-column>
        <el-table-column label="登录IP" width="140">
          <template #default="{ row }">
            {{ row.loginip || '-' }}
          </template>
        </el-table-column>
        <el-table-column label="操作" width="280" fixed="right">
          <template #default="{ row }">
            <el-button link type="primary" @click="handleEdit(row)">
              <el-icon><Edit /></el-icon>
              编辑
            </el-button>
            <el-button link :type="row.islock == 0 ? 'warning' : 'success'" @click="handleToggleLock(row)">
              <el-icon><Lock /></el-icon>
              {{ row.islock == 0 ? '锁定' : '解锁' }}
            </el-button>
            <el-button link type="danger" @click="handleDelete(row)">
              <el-icon><Delete /></el-icon>
              删除
            </el-button>
          </template>
        </el-table-column>
      </el-table>

      <!-- 分页 -->
      <el-pagination
        v-model:current-page="pagination.page"
        v-model:page-size="pagination.pageSize"
        :total="pagination.total"
        :page-sizes="[10, 20, 50, 100]"
        layout="total, sizes, prev, pager, next, jumper"
        @size-change="handleSizeChange"
        @current-change="handleCurrentChange"
        style="margin-top: 20px; justify-content: flex-end"
      />
    </el-card>

    <!-- 新增/编辑对话框 -->
    <el-dialog
      v-model="dialogVisible"
      :title="dialogTitle"
      width="500px"
      @close="handleDialogClose"
    >
      <el-form
        ref="formRef"
        :model="form"
        :rules="rules"
        label-width="100px"
      >
        <el-form-item label="用户名" prop="username">
          <el-input 
            v-model="form.username" 
            placeholder="请输入用户名（4-16位字母数字）"
            :disabled="!!form.id"
          />
        </el-form-item>
        <el-form-item label="密码" :prop="form.id ? '' : 'password'">
          <el-input 
            v-model="form.password" 
            type="password"
            :placeholder="form.id ? '不修改请留空' : '请输入密码（6-16位）'"
            show-password
          />
        </el-form-item>
        <el-form-item label="邮箱" prop="email">
          <el-input v-model="form.email" placeholder="请输入邮箱" />
        </el-form-item>
        <el-form-item label="管理组" prop="groupid">
          <el-select v-model="form.groupid" placeholder="请选择管理组" style="width: 100%">
            <el-option
              v-for="group in groupList"
              :key="group.groupid"
              :label="group.groupname"
              :value="group.groupid"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="安全码" prop="safecode">
          <el-input-number 
            v-model="form.safecode" 
            :min="1000"
            :max="9999999"
            :controls="false"
            placeholder="4-7位数字"
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleSubmit" :loading="submitting">
          确定
        </el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus, Edit, Delete, Lock } from '@element-plus/icons-vue'
import { getAdminList, addAdmin, updateAdmin, deleteAdmin, toggleAdminLock, getAdminGroups } from '@/api/admin'

// 表格数据
const tableData = ref([])
const loading = ref(false)

// 分页
const pagination = reactive({
  page: 1,
  pageSize: 20,
  total: 0
})

// 对话框
const dialogVisible = ref(false)
const dialogTitle = ref('新增管理员')
const formRef = ref()
const submitting = ref(false)

// 管理组列表
const groupList = ref([])

// 表单数据
const form = reactive({
  id: null,
  username: '',
  password: '',
  email: '',
  groupid: 2,
  safecode: 1234
})

// 表单验证规则
const rules = {
  username: [
    { required: true, message: '请输入用户名', trigger: 'blur' },
    { pattern: /^\w{4,16}$/, message: '用户名为4-16位字母数字组合', trigger: 'blur' }
  ],
  password: [
    { required: true, message: '请输入密码', trigger: 'blur' },
    { pattern: /^\w{6,16}$/, message: '密码为6-16位数字字母组合', trigger: 'blur' }
  ],
  groupid: [
    { required: true, message: '请选择管理组', trigger: 'change' }
  ],
  safecode: [
    { required: true, message: '请输入安全码', trigger: 'blur' }
  ]
}

// 加载管理组列表
const loadGroups = async () => {
  try {
    const res = await getAdminGroups()
    if (res.code === 200) {
      groupList.value = res.data.list || []
    }
  } catch (error) {
    console.error('加载管理组失败:', error)
  }
}

// 加载数据
const loadData = async () => {
  loading.value = true
  try {
    const params = {
      page: pagination.page,
      page_size: pagination.pageSize
    }
    
    const res = await getAdminList(params)
    
    if (res.code === 200) {
      tableData.value = res.data.list || []
      pagination.total = res.data.total || 0
    } else {
      ElMessage.error(res.msg || '加载失败')
    }
  } catch (error) {
    ElMessage.error('加载失败')
    console.error(error)
  } finally {
    loading.value = false
  }
}

// 分页
const handleSizeChange = () => {
  loadData()
}

const handleCurrentChange = () => {
  loadData()
}

// 新增
const handleAdd = () => {
  dialogTitle.value = '新增管理员'
  resetForm()
  dialogVisible.value = true
}

// 编辑
const handleEdit = (row) => {
  dialogTitle.value = '编辑管理员'
  form.id = row.id
  form.username = row.username
  form.password = ''
  form.email = row.email || ''
  form.groupid = row.groupid
  form.safecode = row.safecode
  dialogVisible.value = true
}

// 删除
const handleDelete = async (row) => {
  try {
    await ElMessageBox.confirm('确定要删除这个管理员吗？删除后无法恢复！', '警告', {
      type: 'warning',
      confirmButtonText: '确定删除',
      cancelButtonText: '取消'
    })
    
    const res = await deleteAdmin(row.id)
    
    if (res.code === 200) {
      ElMessage.success('删除成功')
      loadData()
    } else {
      ElMessage.error(res.msg || '删除失败')
    }
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('删除失败')
      console.error(error)
    }
  }
}

// 锁定/解锁
const handleToggleLock = async (row) => {
  try {
    const action = row.islock == 0 ? '锁定' : '解锁'
    await ElMessageBox.confirm(`确定要${action}该管理员吗？`, '提示', {
      type: 'warning'
    })
    
    const res = await toggleAdminLock({ id: row.id })
    
    if (res.code === 200) {
      ElMessage.success(`${action}成功`)
      loadData()
    } else {
      ElMessage.error(res.msg || `${action}失败`)
    }
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('操作失败')
      console.error(error)
    }
  }
}

// 提交表单
const handleSubmit = async () => {
  try {
    await formRef.value.validate()
    
    submitting.value = true
    
    let res
    if (form.id) {
      // 编辑
      const data = {
        id: form.id,
        email: form.email,
        groupid: form.groupid,
        safecode: form.safecode
      }
      if (form.password) {
        data.password = form.password
      }
      res = await updateAdmin(data)
    } else {
      // 新增
      res = await addAdmin({
        username: form.username,
        password: form.password,
        email: form.email,
        groupid: form.groupid,
        safecode: form.safecode
      })
    }
    
    if (res.code === 200) {
      ElMessage.success(form.id ? '更新成功' : '添加成功')
      dialogVisible.value = false
      loadData()
    } else {
      ElMessage.error(res.msg || '操作失败')
    }
  } catch (error) {
    console.error(error)
  } finally {
    submitting.value = false
  }
}

// 关闭对话框
const handleDialogClose = () => {
  resetForm()
}

// 重置表单
const resetForm = () => {
  form.id = null
  form.username = ''
  form.password = ''
  form.email = ''
  form.groupid = 2
  form.safecode = 1234
  formRef.value?.clearValidate()
}

// 格式化时间
const formatTime = (timestamp) => {
  if (!timestamp) return '-'
  const date = new Date(timestamp * 1000)
  return date.toLocaleString('zh-CN')
}

onMounted(() => {
  loadGroups()
  loadData()
})
</script>

<style scoped lang="scss">
.admins-container {
  padding: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
</style>
