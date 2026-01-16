<template>
  <div class="activity-page">
    <!-- 顶部导航栏 -->
    <van-nav-bar
      title="活动中心"
      fixed
      placeholder
      :border="false"
      class="nav-bar"
    />

    <!-- 活动列表 -->
    <div class="activity-list">
      <!-- 晋级奖励 -->
      <div class="activity-item" @click="goToPromotion">
        <div class="activity-number bg-orange">
          <span>1</span>
        </div>
        <div class="activity-info">
          <div class="activity-title">晋级奖励</div>
          <div class="activity-desc">会员每晋升一个等级，都能获得奖励，最高可达38888元。</div>
        </div>
        <div class="activity-arrow">
          <van-icon name="arrow" />
        </div>
      </div>

      <!-- 每日加奖 -->
      <div class="activity-item" @click="goToEverydayPlus">
        <div class="activity-number bg-green">
          <span>2</span>
        </div>
        <div class="activity-info">
          <div class="activity-title">每日加奖</div>
          <div class="activity-desc">每日加奖是根据会员昨日下单金额进行加奖，奖金无上限。</div>
        </div>
        <div class="activity-arrow">
          <van-icon name="arrow" />
        </div>
      </div>

      <!-- 其他活动 -->
      <div 
        v-for="(activity, index) in otherActivities" 
        :key="activity.id"
        class="activity-item" 
        @click="goToDetail(activity)"
      >
        <div class="activity-number" :class="`bg-${getColor(index + 3)}`">
          <span>{{ index + 3 }}</span>
        </div>
        <div class="activity-info">
          <div class="activity-title">{{ activity.title }}</div>
          <div class="activity-desc">{{ activity.content || '精彩活动，敬请期待' }}</div>
        </div>
        <div class="activity-arrow">
          <van-icon name="arrow" />
        </div>
      </div>
    </div>

    <!-- 底部导航 -->
    <Tabbar />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { showToast, showDialog } from 'vant'
import Tabbar from '@/components/Tabbar.vue'
import request from '@/api/request'

const router = useRouter()

// 其他活动列表
const otherActivities = ref([])

// 加载活动列表
const loadActivities = async () => {
  try {
    // 这里可以从后端加载活动数据
    // const res = await request.get('/activity/list')
    // if (res.code === 200) {
    //   otherActivities.value = res.data
    // }
    
    // 暂时使用示例数据
    otherActivities.value = [
      { id: 3, title: '新人专享', content: '新用户注册即送体验金，快来领取吧！' },
      { id: 4, title: '充值优惠', content: '充值1000送200，充值越多送越多！' },
      { id: 5, title: '推荐有礼', content: '推荐好友注册，双方都可获得奖励！' }
    ]
  } catch (error) {
    console.error('加载活动失败:', error)
  }
}

// 获取颜色类名
const getColor = (index) => {
  const colors = ['orange', 'green', 'blue', 'purple', 'red']
  return colors[index % colors.length]
}

// 跳转到晋级奖励页面
const goToPromotion = () => {
  showToast('晋级奖励功能开发中')
  // router.push('/activity/promotion')
}

// 跳转到每日加奖页面
const goToEverydayPlus = () => {
  showToast('每日加奖功能开发中')
  // router.push('/activity/everyday-plus')
}

// 查看活动详情
const goToDetail = (activity) => {
  showDialog({
    title: activity.title,
    message: activity.content || '活动详情正在准备中，敬请期待！',
    confirmButtonText: '知道了',
    confirmButtonColor: '#ff6034'
  })
}

onMounted(() => {
  loadActivities()
})
</script>

<style scoped lang="scss">
.activity-page {
  min-height: 100vh;
  background: #f5f5f5;
  padding-bottom: 60px;
}

.nav-bar {
  :deep(.van-nav-bar__title) {
    font-weight: 600;
  }
}

.activity-list {
  padding: 10px;
}

.activity-item {
  background: white;
  border-radius: 8px;
  padding: 15px;
  margin-bottom: 10px;
  display: flex;
  align-items: center;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  cursor: pointer;
  transition: all 0.3s;

  &:active {
    transform: scale(0.98);
    opacity: 0.9;
  }

  .activity-number {
    width: 50px;
    height: 50px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-right: 15px;

    span {
      font-size: 24px;
      font-weight: bold;
      color: white;
    }

    &.bg-orange {
      background: linear-gradient(135deg, #ff6034, #ee0a24);
    }

    &.bg-green {
      background: linear-gradient(135deg, #07c160, #00c48f);
    }

    &.bg-blue {
      background: linear-gradient(135deg, #1989fa, #1677ff);
    }

    &.bg-purple {
      background: linear-gradient(135deg, #7232dd, #8b5cf6);
    }

    &.bg-red {
      background: linear-gradient(135deg, #f43f3b, #ff6b6b);
    }
  }

  .activity-info {
    flex: 1;
    min-width: 0;

    .activity-title {
      font-size: 16px;
      font-weight: 600;
      color: #323233;
      margin-bottom: 5px;
    }

    .activity-desc {
      font-size: 13px;
      color: #969799;
      line-height: 1.5;
      overflow: hidden;
      text-overflow: ellipsis;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
    }
  }

  .activity-arrow {
    flex-shrink: 0;
    margin-left: 10px;
    color: #c8c9cc;
    font-size: 18px;
  }
}
</style>
