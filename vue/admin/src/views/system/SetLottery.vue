<template>
  <div class="set-lottery-container">
    <nav class="breadcrumb-nav">
      <div class="nav-left">
        <el-select 
          v-model="currentCpname" 
          @change="handleCpChange"
          placeholder="请选择彩种"
          style="width: 200px;"
        >
          <el-option
            v-for="cp in cplist"
            :key="cp.name"
            :label="cp.title"
            :value="cp.name"
          />
        </el-select>
      </div>
      <div class="nav-right">
        <el-button type="success" @click="handleRefresh">
          <el-icon><Refresh /></el-icon>
          刷新
        </el-button>
      </div>
    </nav>

    <div class="page-content">
      <el-table 
        v-loading="loading"
        :data="openlist" 
        border 
        class="lottery-table"
      >
        <el-table-column label="彩种" width="100" align="center">
          <template #default="{ row }">
            {{ row.cptitle }}
          </template>
        </el-table-column>
        
        <el-table-column label="期号" width="120" align="center">
          <template #default="{ row }">
            {{ row.expect }}
          </template>
        </el-table-column>
        
        <el-table-column label="开奖号码" min-width="400" align="center">
          <template #default="{ row }">
            <div class="opencode-selects">
              <!-- K3类型 -->
              <template v-if="typeid === 'k3'">
                <el-select 
                  v-for="i in 3" 
                  :key="i"
                  v-model="row[`opencode${i}`]"
                  :placeholder="`第${i}球`"
                  size="small"
                  style="width: 80px; margin: 0 3px;"
                >
                  <el-option 
                    v-for="j in 6" 
                    :key="j" 
                    :label="j" 
                    :value="j"
                  />
                </el-select>
              </template>
              
              <!-- 时时彩类型 -->
              <template v-else-if="typeid === 'ssc'">
                <el-select 
                  v-for="i in 5" 
                  :key="i"
                  v-model="row[`opencode${i}`]"
                  :placeholder="`第${i}球`"
                  size="small"
                  style="width: 80px; margin: 0 3px;"
                >
                  <el-option 
                    v-for="j in [0,1,2,3,4,5,6,7,8,9]" 
                    :key="j" 
                    :label="j" 
                    :value="j"
                  />
                </el-select>
              </template>
              
              <!-- 11选5类型 -->
              <template v-else-if="typeid === 'x5'">
                <el-select 
                  v-for="i in 5" 
                  :key="i"
                  v-model="row[`opencode${i}`]"
                  :placeholder="`第${i}球`"
                  size="small"
                  style="width: 80px; margin: 0 3px;"
                >
                  <el-option 
                    v-for="j in ['01','02','03','04','05','06','07','08','09','10','11']" 
                    :key="j" 
                    :label="j" 
                    :value="j"
                  />
                </el-select>
              </template>
              
              <!-- PK10类型 -->
              <template v-else-if="typeid === 'pk10'">
                <el-select 
                  v-for="i in 10" 
                  :key="i"
                  v-model="row[`opencode${i}`]"
                  :placeholder="`第${i}球`"
                  size="small"
                  style="width: 70px; margin: 0 2px;"
                >
                  <el-option 
                    v-for="j in ['01','02','03','04','05','06','07','08','09','10']" 
                    :key="j" 
                    :label="j" 
                    :value="j"
                  />
                </el-select>
              </template>
              
              <!-- 低频彩类型 -->
              <template v-else-if="typeid === 'dpc'">
                <el-select 
                  v-for="i in 3" 
                  :key="i"
                  v-model="row[`opencode${i}`]"
                  :placeholder="`第${i}球`"
                  size="small"
                  style="width: 80px; margin: 0 3px;"
                >
                  <el-option 
                    v-for="j in [0,1,2,3,4,5,6,7,8,9]" 
                    :key="j" 
                    :label="j" 
                    :value="j"
                  />
                </el-select>
              </template>
            </div>
          </template>
        </el-table-column>
        
        <el-table-column label="开奖时间" width="180" align="center">
          <template #default="{ row }">
            {{ row.opentime }}
          </template>
        </el-table-column>
        
        <el-table-column label="管理人" width="100" align="center">
          <template #default="{ row }">
            {{ row.stateadmin }}
          </template>
        </el-table-column>
        
        <el-table-column label="操作" width="100" align="center" fixed="right">
          <template #default="{ row }">
            <el-button 
              type="primary" 
              size="small"
              @click="handleSave(row)"
              :loading="row.saving"
            >
              保存
            </el-button>
          </template>
        </el-table-column>
      </el-table>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Refresh } from '@element-plus/icons-vue'
