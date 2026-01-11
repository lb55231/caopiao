<template>
  <div class="system-container">
    <h2 class="page-title">系统设置</h2>
    
    <!-- 系统设置标签页 -->
    <el-tabs v-model="activeTab" type="card">
      <!-- 彩种管理 -->
      <el-tab-pane label="彩种管理" name="lottery">
        <el-card>
          <div class="setting-item">
            <el-button type="primary" @click="goToLotteryTypes">
              <el-icon><Setting /></el-icon>
              进入彩种管理
            </el-button>
            <p class="setting-desc">管理系统中的所有彩票种类，包括添加、编辑、删除等操作</p>
          </div>
        </el-card>
      </el-tab-pane>
      
      <!-- 基本设置 -->
      <el-tab-pane label="基本设置" name="basic">
        <el-card>
          <el-form :model="basicForm" label-width="140px">
            <el-form-item label="网站名称">
              <el-input v-model="basicForm.siteName" placeholder="请输入网站名称" />
            </el-form-item>
            
            <el-form-item label="网站标题">
              <el-input v-model="basicForm.siteTitle" placeholder="请输入网站标题" />
            </el-form-item>
            
            <el-form-item label="网站关键词">
              <el-input v-model="basicForm.keywords" type="textarea" :rows="3" placeholder="请输入关键词，多个用逗号分隔" />
            </el-form-item>
            
            <el-form-item label="网站描述">
              <el-input v-model="basicForm.description" type="textarea" :rows="4" placeholder="请输入网站描述" />
            </el-form-item>
            
            <el-form-item label="客服QQ">
              <el-input v-model="basicForm.serviceQQ" placeholder="请输入客服QQ" />
            </el-form-item>
            
            <el-form-item label="联系电话">
              <el-input v-model="basicForm.phone" placeholder="请输入联系电话" />
            </el-form-item>
            
            <el-form-item>
              <el-button type="primary" @click="handleSaveBasic">保存设置</el-button>
              <el-button @click="handleResetBasic">重置</el-button>
            </el-form-item>
          </el-form>
        </el-card>
      </el-tab-pane>
      
      <!-- 注册设置 -->
      <el-tab-pane label="注册设置" name="register">
        <el-card>
          <el-form :model="registerForm" label-width="140px">
            <el-form-item label="是否开放注册">
              <el-switch v-model="registerForm.allowRegister" />
            </el-form-item>
            
            <el-form-item label="注册赠送金额">
              <el-input-number v-model="registerForm.registerBonus" :min="0" :precision="2" />
              <span class="ml-10">元</span>
            </el-form-item>
            
            <el-form-item label="是否需要邀请码">
              <el-switch v-model="registerForm.needInviteCode" />
            </el-form-item>
            
            <el-form-item label="默认用户组">
              <el-select v-model="registerForm.defaultGroup" placeholder="请选择用户组">
                <el-option label="普通会员" value="1" />
                <el-option label="VIP会员" value="2" />
              </el-select>
            </el-form-item>
            
            <el-form-item>
              <el-button type="primary" @click="handleSaveRegister">保存设置</el-button>
              <el-button @click="handleResetRegister">重置</el-button>
            </el-form-item>
          </el-form>
        </el-card>
      </el-tab-pane>
      
      <!-- 投注设置 -->
      <el-tab-pane label="投注设置" name="bet">
        <el-card>
          <el-form :model="betForm" label-width="140px">
            <el-form-item label="最小投注金额">
              <el-input-number v-model="betForm.minBet" :min="0" :precision="2" />
              <span class="ml-10">元</span>
            </el-form-item>
            
            <el-form-item label="最大投注金额">
              <el-input-number v-model="betForm.maxBet" :min="0" :precision="2" />
              <span class="ml-10">元</span>
            </el-form-item>
            
            <el-form-item label="单期最大投注">
              <el-input-number v-model="betForm.maxPerPeriod" :min="0" :precision="2" />
              <span class="ml-10">元</span>
            </el-form-item>
            
            <el-form-item label="是否开启投注">
              <el-switch v-model="betForm.allowBet" />
            </el-form-item>
            
            <el-form-item>
              <el-button type="primary" @click="handleSaveBet">保存设置</el-button>
              <el-button @click="handleResetBet">重置</el-button>
            </el-form-item>
          </el-form>
        </el-card>
      </el-tab-pane>
      
      <!-- 提现设置 -->
      <el-tab-pane label="提现设置" name="withdraw">
        <el-card>
          <el-form :model="withdrawForm" label-width="140px">
            <el-form-item label="是否开启提现">
              <el-switch v-model="withdrawForm.allowWithdraw" />
            </el-form-item>
            
            <el-form-item label="最小提现金额">
              <el-input-number v-model="withdrawForm.minWithdraw" :min="0" :precision="2" />
              <span class="ml-10">元</span>
            </el-form-item>
            
            <el-form-item label="手续费比例">
              <el-input-number v-model="withdrawForm.feeRate" :min="0" :max="100" :precision="2" />
              <span class="ml-10">%</span>
            </el-form-item>
            
            <el-form-item label="每日提现次数">
              <el-input-number v-model="withdrawForm.dailyLimit" :min="0" />
              <span class="ml-10">次</span>
            </el-form-item>
            
            <el-form-item>
              <el-button type="primary" @click="handleSaveWithdraw">保存设置</el-button>
              <el-button @click="handleResetWithdraw">重置</el-button>
            </el-form-item>
          </el-form>
        </el-card>
      </el-tab-pane>
    </el-tabs>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import { Setting } from '@element-plus/icons-vue'

