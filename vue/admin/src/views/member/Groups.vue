<template>
  <div class="member-groups-container">
    <el-card>
      <template #header>
        <div class="card-header">
          <span>会员组管理</span>
          <el-button type="primary" @click="handleAdd">添加会员组</el-button>
        </div>
      </template>

      <el-table :data="groupList" border stripe>
        <el-table-column prop="groupid" label="ID" width="80" />
        <el-table-column prop="groupname" label="会员组名称" />
        <el-table-column prop="level" label="等级" width="80" />
        <el-table-column label="是否代理" width="100">
          <template #default="{ row }">
            <el-tag :type="row.isagent === 1 ? 'success' : 'info'">
              {{ row.isagent === 1 ? '是' : '否' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="默认注册组" width="120">
          <template #default="{ row }">
            <el-tag :type="row.isdefautreg === 1 ? 'success' : 'info'">
              {{ row.isdefautreg === 1 ? '是' : '否' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="状态" width="100">
          <template #default="{ row }">
            <el-switch
              v-model="row.groupstatus"
              :active-value="1"
              :inactive-value="0"
              @change="handleStatusChange(row)"
            />
          </template>
        </el-table-column>
        <el-table-column prop="listorder" label="排序" width="80" />
        <el-table-column label="操作" width="200" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" size="small" @click="handleEdit(row)">编辑</el-button>
            <el-button type="danger" size="small" @click="handleDelete(row)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <!-- 添加/编辑对话框 -->
    <el-dialog
      v-model="dialogVisible"
      :title="dialogTitle"
      width="600px"
    >
      <el-form :model="form" label-width="120px">
        <el-form-item label="会员组名称">
          <el-input v-model="form.groupname" placeholder="请输入会员组名称" />
        </el-form-item>
        <el-form-item label="等级">
          <el-input-number v-model="form.level" :min="0" :max="999" />
        </el-form-item>
        <el-form-item label="是否代理">
          <el-switch v-model="form.isagent" :active-value="1" :inactive-value="0" />
        </el-form-item>
        <el-form-item label="默认注册组">
          <el-switch v-model="form.isdefautreg" :active-value="1" :inactive-value="0" />
        </el-form-item>
        <el-form-item label="状态">
          <el-switch v-model="form.groupstatus" :active-value="1" :inactive-value="0" />
        </el-form-item>
        <el-form-item label="排序">
          <el-input-number v-model="form.listorder" :min="0" />
        </el-form-item>
        <el-form-item label="晋级金额">
          <el-input-number v-model="form.jjje" :precision="2" :min="0" />
        </el-form-item>
        <el-form-item label="最低充值">
          <el-input-number v-model="form.lowest" :min="0" />
        </el-form-item>
        <el-form-item label="最高充值">
          <el-input-number v-model="form.highest" :min="0" />
        </el-form-item>
        <el-form-item label="返点率">
          <el-input v-model="form.fandian" placeholder="例如: 1.5" />
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
import { getMemberGroups, addMemberGroup, updateMemberGroup, deleteMemberGroup, toggleMemberGroupStatus } from '@/api/member'

const groupList = ref([])
const dialogVisible = ref(false)
const dialogTitle = ref('添加会员组')
const isEdit = ref(false)

const form = ref({
  groupid: null,
  groupname: '',
  level: 0,
  isagent: 0,
  isdefautreg: 0,
  groupstatus: 1,
  listorder: 0,
  jjje: 0,
  lowest: 10,
  highest: 50000,
  fandian: '0'
})

const loadData = async () => {
  try {
    const res = await getMemberGroups()
    if (res.code === 200) {
      groupList.value = res.data
    }
  } catch (error) {
    ElMessage.error('加载数据失败')
  }
}

const handleAdd = () => {
  isEdit.value = false
  dialogTitle.value = '添加会员组'
  form.value = {
    groupid: null,
    groupname: '',
    level: 0,
    isagent: 0,
    isdefautreg: 0,
    groupstatus: 1,
    listorder: 0,
    jjje: 0,
    lowest: 10,
    highest: 50000,
    fandian: '0'
  }
  dialogVisible.value = true
}

const handleEdit = (row) => {
  isEdit.value = true
  dialogTitle.value = '编辑会员组'
  form.value = { ...row }
  dialogVisible.value = true
}

const handleSubmit = async () => {
  try {
    if (isEdit.value) {
      const res = await updateMemberGroup(form.value.groupid, form.value)
      if (res.code === 200) {
        ElMessage.success('修改成功')
        dialogVisible.value = false
        loadData()
      } else {
        ElMessage.error(res.msg || '修改失败')
      }
    } else {
      const res = await addMemberGroup(form.value)
      if (res.code === 200) {
        ElMessage.success('添加成功')
        dialogVisible.value = false
        loadData()
      } else {
        ElMessage.error(res.msg || '添加失败')
      }
    }
  } catch (error) {
    ElMessage.error(error.message || '操作失败')
  }
}

const handleStatusChange = async (row) => {
  try {
    const res = await toggleMemberGroupStatus(row.groupid, row.groupstatus)
    if (res.code === 200) {
      ElMessage.success('状态已更新')
    } else {
      ElMessage.error(res.msg || '更新失败')
      row.groupstatus = row.groupstatus === 1 ? 0 : 1
    }
  } catch (error) {
    ElMessage.error('更新失败')
    row.groupstatus = row.groupstatus === 1 ? 0 : 1
  }
}

const handleDelete = async (row) => {
  try {
    await ElMessageBox.confirm('确定要删除该会员组吗？', '提示', {
      type: 'warning'
    })
    const res = await deleteMemberGroup(row.groupid)
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

onMounted(() => {
  loadData()
})
</script>

<style scoped>
.member-groups-container {
  padding: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
</style>

