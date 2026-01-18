<template>
  <div class="set-lottery-container">
    <div class="page-header">
      <div class="title">设置开奖</div>
      <div class="subtitle">查看预设开奖号码（代理端只读）</div>
    </div>

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
                <span
                  v-for="i in 3"
                  :key="i"
                  class="number-display"
                  :class="{ 'is-set': row[`opencode${i}`] !== '' }"
                >
                  {{ row[`opencode${i}`] || '-' }}
                </span>
              </template>
              
              <!-- 时时彩类型 -->
              <template v-else-if="typeid === 'ssc'">
                <span
                  v-for="i in 5"
                  :key="i"
                  class="number-display"
                  :class="{ 'is-set': row[`opencode${i}`] !== '' && row[`opencode${i}`] !== undefined }"
                >
                  {{ row[`opencode${i}`] ?? '-' }}
                </span>
              </template>
              
              <!-- 11选5类型 -->
              <template v-else-if="typeid === 'x5'">
                <span
                  v-for="i in 5"
                  :key="i"
                  class="number-display"
                  :class="{ 'is-set': row[`opencode${i}`] !== '' }"
                >
                  {{ row[`opencode${i}`] || '-' }}
                </span>
              </template>
              
              <!-- PK10类型 -->
              <template v-else-if="typeid === 'pk10'">
                <span
                  v-for="i in 10"
                  :key="i"
                  class="number-display small"
                  :class="{ 'is-set': row[`opencode${i}`] !== '' }"
                >
                  {{ row[`opencode${i}`] || '-' }}
                </span>
              </template>
              
              <!-- 低频彩类型 -->
              <template v-else-if="typeid === 'dpc'">
                <span
                  v-for="i in 3"
                  :key="i"
                  class="number-display"
                  :class="{ 'is-set': row[`opencode${i}`] !== '' && row[`opencode${i}`] !== undefined }"
                >
                  {{ row[`opencode${i}`] ?? '-' }}
                </span>
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
            <el-tag v-if="row.stateadmin" type="success" size="small">
              {{ row.stateadmin }}
            </el-tag>
            <span v-else class="text-muted">-</span>
          </template>
        </el-table-column>
      </el-table>
      
      <div class="readonly-tip">
        <el-alert
          title="代理端只读模式"
          type="info"
          description="您当前是代理账号，只能查看预设开奖号码，不能修改。如需设置开奖，请联系管理员。"
          show-icon
          :closable="false"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { Refresh } from '@element-plus/icons-vue'
import { getYukaijiangList } from '@/api/lottery'

const loading = ref(false)
const cplist = ref([])
const currentCpname = ref('')
const openlist = ref([])
const typeid = ref('')

// 加载预开奖列表
const loadData = async (name = '') => {
  const cpnameToLoad = name || currentCpname.value
  
  loading.value = true
  try {
    // 调用接口，参数名与后台一致
    const data = await getYukaijiangList({ name: cpnameToLoad })
    
    cplist.value = data.cplist || []
    typeid.value = data.typeid || ''
    currentCpname.value = data.cpname || cpnameToLoad
    
    // 处理开奖号码，分解到各个选择器
    const list = data.openlist || []
    list.forEach(item => {
      const codes = item.opencode ? item.opencode.split(',') : []
      for (let i = 0; i < 20; i++) {
        item[`opencode${i+1}`] = codes[i] || ''
      }
      item.saving = false
    })
    openlist.value = list
    
  } catch (error) {
    console.error('加载失败:', error)
    ElMessage.error(error.message || '加载失败')
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

onMounted(() => {
  // 页面加载时直接调用API，后端会自动返回第一个彩种
  loadData()
})
</script>

<style scoped lang="scss">
.set-lottery-container {
  padding: 20px;
}

.page-header {
  margin-bottom: 20px;
  
  .title {
    font-size: 20px;
    font-weight: 600;
    color: #303133;
    margin-bottom: 5px;
  }
  
  .subtitle {
    font-size: 14px;
    color: #909399;
  }
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

.opencode-selects {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  align-items: center;
  padding: 5px 0;
  gap: 6px;
}

.number-display {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 40px;
  height: 40px;
  padding: 0 8px;
  border-radius: 50%;
  border: 2px solid #dcdfe6;
  color: #909399;
  font-weight: 600;
  font-size: 16px;
  background: #f5f7fa;
  
  &.small {
    min-width: 36px;
    height: 36px;
    font-size: 14px;
  }
  
  &.is-set {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    border-color: #667eea;
  }
}

.text-muted {
  color: #909399;
}

.readonly-tip {
  margin-top: 20px;
}
</style>
