<template>
  <div class="k3-game-page">
    <!-- 顶部导航 -->
    <header class="game-header">
      <div class="header-left" @click="$router.back()">
        <van-icon name="arrow-left" />
      </div>
      <h1 class="header-title" @click="showGameSelector">
        {{ gameInfo.title }} <van-icon name="arrow-down" size="14" />
      </h1>
      <div class="header-right"></div>
    </header>

    <!-- 游戏选择器弹窗 -->
    <van-popup 
      v-model:show="showGameList" 
      position="top" 
      :style="{ height: 'auto', maxHeight: '80vh' }"
      round
    >
      <div class="game-selector">
        <div class="game-selector-header">
          <h3>选择游戏</h3>
          <van-icon name="cross" @click="showGameList = false" />
        </div>
        <div class="game-list">
          <div 
            v-for="game in availableGames" 
            :key="game.id"
            :class="['game-item', { 'active': game.name === gameInfo.name }]"
            @click="switchGame(game)"
          >
            <img :src="getImageUrl(game.logo)" :alt="game.title" class="game-logo" />
            <div class="game-info">
              <div class="game-title">{{ game.title }}</div>
              <div class="game-desc">{{ game.ftitle || '官方游戏' }}</div>
            </div>
            <van-icon 
              v-if="game.name === gameInfo.name" 
              name="success" 
              color="#F31C1D" 
              size="20"
            />
          </div>
        </div>
      </div>
    </van-popup>

    <!-- 期号和开奖信息区 -->
    <div class="betting-issue-countdown cz-opennumb">
      <dl class="dl-container dl-flex">
        <!-- 上期开奖结果 -->
        <dt class="result-header">
          <div>{{ lastPeriodShort || '---' }}</div>
          <div class="result-title">订单匹配结果</div>
        </dt>
        <dd class="dl-flex result-content">
          <div class="result-text">{{ lastResultDisplay.text1 }}</div>
          <div class="result-text">{{ lastResultDisplay.text2 }}</div>
        </dd>
      </dl>

      <dl class="dl-container dl-flex countdown-row">
        <!-- 当前期倒计时 -->
        <dt class="countdown-header">
          <div>{{ currentPeriodShort || '---' }}</div>
          <div class="countdown-title">订单匹配截止</div>
        </dt>
        <dd class="countdown-content">
          <i class="j-lottery-time">
            <span class="time-unit hh">
              <span class="bj">{{ displayHour.charAt(0) }}</span>
              <span class="bj">{{ displayHour.charAt(1) }}</span>
            </span>
            <em>:</em>
            <span class="time-unit mm">
              <span class="bj">{{ displayMinute.charAt(0) }}</span>
              <span class="bj">{{ displayMinute.charAt(1) }}</span>
            </span>
            <em>:</em>
            <span class="time-unit ss">
              <span class="bj">{{ displaySecond.charAt(0) }}</span>
              <span class="bj">{{ displaySecond.charAt(1) }}</span>
            </span>
          </i>
        </dd>
      </dl>
    </div>

    <!-- 投注区域 -->
    <section class="ui-container">
      <div class="betting-box">
        <!-- 四个主要选项（在品牌上方） -->
        <div class="menu dl-container">
          <div 
            v-for="option in mainOptions" 
            :key="option.playid"
            :class="['menu-item ball-number', { 'menu-active': isSelected(option.value) }]"
            :playid="option.playid"
            @click="toggleNumber(option.value)"
          >
            {{ option.label }}
          </div>
        </div>

        <!-- 品牌图片展示区（仅展示，不可选择） -->
        <div class="shop-item">
          <div class="shop-item-container dl-container">
            <ul class="ball-list-ul">
              <li 
                v-for="brand in brandList" 
                :key="brand.code"
                class="ball-item"
              >
                <a class="ball-number1">
                  <img :src="brand.logo" :alt="brand.name" />
                </a>
              </li>
            </ul>
          </div>
        </div>

        <!-- 当前余额显示 -->
        <div class="balance-container" v-if="userStore.token">
          <div class="balance-text">
            当前余额：<span class="balance-amount">{{ userStore.balance || '0.00' }}</span>
          </div>
        </div>

        <!-- 金额输入框 -->
        <div class="amount-input-container">
          <input 
            type="number" 
            v-model="betAmount" 
            class="amount-input" 
            placeholder="2"
          />
          <span class="amount-unit">元</span>
        </div>

        <!-- 快捷金额按钮 -->
        <div class="quick-amount-container">
          <div 
            v-for="amount in quickAmounts" 
            :key="amount"
            class="quick-amount-btn" 
            @click="setBetAmount(amount)"
          >
            {{ amount }}
          </div>
        </div>

        <!-- 底部操作栏 -->
        <div class="clear-btn">
          <div class="betting-empty" @click="clearSelection">清空</div>
          <div class="clear-btn-right">
            <div class="total">
              共 <span class="betting-sum">{{ totalBets }}</span> 件 合计 ￥<span class="price betting-sum-money">{{ finalTotalAmount }}</span>元
            </div>
            <div class="submit-btn" @click="submitOrder">提交订单</div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { showToast, showDialog } from 'vant'
