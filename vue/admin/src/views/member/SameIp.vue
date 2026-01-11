<template>
  <div class="same-ip-container">
    <el-card>
      <template #header>
        <span>同IP会员检测</span>
      </template>

      <!-- 搜索 -->
      <el-form :inline="true" class="search-form">
        <el-form-item label="IP地址">
          <el-input v-model="searchForm.ip" placeholder="请输入IP地址" clearable />
        </el-form-item>
        <el-form-item label="最小数量">
          <el-input-number v-model="searchForm.min_count" :min="2" :max="999" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="loadData">查询</el-button>
        </el-form-item>
      </el-form>

      <!-- 表格 -->
      <el-table :data="dataList" border stripe>
        <el-table-column prop="ip" label="IP地址" width="150" />
        <el-table-column prop="member_count" label="会员数量" width="120">
          <template #default="{ row }">
            <el-tag type="danger">{{ row.member_count }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="会员列表">
          <template #default="{ row }">
            <el-tag v-for="member in row.members" :key="member.id" style="margin-right: 10px;">
              {{ member.username }} (余额: {{ member.balance }})
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="注册时间范围" width="200">
          <template #default="{ row }">
            {{ formatTime(row.first_reg_time) }} <br />至<br /> {{ formatTime(row.last_reg_time) }}
          </template>
        </el-table-column>
      </el-table>

      <!-- 分页 -->
      <el-pagination
        v-model:current-page="pagination.page"
        v-model:page-size="pagination.page_size"
        :total="pagination.total"
        :page-sizes="[10, 20, 50, 100]"
        layout="total, sizes, prev, pager, next, jumper"
        @size-change="loadData"
        @current-change="loadData"
      />
    </el-card>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { getSameIpMembers } from '@/api/member'

const dataList = ref([])
const searchForm = ref({
  ip: '',
  min_count: 2
})

const pagination = ref({
  page: 1,
  page_size: 20,
  total: 0
})

const loadData = async () => {
  try {
    const params = {
      page: pagination.value.page,
      page_size: pagination.value.page_size,
      ...searchForm.value
    }
    const res = await getSameIpMembers(params)
    if (res.code === 200) {
      dataList.value = res.data.list
      pagination.value.total = res.data.total
    }
  } catch (error) {
    ElMessage.error('加载数据失败')
  }
}

const formatTime = (timestamp) => {
  if (!timestamp) return '-'
  const date = new Date(timestamp * 1000)
  return date.toLocaleString('zh-CN')
}

onMounted(() => {
  loadData()
})
</script>

<style scoped>
.same-ip-container {
  padding: 20px;
}

.search-form {
  margin-bottom: 20px;
}

.el-pagination {
  margin-top: 20px;
  justify-content: flex-end;
}
</style>

