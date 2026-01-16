<template>
  <div class="page-container">
    <div class="page-header">
      <div class="title">设置开奖</div>
      <div class="subtitle">手动设置彩票开奖号码</div>
    </div>
    
    <el-card shadow="never">
      <el-form
        ref="formRef"
        :model="form"
        :rules="rules"
        label-width="120px"
        style="max-width: 800px;"
      >
        <el-form-item label="彩种" prop="lotteryId">
          <el-select
            v-model="form.lotteryId"
            placeholder="请选择彩种"
            style="width: 300px;"
            @change="handleLotteryChange"
          >
            <el-option
              v-for="item in lotteryList"
              :key="item.id"
              :label="item.name"
              :value="item.id"
            />
          </el-select>
        </el-form-item>
        
        <el-form-item label="期号" prop="issue">
          <el-input
            v-model="form.issue"
            placeholder="请输入期号"
            style="width: 300px;"
          />
          <div class="tip">当前最新期号：{{ currentIssue }}</div>
        </el-form-item>
        
        <el-form-item label="开奖号码" prop="drawNumber" required>
          <div class="number-input-group">
            <template v-if="selectedLottery">
              <el-input
                v-for="(num, index) in form.drawNumbers"
                :key="index"
                v-model="form.drawNumbers[index]"
                :placeholder="`号码${index + 1}`"
                style="width: 80px;"
                @input="validateNumber(index)"
              />
            </template>
            <span v-else class="placeholder-text">请先选择彩种</span>
          </div>
          <div class="tip">{{ numberTip }}</div>
        </el-form-item>
        
        <el-form-item label="开奖时间" prop="drawTime">
          <el-date-picker
            v-model="form.drawTime"
            type="datetime"
            placeholder="选择开奖时间"
            value-format="YYYY-MM-DD HH:mm:ss"
            style="width: 300px;"
          />
        </el-form-item>
        
        <el-form-item label="备注">
          <el-input
            v-model="form.remark"
            type="textarea"
            :rows="3"
            placeholder="请输入备注信息"
            style="width: 500px;"
          />
        </el-form-item>
        
        <el-form-item>
          <el-button type="primary" @click="handleSubmit" :loading="loading">
            <el-icon><Check /></el-icon>
            确认开奖
          </el-button>
          <el-button @click="handleReset">
            <el-icon><Refresh /></el-icon>
            重置
          </el-button>
        </el-form-item>
      </el-form>
    </el-card>
    
    <!-- 最近开奖记录 -->
    <el-card shadow="never" style="margin-top: 20px;">
      <template #header>
        <span style="font-weight: 600;">最近开奖记录</span>
      </template>
      
      <el-table :data="recentDraws" border stripe>
        <el-table-column prop="lotteryName" label="彩种" width="150" />
        <el-table-column prop="issue" label="期号" width="120" align="center" />
        <el-table-column prop="drawNumber" label="开奖号码" min-width="250">
          <template #default="{ row }">
            <div class="draw-numbers">
              <span
                v-for="(num, index) in row.drawNumber.split(',')"
                :key="index"
                class="number-ball"
              >
                {{ num }}
              </span>
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="drawTime" label="开奖时间" width="180" />
        <el-table-column prop="operator" label="操作员" width="120" />
      </el-table>
    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { ElMessage } from 'element-plus'

const formRef = ref(null)
const loading = ref(false)

const lotteryList = ref([
  { id: 1, name: '重庆时时彩', code: 'CQSSC', type: 'ssc', numberCount: 5, numberRange: [0, 9] },
  { id: 2, name: '新疆时时彩', code: 'XJSSC', type: 'ssc', numberCount: 5, numberRange: [0, 9] },
  { id: 3, name: '北京赛车PK10', code: 'PK10', type: 'pk10', numberCount: 10, numberRange: [1, 10] },
  { id: 4, name: '快3', code: 'K3', type: 'k3', numberCount: 3, numberRange: [1, 6] },
  { id: 5, name: '11选5', code: '11X5', type: '11x5', numberCount: 5, numberRange: [1, 11] }
])

const selectedLottery = ref(null)
const currentIssue = ref('20240320072')

const form = reactive({
  lotteryId: '',
  issue: '',
  drawNumbers: [],
  drawTime: '',
  remark: ''
})

const rules = {
  lotteryId: [{ required: true, message: '请选择彩种', trigger: 'change' }],
  issue: [{ required: true, message: '请输入期号', trigger: 'blur' }],
  drawTime: [{ required: true, message: '请选择开奖时间', trigger: 'change' }]
}

const recentDraws = ref([
  {
    lotteryName: '重庆时时彩',
    issue: '20240320071',
    drawNumber: '3,5,8,1,9',
    drawTime: '2024-03-20 18:10:00',
    operator: 'admin'
  },
  {
    lotteryName: '新疆时时彩',
    issue: '20240320070',
    drawNumber: '1,4,6,2,8',
    drawTime: '2024-03-20 18:00:00',
    operator: 'admin'
  },
  {
    lotteryName: '北京赛车PK10',
    issue: '20240320143',
    drawNumber: '03,07,01,10,05,02,09,04,08,06',
    drawTime: '2024-03-20 17:55:00',
    operator: 'admin'
  }
])

const numberTip = computed(() => {
  if (!selectedLottery.value) return ''
  const lottery = selectedLottery.value
  return `请输入${lottery.numberCount}个号码，范围：${lottery.numberRange[0]}-${lottery.numberRange[1]}`
})

const handleLotteryChange = (id) => {
  selectedLottery.value = lotteryList.value.find(item => item.id === id)
  if (selectedLottery.value) {
    form.drawNumbers = new Array(selectedLottery.value.numberCount).fill('')
    // 模拟获取当前期号
    currentIssue.value = `2024032007${Math.floor(Math.random() * 10)}`
  }
}

const validateNumber = (index) => {
  if (!selectedLottery.value) return
  
  const num = parseInt(form.drawNumbers[index])
  const [min, max] = selectedLottery.value.numberRange
  
  if (isNaN(num) || num < min || num > max) {
    form.drawNumbers[index] = ''
  }
}

const handleSubmit = () => {
  formRef.value.validate((valid) => {
    if (valid) {
      // 验证号码是否填写完整
      const hasEmpty = form.drawNumbers.some(num => num === '' || num === null || num === undefined)
      if (hasEmpty) {
        ElMessage.error('请填写完整的开奖号码')
        return
      }
      
      loading.value = true
      
      // 模拟提交
      setTimeout(() => {
        ElMessage.success('开奖设置成功')
        handleReset()
        loading.value = false
      }, 1000)
    }
  })
}

const handleReset = () => {
  formRef.value?.resetFields()
  form.drawNumbers = []
  selectedLottery.value = null
}
</script>

<style lang="scss" scoped>
.tip {
  font-size: 12px;
  color: #909399;
  margin-top: 5px;
}

.number-input-group {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.placeholder-text {
  color: #c0c4cc;
  font-size: 14px;
}

.draw-numbers {
  display: flex;
  gap: 8px;
  align-items: center;
  flex-wrap: wrap;
}

.number-ball {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #409eff;
  color: #fff;
  font-weight: 600;
  font-size: 14px;
}
</style>