import { getCurrentPeriod, submitBet } from '@/api/game'
import { getLotteryList } from '@/api/lottery'
import { useUserStore } from '@/stores/user'
import { getImageUrl } from '@/utils/image'

const route = useRoute()
const router = useRouter()
const userStore = useUserStore()

const gameInfo = ref({
  name: route.params.name || 'hebk3',
  title: route.query.title || '河北快3'
})

// 游戏列表相关
const showGameList = ref(false)
const availableGames = ref([])

// 显示游戏选择器
const showGameSelector = () => {
  showGameList.value = true
}

// 切换游戏
const switchGame = (game) => {
  if (game.name === gameInfo.value.name) {
    showGameList.value = false
    return
  }
  
  showGameList.value = false
  
  // 清空选中的号码
  clearSelection()
  
  // 跳转到新游戏
  router.push({
    path: `/game/${game.typeid}/${game.name}`,
    query: { title: game.title }
  })
}

// 加载可用游戏列表
const loadAvailableGames = async () => {
  try {
    const result = await getLotteryList()
    
    if (result.code === 200 && result.data) {
      availableGames.value = result.data.filter(game => 
        game.issys == 1 && game.isopen == 1 // 只显示系统彩票且启用的
      )
    }
  } catch (error) {
    console.error('加载游戏列表失败:', error)
  }
}

// 期号和开奖信息
const lotteryData = ref(null)
const currentPeriod = ref('---')
const currentPeriodShort = ref('---')
const lastPeriod = ref('---')
const lastPeriodShort = ref('---')
const remainTime = ref(0)
const displayTime = ref('00:00:00')
const betAmount = ref(2) // 每注金额

// 倒计时定时器
let countdownTimer = null
let periodRefreshTimer = null

// 格式化倒计时显示的时、分、秒
const displayHour = computed(() => {
  const match = displayTime.value.match(/(\d{2}):(\d{2}):(\d{2})/)
  return match ? match[1] : '00'
})

const displayMinute = computed(() => {
  const match = displayTime.value.match(/(\d{2}):(\d{2}):(\d{2})/)
  return match ? match[2] : '00'
})

const displaySecond = computed(() => {
  const match = displayTime.value.match(/(\d{2}):(\d{2}):(\d{2})/)
  return match ? match[3] : '00'
})

// 快捷金额选项
const quickAmounts = [2, 4, 10, 20, 50, 100]

// 上期开奖结果显示（转换为伪装文本）
const lastResultDisplay = computed(() => {
  if (!lotteryData.value || !lotteryData.value.lastResult || !lotteryData.value.lastResult.opencode) {
    return { text1: '---', text2: '---' }
  }
  
  // 检查期号是否匹配（如果数据库中的期号和计算的上期期号不一致，说明数据过期）
  const dbExpect = lotteryData.value.lastResult.expect || ''
  const calculatedExpect = lotteryData.value.lastFullExpect || ''
  
  // 如果期号不匹配，显示等待开奖
  if (dbExpect !== calculatedExpect) {
    console.warn('期号不匹配！数据库:', dbExpect, '计算:', calculatedExpect)
    return { text1: '等待开奖', text2: '' }
  }
  
  const opencode = lotteryData.value.lastResult.opencode
  const codes = opencode.split(',').map(Number)
  const sum = codes.reduce((a, b) => a + b, 0)
  
  // 和值 > 10 为"普货"，<= 10 为"精品"
  const text1 = sum > 10 ? '普货' : '精品'
  // 和值为偶数为"一件"，奇数为"多件"
  const text2 = sum % 2 === 0 ? '一件' : '多件'
  
  console.log('开奖结果:', { opencode, sum, text1, text2, expect: dbExpect })
  
  return { text1, text2 }
})

