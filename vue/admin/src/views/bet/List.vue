<template>
  <div class="bet-list-container">
    <h2 class="page-title">投注记录</h2>
    
    <el-card>
      <div class="toolbar">
        <el-select v-model="searchForm.cpname" placeholder="选择彩种" style="width: 150px; margin-right: 10px;" clearable>
          <el-option label="全部" value="" />
          <el-option label="河北快3" value="hebk3" />
        </el-select>
        <el-button type="primary">
          <el-icon><Search /></el-icon>
          搜索
        </el-button>
        <el-button type="success">
          <el-icon><Refresh /></el-icon>
          刷新
        </el-button>
      </div>
      
      <el-table :data="tableData" border stripe style="width: 100%; margin-top: 20px;">
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="username" label="用户" width="120" />
        <el-table-column prop="cpname" label="彩种" width="120" />
        <el-table-column prop="expect" label="期号" width="150" />
        <el-table-column prop="title" label="玩法" width="120" />
        <el-table-column prop="money" label="金额" width="100" />
        <el-table-column prop="status" label="状态" width="100">
          <template #default="scope">
            <el-tag :type="scope.row.status == 1 ? 'success' : scope.row.status == 2 ? 'danger' : 'info'">
              {{ scope.row.status == 0 ? '未开奖' : scope.row.status == 1 ? '已中奖' : '未中奖' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="addtime" label="投注时间" width="180" />
        <el-table-column label="操作" width="120">
          <template #default="scope">
            <el-button type="primary" size="small" link>详情</el-button>
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
import { ref, reactive } from 'vue'
import { Search, Refresh } from '@element-plus/icons-vue'

const searchForm = reactive({
  cpname: ''
})

const tableData = ref([])
</script>

<style scoped lang="scss">
.bet-list-container {
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

