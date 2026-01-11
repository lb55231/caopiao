<template>
  <div class="system-container">
    <h2 class="page-title">系统设置</h2>
    
    <!-- 系统设置标签页 -->
    <el-tabs v-model="activeTab" type="card">
      <!-- 网站设置 -->
      <el-tab-pane label="网站设置" name="website">
        <el-card>
          <el-form :model="settingsForm" label-width="140px">
            <el-form-item label="网站名称">
              <el-input 
                v-model="settingsForm.webtitle" 
                placeholder="请输入网站名称" 
                style="width: 400px" 
              />
              <div class="form-tip">网站名称将显示在前台和后台顶部</div>
            </el-form-item>
            
            <el-form-item label="网站Logo">
              <div class="logo-upload-wrapper">
                <!-- Logo预览 -->
                <div class="logo-preview" v-if="settingsForm.weblogo">
                  <img :src="getImageUrl(settingsForm.weblogo)" alt="网站Logo" />
                  <div class="preview-actions">
                    <el-button type="danger" size="small" @click="handleRemoveLogo">删除</el-button>
                  </div>
                </div>
                
                <!-- 上传按钮 -->
                <el-upload
                  :action="uploadAction"
                  :headers="uploadHeaders"
                  :show-file-list="false"
                  :on-success="handleLogoSuccess"
                  :on-error="handleLogoError"
                  :before-upload="beforeLogoUpload"
                  accept="image/png,image/jpeg,image/jpg,image/webp"
                >
                  <el-button type="primary" size="small">
                    <i class="el-icon-upload"></i> 上传Logo
                  </el-button>
                </el-upload>
                
                <!-- URL输入 -->
                <div class="url-input-wrapper">
                  <el-input 
                    v-model="settingsForm.weblogo" 
                    placeholder="或输入Logo URL" 
                    size="small"
                    style="width: 300px"
                  />
                </div>
              </div>
              <div class="form-tip">建议尺寸：200x60px，支持PNG、JPG、WEBP格式，大小不超过2MB</div>
            </el-form-item>
            
            <el-form-item label="网站关键词">
              <el-input 
                v-model="settingsForm.keywords" 
                type="textarea" 
                :rows="3" 
                placeholder="请输入关键词，多个用逗号分隔"
                style="width: 600px"
              />
            </el-form-item>
            
            <el-form-item label="网站描述">
              <el-input 
                v-model="settingsForm.description" 
                type="textarea" 
                :rows="4" 
                placeholder="请输入网站描述"
                style="width: 600px"
              />
            </el-form-item>
            
            <el-form-item label="版权信息">
              <el-input 
                v-model="settingsForm.copyright" 
                placeholder="例如：© 2026 彩票系统 All Rights Reserved"
                style="width: 600px"
              />
            </el-form-item>
            
            <el-form-item label="ICP备案号">
              <el-input 
                v-model="settingsForm.icp" 
                placeholder="请输入ICP备案号"
                style="width: 400px"
              />
            </el-form-item>
            
            <el-form-item label="客服QQ">
              <el-input 
                v-model="settingsForm.serviceqq" 
                placeholder="请输入客服QQ号"
                style="width: 400px"
              />
              <div class="form-tip">用于前台用户联系客服</div>
            </el-form-item>
            
            <el-form-item label="第三方客服链接">
              <el-input 
                v-model="settingsForm.servicecode" 
                type="textarea"
                :rows="5"
                placeholder="请输入第三方客服代码或链接（如：在线客服系统链接、客服代码等）"
                style="width: 600px"
              />
              <div class="form-tip">支持第三方客服系统链接或嵌入代码</div>
            </el-form-item>
            
            <el-form-item label="注册送金额">
              <el-input-number 
                v-model="settingsForm.registerbonus" 
                :min="0"
                :max="10000"
                :precision="2"
                :step="1"
                style="width: 200px"
              />
              <span style="margin-left: 10px">元</span>
              <div class="form-tip">新用户注册成功后自动赠送的金额，设置为0则不赠送</div>
            </el-form-item>
            
            <el-form-item label="是否必须邀请码">
              <el-switch 
                v-model="settingsForm.needinvitecode"
                :active-value="1"
                :inactive-value="0"
              />
              <span style="margin-left: 10px">{{ settingsForm.needinvitecode ? '必须填写' : '可选填写' }}</span>
              <div class="form-tip">开启后，用户注册时必须填写正确的邀请码才能注册</div>
            </el-form-item>
            
            <el-form-item label="充值打码量">
              <el-input-number 
                v-model="settingsForm.damaliang" 
                :min="0"
                :max="500000"
                :precision="0"
                :step="1"
                style="width: 200px"
              />
              <span style="margin-left: 10px">%</span>
              <div class="form-tip">
                充值后需要完成的打码量百分比。<br>
                <strong>建议设置为100%</strong>，即充值多少就需要投注多少才能提现。<br>
                例如：充值2000元，打码量100%，洗码余额增加2000元，需投注2000元后才能提现。
              </div>
            </el-form-item>
            
            <el-form-item>
              <el-button type="primary" @click="handleSaveSettings" :loading="saving">保存设置</el-button>
              <el-button @click="loadSettings">重置</el-button>
            </el-form-item>
          </el-form>
        </el-card>
      </el-tab-pane>
    </el-tabs>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import request from '@/utils/request'