// 四个主要选项
const mainOptions = [
  { playid: 'k3hzbig', value: '大', label: '普货' },
  { playid: 'k3hzsmall', value: '小', label: '精品' },
  { playid: 'k3hzodd', value: '单', label: '一件' },
  { playid: 'k3hzeven', value: '双', label: '多件' }
]

// 品牌列表
const baseUrl = import.meta.env.BASE_URL
const headerBgUrl = `url(${baseUrl}resources/k4/img_1.png)`
const brandList = [
  { code: '3', name: '李宁', logo: baseUrl + 'resources/k4/5.jpg' },
  { code: '4', name: '361°', logo: baseUrl + 'resources/k4/6.jpg' },
  { code: '5', name: '鸿星尔克', logo: baseUrl + 'resources/k4/7.jpg' },
  { code: '6', name: '顺丰', logo: baseUrl + 'resources/k4/8.jpg' },
  { code: '7', name: '百雀羚', logo: baseUrl + 'resources/k4/9.jpg' },
  { code: '8', name: '自然堂', logo: baseUrl + 'resources/k4/10.jpg' },
  { code: '9', name: '华为', logo: baseUrl + 'resources/k4/11.jpg' },
  { code: '10', name: '中兴', logo: baseUrl + 'resources/k4/12.jpg' },
  { code: '11', name: 'VIVO', logo: baseUrl + 'resources/k4/13.jpg' },
  { code: '12', name: '苏宁', logo: baseUrl + 'resources/k4/14.jpg' },
  { code: '13', name: '美的', logo: baseUrl + 'resources/k4/15.jpg' },
  { code: '14', name: '格力', logo: baseUrl + 'resources/k4/16.jpg' }
]

// 选中的号码（存储完整的选项对象，包含playid等信息）
const selectedNumbers = ref([])

// 是否选中（只检查主要选项）
const isSelected = (value) => {
  return selectedNumbers.value.some(item => item.value === value)
}

// 切换选择（只能选择主要选项：大小单双）
const toggleNumber = (value) => {
  // 只允许选择mainOptions中的选项
  const option = mainOptions.find(opt => opt.value === value)
  if (!option) return // 如果不是主要选项，不处理
  
  const index = selectedNumbers.value.findIndex(item => item.value === value)
  if (index > -1) {
    selectedNumbers.value.splice(index, 1)
  } else {
    selectedNumbers.value.push({
      value: option.value,
      playid: option.playid,
      label: option.label
    })
  }
}

// 设置投注金额
const setBetAmount = (amount) => {
  betAmount.value = amount
}

// 计算总注数和金额（按原项目逻辑：总金额 = 单注金额 × 注数）
const totalBets = computed(() => selectedNumbers.value.length)
const finalTotalAmount = computed(() => {
  const amount = Number(betAmount.value) || 2
  return (totalBets.value * amount).toFixed(2)
})

// 清空选择
const clearSelection = () => {
  selectedNumbers.value = []
}