import { getYukaijiang, saveYkj } from '@/api/lottery'

const loading = ref(false)
const cplist = ref([])
const currentCpname = ref('')
const openlist = ref([])
const typeid = ref('')

// 加载预开奖列表
const loadData = async (name = '') => {
  // 如果传入了name，使用传入的；否则使用当前选中的
  const cpnameToLoad = name || currentCpname.value
  
  loading.value = true
  try {
    const res = await getYukaijiang({ name: cpnameToLoad })
    if (res.code === 200) {
      cplist.value = res.data.cplist || []
      typeid.value = res.data.typeid || ''
      currentCpname.value = res.data.cpname || cpnameToLoad
      
      // 处理开奖号码，分解到各个选择器
      const list = res.data.openlist || []
      list.forEach(item => {
        const codes = item.opencode ? item.opencode.split(',') : []
        for (let i = 0; i < 20; i++) {
          item[`opencode${i+1}`] = codes[i] || ''
        }
        item.saving = false
      })
      openlist.value = list
    } else {
      ElMessage.error(res.msg || '加载失败')
    }
  } catch (error) {
    ElMessage.error('加载失败: ' + error.message)
  } finally {
    loading.value = false
  }
}

// 切换彩种
const handleCpChange = () => {
  loadData()
}

// 刷新
const handleRefresh = () => {
  loadData()
}

// 保存
const handleSave = async (row) => {
  // 收集开奖号码
  const data = {
    expect: row.expect,
    name: row.name,
    opentime: row.opentime
  }
  
  // 根据彩种类型收集号码
  let codeCount = 0
  if (typeid.value === 'k3' || typeid.value === 'dpc') {
    codeCount = 3
  } else if (typeid.value === 'ssc' || typeid.value === 'x5') {
    codeCount = 5
  } else if (typeid.value === 'pk10') {
    codeCount = 10
  }
  
  for (let i = 1; i <= codeCount; i++) {
    data[`opencode${i}`] = row[`opencode${i}`] || ''
  }
  
  // 验证是否填写完整
  let hasEmpty = false
  for (let i = 1; i <= codeCount; i++) {
    if (data[`opencode${i}`] === '') {
      hasEmpty = true
      break
    }
  }
  
  if (hasEmpty) {
    ElMessage.warning('请选择完整的开奖号码')
    return
  }
  
  try {
    await ElMessageBox.confirm('确定修改吗？', '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })
    
    row.saving = true
    const res = await saveYkj(data)
    
    if (res.code === 200) {
      ElMessage.success('保存成功')
      row.stateadmin = res.data.stateadmin || ''
      // 标记为已保存（绿色背景）
      row.isbc = 1
    } else {
      ElMessage.error(res.msg || '保存失败')
    }
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('保存失败: ' + error.message)
    }
  } finally {
    row.saving = false
  }
}

onMounted(() => {
  // 页面加载时直接调用API，后端会自动返回第一个彩种
  loadData()
})
</script>

<style scoped lang="scss">
.set-lottery-container {
  padding: 20px;
}

.breadcrumb-nav {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding: 10px;
  background: #fff;
  border-radius: 4px;
}

.page-content {
  background: #fff;
  padding: 20px;
  border-radius: 4px;
}

.lottery-table {
  :deep(.el-table__row) {
    &.success {
      background-color: #f0f9ff !important;
    }
  }
}

.opencode-selects {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  align-items: center;
  padding: 5px 0;
}
</style>
