<template>
  <div class="lottery-types-container">
    <h2 class="page-title">彩种管理</h2>
    
    <!-- 操作栏 -->
    <el-card class="mb-20">
      <el-button type="primary" @click="handleAdd">
        <el-icon><Plus /></el-icon>
        添加彩票
      </el-button>
      <el-button type="success" @click="handleRefresh">
        <el-icon><Refresh /></el-icon>
        刷新
      </el-button>
      
      <el-radio-group v-model="currentTypeId" @change="handleTypeChange" class="ml-20">
        <el-radio-button :value="''">全部</el-radio-button>
        <el-radio-button 
          v-for="(label, key) in lotteryCategories" 
          :key="key"
          :value="key"
        >
          {{ label }}
        </el-radio-button>
      </el-radio-group>
    </el-card>
    
    <!-- 彩种列表 -->
    <el-card>
      <el-form :model="listForm" @submit.prevent="handleSaveOrder">
        <el-table
          v-loading="loading"
          :data="lotteryList"
          border
          stripe
          style="width: 100%"
        >
          <el-table-column type="selection" width="55" />
          <el-table-column label="排序" width="80">
            <template #default="{ row }">
              <el-input-number 
                v-model="row.listorder" 
                :min="0" 
                size="small"
                style="width: 70px;"
              />
            </template>
          </el-table-column>
          <el-table-column prop="id" label="ID" width="60" />
          <el-table-column label="图标" width="80" align="center">
            <template #default="{ row }">
              <img 
                :src="getImageUrl(row.logo)" 
                :alt="row.title"
                class="lottery-logo"
                @error="handleImageError"
              />
            </template>
          </el-table-column>
          <el-table-column prop="typeid" label="彩票分类" width="100">
            <template #default="{ row }">
              {{ lotteryCategories[row.typeid] || row.typeid }}
            </template>
          </el-table-column>
          <el-table-column prop="title" label="彩种名称" width="150">
            <template #default="{ row }">
              <el-link type="primary" @click="handleEdit(row)">{{ row.title }}</el-link>
            </template>
          </el-table-column>
          <el-table-column prop="name" label="彩种标示" width="100" />
          <el-table-column prop="ftime" label="停止投注间隔" width="120" />
          <el-table-column prop="ftitle" label="彩种简介" width="150" show-overflow-tooltip />
          <el-table-column prop="issys" label="彩票类型" width="100">
            <template #default="{ row }">
              <el-tag :type="row.issys == 1 ? 'success' : 'info'">
                {{ row.issys == 1 ? '系统彩' : '第三方彩' }}
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column prop="qishu" label="期数" width="80" />
          <el-table-column prop="iswh" label="维护" width="80">
            <template #default="{ row }">
              <el-tag 
                :type="row.iswh == 0 ? 'success' : 'warning'"
                style="cursor: pointer;"
                @click="handleToggleStatus(row, 'iswh')"
              >
                {{ row.iswh == 0 ? '正常' : '维护中' }}
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column prop="isopen" label="状态" width="80">
            <template #default="{ row }">
              <el-tag 
                :type="row.isopen == 1 ? 'success' : 'info'"
                style="cursor: pointer;"
                @click="handleToggleStatus(row, 'isopen')"
              >
                {{ row.isopen == 1 ? '启用' : '禁用' }}
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column label="操作" width="150" fixed="right">
            <template #default="{ row }">
              <el-button type="primary" size="small" @click="handleEdit(row)" link>
                <el-icon><Edit /></el-icon>
                编辑
              </el-button>
              <el-button type="danger" size="small" @click="handleDelete(row)" link>
                <el-icon><Delete /></el-icon>
                删除
              </el-button>
            </template>
          </el-table-column>
        </el-table>
        
        <div class="footer-bar">
          <el-button type="danger" @click="handleBatchDelete">批量删除</el-button>
          <el-button type="primary" @click="handleSaveOrder">保存排序</el-button>
          <span class="total-count">共有数据：<strong>{{ total }}</strong> 条</span>
        </div>
      </el-form>
    </el-card>
    
    <!-- 添加/编辑对话框 -->
    <el-dialog 
      v-model="dialogVisible" 
      :title="isEdit ? '编辑彩种' : '添加彩种'"
      width="600px"
    >
      <el-form 
        ref="formRef"
        :model="form" 
        :rules="rules"
        label-width="140px"
      >
        <el-form-item label="彩票分类" prop="typeid">
          <el-select v-model="form.typeid" placeholder="请选择彩票分类">
            <el-option 
              v-for="(label, key) in lotteryCategories" 
              :key="key"
              :label="label"
              :value="key"
            />
          </el-select>
        </el-form-item>
        
        <el-form-item label="彩种名称" prop="title">
          <el-input v-model="form.title" placeholder="请输入彩种名称" />
        </el-form-item>
        
        <el-form-item label="彩种标示" prop="name" v-if="!isEdit">
          <el-input v-model="form.name" placeholder="如: hebk3, cqssc（唯一值）" />
        </el-form-item>
        
        <el-form-item label="停止投注间隔" prop="ftime">
          <el-input v-model="form.ftime" placeholder="如: 60秒" />
        </el-form-item>
        
        <el-form-item label="期数" prop="qishu">
          <el-input-number v-model="form.qishu" :min="0" style="width: 100%;" />
        </el-form-item>
        
        <el-form-item label="彩种简介">
          <el-input v-model="form.ftitle" placeholder="请输入彩种简介" />
        </el-form-item>
        
        <el-form-item label="彩票图标">
          <div class="logo-upload-container">
            <el-upload
              class="logo-uploader"
              :action="uploadAction"
              :show-file-list="false"
              :on-success="handleLogoSuccess"
              :before-upload="beforeLogoUpload"
              accept="image/*"
            >
              <img v-if="form.logo" :src="getImageUrl(form.logo)" class="logo-preview" />
              <el-icon v-else class="logo-uploader-icon"><Plus /></el-icon>
            </el-upload>
            <div class="logo-tips">
              <p>建议尺寸: 200x200 像素</p>
              <p>支持格式: JPG、PNG、GIF</p>
              <p>文件大小: 不超过 2MB</p>
              <el-input 
                v-model="form.logo" 
                placeholder="或输入图片URL" 
                style="margin-top: 10px;"
              >
                <template #prepend>URL</template>
              </el-input>
            </div>
          </div>
        </el-form-item>
        
        <el-form-item label="彩票类型" prop="issys">
          <el-radio-group v-model="form.issys">
            <el-radio :value="1">系统彩票</el-radio>
            <el-radio :value="0">第三方平台</el-radio>
          </el-radio-group>
        </el-form-item>
        
        <template v-if="form.issys == 1">
          <el-divider content-position="left">系统彩票设置</el-divider>
          
          <el-form-item label="关盘开始时间">
            <el-time-picker 
              v-model="form.closetime1" 
              format="HH:mm:ss"
              value-format="HH:mm:ss"
              placeholder="选择时间"
              clearable
            />
          </el-form-item>
          
          <el-form-item label="关盘结束时间">
            <el-time-picker 
              v-model="form.closetime2" 
              format="HH:mm:ss"
              value-format="HH:mm:ss"
              placeholder="选择时间"
              clearable
            />
          </el-form-item>
          
          <el-form-item label="开奖时间间隔" prop="expecttime">
            <el-select v-model="form.expecttime" placeholder="请选择">
              <el-option label="1分钟" value="1" />
              <el-option label="1.5分钟" value="1.5" />
              <el-option label="2分钟" value="2" />
              <el-option label="2.5分钟" value="2.5" />
              <el-option label="3分钟" value="3" />
              <el-option label="5分钟" value="5" />
              <el-option label="10分钟" value="10" />
            </el-select>
          </el-form-item>
        </template>
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
import { ref, reactive, onMounted, computed } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus, Refresh, Edit, Delete } from '@element-plus/icons-vue'
import { getLotteryTypes, addLotteryType, updateLotteryType, deleteLotteryType, toggleLotteryStatus, saveLotteryOrder } from '@/api/lottery'
import { getImageUrl } from '@/utils/image'