// 提交订单
const submitOrder = async () => {
  if (totalBets.value === 0) {
    showToast('请选择商品')
    return
  }

  if (!userStore.token) {
    showToast('请先登录')
    router.push('/login')
    return
  }

  // 检查是否有当前期号
  if (!currentPeriod.value || currentPeriod.value === '---') {
    showToast('期号信息加载中，请稍后')
    return
  }

  // 检查倒计时是否已结束
  if (remainTime.value <= 0) {
    showToast('当前期已截止，请等待下一期')
    return
  }

  showDialog({
    title: '确认下单',
    message: `确认下单 ${totalBets.value} 件商品，共 ￥${finalTotalAmount.value} 元？`,
    showCancelButton: true
  }).then(async () => {
    try {
      const amount = Number(betAmount.value) || 2
      
      // 构造投注数据（按原项目格式）
      const bets = selectedNumbers.value.map(item => ({
        playId: item.playid,
        playName: item.label,
        numbersText: item.value, // 投注内容：大/小/单/双
        count: 1, // 每个选项算1注
        multiple: 1, // 倍数固定为1
        price: amount, // 单注金额
        rate: 2.0 // 赔率（简化处理，实际应从后端获取）
      }))
      
      // 提交投注
      const response = await submitBet({
        cpname: gameInfo.value.name,
        cptitle: gameInfo.value.title,
        period: currentPeriod.value,
        bets: bets
      })
      
      showToast('订单提交成功')
      
      // 更新用户余额
      if (response.data && response.data.newBalance !== undefined) {
        userStore.updateBalance(response.data.newBalance)
      } else {
        // 如果后端没返回新余额，手动计算
        const newBalance = (parseFloat(userStore.balance) - parseFloat(finalTotalAmount.value)).toFixed(2)
        userStore.updateBalance(newBalance)
      }
      
      clearSelection()
    } catch (error) {
      showToast(error.message || '提交失败')
    }
  }).catch(() => {
    // 用户取消
  })
}

// 格式化倒计时
const formatCountdown = (seconds) => {
  if (seconds <= 0) return '00:00:00'
  
  const h = Math.floor(seconds / 3600)
  const m = Math.floor((seconds % 3600) / 60)
  const s = seconds % 60
  
  const h1 = h < 10 ? '0' : '' + Math.floor(h / 10)
  const h2 = h < 10 ? '' + h : '' + (h % 10)
  const m1 = m < 10 ? '0' : '' + Math.floor(m / 10)
  const m2 = m < 10 ? '' + m : '' + (m % 10)
  const s1 = s < 10 ? '0' : '' + Math.floor(s / 10)
  const s2 = s < 10 ? '' + s : '' + (s % 10)
  
  return h1 + h2 + ':' + m1 + m2 + ':' + s1 + s2
}

// 启动倒计时
const startCountdown = (leftSec) => {
  if (countdownTimer) {
    clearInterval(countdownTimer)
  }
  if (periodRefreshTimer) {
    clearTimeout(periodRefreshTimer)
  }
  
  const localCurrentTime = new Date()
  const t = leftSec * 1000
  const endTime = localCurrentTime.getTime() + t
  
  remainTime.value = leftSec
  displayTime.value = formatCountdown(leftSec)
  
  if (t > 0) {
    countdownTimer = setInterval(() => {
      const now = new Date().getTime()
      const remaining = Math.floor((endTime - now) / 1000)
      
      if (remaining >= 0) {
        remainTime.value = remaining
        displayTime.value = formatCountdown(remaining)
      } else {
        clearInterval(countdownTimer)
        displayTime.value = '00:00:00'
        
        periodRefreshTimer = setTimeout(() => {
          loadPeriod()
        }, 3000)
      }
    }, 1000)
  } else {
    periodRefreshTimer = setTimeout(() => {
      loadPeriod()
    }, 1000)
  }
}

// 加载期号
const loadPeriod = async () => {
  try {
    const res = await getCurrentPeriod(gameInfo.value.name)
    
    if (res.code === 200 && res.data) {
      lotteryData.value = res.data
      
      if (res.data.currFullExpect) {
        currentPeriod.value = res.data.currFullExpect
        currentPeriodShort.value = res.data.currFullExpect 
      }
      if (res.data.lastFullExpect) {
        lastPeriod.value = res.data.lastFullExpect
        lastPeriodShort.value = res.data.lastFullExpect 
      }
      
      // 打印调试信息
      console.log('期号信息:', {
        当前期: currentPeriod.value,
        上期: lastPeriod.value,
        数据库上期: res.data.lastResult?.expect,
        倒计时: res.data.remainTime
      })
      
      if (res.data.remainTime && res.data.remainTime > 1) {
        startCountdown(res.data.remainTime)
      } else {
        periodRefreshTimer = setTimeout(() => {
          loadPeriod()
        }, 5000)
      }
    }
  } catch (error) {
    console.error('加载期号失败:', error)
    periodRefreshTimer = setTimeout(() => {
      loadPeriod()
    }, 5000)
  }
}

