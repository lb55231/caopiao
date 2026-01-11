<template>
  <div class="payset-container">
    <h2 class="page-title">存款方式配置</h2>
    
    <!-- 工具栏 -->
    <div class="toolbar">
      <el-button type="primary" @click="handleAdd">
        <el-icon><Plus /></el-icon>
        添加存款方式
      </el-button>
      <el-button type="success" @click="loadList">
        <el-icon><Refresh /></el-icon>
        刷新
      </el-button>
    </div>
    
    <!-- 列表 -->
    <el-table
      :data="list"
      border
      style="width: 100%"
      v-loading="loading"
      @selection-change="handleSelectionChange"
    >
      <el-table-column type="selection" width="55" />
      <el-table-column label="排序" width="100" align="center">
        <template #default="{ row }">
          <el-input-number 
            v-model="row.listorder" 
            :min="0" 
            :max="9999"
            size="small"
            controls-position="right"
          />
        </template>
      </el-table-column>
      <el-table-column prop="paytype" label="标识" width="120" />
      <el-table-column prop="paytypetitle" label="支付名称" width="200">
        <template #default="{ row }">
          <el-link type="primary" @click="handleEdit(row)">{{ row.paytypetitle }}</el-link>
        </template>
      </el-table-column>
      <el-table-column prop="ftitle" label="副名称" width="150" />
      <el-table-column label="线上支付" width="100" align="center">
        <template #default="{ row }">
          <el-tag :type="row.isonline == 1 ? 'success' : 'info'">
            {{ row.isonline == 1 ? '是' : '否' }}
          </el-tag>
        </template>
      </el-table-column>
      <el-table-column label="状态" width="100" align="center">
        <template #default="{ row }">
          <el-tag :type="row.state == 1 ? 'success' : 'info'" @click="handleToggleStatus(row)" style="cursor: pointer">
            {{ row.state == 1 ? '启用' : '禁用' }}
          </el-tag>
        </template>
      </el-table-column>
      <el-table-column label="操作" width="150" align="center">
        <template #default="{ row }">
          <el-link type="primary" @click="handleEdit(row)" style="margin-right: 10px">修改</el-link>
          <el-link type="danger" @click="handleDelete(row)">删除</el-link>
        </template>
      </el-table-column>
    </el-table>
    
    <!-- 底部工具栏 -->
    <div class="footer-toolbar">
      <div class="footer-left">
        <el-button type="primary" @click="handleBatchSaveOrder">
          保存排序
        </el-button>
      </div>
      <div class="footer-right">
        <span>共有数据：<strong>{{ total }}</strong> 条</span>
      </div>
    </div>
    
    <!-- 添加/编辑对话框 -->
    <el-dialog
      v-model="dialogVisible"
      :title="dialogTitle"
      width="700px"
      @close="resetForm"
    >
      <el-form :model="form" label-width="140px" ref="formRef">
        <el-form-item label="是否线上支付" required>
          <el-radio-group v-model="form.isonline">
            <el-radio :value="1">是</el-radio>
            <el-radio :value="-1">否</el-radio>
          </el-radio-group>
        </el-form-item>
        
        <el-form-item label="标识" required>
          <el-input v-model="form.paytype" placeholder="如：alipay, weixin, linepay" />
        </el-form-item>
        
        <el-form-item label="名称" required>
          <el-input v-model="form.paytypetitle" placeholder="支付方式名称" />
        </el-form-item>
        
        <el-form-item label="副名称">
          <el-input v-model="form.ftitle" placeholder="副名称" />
        </el-form-item>
        
        <el-form-item label="充值金额设置">
          <div class="money-range">
            <span>最低充值：</span>
            <el-input-number v-model="form.minmoney" :min="0" :precision="2" style="width: 120px" />
            <span style="margin: 0 10px">元</span>
            <span style="margin-left: 20px">最高充值：</span>
            <el-input-number v-model="form.maxmoney" :min="0" :precision="2" style="width: 120px" />
            <span style="margin-left: 10px">元</span>
          </div>
        </el-form-item>
        
        <!-- 线下支付配置 -->
        <template v-if="form.isonline == -1">
          <el-divider content-position="left">线下支付配置</el-divider>
          
          <el-form-item label="收款人姓名">
            <el-input v-model="form.configs.bankname" placeholder="收款人姓名" />
          </el-form-item>
          
          <el-form-item label="收款人账号">
            <el-input v-model="form.configs.bankcode" placeholder="收款人账号" />
          </el-form-item>
          
          <el-form-item label="是否二维码支付">
            <el-radio-group v-model="form.configs.isewm">
              <el-radio value="1">是</el-radio>
              <el-radio value="-1">否</el-radio>
            </el-radio-group>
          </el-form-item>
          
          <el-form-item label="二维码图片" v-if="form.configs.isewm === '1'">
            <el-upload
              class="qrcode-uploader"
              :action="uploadAction"
              :headers="uploadHeaders"
              :show-file-list="false"
              :on-success="handleQrcodeUploadSuccess"
              :before-upload="beforeQrcodeUpload"
            >
              <img v-if="form.configs.ewmurl" :src="getImageUrl(form.configs.ewmurl)" class="qrcode-preview" />
              <el-icon v-else class="qrcode-uploader-icon"><Plus /></el-icon>
            </el-upload>
            <div class="upload-tip">建议尺寸：300x300像素，大小不超过2MB</div>
            <el-button v-if="form.configs.ewmurl" size="small" type="danger" @click="handleRemoveQrcode" style="margin-top: 10px">
              删除二维码
            </el-button>
          </el-form-item>
        </template>
        
        <!-- 线上支付配置 -->
        <template v-if="form.isonline == 1">
          <el-divider content-position="left">线上支付配置</el-divider>
          
          <el-form-item label="商户标识">
            <el-input v-model="form.configs.merchantid" placeholder="商户标识" />
          </el-form-item>
          
          <el-form-item label="商家密钥1">
            <el-input v-model="form.configs.merchantkey1" placeholder="商家密钥1" />
          </el-form-item>
          
          <el-form-item label="商家密钥2">
            <el-input v-model="form.configs.merchantkey2" placeholder="商家密钥2" />
          </el-form-item>
          
          <el-form-item label="前台跳转地址">
            <el-input v-model="form.configs.redirecturl" placeholder="前台跳转地址" />
          </el-form-item>
          
          <el-form-item label="前台通知地址">
            <el-input v-model="form.configs.hrefbackurl" placeholder="前台通知地址" />
          </el-form-item>
          
          <el-form-item label="异步通知地址">
            <el-input v-model="form.configs.returnbackurl" placeholder="异步通知地址" />
          </el-form-item>
        </template>
        
        <el-form-item label="支付说明">
          <el-input 
            v-model="form.remark" 
            type="textarea" 
            :rows="5" 
            placeholder="支付说明（支持HTML）"
          />
        </el-form-item>
        
        <el-form-item label="状态" required>
          <el-radio-group v-model="form.state">
            <el-radio :value="1">启用</el-radio>
            <el-radio :value="-1">禁用</el-radio>
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
import { Plus, Refresh } from '@element-plus/icons-vue'
import { getPaySetList, addPaySet, updatePaySet, deletePaySet, togglePaySetStatus, updatePaySetOrder } from '@/api/bank'
import { useAdminStore } from '@/stores/admin'
import { getImageUrl } from '@/utils/image'