const loading = ref(false)
const submitting = ref(false)
const dialogVisible = ref(false)
const isEdit = ref(false)
const formRef = ref(null)
const currentTypeId = ref('')
const lotteryList = ref([])
const total = ref(0)

const listForm = reactive({
  orders: {}
})

// 彩种分类
const lotteryCategories = {
  // 'ssc': '时时彩',
  'k3': '快三',
  // 'x5': '11选5',
  // 'keno': '快乐彩',
  // 'pk10': 'PK10',
  // 'dpc': '低频彩',
  // 'lhc': '六合彩'
}

// 表单数据
const form = reactive({
  id: null,
  typeid: 'k3',
  title: '',
  name: '',
  ftime: '',
  qishu: 0,
  ftitle: '',
  logo: '',
  issys: 1,
  closetime1: null,
  closetime2: null,
  expecttime: '1',
  listorder: 0
})

// 图片上传地址 (使用代理路径)
const uploadAction = '/adminapi/upload/image'

// 表单验证规则
const rules = {
  typeid: [{ required: true, message: '请选择彩票分类', trigger: 'change' }],
  title: [{ required: true, message: '请输入彩种名称', trigger: 'blur' }],
  name: [{ required: true, message: '请输入彩种标示', trigger: 'blur' }],
  ftime: [{ required: true, message: '请输入停止投注间隔', trigger: 'blur' }],
  expecttime: [{ required: true, message: '请选择开奖时间间隔', trigger: 'change' }]
}