onMounted(() => {
  loadPeriod()
  loadAvailableGames()
})

// 监听路由变化
watch(() => route.params.name, (newName, oldName) => {
  if (newName && newName !== oldName) {
    // 更新游戏信息
    gameInfo.value = {
      name: newName,
      title: route.query.title || newName
    }
    
    // 清理定时器
    if (countdownTimer) {
      clearInterval(countdownTimer)
      countdownTimer = null
    }
    if (periodRefreshTimer) {
      clearTimeout(periodRefreshTimer)
      periodRefreshTimer = null
    }
    
    // 重新加载期号数据
    loadPeriod()
  }
})

onUnmounted(() => {
  if (countdownTimer) {
    clearInterval(countdownTimer)
  }
  if (periodRefreshTimer) {
    clearTimeout(periodRefreshTimer)
  }
})
</script>

<style scoped lang="scss">
* {
  box-sizing: border-box;
}

.k3-game-page {
  min-height: 100vh;
  background: #f5f5f5;
  overflow-x: hidden;
  max-width: 100vw;
}

/* 顶部导航 */
.game-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  height: 46px;
  background: #fff;
  color: #333;
  padding: 0 15px;
  border-bottom: 1px solid #eee;
}

.header-left, .header-right {
  width: 50px;
  font-size: 20px;
  cursor: pointer;
}

.header-title {
  flex: 1;
  text-align: center;
  font-size: 18px;
  font-weight: normal;
  margin: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 5px;
  cursor: pointer;
  user-select: none;
  
  &:active {
    opacity: 0.7;
  }
}

/* 游戏选择器样式 */
.game-selector {
  padding: 20px 0;
  max-height: 80vh;
  overflow-y: auto;
}

.game-selector-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 20px 15px;
  border-bottom: 1px solid #eee;
  
  h3 {
    margin: 0;
    font-size: 18px;
    color: #333;
  }
  
  .van-icon {
    font-size: 20px;
    color: #999;
    cursor: pointer;
  }
}

.game-list {
  padding: 10px 0;
}

.game-item {
  display: flex;
  align-items: center;
  padding: 15px 20px;
  border-bottom: 1px solid #f5f5f5;
  cursor: pointer;
  transition: background 0.2s;
  
  &:active {
    background: #f5f5f5;
  }
  
  &.active {
    background: #fff5f5;
  }
}

.game-logo {
  width: 40px;
  height: 40px;
  border-radius: 8px;
  object-fit: cover;
  margin-right: 12px;
}

.game-info {
  flex: 1;
}

.game-title {
  font-size: 15px;
  font-weight: bold;
  color: #333;
  margin-bottom: 4px;
}

.game-desc {
  font-size: 12px;
  color: #999;
}

/* 期号和开奖信息区 */
.betting-issue-countdown {
  padding-top: 5px;
  background: #ffffff;
  color: #000;
  position: relative;
  min-height: 130px;
  overflow: visible;
}

.cz-opennumb {
  padding: 10px 15px;
}