const adminStore = useAdminStore()
const loading = ref(false)
const submitting = ref(false)
const list = ref([])
const total = ref(0)
const selectedIds = ref([])

// 图片上传配置
const uploadAction = '/adminapi/upload/image'
const uploadHeaders = {
  'Authorization': `Bearer ${adminStore.token}`
}

// 对话框
const dialogVisible = ref(false)
const dialogTitle = ref('添加存款方式')
const formRef = ref(null)
const form = reactive({
  id: null,
  paytype: '',
  paytypetitle: '',
  ftitle: '',
  minmoney: 0,
  maxmoney: 0,
  remark: '',
  isonline: -1,
  state: 1,
  listorder: 0,
  configs: {
    bankname: '',
    bankcode: '',
    isewm: '-1',
    ewmurl: '',
    merchantid: '',
    merchantkey1: '',
    merchantkey2: '',
    redirecturl: '',
    hrefbackurl: '',
    returnbackurl: ''
  }
})

// 加载列表
const loadList = async () => {
  try {
    loading.value = true
    const res = await getPaySetList()
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
  dialogTitle.value = '添加存款方式'
  resetForm()
  dialogVisible.value = true
}

// 编辑
const handleEdit = (row) => {
  dialogTitle.value = '编辑存款方式'
  
  // 复制基本字段
  Object.assign(form, {
    id: row.id,
    paytype: row.paytype,
    paytypetitle: row.paytypetitle,
    ftitle: row.ftitle,
    minmoney: row.minmoney,
    maxmoney: row.maxmoney,
    remark: row.remark,
    isonline: row.isonline,
    state: row.state,
    listorder: row.listorder
  })
  
  // 重置 configs 为默认值
  form.configs = {
    bankname: '',
    bankcode: '',
    isewm: '-1',
    ewmurl: '',
    merchantid: '',
    merchantkey1: '',
    merchantkey2: '',
    redirecturl: '',
    hrefbackurl: '',
    returnbackurl: ''
  }
  
  // 解析并合并configs
  if (row.configs_array && typeof row.configs_array === 'object') {
    Object.assign(form.configs, row.configs_array)
  }
  
  dialogVisible.value = true
}

// 二维码上传成功
const handleQrcodeUploadSuccess = (response) => {
  if (response.code === 200) {
    form.configs.ewmurl = response.data.url || response.data.path
    ElMessage.success('二维码上传成功')
  } else {
    ElMessage.error(response.msg || '上传失败')
  }
}

// 上传前校验
const beforeQrcodeUpload = (file) => {
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

// 删除二维码
const handleRemoveQrcode = () => {
  form.configs.ewmurl = ''
  ElMessage.success('已删除二维码')
}

// 提交
const handleSubmit = async () => {
  if (!form.paytype || !form.paytypetitle) {
    ElMessage.error('请填写标识和名称')
    return
  }
  
  try {
    submitting.value = true
    const apiFunc = form.id ? updatePaySet : addPaySet
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
  ElMessageBox.confirm('确定要删除这个存款方式吗？', '提示', {
    confirmButtonText: '确定',
    cancelButtonText: '取消',
    type: 'warning'
  }).then(async () => {
    try {
      const res = await deletePaySet(row.id)
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
    const res = await togglePaySetStatus({ id: row.id })
    if (res.code === 200) {
      ElMessage.success('状态更新成功')
      loadList()
    } else {
      ElMessage.error(res.msg || '状态更新失败')
    }
  } catch (error) {
    ElMessage.error('状态更新失败：' + error.message)
  }
}

// 重置表单
const resetForm = () => {
  form.id = null
  form.paytype = ''
  form.paytypetitle = ''
  form.ftitle = ''
  form.minmoney = 0
  form.maxmoney = 0
  form.remark = ''
  form.isonline = -1
  form.state = 1
  form.listorder = 0
  form.configs = {
    bankname: '',
    bankcode: '',
    isewm: '-1',
    ewmurl: '',
    merchantid: '',
    merchantkey1: '',
    merchantkey2: '',
    redirecturl: '',
    hrefbackurl: '',
    returnbackurl: ''
  }
}

// 处理选择变化
const handleSelectionChange = (selection) => {
  selectedIds.value = selection.map(item => item.id)
}

// 批量保存排序
const handleBatchSaveOrder = async () => {
  try {
    loading.value = true
    
    const orderData = list.value.map(item => ({
      id: item.id,
      listorder: item.listorder
    }))
    
    const res = await updatePaySetOrder(orderData)
    
    if (res.code === 200) {
      ElMessage.success('保存排序成功')
      loadList()
    } else {
      ElMessage.error(res.msg || '保存排序失败')
    }
  } catch (error) {
    ElMessage.error('保存排序失败：' + error.message)
  } finally {
    loading.value = false
  }
}

// 页面加载时获取数据
onMounted(() => {
  loadList()
})
</script>

<style scoped lang="scss">
.payset-container {
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
  display: flex;
  gap: 10px;
}

.money-range {
  display: flex;
  align-items: center;
}

.footer-toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 20px;
  padding: 15px 20px;
  background: #f5f7fa;
  border-radius: 4px;
  
  .footer-left {
    display: flex;
    gap: 10px;
  }
  
  .footer-right {
    font-size: 14px;
    color: #606266;
    
    strong {
      color: #409EFF;
      font-size: 16px;
    }
  }
}

// 二维码上传样式
.qrcode-uploader {
  :deep(.el-upload) {
    border: 1px dashed #d9d9d9;
    border-radius: 6px;
    cursor: pointer;
    position: relative;
    overflow: hidden;
    transition: all 0.3s;
    
    &:hover {
      border-color: #409eff;
    }
  }
}

.qrcode-uploader-icon {
  font-size: 28px;
  color: #8c939d;
  width: 178px;
  height: 178px;
  text-align: center;
  line-height: 178px;
}

.qrcode-preview {
  width: 178px;
  height: 178px;
  display: block;
  object-fit: contain;
}

.upload-tip {
  font-size: 12px;
  color: #999;
  margin-top: 8px;
}
</style>