// 加载列表
const loadList = async () => {
  loading.value = true
  try {
    const params = currentTypeId.value ? { typeid: currentTypeId.value } : {}
    const res = await getLotteryTypes(params)
    if (res.code === 200) {
      lotteryList.value = res.data.list || []
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

// 切换分类
const handleTypeChange = () => {
  loadList()
}

// 刷新
const handleRefresh = () => {
  loadList()
}

// 图片上传成功回调
const handleLogoSuccess = (response) => {
  if (response.code === 200) {
    form.logo = response.data.url
    ElMessage.success('图片上传成功')
  } else {
    ElMessage.error(response.msg || '图片上传失败')
  }
}

// 上传前校验
const beforeLogoUpload = (file) => {
  const isImage = file.type.startsWith('image/')
  const isLt2M = file.size / 1024 / 1024 < 2

  if (!isImage) {
    ElMessage.error('只能上传图片文件!')
    return false
  }
  if (!isLt2M) {
    ElMessage.error('图片大小不能超过 2MB!')
    return false
  }
  return true
}

// 添加
const handleAdd = () => {
  isEdit.value = false
  Object.assign(form, {
    id: null,
    typeid: 'k3',
    title: '',
    name: '',
    ftime: '',
    qishu: 0,
    ftitle: '',
    logo: '',
    issys: 1,
    closetime1: null,
    closetime2: null,
    expecttime: '1',
    listorder: 0
  })
  dialogVisible.value = true
}

// 编辑
const handleEdit = (row) => {
  isEdit.value = true
  
  // 由于 el-time-picker 使用了 value-format="HH:mm:ss"，v-model 应该绑定字符串
  Object.assign(form, {
    id: row.id,
    typeid: row.typeid,
    title: row.title,
    name: row.name,
    ftime: row.ftime,
    qishu: row.qishu,
    ftitle: row.ftitle || '',
    logo: row.logo || '',
    issys: row.issys,
    closetime1: row.closetime1 || null,
    closetime2: row.closetime2 || null,
    expecttime: row.expecttime || '1',
    listorder: row.listorder || 0
  })
  dialogVisible.value = true
}

// 提交表单
const handleSubmit = async () => {
  if (!formRef.value) return
  
  await formRef.value.validate(async (valid) => {
    if (!valid) return
    
    submitting.value = true
    
    try {
      // 由于 el-time-picker 使用了 value-format="HH:mm:ss"，closetime1/2 已经是字符串格式
      const submitData = { ...form }
      
      // 确保时间字段有默认值
      if (!submitData.closetime1) {
        submitData.closetime1 = '00:00:00'
      }
      if (!submitData.closetime2) {
        submitData.closetime2 = '23:59:59'
      }
      
      const apiCall = isEdit.value ? updateLotteryType : addLotteryType
      const res = await apiCall(submitData)
      if (res.code === 200) {
        ElMessage.success(isEdit.value ? '编辑成功' : '添加成功')
        dialogVisible.value = false
        loadList()
      } else {
        ElMessage.error(res.msg || '操作失败')
      }
    } catch (error) {
      ElMessage.error('操作失败: ' + error.message)
    } finally {
      submitting.value = false
    }
  })
}

// 切换状态
const handleToggleStatus = async (row, field) => {
  const fieldName = field === 'iswh' ? '维护状态' : '启用状态'
  try {
    await ElMessageBox.confirm(`确定要切换${fieldName}吗？`, '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })
    
    const res = await toggleLotteryStatus({ id: row.id, field: field })
    if (res.code === 200) {
      row[field] = row[field] == 1 ? 0 : 1
      ElMessage.success('操作成功')
    } else {
      ElMessage.error(res.msg || '操作失败')
    }
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('操作失败')
    }
  }
}

// 删除
const handleDelete = async (row) => {
  try {
    await ElMessageBox.confirm('确定要删除该彩种吗？', '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })
    
    const res = await deleteLotteryType({ id: row.id })
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

// 批量删除
const handleBatchDelete = () => {
  ElMessage.warning('批量删除功能开发中')
}

// 保存排序
const handleSaveOrder = async () => {
  try {
    const orders = {}
    lotteryList.value.forEach(item => {
      orders[item.id] = item.listorder
    })
    
    const res = await saveLotteryOrder({ orders })
    if (res.code === 200) {
      ElMessage.success('排序保存成功')
    } else {
      ElMessage.error(res.msg || '保存失败')
    }
  } catch (error) {
    ElMessage.error('保存失败: ' + error.message)
  }
}

// 图片加载失败处理
const handleImageError = (e) => {
  e.target.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIGZpbGw9IiNmNWY1ZjUiLz48dGV4dCB4PSI1MCUiIHk9IjUwJSIgZm9udC1zaXplPSIxMiIgZmlsbD0iIzk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPuaXoOWbvuaghzwvdGV4dD48L3N2Zz4='
}

onMounted(() => {
  loadList()
})
</script>

<style scoped lang="scss">
.lottery-types-container {
  padding: 20px;
}

/* 列表中的图标样式 */
.lottery-logo {
  width: 50px;
  height: 50px;
  object-fit: contain;
  border-radius: 4px;
  border: 1px solid #eee;
  padding: 4px;
  background: #fff;
  transition: all 0.3s;
  
  &:hover {
    transform: scale(1.2);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
  }
}

/* Logo上传样式 */
.logo-upload-container {
  display: flex;
  gap: 20px;
  align-items: flex-start;
}

.logo-uploader {
  border: 1px dashed #d9d9d9;
  border-radius: 6px;
  cursor: pointer;
  overflow: hidden;
  transition: all 0.3s;
  
  &:hover {
    border-color: #409EFF;
  }
}

.logo-preview {
  width: 150px;
  height: 150px;
  display: block;
  object-fit: contain;
}

.logo-uploader-icon {
  font-size: 28px;
  color: #8c939d;
  width: 150px;
  height: 150px;
  text-align: center;
  line-height: 150px;
  display: block;
}

.logo-tips {
  flex: 1;
  
  p {
    margin: 0 0 8px 0;
    font-size: 13px;
    color: #999;
  }
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

.ml-20 {
  margin-left: 20px;
}

.footer-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 20px;
  padding-top: 15px;
  border-top: 1px solid #eee;
  
  .total-count {
    font-size: 14px;
    color: #666;
    
    strong {
      color: #333;
      font-size: 16px;
    }
  }
}

:deep(.el-radio-group) {
  .el-radio-button__inner {
    padding: 8px 15px;
  }
}
</style>

