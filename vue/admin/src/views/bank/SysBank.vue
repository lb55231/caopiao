<template>
  <div class="sysbank-container">
    <h2 class="page-title">提款银行管理</h2>
    
    <!-- 工具栏 -->
    <div class="toolbar">
      <el-button type="primary" @click="handleAdd">
        <el-icon><Plus /></el-icon>
        添加银行
      </el-button>
    </div>
    
    <!-- 列表 -->
    <el-table
      :data="list"
      border
      stripe
      style="width: 100%"
      v-loading="loading"
    >
      <el-table-column prop="id" label="ID" width="80" />
      <el-table-column prop="bankcode" label="银行代码" width="120" />
      <el-table-column prop="bankname" label="银行名称" width="200" />
      <el-table-column prop="banklogo" label="银行Logo" width="300">
        <template #default="{ row }">
          <div v-if="row.banklogo" class="logo-preview">
            <img :src="getImageUrl(row.banklogo)" alt="Logo" />
          </div>
          <span v-else class="text-muted">未设置</span>
        </template>
      </el-table-column>
      <el-table-column prop="listorder" label="排序" width="100" align="center" />
      <el-table-column prop="state" label="状态" width="100" align="center">
        <template #default="{ row }">
          <el-switch
            v-model="row.state"
            :active-value="1"
            :inactive-value="0"
            @change="handleToggleStatus(row)"
          />
        </template>
      </el-table-column>
      <el-table-column label="操作" width="200" align="center">
        <template #default="{ row }">
          <el-button type="primary" size="small" @click="handleEdit(row)">编辑</el-button>
          <el-button type="danger" size="small" @click="handleDelete(row)">删除</el-button>
        </template>
      </el-table-column>
    </el-table>
    
    <!-- 分页 -->
    <el-pagination
      v-model:current-page="page"
      v-model:page-size="pageSize"
      :total="total"
      :page-sizes="[10, 20, 50, 100]"
      layout="total, sizes, prev, pager, next, jumper"
      @current-change="loadList"
      @size-change="loadList"
      style="margin-top: 20px; justify-content: flex-end"
    />
    
    <!-- 添加/编辑对话框 -->
    <el-dialog
      v-model="dialogVisible"
      :title="dialogTitle"
      width="600px"
      @close="resetForm"
    >
      <el-form :model="form" label-width="100px" ref="formRef">
        <el-form-item label="银行代码" required>
          <el-input v-model="form.bankcode" placeholder="请输入银行代码（如：102）" />
        </el-form-item>
        
        <el-form-item label="银行名称" required>
          <el-input v-model="form.bankname" placeholder="请输入银行名称" />
        </el-form-item>
        
        <el-form-item label="银行Logo">
          <div class="upload-wrapper">
            <div v-if="form.banklogo" class="logo-preview-large">
              <img :src="getImageUrl(form.banklogo)" alt="Logo" />
              <el-button type="danger" size="small" @click="form.banklogo = ''">删除</el-button>
            </div>
            
            <el-upload
              v-else
              :action="uploadAction"
              :headers="uploadHeaders"
              :show-file-list="false"
              :on-success="handleLogoSuccess"
              :on-error="handleLogoError"
              :before-upload="beforeLogoUpload"
              accept="image/*"
            >
              <el-button type="primary" size="small">上传Logo</el-button>
            </el-upload>
            
            <el-input
              v-model="form.banklogo"
              placeholder="或输入Logo URL"
              style="width: 100%; margin-top: 10px"
              size="small"
            />
          </div>
        </el-form-item>
        
        <el-form-item label="图片背景色">
          <el-input v-model="form.imgbg" placeholder="如：#FF5722" />
        </el-form-item>
        
        <el-form-item label="排序">
          <el-input-number v-model="form.listorder" :min="0" :max="9999" />
          <span class="form-tip">数字越小越靠前</span>
        </el-form-item>
        
        <el-form-item label="状态">
          <el-radio-group v-model="form.state">
            <el-radio :value="1">启用</el-radio>
            <el-radio :value="0">禁用</el-radio>
          </el-radio-group>
        </el-form-item>
      </el-form>
      
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleSubmit" :loading="submitting">确定</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus } from '@element-plus/icons-vue'
import { getSysBankList, addSysBank, updateSysBank, deleteSysBank, toggleSysBankStatus } from '@/api/bank'
import { getImageUrl } from '@/utils/image'
import { useAdminStore } from '@/stores/admin'

const adminStore = useAdminStore()
const loading = ref(false)
const submitting = ref(false)
const list = ref([])
const total = ref(0)
const page = ref(1)
const pageSize = ref(20)

