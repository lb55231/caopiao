<template>
  <div class="activity-page">
    <!-- 顶部导航栏 -->
    <van-nav-bar
      title="活动中心"
      fixed
      placeholder
      :border="false"
    />

    <!-- 轮播活动 -->
    <van-swipe :autoplay="4000" indicator-color="white" class="activity-banner">
      <van-swipe-item v-for="(item, index) in bannerList" :key="index">
        <img :src="item.image" @click="goToDetail(item)" />
      </van-swipe-item>
    </van-swipe>

    <!-- 活动分类 -->
    <van-tabs v-model:active="activeTab" @change="onTabChange">
      <van-tab title="全部"></van-tab>
      <van-tab title="进行中"></van-tab>
      <van-tab title="即将开始"></van-tab>
      <van-tab title="已结束"></van-tab>
    </van-tabs>

    <!-- 活动列表 -->
    <van-pull-refresh v-model="refreshing" @refresh="onRefresh">
      <div v-if="activityList.length > 0" class="activity-list">
        <div
          v-for="activity in activityList"
          :key="activity.id"
          class="activity-card"
          @click="goToDetail(activity)"
        >
          <!-- 活动封面 -->
          <div class="activity-cover">
            <img :src="activity.cover" />
            <div v-if="activity.status === 'ongoing'" class="activity-badge ongoing">
              进行中
            </div>
            <div v-else-if="activity.status === 'upcoming'" class="activity-badge upcoming">
              即将开始
            </div>
            <div v-else class="activity-badge ended">
              已结束
            </div>
          </div>

          <!-- 活动信息 -->
          <div class="activity-content">
            <div class="activity-title">{{ activity.title }}</div>
            <div class="activity-desc">{{ activity.description }}</div>
            
            <div class="activity-info">
              <div class="activity-time">
                <van-icon name="clock-o" />
                {{ formatDate(activity.startTime) }} - {{ formatDate(activity.endTime) }}
              </div>
              <div class="activity-reward">
                <van-icon name="gift-o" color="#ff6034" />
                <span class="reward-text">奖励：{{ activity.reward }}</span>
              </div>
            </div>

            <div class="activity-action">
              <van-button
                v-if="activity.status === 'ongoing'"
                type="primary"
                size="small"
                round
                color="#ff6034"
              >
                立即参与
              </van-button>
              <van-button
                v-else-if="activity.status === 'upcoming'"
                type="default"
                size="small"
                round
              >
                敬请期待
              </van-button>
              <van-button
                v-else
                type="default"
                size="small"
                round
                disabled
              >
                活动已结束
              </van-button>
            </div>
          </div>
        </div>
      </div>

      <!-- 空状态 -->
      <van-empty
        v-else
        image="search"
        description="暂无活动"
      />
    </van-pull-refresh>

    <!-- 底部导航 -->
    <Tabbar />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { showToast } from 'vant'
import Tabbar from '@/components/Tabbar.vue'

const router = useRouter()
const activeTab = ref(0)
const refreshing = ref(false)

// 示例轮播数据
const bannerList = ref([
  {
    id: 1,
    image: '/images/activity/banner1.jpg',
    title: '新人专享福利'
  },
  {
    id: 2,
    image: '/images/activity/banner2.jpg',
    title: '充值送好礼'
  },
  {
    id: 3,
    image: '/images/activity/banner3.jpg',
    title: '每日签到领红包'
  }
])