.dl-container {
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.dl-flex {
  display: flex;
  align-items: center;
  gap: 15px;
  flex-direction: row;
  justify-content: space-around;
}

.result-header, .countdown-header {
  font-size: 16px;
  background-image: v-bind(headerBgUrl);
  background-size: 100% 100%;
  padding: 2px 26px 2px 14px;
  color: #fff;
  display: flex;
  align-items: center;
  white-space: nowrap;
  flex-shrink: 0;
  height: 60px;
  width: 146px;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.result-title, .countdown-title {
  padding-left: 6px;
  font-size: 14px;
}

.result-content {
  font-size: 14px;
  font-weight: bold;
  gap: 37px;
  flex: 1;
}

.countdown-row {
  margin-top: 14px;
  color: #000;
}

.countdown-content {
  font-size: 20px;
  flex: 1;
}

.j-lottery-time {
  display: flex;
  align-items: center;
  gap: 3px;
  
  .time-unit {
    display: flex;
    gap: 2px;
  }
  
  .bj {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 24px;
    height: 30px;
    background: #f0f0f0;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-weight: bold;
    color: #333;
  }
  
  em {
    font-style: normal;
    margin: 0 3px;
  }
}

/* 投注区域 */
.ui-container {
  margin-top: 10px;
}

.betting-box {
  background: #fff;
  padding: 15px;
}

/* 四个主要选项（红色圆形按钮） */
.menu {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 10px;
  margin-bottom: 15px;
}

.menu-item {
  background: #fff;
  border: 2px solid #e8e8e8;
  border-radius: 50px;
  padding: 10px 0;
  text-align: center;
  font-size: 15px;
  font-weight: bold;
  color: #333;
  cursor: pointer;
  transition: all 0.2s;
  
  &:active {
    transform: scale(0.95);
  }
  
  &.menu-active {
    background: linear-gradient(to bottom, #F31C1D, #F31C1D);
    color: #fff;
    border-color: #F31C1D;
  }
  
  // 前两个按钮（普货、精品）默认红色
  &:nth-child(1),
  &:nth-child(2) {
    &.menu-active {
      background: linear-gradient(to bottom, #F31C1D, #F31C1D);
    }
  }
}

/* 品牌图片选择区 */
.shop-item {
  margin-bottom: 15px;
}

.shop-item-container {
  width: 100%;
}

.ball-list-ul {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 10px;
  list-style: none;
  padding: 0;
  margin: 0;
}

.ball-item {
  aspect-ratio: 1;
  background: #fff;
  border: 3px solid transparent;
  border-radius: 8px;
  overflow: hidden;
  cursor: default; /* 品牌图片不可点击 */
  transition: none;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
  opacity: 0.8; /* 降低透明度表示不可选 */
  
  a {
    display: block;
    width: 100%;
    height: 100%;
    padding: 8px;
    
    img {
      width: 100%;
      height: 100%;
      object-fit: contain;
    }
  }
  
  &:active {
    /* 移除点击效果 */
    transform: none;
  }
  
  &.selected {
    /* 品牌图片不会有selected状态 */
    border-color: transparent;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    opacity: 0.8;
  }
}

/* 余额显示 */
.balance-container {
  margin-bottom: 10px;
  padding: 8px 0;
  border-top: 1px solid #eee;
  border-bottom: 1px solid #eee;
}

.balance-text {
  font-size: 14px;
  color: #666;
}

.balance-amount {
  color: #ff6b6b;
  font-weight: bold;
  font-size: 16px;
  margin-left: 5px;
}

/* 金额输入 */
.amount-input-container {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 15px;
  padding: 10px 0;
}

.amount-input {
  flex: 0 0 80px;
  height: 36px;
  padding: 0 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
  text-align: center;
}

.amount-unit {
  font-size: 14px;
  color: #666;
}

/* 快捷金额按钮组 */
.quick-amount-container {
  display: flex;
  gap: 8px;
  padding: 5px 0 10px;
  flex-wrap: wrap;
}

.quick-amount-btn {
  min-width: 45px;
  height: 32px;
  line-height: 32px;
  padding: 0 12px;
  background: linear-gradient(180deg, #4CAF50 0%, #45a049 100%);
  border-radius: 16px;
  font-size: 13px;
  color: #fff;
  cursor: pointer;
  text-align: center;
  font-weight: 500;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  border: none;
  white-space: nowrap;
  
  &:active {
    transform: scale(0.95);
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
  }
}

/* 底部操作栏 */
.clear-btn {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 0 0;
  border-top: 1px solid #eee;
}

.betting-empty {
  font-size: 16px;
  font-weight: bold;
  color: #666;
  cursor: pointer;
}

.clear-btn-right {
  display: flex;
  align-items: center;
  gap: 15px;
}

.total {
  font-size: 14px;
  color: #666;
  white-space: nowrap;
  
  .betting-sum {
    color: #939298;
    padding: 0 3px;
    font-weight: bold;
  }
  
  .price {
    color: #CE4349;
    font-weight: bold;
  }
}

.submit-btn {
  background: linear-gradient(to bottom, #67c23a, #5daf34);
  color: #fff;
  padding: 8px 20px;
  border-radius: 4px;
  font-size: 14px;
  font-weight: bold;
  cursor: pointer;
  white-space: nowrap;
  box-shadow: 0 2px 8px rgba(103, 194, 58, 0.3);
  
  &:active {
    opacity: 0.9;
    transform: translateY(1px);
  }
}
</style>
