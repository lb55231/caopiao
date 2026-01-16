<template>
  <div class="page-container">
    <div class="page-header">
      <div class="title">账户管理</div>
      <div class="subtitle">查看和管理代理账户信息</div>
    </div>
    
    <!-- 账户信息卡片 -->
    <el-row :gutter="20" class="info-cards">
      <el-col :span="6">
        <div class="info-card">
          <div class="card-icon" style="background: #409eff;">
            <el-icon :size="32"><Wallet /></el-icon>
          </div>
          <div class="card-content">
            <div class="card-value">¥{{ accountInfo.balance }}</div>
            <div class="card-label">账户余额</div>
          </div>
        </div>
      </el-col>
      
      <el-col :span="6">
        <div class="info-card">
          <div class="card-icon" style="background: #67c23a;">
            <el-icon :size="32"><TrendCharts /></el-icon>
          </div>
          <div class="card-content">
            <div class="card-value">¥{{ accountInfo.todayProfit }}</div>
            <div class="card-label">今日收益</div>
          </div>
        </div>
      </el-col>
      
      <el-col :span="6">
        <div class="info-card">
          <div class="card-icon" style="background: #e6a23c;">
            <el-icon :size="32"><Coin /></el-icon>
          </div>
          <div class="card-content">
            <div class="card-value">¥{{ accountInfo.totalProfit }}</div>
            <div class="card-label">累计收益</div>
          </div>
        </div>
      </el-col>
      
      <el-col :span="6">
        <div class="info-card">
          <div class="card-icon" style="background: #f56c6c;">
            <el-icon :size="32"><User /></el-icon>
          </div>
          <div class="card-content">
            <div class="card-value">{{ accountInfo.userCount }}</div>
            <div class="card-label">下级用户</div>
          </div>
        </div>
      </el-col>
    </el-row>
    
    <!-- 账户详情 -->
    <el-card class="detail-card" shadow="never">
      <template #header>
        <div class="card-header">
          <span>账户详情</span>
          <el-button type="primary" size="small" @click="handleEdit">
            <el-icon><Edit /></el-icon>
            编辑资料
          </el-button>
        </div>
      </template>
      
      <el-descriptions :column="2" border>
        <el-descriptions-item label="代理账号">
          {{ accountInfo.username }}
        </el-descriptions-item>
        <el-descriptions-item label="代理等级">
          <el-tag type="success">{{ accountInfo.level }}</el-tag>
        </el-descriptions-item>
        <el-descriptions-item label="真实姓名">
          {{ accountInfo.realname }}
        </el-descriptions-item>
        <el-descriptions-item label="联系电话">
          {{ accountInfo.phone }}
        </el-descriptions-item>
        <el-descriptions-item label="注册时间">
          {{ accountInfo.registerTime }}
        </el-descriptions-item>
        <el-descriptions-item label="最后登录">
          {{ accountInfo.lastLoginTime }}
        </el-descriptions-item>
        <el-descriptions-item label="状态">
          <el-tag :type="accountInfo.status === 1 ? 'success' : 'danger'">
            {{ accountInfo.status === 1 ? '正常' : '禁用' }}
          </el-tag>
        </el-descriptions-item>
        <el-descriptions-item label="备注">
          {{ accountInfo.remark || '-' }}
        </el-descriptions-item>
      </el-descriptions>
    </el-card>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { getAccountInfo } from '@/api/account'

const loading = ref(false)
const accountInfo = ref({
  username: '-',
  realname: '-',
  phone: '-',
  level: '代理',
  status: 1,
  balance: '0.00',
  todayProfit: '0.00',
  totalProfit: '0.00',
  userCount: 0,
  registerTime: '-',
  lastLoginTime: '-',
  remark: '-'
})

// 获取账户信息
const fetchAccountInfo = async () => {
  loading.value = true
  try {
    const data = await getAccountInfo()
    accountInfo.value = {
      username: data.username || '-',
      realname: data.realname || '-',
      phone: data.phone || '-',
      level: data.level || '代理',
      status: data.status || 1,
      balance: data.balance || '0.00',
      todayProfit: data.todayProfit || '0.00',
      totalProfit: data.totalProfit || '0.00',
      userCount: data.userCount || 0,
      registerTime: data.registerTime || '-',
      lastLoginTime: data.lastLoginTime || '-',
      remark: data.remark || '-'
    }
  } catch (error) {
    console.error('获取账户信息失败:', error)
    ElMessage.error('获取账户信息失败')
  } finally {
    loading.value = false
  }
}

const handleEdit = () => {
  ElMessage.info('编辑功能开发中...')
}

onMounted(() => {
  fetchAccountInfo()
})
</script>

<style lang="scss" scoped>
.info-cards {
  margin-bottom: 20px;
}

.info-card {
  display: flex;
  align-items: center;
  gap: 20px;
  padding: 24px;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  transition: all 0.3s;
  
  &:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
  }
  
  .card-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
  }
  
  .card-content {
    flex: 1;
    
    .card-value {
      font-size: 24px;
      font-weight: 600;
      color: #303133;
      margin-bottom: 8px;
    }
    
    .card-label {
      font-size: 14px;
      color: #909399;
    }
  }
}

.detail-card {
  .card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-weight: 600;
  }
}
</style>