// 对话框
const dialogVisible = ref(false)
const dialogTitle = ref('添加银行')
const formRef = ref(null)
const form = reactive({
  id: null,
  bankcode: '',
  bankname: '',
  banklogo: '',
  imgbg: '',
  state: 1,
  listorder: 0
})

// 上传配置
const uploadAction = '/adminapi/upload/image'
const uploadHeaders = {
  'Authorization': `Bearer ${adminStore.token}`,
  'Token': adminStore.token
}

// 加载列表
const loadList = async () => {
  try {
    loading.value = true
    const res = await getSysBankList({
      page: page.value,
      page_size: pageSize.value
    })
    if (res.code === 200) {
      list.value = res.data.list
      total.value = res.data.total
    }
  } catch (error) {
    ElMessage.error('加载失败：' + error.message)
  } finally {
    loading.value = false
  }
}

// 添加
const handleAdd = () => {
  dialogTitle.value = '添加银行'
  dialogVisible.value = true
}

// 编辑
const handleEdit = (row) => {
  dialogTitle.value = '编辑银行'
  Object.assign(form, row)
  dialogVisible.value = true
}

// 提交
const handleSubmit = async () => {
  if (!form.bankcode || !form.bankname) {
    ElMessage.error('请填写银行代码和银行名称')
    return
  }
  
  try {
    submitting.value = true
    const apiFunc = form.id ? updateSysBank : addSysBank
    const res = await apiFunc(form)
    
    if (res.code === 200) {
      ElMessage.success(form.id ? '更新成功' : '添加成功')
      dialogVisible.value = false
      loadList()
    } else {
      ElMessage.error(res.msg || '操作失败')
    }
  } catch (error) {
    ElMessage.error('操作失败：' + error.message)
  } finally {
    submitting.value = false
  }
}

// 删除
const handleDelete = (row) => {
  ElMessageBox.confirm('确定要删除这个银行吗？', '提示', {
    confirmButtonText: '确定',
    cancelButtonText: '取消',
    type: 'warning'
  }).then(async () => {
    try {
      const res = await deleteSysBank(row.id)
      if (res.code === 200) {
        ElMessage.success('删除成功')
        loadList()
      } else {
        ElMessage.error(res.msg || '删除失败')
      }
    } catch (error) {
      ElMessage.error('删除失败：' + error.message)
    }
  }).catch(() => {})
}

// 切换状态
const handleToggleStatus = async (row) => {
  try {
    const res = await toggleSysBankStatus({ id: row.id })
    if (res.code === 200) {
      ElMessage.success('状态更新成功')
    } else {
      // 恢复原状态
      row.state = row.state == 1 ? 0 : 1
      ElMessage.error(res.msg || '状态更新失败')
    }
  } catch (error) {
    // 恢复原状态
    row.state = row.state == 1 ? 0 : 1
    ElMessage.error('状态更新失败：' + error.message)
  }
}

// Logo上传
const beforeLogoUpload = (file) => {
  const isImage = file.type.startsWith('image/')
  const isLt2M = file.size / 1024 / 1024 < 2

  if (!isImage) {
    ElMessage.error('只能上传图片文件！')
    return false
  }
  if (!isLt2M) {
    ElMessage.error('图片大小不能超过2MB！')
    return false
  }
  return true
}

const handleLogoSuccess = (response) => {
  if (response.code === 200) {
    form.banklogo = response.data.url
    ElMessage.success('上传成功')
  } else {
    ElMessage.error(response.msg || '上传失败')
  }
}

const handleLogoError = () => {
  ElMessage.error('上传失败，请重试')
}

// 重置表单
const resetForm = () => {
  form.id = null
  form.bankcode = ''
  form.bankname = ''
  form.banklogo = ''
  form.imgbg = ''
  form.state = 1
  form.listorder = 0
}

// 页面加载时获取数据
onMounted(() => {
  loadList()
})
</script>

<style scoped lang="scss">
.sysbank-container {
  padding: 20px;
}

.page-title {
  font-size: 24px;
  font-weight: 500;
  margin-bottom: 20px;
  color: #303133;
}

.toolbar {
  margin-bottom: 20px;
}

.logo-preview {
  img {
    max-width: 200px;
    max-height: 40px;
    object-fit: contain;
  }
}

.logo-preview-large {
  text-align: center;
  margin-bottom: 10px;
  
  img {
    max-width: 200px;
    max-height: 60px;
    object-fit: contain;
    display: block;
    margin: 0 auto 10px;
  }
}

.upload-wrapper {
  width: 100%;
}

.form-tip {
  margin-left: 10px;
  font-size: 12px;
  color: #909399;
}

.text-muted {
  color: #909399;
  font-size: 12px;
}
</style>

