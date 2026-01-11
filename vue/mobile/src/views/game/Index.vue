<template>
  <div class="game-page">
    <van-nav-bar 
      :title="lotteryInfo.type_name" 
      left-arrow 
      @click-left="router.back()"
      fixed
    />

    <div class="game-content">
      <!-- 彩票信息 -->
      <div class="lottery-header">
        <div class="lottery-info-box">
          <div class="info-item">
            <span class="label">当前期号</span>
            <span class="value">{{ currentPeriod.period }}</span>
          </div>
          <div class="info-item">
            <span class="label">封盘倒计时</span>
            <span class="value countdown">{{ countdown }}</span>
          </div>
        </div>
        
        <!-- 历史开奖 -->
        <div class="history-box">
          <div class="history-title">
            <van-icon name="clock-o" />
            历史开奖
          </div>
          <div class="history-list">
            <div 
              v-for="item in historyList" 
              :key="item.period"
              class="history-item"
            >
              <span class="period">{{ item.period }}</span>
              <div class="numbers">
                <span 
                  v-for="(num, index) in item.open_code.split(',')" 
                  :key="index"
                  class="num-ball"
                >
                  {{ num }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 玩法选择 -->
      <div class="play-section">
        <van-tabs v-model:active="activePlay" @change="onPlayChange">
          <van-tab 
            v-for="play in playList" 
            :key="play.id" 
            :title="play.play_name"
          >
            <div class="play-content">
              <!-- 投注区域 -->
              <div class="bet-area">
                <div class="bet-grid">
                  <div
                    v-for="num in getBetNumbers(play)"
                    :key="num"
                    class="bet-item"
                    :class="{ active: selectedNumbers.includes(num) }"
                    @click="toggleNumber(num)"
                  >
                    <div class="bet-num">{{ num }}</div>
                    <div class="bet-odds">{{ play.odds }}</div>
                  </div>
                </div>
              </div>

              <!-- 投注信息 -->
              <div class="bet-info">
                <div class="info-row">
                  <span>已选：<em>{{ selectedNumbers.length }}</em> 注</span>
                  <span>单注金额：<em>{{ betAmount }}</em> 元</span>
                </div>
                <div class="info-row">
                  <span>总投注额：<em class="total">{{ totalAmount }}</em> 元</span>
                  <span>预计奖金：<em class="win">{{ expectedWin }}</em> 元</span>
                </div>
              </div>

              <!-- 金额选择 -->
              <div class="amount-select">
                <div class="amount-label">单注金额</div>
                <div class="amount-btns">
                  <button
                    v-for="amount in amountOptions"
                    :key="amount"
                    class="amount-btn"
                    :class="{ active: betAmount === amount }"
                    @click="betAmount = amount"
                  >
                    {{ amount }}
                  </button>
                </div>
              </div>

              <!-- 底部操作栏 -->
              <div class="bottom-bar">
                <div class="balance-info">
                  余额：<span>{{ userStore.userInfo?.balance || 0 }}</span> 元
                </div>
                <div class="action-btns">
                  <van-button 
                    plain 
                    type="warning" 
                    @click="clearSelected"
                  >
                    清空
                  </van-button>
                  <van-button 
                    type="danger" 
                    :disabled="!canSubmit"
                    @click="submitBet"
                  >
                    确认投注
                  </van-button>
                </div>
              </div>
            </div>
          </van-tab>
        </van-tabs>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useUserStore } from '@/stores/user'
import { 
  getLotteryDetail, 
  getCurrentPeriod, 
  getLotteryHistory, 
  getPlays,
  submitBet 
} from '@/api/lottery'
import { showToast, showDialog } from 'vant'

