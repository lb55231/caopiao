<template>
  <div class="dashboard-container">
    <h2 class="page-title">数据统计</h2>
    
    <!-- 统计卡片 -->
    <el-row :gutter="20" class="stat-cards">
      <el-col :xs="12" :sm="12" :md="6" :lg="6">
        <el-card shadow="hover" class="stat-card">
          <div class="stat-content">
            <div class="stat-info">
              <div class="stat-label">今日投注金额</div>
              <div class="stat-value">¥ {{ statistics.todayBetAmount }}</div>
            </div>
            <el-icon class="stat-icon" color="#409EFF">
              <Money />
            </el-icon>
          </div>
        </el-card>
      </el-col>
      
      <el-col :xs="12" :sm="12" :md="6" :lg="6">
        <el-card shadow="hover" class="stat-card">
          <div class="stat-content">
            <div class="stat-info">
              <div class="stat-label">今日投注笔数</div>
              <div class="stat-value">{{ statistics.todayBetCount }}</div>
            </div>
            <el-icon class="stat-icon" color="#67C23A">
              <Document />
            </el-icon>
          </div>
        </el-card>
      </el-col>
      
      <el-col :xs="12" :sm="12" :md="6" :lg="6">
        <el-card shadow="hover" class="stat-card">
          <div class="stat-content">
            <div class="stat-info">
              <div class="stat-label">今日中奖金额</div>
              <div class="stat-value">¥ {{ statistics.todayWinAmount }}</div>
            </div>
            <el-icon class="stat-icon" color="#E6A23C">
              <Trophy />
            </el-icon>
          </div>
        </el-card>
      </el-col>
      
      <el-col :xs="12" :sm="12" :md="6" :lg="6">
        <el-card shadow="hover" class="stat-card">
          <div class="stat-content">
            <div class="stat-info">
              <div class="stat-label">总会员数</div>
              <div class="stat-value">{{ statistics.totalMembers }}</div>
            </div>
            <el-icon class="stat-icon" color="#F56C6C">
              <User />
            </el-icon>
          </div>
        </el-card>
      </el-col>
    </el-row>
    
    <!-- 数据图表区域 -->
    <el-row :gutter="20" class="mt-20">
      <el-col :xs="24" :sm="24" :md="12" :lg="12">
        <el-card shadow="hover">
          <template #header>
            <div class="card-header">
              <span>最近7天投注趋势</span>
            </div>
          </template>
          <div class="chart-container" id="betTrendChart" style="height: 300px;">
            <el-empty description="图表功能开发中" />
          </div>
        </el-card>
      </el-col>
      
      <el-col :xs="24" :sm="24" :md="12" :lg="12">
        <el-card shadow="hover">
          <template #header>
            <div class="card-header">
              <span>彩种投注占比</span>
            </div>
          </template>
          <div class="chart-container" id="lotteryPieChart" style="height: 300px;">
            <el-empty description="图表功能开发中" />
          </div>
        </el-card>
      </el-col>
    </el-row>
    
    <!-- 快捷操作 -->
    <el-row :gutter="20" class="mt-20">
      <el-col :span="24">
        <el-card shadow="hover">
          <template #header>
            <div class="card-header">
              <span>快捷操作</span>
            </div>
          </template>
          <div class="quick-actions">
            <el-button type="primary" @click="$router.push('/members')">
              <el-icon><User /></el-icon>
              会员管理
            </el-button>
            <el-button type="success" @click="$router.push('/bets')">
              <el-icon><Document /></el-icon>
              投注记录
            </el-button>
            <el-button type="warning" @click="$router.push('/lottery')">
              <el-icon><Trophy /></el-icon>
              开奖管理
            </el-button>
            <el-button type="info" @click="$router.push('/finance/recharge')">
              <el-icon><Wallet /></el-icon>
              财务管理
            </el-button>
          </div>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { getStatistics } from '@/api/admin'
import { ElMessage } from 'element-plus'
import { Money, Document, Trophy, User, Wallet } from '@element-plus/icons-vue'

const statistics = ref({
  todayBetAmount: '0.00',
  todayBetCount: 0,
  todayWinAmount: '0.00',
  totalMembers: 0
})

const loadStatistics = async () => {
  try {
    // Mock data for now
    statistics.value = {
      todayBetAmount: '12,580.00',
      todayBetCount: 358,
      todayWinAmount: '8,240.00',
      totalMembers: 1256
    }
    
    // 待后端API准备好后使用以下代码
    /*
    const res = await getStatistics()
    if (res.code === 200) {
      statistics.value = res.data
    } else {
      ElMessage.error(res.msg || '加载统计数据失败')
    }
    */
  } catch (error) {
    console.error('加载统计数据失败:', error)
    // ElMessage.error('加载统计数据失败: ' + error.message)
  }
}

onMounted(() => {
  loadStatistics()
})
</script>

<style scoped lang="scss">
.dashboard-container {
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

.stat-cards {
  margin-bottom: 20px;
}

.stat-card {
  .stat-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    
    .stat-info {
      flex: 1;
      
      .stat-label {
        font-size: 14px;
        color: #909399;
        margin-bottom: 10px;
      }
      
      .stat-value {
        font-size: 24px;
        font-weight: bold;
        color: #303133;
      }
    }
    
    .stat-icon {
      font-size: 48px;
      opacity: 0.3;
    }
  }
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 16px;
  font-weight: bold;
  color: #333;
}

.chart-container {
  display: flex;
  align-items: center;
  justify-content: center;
}

.quick-actions {
  display: flex;
  gap: 15px;
  flex-wrap: wrap;
  
  .el-button {
    min-width: 120px;
  }
}

@media screen and (max-width: 768px) {
  .dashboard-container {
    padding: 10px;
  }
  
  .page-title {
    font-size: 20px;
  }
  
  .stat-card {
    margin-bottom: 10px;
    
    .stat-content {
      .stat-info {
        .stat-value {
          font-size: 20px;
        }
      }
      
      .stat-icon {
        font-size: 36px;
      }
    }
  }
  
  .quick-actions {
    .el-button {
      min-width: 100px;
    }
  }
}
</style>