// 示例活动数据
const allActivities = ref([
  {
    id: 1,
    title: '新用户注册送礼',
    description: '注册即送88元体验金，首次充值再送100%',
    cover: '/images/activity/activity1.jpg',
    status: 'ongoing',
    startTime: '2026-01-01',
    endTime: '2026-12-31',
    reward: '88元体验金'
  },
  {
    id: 2,
    title: '每日签到有礼',
    description: '连续签到7天，赢取超级大奖',
    cover: '/images/activity/activity2.jpg',
    status: 'ongoing',
    startTime: '2026-01-01',
    endTime: '2026-12-31',
    reward: '现金红包'
  },
  {
    id: 3,
    title: '充值优惠活动',
    description: '充值1000送200，充值越多送越多',
    cover: '/images/activity/activity3.jpg',
    status: 'ongoing',
    startTime: '2026-01-10',
    endTime: '2026-02-10',
    reward: '20%充值奖励'
  },
  {
    id: 4,
    title: '春节特惠活动',
    description: '春节期间全场优惠，下注返现金',
    cover: '/images/activity/activity4.jpg',
    status: 'upcoming',
    startTime: '2026-01-28',
    endTime: '2026-02-12',
    reward: '返现金'
  },
  {
    id: 5,
    title: '元旦狂欢',
    description: '元旦期间充值加赠，最高可得500元',
    cover: '/images/activity/activity5.jpg',
    status: 'ended',
    startTime: '2026-01-01',
    endTime: '2026-01-03',
    reward: '最高500元'
  }
])

// 根据标签筛选活动
const activityList = computed(() => {
  if (activeTab.value === 0) {
    return allActivities.value
  } else if (activeTab.value === 1) {
    return allActivities.value.filter(a => a.status === 'ongoing')
  } else if (activeTab.value === 2) {
    return allActivities.value.filter(a => a.status === 'upcoming')
  } else {
    return allActivities.value.filter(a => a.status === 'ended')
  }
})

// 切换标签
const onTabChange = () => {
  // 可以在这里加载不同状态的活动
}

// 下拉刷新
const onRefresh = () => {
  setTimeout(() => {
    refreshing.value = false
    showToast('刷新成功')
  }, 1000)
}

// 格式化日期
const formatDate = (dateStr) => {
  return dateStr.replace(/-/g, '.')
}

// 查看活动详情
const goToDetail = (activity) => {
  showToast('活动详情')
  // router.push(`/activity/${activity.id}`)
}

onMounted(() => {
  // 可以在这里加载活动数据
})
</script>

<style scoped lang="scss">
.activity-page {
  min-height: 100vh;
  background: #f7f8fa;
  padding-bottom: 60px;
}

.activity-banner {
  width: 100%;
  height: 180px;
  background: #fff;

  img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
}

.activity-list {
  padding: 10px;
}

.activity-card {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  margin-bottom: 12px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);

  .activity-cover {
    position: relative;
    width: 100%;
    height: 160px;
    overflow: hidden;

    img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .activity-badge {
      position: absolute;
      top: 10px;
      right: 10px;
      padding: 4px 12px;
      border-radius: 12px;
      font-size: 12px;
      font-weight: 500;
      color: white;

      &.ongoing {
        background: linear-gradient(135deg, #ff6034, #ee0a24);
      }

      &.upcoming {
        background: linear-gradient(135deg, #07c160, #00c48f);
      }

      &.ended {
        background: #969799;
      }
    }
  }

  .activity-content {
    padding: 15px;

    .activity-title {
      font-size: 17px;
      font-weight: 600;
      color: #323233;
      margin-bottom: 8px;
      overflow: hidden;
      text-overflow: ellipsis;
      display: -webkit-box;
      -webkit-line-clamp: 1;
      -webkit-box-orient: vertical;
    }

    .activity-desc {
      font-size: 14px;
      color: #646566;
      margin-bottom: 12px;
      overflow: hidden;
      text-overflow: ellipsis;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      line-height: 1.5;
    }

    .activity-info {
      margin-bottom: 12px;

      .activity-time {
        display: flex;
        align-items: center;
        font-size: 13px;
        color: #969799;
        margin-bottom: 6px;

        .van-icon {
          margin-right: 4px;
        }
      }

      .activity-reward {
        display: flex;
        align-items: center;
        font-size: 13px;

        .van-icon {
          margin-right: 4px;
        }

        .reward-text {
          color: #ff6034;
          font-weight: 500;
        }
      }
    }

    .activity-action {
      :deep(.van-button) {
        width: 100%;
        height: 38px;
        font-size: 15px;
      }
    }
  }
}
</style>
