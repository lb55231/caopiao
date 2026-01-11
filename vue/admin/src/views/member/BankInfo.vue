<template>
  <div class="bank-info-container">
    <el-card>
      <template #header>
        <span>银行信息</span>
      </template>

      <!-- 搜索筛选 -->
      <el-form :inline="true" class="search-form">
        <el-form-item label="用户名">
          <el-input v-model="searchForm.username" placeholder="请输入用户名" clearable />
        </el-form-item>
        <el-form-item label="银行名称">
          <el-input v-model="searchForm.bankname" placeholder="请输入银行名称" clearable />
        </el-form-item>
        <el-form-item label="持卡人">
          <el-input v-model="searchForm.accountname" placeholder="请输入持卡人姓名" clearable />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleSearch">搜索</el-button>
          <el-button @click="handleReset">重置</el-button>
        </el-form-item>
      </el-form>

      <!-- 表格 -->
      <el-table :data="dataList" border stripe>
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="username" label="用户名" width="120" />
        <el-table-column prop="accountname" label="持卡人" width="120" />
        <el-table-column prop="bankname" label="银行名称" width="150" />
        <el-table-column prop="banknumber" label="银行卡号" width="200" />
        <el-table-column prop="bankbranch" label="开户行" min-width="200" />
        <el-table-column label="默认卡" width="100">
          <template #default="{ row }">
            <el-tag :type="row.isdefault === 1 ? 'success' : 'info'">
              {{ row.isdefault === 1 ? '是' : '否' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="状态" width="100">
          <template #default="{ row }">
            <el-tag :type="row.state === 1 ? 'success' : 'danger'">
              {{ row.state === 1 ? '正常' : '禁用' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="绑定时间" width="180">
          <template #default="{ row }">
            {{ row.date }}
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
import { getMemberBanks } from '@/api/member'

const dataList = ref([])

const searchForm = ref({
  username: '',
  bankname: '',
  accountname: ''
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
    const res = await getMemberBanks(params)
    if (res.code === 200) {
      dataList.value = res.data.list
      pagination.value.total = res.data.total
    }
  } catch (error) {
    ElMessage.error('加载数据失败')
  }
}

const handleSearch = () => {
  pagination.value.page = 1
  loadData()
}

const handleReset = () => {
  searchForm.value = {
    username: '',
    bankname: '',
    accountname: ''
  }
  handleSearch()
}

onMounted(() => {
  loadData()
})
</script>

<style scoped>
.bank-info-container {
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

