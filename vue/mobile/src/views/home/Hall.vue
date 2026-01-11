<template>
  <div class="hall-page">
    <van-nav-bar title="下单大厅" fixed />

    <van-tabs v-model:active="activeTab" @change="onTabChange" class="hall-tabs">
      <van-tab title="快3" name="k3"></van-tab>
      <van-tab title="时时彩" name="ssc"></van-tab>
      <van-tab title="pk10" name="pk10"></van-tab>
      <van-tab title="低频彩" name="dpc"></van-tab>
      <van-tab title="11选5" name="x5"></van-tab>
      <van-tab title="快乐彩" name="keno"></van-tab>
      <van-tab title="六合彩" name="lhc"></van-tab>
    </van-tabs>

    <div class="lottery-list">
      <van-loading v-if="loading" class="loading" />
      
      <div v-else-if="currentList.length" class="list-wrapper">
        <div
          v-for="item in currentList"
          :key="item.id"
          class="lottery-item"
          @click="goToGame(item)"
        >
          <img :src="item.logo" class="lottery-icon" />
          
          <div class="lottery-info">
            <h3 class="lottery-title">{{ item.title }}</h3>
            
            <div class="prize-num" v-if="item.lastResult">
              <span
                v-for="(num, index) in item.lastResult.split(',')"
                :key="index"
                class="num-ball"
                :class="`ball-${num}`"
              >
                {{ num }}
              </span>
            </div>
            
            <p class="lottery-period">
              第<span>{{ item.currentPeriod }}</span>期 
              截至<span class="timer">{{ item.countdown }}</span>
            </p>
          </div>
        </div>
      </div>
      
      <van-empty v-else description="暂无数据" />
    </div>

    <van-tabbar v-model="tabbarActive" route>
      <van-tabbar-item icon="wap-home-o" to="/home">首页</van-tabbar-item>
      <van-tabbar-item icon="shopping-cart-o" to="/hall">下单</van-tabbar-item>
      <van-tabbar-item icon="user-o" to="/user">我的</van-tabbar-item>
    </van-tabbar>
  </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { getLotteryTypes, getCurrentPeriod, getLotteryHistory } from '@/api/lottery'
import { showToast } from 'vant'

export default {
  name: 'Hall',
  setup() {
    const router = useRouter()
    const activeTab = ref('k3')
    const tabbarActive = ref(1)
    const loading = ref(false)
    const allLotteryList = ref([])

    // 当前分类的彩票列表
    const currentList = computed(() => {
      return allLotteryList.value.filter(item => item.typeid === activeTab.value)
    })

    // 切换分类
    const onTabChange = (name) => {
      activeTab.value = name
    }

    let timer = null

    // 跳转到游戏页
    const goToGame = (lottery) => {
      router.push(`/game/${lottery.name}`)
    }

    // 加载彩票列表
    const loadLotteryList = async () => {
      loading.value = true
      try {
        const res = await getLotteryTypes()
        if (res.code === 200) {
          allLotteryList.value = res.data.map(item => ({
            id: item.id,
            name: item.type_code,
            typeid: getTypeCategory(item.type_code),
            title: item.type_name,
            logo: item.icon || `/app/${item.type_code}.png`,
            lastResult: '',
            currentPeriod: '',
            countdown: '',
            countdownSeconds: 0
          }))
          
          // 加载每个彩种的当前期号和历史开奖
          await loadLotteryDetails()
          
          // 启动倒计时
          startCountdown()
        }
      } catch (error) {
        console.error('加载失败:', error)
        showToast('加载失败')
      } finally {
        loading.value = false
      }
    }
    
    // 根据彩种代码获取分类
    const getTypeCategory = (code) => {
      if (code.includes('k3')) return 'k3'
      if (code.includes('ssc')) return 'ssc'
      if (code.includes('pk10')) return 'pk10'
      if (code.includes('11x5')) return 'x5'
      if (code.includes('keno')) return 'keno'
      if (code.includes('lhc')) return 'lhc'
      return 'dpc'
    }
    
    // 加载彩票详情（当前期号和历史开奖）
    const loadLotteryDetails = async () => {
      for (let lottery of allLotteryList.value) {
        try {
          // 获取当前期号
          const periodRes = await getCurrentPeriod(lottery.name)
          if (periodRes.code === 200) {
            lottery.currentPeriod = periodRes.data.period
            lottery.countdownSeconds = periodRes.data.countdown
          }
          
          // 获取最近一期开奖结果
          const historyRes = await getLotteryHistory(lottery.name, 1)
          if (historyRes.code === 200 && historyRes.data.length > 0) {
            lottery.lastResult = historyRes.data[0].open_code
          }
        } catch (error) {
          console.error(`加载${lottery.title}详情失败:`, error)
        }
      }
    }
    
    // 启动倒计时
    const startCountdown = () => {
      if (timer) clearInterval(timer)
      
      timer = setInterval(() => {
        allLotteryList.value.forEach(lottery => {
          if (lottery.countdownSeconds > 0) {
            lottery.countdownSeconds--
            lottery.countdown = formatCountdown(lottery.countdownSeconds)
          } else {
            lottery.countdown = '00:00:00'
          }
        })
      }, 1000)
    }
    
    // 格式化倒计时
    const formatCountdown = (seconds) => {
      const h = Math.floor(seconds / 3600)
      const m = Math.floor((seconds % 3600) / 60)
      const s = seconds % 60
      return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`
    }

    onMounted(() => {
      loadLotteryList()
    })
    
    onUnmounted(() => {
      if (timer) {
        clearInterval(timer)
      }
    })

    return {
      activeTab,
      tabbarActive,
      loading,
      currentList,
      onTabChange,
      goToGame
    }
  }
}
</script>

<style lang="scss" scoped>
.hall-page {
  min-height: 100vh;
  background: #f5f5f5;
  padding-top: 46px;
  padding-bottom: 50px;
}

.hall-tabs {
  position: fixed;
  top: 46px;
  left: 0;
  right: 0;
  z-index: 100;
  background: #8f0008;
  
  :deep(.van-tabs__nav) {
    background: #8f0008;
  }
  
  :deep(.van-tab) {
    color: white;
  }
  
  :deep(.van-tab--active) {
    color: white;
    background: #8f0008;
  }
  
  :deep(.van-tabs__line) {
    background: white;
  }
}

.lottery-list {
  margin-top: 44px;
  padding: 10px;
}

.loading {
  margin-top: 100px;
}

.lottery-item {
  background: white;
  border-radius: 8px;
  padding: 15px;
  margin-bottom: 10px;
  display: flex;
  align-items: center;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  
  .lottery-icon {
    width: 60px;
    height: 60px;
    margin-right: 15px;
  }
  
  .lottery-info {
    flex: 1;
  }
  
  .lottery-title {
    font-size: 16px;
    font-weight: bold;
    margin: 0 0 8px;
  }
  
  .prize-num {
    margin-bottom: 8px;
    
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
      margin-right: 5px;
    }
  }
  
  .lottery-period {
    font-size: 12px;
    color: #999;
    margin: 0;
    
    span {
      color: #666;
    }
    
    .timer {
      color: #ff6b6b;
    }
  }
}
</style>

