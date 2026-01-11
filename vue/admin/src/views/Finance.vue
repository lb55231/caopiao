<template>
  <div class="finance-container">
    <h2 class="page-title">财务管理</h2>
    
    <el-tabs v-model="activeTab" type="card" @tab-click="handleTabClick">
      <el-tab-pane label="充值记录" name="recharge">
        <el-empty description="充值记录功能开发中" />
      </el-tab-pane>
      <el-tab-pane label="提现记录" name="withdraw">
        <el-empty description="提现记录功能开发中" />
      </el-tab-pane>
      <el-tab-pane label="账变记录" name="records">
        <el-empty description="账变记录功能开发中" />
      </el-tab-pane>
    </el-tabs>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const activeTab = ref('recharge')

const handleTabClick = (tab) => {
  console.log('Tab clicked:', tab.props.name)
}
</script>

<style scoped lang="scss">
.finance-container {
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
</style>

