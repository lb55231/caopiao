<template>
  <div class="agent-links-container">
    <el-card>
      <template #header>
        <div class="card-header">
          <span>代理注册链接</span>
          <el-button type="primary" @click="handleAdd">生成链接</el-button>
        </div>
      </template>

      <!-- 表格 -->
      <el-table :data="dataList" border stripe>
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="username" label="代理账号" width="150" />
        <el-table-column prop="invite_code" label="邀请码" width="150">
          <template #default="{ row }">
            <el-tag type="primary">{{ row.invite_code }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="注册链接" min-width="400">
          <template #default="{ row }">
            <el-input :value="getRegisterUrl(row.invite_code)" readonly>
              <template #append>
                <el-button @click="copyLink(row.invite_code)">复制</el-button>
              </template>
            </el-input>
          </template>
        </el-table-column>
        <el-table-column prop="reg_count" label="注册人数" width="100" />
        <el-table-column label="创建时间" width="180">
          <template #default="{ row }">
            {{ formatTime(row.created_at) }}
          </template>
        </el-table-column>
        <el-table-column label="操作" width="100" fixed="right">
          <template #default="{ row }">
            <el-button type="danger" size="small" @click="handleDelete(row)">删除</el-button>
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

    <!-- 生成链接对话框 -->
    <el-dialog v-model="dialogVisible" title="生成代理注册链接" width="500px">
      <el-form :model="form" label-width="120px">
        <el-form-item label="代理账号">
          <el-input v-model="form.username" placeholder="请输入代理账号" />
        </el-form-item>
        <el-form-item label="邀请码">
          <el-input v-model="form.invite_code" placeholder="可自定义或留空自动生成">
            <template #append>
              <el-button @click="generateCode">随机生成</el-button>
            </template>
          </el-input>
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleSubmit">确定</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { getAgentLinks, addAgentLink, deleteAgentLink } from '@/api/member'

const dataList = ref([])
const dialogVisible = ref(false)

const form = ref({
  username: '',
  invite_code: ''
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
      page_size: pagination.value.page_size
    }
    const res = await getAgentLinks(params)
    if (res.code === 200) {
      dataList.value = res.data.list
      pagination.value.total = res.data.total
    }
  } catch (error) {
    ElMessage.error('加载数据失败')
  }
}

const getRegisterUrl = (code) => {
  return `${window.location.origin}/register?invite_code=${code}`
}

const copyLink = (code) => {
  const url = getRegisterUrl(code)
  navigator.clipboard.writeText(url).then(() => {
    ElMessage.success('链接已复制到剪贴板')
  }).catch(() => {
    ElMessage.error('复制失败，请手动复制')
  })
}

const generateCode = () => {
  const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'
  let code = ''
  for (let i = 0; i < 8; i++) {
    code += chars.charAt(Math.floor(Math.random() * chars.length))
  }
  form.value.invite_code = code
}

const handleAdd = () => {
  form.value = {
    username: '',
    invite_code: ''
  }
  dialogVisible.value = true
}

const handleSubmit = async () => {
  try {
    if (!form.value.username) {
      ElMessage.warning('请输入代理账号')
      return
    }
    const res = await addAgentLink(form.value)
    if (res.code === 200) {
      ElMessage.success('生成成功')
      dialogVisible.value = false
      loadData()
    } else {
      ElMessage.error(res.msg || '生成失败')
    }
  } catch (error) {
    ElMessage.error(error.message || '生成失败')
  }
}

const handleDelete = async (row) => {
  try {
    await ElMessageBox.confirm('确定要删除该链接吗？', '提示', {
      type: 'warning'
    })
    const res = await deleteAgentLink(row.id)
    if (res.code === 200) {
      ElMessage.success('删除成功')
      loadData()
    } else {
      ElMessage.error(res.msg || '删除失败')
    }
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('删除失败')
    }
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
.agent-links-container {
  padding: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.el-pagination {
  margin-top: 20px;
  justify-content: flex-end;
}
</style>