export default {
  name: 'Game',
  setup() {
    const router = useRouter()
    const route = useRoute()
    const userStore = useUserStore()
    
    const typeCode = computed(() => route.params.type)
    const lotteryInfo = ref({})
    const currentPeriod = ref({})
    const historyList = ref([])
    const playList = ref([])
    const activePlay = ref(0)
    const selectedNumbers = ref([])
    const betAmount = ref(2)
    const amountOptions = [2, 5, 10, 20, 50, 100]
    const countdownSeconds = ref(0)
    const countdown = ref('00:00')
    let timer = null

    // 计算总金额
    const totalAmount = computed(() => {
      return selectedNumbers.value.length * betAmount.value
    })

    // 计算预计奖金
    const expectedWin = computed(() => {
      if (playList.value.length === 0 || selectedNumbers.value.length === 0) return 0
      const currentPlay = playList.value[activePlay.value]
      return (betAmount.value * currentPlay.odds).toFixed(2)
    })

    // 是否可以提交
    const canSubmit = computed(() => {
      return selectedNumbers.value.length > 0 && betAmount.value >= 2
    })

    // 获取投注号码
    const getBetNumbers = (play) => {
      // 根据玩法类型返回不同的号码
      const code = lotteryInfo.value.type_code
      if (code.includes('k3')) {
        // 快3的号码
        return ['大', '小', '单', '双', '和值大', '和值小']
      } else if (code.includes('ssc')) {
        // 时时彩的号码
        return ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9']
      } else if (code.includes('11x5')) {
        // 11选5的号码
        return Array.from({ length: 11 }, (_, i) => String(i + 1).padStart(2, '0'))
      } else if (code.includes('pk10')) {
        // PK10的号码
        return Array.from({ length: 10 }, (_, i) => String(i + 1).padStart(2, '0'))
      }
      return []
    }

    // 切换号码选择
    const toggleNumber = (num) => {
      const index = selectedNumbers.value.indexOf(num)
      if (index > -1) {
        selectedNumbers.value.splice(index, 1)
      } else {
        selectedNumbers.value.push(num)
      }
    }

    // 清空选择
    const clearSelected = () => {
      selectedNumbers.value = []
    }

    // 切换玩法
    const onPlayChange = () => {
      clearSelected()
    }

    // 提交投注
    const submitBet = async () => {
      if (!userStore.token) {
        showToast('请先登录')
        router.push('/login')
        return
      }

      if (selectedNumbers.value.length === 0) {
        showToast('请选择投注号码')
        return
      }

      const currentPlay = playList.value[activePlay.value]
      
      showDialog({
        title: '确认投注',
        message: `投注 ${selectedNumbers.value.length} 注，共 ${totalAmount.value} 元`,
      }).then(async () => {
        try {
          const res = await submitBet({
            type_code: typeCode.value,
            play_id: currentPlay.id,
            bet_content: selectedNumbers.value.join(','),
            bet_amount: totalAmount.value
          })

          if (res.code === 200) {
            showToast('投注成功')
            // 更新余额
            userStore.userInfo.balance = res.data.balance
            clearSelected()
          }
        } catch (error) {
          console.error('投注失败:', error)
        }
      }).catch(() => {
        // 取消投注
      })
    }

    // 加载彩种信息
    const loadLotteryInfo = async () => {
      try {
        const res = await getLotteryDetail(typeCode.value)
        if (res.code === 200) {
          lotteryInfo.value = res.data
        }
      } catch (error) {
        console.error('加载彩种信息失败:', error)
      }
    }

    // 加载当前期号
    const loadCurrentPeriod = async () => {
      try {
        const res = await getCurrentPeriod(typeCode.value)
        if (res.code === 200) {
          currentPeriod.value = res.data
          countdownSeconds.value = res.data.countdown
          startCountdown()
        }
      } catch (error) {
        console.error('加载期号失败:', error)
      }
    }

    // 加载历史开奖
    const loadHistory = async () => {
      try {
        const res = await getLotteryHistory(typeCode.value, 5)
        if (res.code === 200) {
          historyList.value = res.data
        }
      } catch (error) {
        console.error('加载历史失败:', error)
      }
    }

    // 加载玩法列表
    const loadPlays = async () => {
      try {
        const res = await getPlays(typeCode.value)
        if (res.code === 200) {
          playList.value = res.data.filter(item => item.parent_id === 0)
        }
      } catch (error) {
        console.error('加载玩法失败:', error)
      }
    }

    // 启动倒计时
    const startCountdown = () => {
      if (timer) clearInterval(timer)
      
      timer = setInterval(() => {
        if (countdownSeconds.value > 0) {
          countdownSeconds.value--
          countdown.value = formatCountdown(countdownSeconds.value)
        } else {
          countdown.value = '已封盘'
          clearInterval(timer)
          // 重新加载期号
          setTimeout(() => {
            loadCurrentPeriod()
          }, 3000)
        }
      }, 1000)
    }

    // 格式化倒计时
    const formatCountdown = (seconds) => {
      const m = Math.floor(seconds / 60)
      const s = seconds % 60
      return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`
    }

    onMounted(() => {
      loadLotteryInfo()
      loadCurrentPeriod()
      loadHistory()
      loadPlays()
    })

    onUnmounted(() => {
      if (timer) {
        clearInterval(timer)
      }
    })

    return {
      router,
      userStore,
      lotteryInfo,
      currentPeriod,
      historyList,
      playList,
      activePlay,
      selectedNumbers,
      betAmount,
      amountOptions,
      countdown,
      totalAmount,
      expectedWin,
      canSubmit,
      getBetNumbers,
      toggleNumber,
      clearSelected,
      onPlayChange,
      submitBet
    }
  }
}
</script>

<style lang="scss" scoped>
.game-page {
  min-height: 100vh;
  background: #f5f5f5;
  padding-top: 46px;
}

.game-content {
  padding-bottom: 70px;
}

.lottery-header {
  background: white;
  margin-bottom: 10px;
}

.lottery-info-box {
  display: flex;
  justify-content: space-around;
  padding: 15px;
  border-bottom: 1px solid #f0f0f0;
  
  .info-item {
    text-align: center;
    
    .label {
      display: block;
      font-size: 12px;
      color: #999;
      margin-bottom: 5px;
    }
    
    .value {
      font-size: 16px;
      font-weight: bold;
      color: #333;
      
      &.countdown {
        color: #ff6b6b;
      }
    }
  }
}

.history-box {
  padding: 15px;
  
  .history-title {
    font-size: 14px;
    font-weight: bold;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 5px;
  }
  
  .history-list {
    max-height: 200px;
    overflow-y: auto;
  }
  
  .history-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #f5f5f5;
    
    .period {
      font-size: 12px;
      color: #999;
    }
    
    .numbers {
      display: flex;
      gap: 5px;
      
      .num-ball {
        display: inline-block;
        width: 24px;
        height: 24px;
        line-height: 24px;
        text-align: center;
        border-radius: 50%;
        background: #ff6b6b;
        color: white;
        font-size: 12px;
      }
    }
  }
}

.play-section {
  background: white;
}

.play-content {
  padding: 15px;
}

.bet-area {
  margin-bottom: 20px;
}

.bet-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 10px;
}

.bet-item {
  background: #f5f5f5;
  border-radius: 8px;
  padding: 15px 10px;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s;
  
  &.active {
    background: #ff6b6b;
    color: white;
    
    .bet-odds {
      color: rgba(255, 255, 255, 0.8);
    }
  }
  
  .bet-num {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 5px;
  }
  
  .bet-odds {
    font-size: 12px;
    color: #999;
  }
}

.bet-info {
  background: #fff7e6;
  border-radius: 8px;
  padding: 15px;
  margin-bottom: 20px;
  
  .info-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
    font-size: 14px;
    
    &:last-child {
      margin-bottom: 0;
    }
    
    em {
      color: #ff6b6b;
      font-style: normal;
      font-weight: bold;
      
      &.total {
        color: #f39c12;
      }
      
      &.win {
        color: #27ae60;
      }
    }
  }
}

.amount-select {
  margin-bottom: 20px;
  
  .amount-label {
    font-size: 14px;
    margin-bottom: 10px;
  }
  
  .amount-btns {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 8px;
  }
  
  .amount-btn {
    padding: 8px;
    border: 1px solid #ddd;
    background: white;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    
    &.active {
      border-color: #ff6b6b;
      background: #ff6b6b;
      color: white;
    }
  }
}

.bottom-bar {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  background: white;
  padding: 10px 15px;
  border-top: 1px solid #f0f0f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
  z-index: 100;
  
  .balance-info {
    font-size: 14px;
    
    span {
      color: #ff6b6b;
      font-weight: bold;
    }
  }
  
  .action-btns {
    display: flex;
    gap: 10px;
    
    .van-button {
      min-width: 80px;
    }
  }
}
</style>

