<template>
  <div class="lottery-list-container">
    <h2 class="page-title">开奖管理</h2>
    
    <el-card>
      <div class="toolbar">
        <el-button type="primary">
          <el-icon><Plus /></el-icon>
          手动开奖
        </el-button>
        <el-button type="success">
          <el-icon><Refresh /></el-icon>
          刷新
        </el-button>
      </div>
      
      <el-table :data="tableData" border stripe style="width: 100%; margin-top: 20px;">
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="cpname" label="彩种" width="120" />
        <el-table-column prop="expect" label="期号" width="150" />
        <el-table-column prop="opencode" label="开奖号码" width="150" />
        <el-table-column prop="opentime" label="开奖时间" width="180" />
        <el-table-column label="操作" width="150">
          <template #default="scope">
            <el-button type="primary" size="small" link>查看详情</el-button>
          </template>
        </el-table-column>
      </el-table>
      
      <div class="pagination-container">
        <el-pagination
          background
          layout="total, prev, pager, next"
          :total="100"
        />
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Plus, Refresh } from '@element-plus/icons-vue'

const tableData = ref([])
</script>

<style scoped lang="scss">
.lottery-list-container {
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

.toolbar {
  margin-bottom: 10px;
}

.pagination-container {
  margin-top: 20px;
  display: flex;
  justify-content: flex-end;
}
</style>

