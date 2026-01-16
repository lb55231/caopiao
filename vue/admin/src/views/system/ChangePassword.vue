<template>
  <div class="change-password-container">
    <el-row :gutter="20">
      <!-- 修改登录密码 -->
      <el-col :span="12">
        <el-card class="box-card">
          <template #header>
            <div class="card-header">
              <el-icon><Lock /></el-icon>
              <span>修改登录密码</span>
            </div>
          </template>

          <el-form
            ref="passwordFormRef"
            :model="passwordForm"
            :rules="passwordRules"
            label-width="120px"
          >
            <el-form-item label="旧密码" prop="old_password">
              <el-input
                v-model="passwordForm.old_password"
                type="password"
                placeholder="请输入旧密码"
                show-password
              />
            </el-form-item>
            <el-form-item label="新密码" prop="new_password">
              <el-input
                v-model="passwordForm.new_password"
                type="password"
                placeholder="请输入新密码（6-16位）"
                show-password
              />
            </el-form-item>
            <el-form-item label="确认密码" prop="confirm_password">
              <el-input
                v-model="passwordForm.confirm_password"
                type="password"
                placeholder="请再次输入新密码"
                show-password
              />
            </el-form-item>
            <el-form-item>
              <el-button type="primary" @click="handleChangePassword" :loading="passwordSubmitting">
                确认修改
              </el-button>
              <el-button @click="resetPasswordForm">重置</el-button>
            </el-form-item>
          </el-form>

          <el-alert
            title="密码修改说明"
            type="warning"
            :closable="false"
            show-icon
          >
            <ul style="margin: 0; padding-left: 20px;">
              <li>密码长度为 6-16 位</li>
              <li>只能包含字母、数字和下划线</li>
              <li>修改成功后需要重新登录</li>
            </ul>
          </el-alert>
        </el-card>
      </el-col>

      <!-- 修改安全码 -->
      <el-col :span="12">
        <el-card class="box-card">
          <template #header>
            <div class="card-header">
              <el-icon><Key /></el-icon>
              <span>修改安全码</span>
            </div>
          </template>

          <el-form
            ref="safecodeFormRef"
            :model="safecodeForm"
            :rules="safecodeRules"
            label-width="120px"
          >
            <el-form-item label="旧安全码" prop="old_safecode">
              <el-input
                v-model="safecodeForm.old_safecode"
                type="password"
                placeholder="请输入旧安全码"
                show-password
              />
            </el-form-item>
            <el-form-item label="新安全码" prop="new_safecode">
              <el-input
                v-model="safecodeForm.new_safecode"
                type="password"
                placeholder="请输入新安全码（4-7位数字）"
                show-password
              />
            </el-form-item>
            <el-form-item label="确认安全码" prop="confirm_safecode">
              <el-input
                v-model="safecodeForm.confirm_safecode"
                type="password"
                placeholder="请再次输入新安全码"
                show-password
              />
            </el-form-item>
            <el-form-item>
              <el-button type="primary" @click="handleChangeSafecode" :loading="safecodeSubmitting">
                确认修改
              </el-button>
              <el-button @click="resetSafecodeForm">重置</el-button>
            </el-form-item>
          </el-form>

          <el-alert
            title="安全码说明"
            type="info"
            :closable="false"
            show-icon
          >
            <ul style="margin: 0; padding-left: 20px;">
              <li>安全码长度为 4-7 位数字</li>
              <li>用于敏感操作的二次验证</li>
              <li>请妥善保管，避免泄露</li>
            </ul>
          </el-alert>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { ElMessage } from 'element-plus'
import { Lock, Key } from '@element-plus/icons-vue'
import { changePassword, changeSafecode } from '@/api/admin'
import { useAdminStore } from '@/stores/admin'
import { useRouter } from 'vue-router'

const router = useRouter()
const adminStore = useAdminStore()

// 修改密码表单
const passwordFormRef = ref()
const passwordSubmitting = ref(false)
const passwordForm = reactive({
  old_password: '',
  new_password: '',
  confirm_password: ''
})

// 密码验证规则
const validateConfirmPassword = (rule, value, callback) => {
  if (value !== passwordForm.new_password) {
    callback(new Error('两次输入的密码不一致'))
  } else {
    callback()
  }
}

const passwordRules = {
  old_password: [
    { required: true, message: '请输入旧密码', trigger: 'blur' }
  ],
  new_password: [
    { required: true, message: '请输入新密码', trigger: 'blur' },
    { pattern: /^\w{6,16}$/, message: '密码为6-16位字母数字组合', trigger: 'blur' }
  ],
  confirm_password: [
    { required: true, message: '请再次输入新密码', trigger: 'blur' },
    { validator: validateConfirmPassword, trigger: 'blur' }
  ]
}

// 修改安全码表单
const safecodeFormRef = ref()
const safecodeSubmitting = ref(false)
const safecodeForm = reactive({
  old_safecode: '',
  new_safecode: '',
  confirm_safecode: ''
})

// 安全码验证规则
const validateConfirmSafecode = (rule, value, callback) => {
  if (value !== safecodeForm.new_safecode) {
    callback(new Error('两次输入的安全码不一致'))
  } else {
    callback()
  }
}

const safecodeRules = {
  old_safecode: [
    { required: true, message: '请输入旧安全码', trigger: 'blur' }
  ],
  new_safecode: [
    { required: true, message: '请输入新安全码', trigger: 'blur' },
    { pattern: /^\d{4,7}$/, message: '安全码为4-7位数字', trigger: 'blur' }
  ],
  confirm_safecode: [
    { required: true, message: '请再次输入新安全码', trigger: 'blur' },
    { validator: validateConfirmSafecode, trigger: 'blur' }
  ]
}

// 修改密码
const handleChangePassword = async () => {
  try {
    await passwordFormRef.value.validate()
    
    passwordSubmitting.value = true
    
    const res = await changePassword({
      old_password: passwordForm.old_password,
      new_password: passwordForm.new_password
    })
    
    if (res.code === 200) {
      ElMessage.success('密码修改成功，请重新登录')
      // 清除登录信息
      adminStore.logout()
      setTimeout(() => {
        router.push('/login')
      }, 1500)
    } else {
      ElMessage.error(res.msg || '修改失败')
    }
  } catch (error) {
    if (error !== 'cancel') {
      console.error(error)
    }
  } finally {
    passwordSubmitting.value = false
  }
}

// 修改安全码
const handleChangeSafecode = async () => {
  try {
    await safecodeFormRef.value.validate()
    
    safecodeSubmitting.value = true
    
    const res = await changeSafecode({
      old_safecode: safecodeForm.old_safecode,
      new_safecode: safecodeForm.new_safecode
    })
    
    if (res.code === 200) {
      ElMessage.success('安全码修改成功')
      resetSafecodeForm()
    } else {
      ElMessage.error(res.msg || '修改失败')
    }
  } catch (error) {
    console.error(error)
  } finally {
    safecodeSubmitting.value = false
  }
}

// 重置表单
const resetPasswordForm = () => {
  passwordForm.old_password = ''
  passwordForm.new_password = ''
  passwordForm.confirm_password = ''
  passwordFormRef.value?.clearValidate()
}

const resetSafecodeForm = () => {
  safecodeForm.old_safecode = ''
  safecodeForm.new_safecode = ''
  safecodeForm.confirm_safecode = ''
  safecodeFormRef.value?.clearValidate()
}
</script>

<style scoped lang="scss">
.change-password-container {
  padding: 20px;
}

.card-header {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 600;
}

.el-alert {
  margin-top: 20px;
}
</style>
