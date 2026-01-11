<template>
  <div class="member-detail-container">
    <h2 class="page-title">会员详情</h2>
    
    <el-card>
      <el-descriptions title="基本信息" :column="2" border>
        <el-descriptions-item label="用户名">testuser</el-descriptions-item>
        <el-descriptions-item label="手机号">13800138000</el-descriptions-item>
        <el-descriptions-item label="余额">1000.00</el-descriptions-item>
        <el-descriptions-item label="状态">
          <el-tag type="success">正常</el-tag>
        </el-descriptions-item>
        <el-descriptions-item label="注册时间">2024-01-01 00:00:00</el-descriptions-item>
        <el-descriptions-item label="上次登录">2024-01-09 12:00:00</el-descriptions-item>
      </el-descriptions>
      
      <div style="margin-top: 20px;">
        <el-button type="primary" @click="$router.back()">返回</el-button>
      </div>
    </el-card>
  </div>
</template>

<script setup>
</script>

<style scoped lang="scss">
.member-detail-container {
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