const router = useRouter()
const activeTab = ref('lottery')

// 基本设置表单
const basicForm = reactive({
  siteName: '彩票系统',
  siteTitle: '专业的彩票管理系统',
  keywords: '彩票,快三,时时彩,管理系统',
  description: '提供专业的彩票服务',
  serviceQQ: '',
  phone: ''
})

// 注册设置表单
const registerForm = reactive({
  allowRegister: true,
  registerBonus: 0,
  needInviteCode: false,
  defaultGroup: '1'
})

// 投注设置表单
const betForm = reactive({
  minBet: 2,
  maxBet: 10000,
  maxPerPeriod: 50000,
  allowBet: true
})

// 提现设置表单
const withdrawForm = reactive({
  allowWithdraw: true,
  minWithdraw: 100,
  feeRate: 0,
  dailyLimit: 3
})

// 跳转到彩种管理
const goToLotteryTypes = () => {
  router.push('/lottery-types')
}

// 保存基本设置
const handleSaveBasic = () => {
  ElMessage.success('基本设置保存成功')
  // TODO: 调用API保存设置
}

// 重置基本设置
const handleResetBasic = () => {
  Object.assign(basicForm, {
    siteName: '彩票系统',
    siteTitle: '专业的彩票管理系统',
    keywords: '彩票,快三,时时彩,管理系统',
    description: '提供专业的彩票服务',
    serviceQQ: '',
    phone: ''
  })
  ElMessage.info('已重置')
}

// 保存注册设置
const handleSaveRegister = () => {
  ElMessage.success('注册设置保存成功')
  // TODO: 调用API保存设置
}

// 重置注册设置
const handleResetRegister = () => {
  Object.assign(registerForm, {
    allowRegister: true,
    registerBonus: 0,
    needInviteCode: false,
    defaultGroup: '1'
  })
  ElMessage.info('已重置')
}

// 保存投注设置
const handleSaveBet = () => {
  ElMessage.success('投注设置保存成功')
  // TODO: 调用API保存设置
}

// 重置投注设置
const handleResetBet = () => {
  Object.assign(betForm, {
    minBet: 2,
    maxBet: 10000,
    maxPerPeriod: 50000,
    allowBet: true
  })
  ElMessage.info('已重置')
}

// 保存提现设置
const handleSaveWithdraw = () => {
  ElMessage.success('提现设置保存成功')
  // TODO: 调用API保存设置
}

// 重置提现设置
const handleResetWithdraw = () => {
  Object.assign(withdrawForm, {
    allowWithdraw: true,
    minWithdraw: 100,
    feeRate: 0,
    dailyLimit: 3
  })
  ElMessage.info('已重置')
}
</script>

<style scoped lang="scss">
.system-container {
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

.setting-item {
  padding: 20px;
  text-align: center;
  
  .setting-desc {
    margin-top: 15px;
    color: #666;
    font-size: 14px;
  }
}

.ml-10 {
  margin-left: 10px;
  color: #666;
}

:deep(.el-tabs__item) {
  font-size: 15px;
  padding: 0 30px;
}

:deep(.el-form-item) {
  margin-bottom: 22px;
}
</style>