import { getImageUrl } from '@/utils/image'
import { useAdminStore } from '@/stores/admin'

const adminStore = useAdminStore()
const activeTab = ref('website')
const saving = ref(false)

// 网站设置表单
const settingsForm = reactive({
  webtitle: '',
  weblogo: '',
  keywords: '',
  description: '',
  copyright: '',
  icp: '',
  serviceqq: '',
  servicecode: '',
  registerbonus: 0,
  needinvitecode: 0,
  damaliang: 0
})

// 上传配置
const uploadAction = '/adminapi/upload/image'
const uploadHeaders = {
  'Authorization': `Bearer ${adminStore.token}`,
  'Token': adminStore.token
}

// 加载设置
const loadSettings = async () => {
  try {
    const res = await request.get('/admin/settings')
    if (res.code === 200) {
      // 转换数据类型
      const data = res.data
      
      // 数字类型字段
      if (data.registerbonus !== undefined) {
        data.registerbonus = parseFloat(data.registerbonus) || 0
      }
      
      // 开关类型字段（字符串转数字）
      if (data.needinvitecode !== undefined) {
        data.needinvitecode = parseInt(data.needinvitecode) || 0
      }
      
      Object.assign(settingsForm, data)
    }
  } catch (error) {
    ElMessage.error('加载设置失败：' + error.message)
  }
}

// 保存设置
const handleSaveSettings = async () => {
  try {
    saving.value = true
    const res = await request.post('/admin/settings/save', settingsForm)
    if (res.code === 200) {
      ElMessage.success('保存成功')
      loadSettings()
    } else {
      ElMessage.error(res.msg || '保存失败')
    }
  } catch (error) {
    ElMessage.error('保存失败：' + error.message)
  } finally {
    saving.value = false
  }
}

// Logo上传前验证
const beforeLogoUpload = (file) => {
  const isImage = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'].includes(file.type)
  const isLt2M = file.size / 1024 / 1024 < 2

  if (!isImage) {
    ElMessage.error('只能上传JPG、PNG、WEBP格式的图片！')
    return false
  }
  if (!isLt2M) {
    ElMessage.error('图片大小不能超过2MB！')
    return false
  }
  return true
}

// Logo上传成功
const handleLogoSuccess = (response) => {
  if (response.code === 200) {
    settingsForm.weblogo = response.data.url
    ElMessage.success('上传成功')
  } else {
    ElMessage.error(response.msg || '上传失败')
  }
}

// Logo上传失败
const handleLogoError = () => {
  ElMessage.error('上传失败，请重试')
}

// 删除Logo
const handleRemoveLogo = () => {
  settingsForm.weblogo = ''
  ElMessage.success('已删除Logo')
}

// 页面加载时获取设置
onMounted(() => {
  loadSettings()
})
</script>

<style scoped lang="scss">
.system-container {
  padding: 20px;
}

.page-title {
  font-size: 24px;
  font-weight: 500;
  margin-bottom: 20px;
  color: #303133;
}

.form-tip {
  margin-top: 5px;
  font-size: 12px;
  color: #909399;
  line-height: 1.5;
}

.logo-upload-wrapper {
  display: flex;
  align-items: flex-start;
  gap: 15px;
}

.logo-preview {
  position: relative;
  height: 80px;
  border: 1px solid #dcdfe6;
  border-radius: 4px;
  overflow: hidden;
  background: #f5f7fa;
  display: flex;
  align-items: center;
  justify-content: center;
  
  img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
  }
  
  .preview-actions {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s;
  }
  
  &:hover .preview-actions {
    opacity: 1;
  }
}

.url-input-wrapper {
  display: flex;
  align-items: center;
  gap: 10px;
}

.ml-10 {
  margin-left: 10px;
}
</style>
