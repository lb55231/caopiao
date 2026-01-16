<template>
  <div class="merchants-page">
    <!-- 顶部导航栏 -->
    <van-nav-bar
      title="商家列表"
      fixed
      placeholder
      :border="false"
    />

    <!-- 搜索栏 
    <div class="search-bar">
      <van-search
        v-model="searchKeyword"
        shape="round"
        placeholder="搜索商家名称"
        @search="onSearch"
      />
    </div>-->

    <!-- 分类标签
    <van-tabs
      v-model:active="activeCategory"
      sticky
      :offset-top="92"
      @change="onCategoryChange"
    >
      <van-tab title="全部" name="all"></van-tab>
      <van-tab title="快3" name="k3"></van-tab>
      <van-tab title="时时彩" name="ssc"></van-tab>
      <van-tab title="11选5" name="11x5"></van-tab>
      <van-tab title="PK10" name="pk10"></van-tab>
    </van-tabs> -->

    <!-- 商家列表 -->
    <van-pull-refresh v-model="refreshing" @refresh="onRefresh">
      <van-list
        v-model:loading="loading"
        :finished="finished"
        finished-text="没有更多了"
        @load="onLoad"
      >
        <div v-if="merchantList.length > 0" class="merchant-list">
          <div
            v-for="merchant in merchantList"
            :key="merchant.id"
            class="merchant-card"
            @click="goToGame(merchant)"
          >
            <!-- 商家图标和信息 -->
            <div class="merchant-header">
              <div class="merchant-logo">
                <img :src="getImageUrl(merchant.logo)" />
              </div>
              <div class="merchant-info">
                <div class="merchant-name">{{ merchant.title }}</div>
                <div class="merchant-desc">{{ merchant.ftitle }}</div>
                <div class="merchant-tags">
                  <van-tag type="primary" size="mini">{{ getCategoryName(merchant.typeid) }}</van-tag>
                  <van-tag v-if="merchant.issys" type="success" size="mini">官方</van-tag>
                </div>
              </div>
              <div class="merchant-status">
                <van-tag v-if="merchant.isopen" type="success" round>营业中</van-tag>
                <van-tag v-else type="danger" round>休息中</van-tag>
              </div>
            </div>

            <!-- 商家统计信息 -->
            <div class="merchant-stats">
              <div class="stat-item">
                <div class="stat-value">{{ merchant.qishu }}</div>
                <div class="stat-label">每日期数</div>
              </div>
              <div class="stat-item">
                <div class="stat-value">{{ merchant.ftime }}分</div>
                <div class="stat-label">开奖频率</div>
              </div>
              <div class="stat-item">
                <div class="stat-value">{{ formatTime(merchant.closetime1, merchant.closetime2) }}</div>
                <div class="stat-label">营业时间</div>
              </div>
            </div>

            <!-- 进入按钮 -->
            <div class="merchant-action">
              <van-button
                type="primary"
                size="small"
                round
                block
                color="#ff6034"
                :disabled="!merchant.isopen"
              >
                {{ merchant.isopen ? '立即下单' : '休息中' }}
              </van-button>
            </div>
          </div>
        </div>

        <!-- 空状态 -->
        <van-empty
          v-else
          image="search"
          description="暂无商家"
        />
      </van-list>
    </van-pull-refresh>

    <!-- 底部导航 -->
    <Tabbar />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { showToast } from 'vant'
import { getImageUrl } from '@/utils/image'
import { getLotteryList } from '@/api/lottery'
import Tabbar from '@/components/Tabbar.vue'

const router = useRouter()
const searchKeyword = ref('')
const activeCategory = ref('all')
const merchantList = ref([])
const allMerchants = ref([])
const loading = ref(false)
const finished = ref(false)
const refreshing = ref(false)

// 加载商家列表
const loadMerchants = async () => {
  try {
    loading.value = true
    const res = await getLotteryList()
    
    if (res.code === 200) {
      allMerchants.value = res.data || []
      filterMerchants()
    }
  } catch (error) {
    showToast('加载失败')
    console.error('加载商家列表失败:', error)
  } finally {
    loading.value = false
    refreshing.value = false
    finished.value = true
  }
}

// 筛选商家
const filterMerchants = () => {
  let filtered = [...allMerchants.value]

  // 按分类筛选
  if (activeCategory.value !== 'all') {
    filtered = filtered.filter(m => m.typeid === activeCategory.value)
  }

  // 按关键词搜索
  if (searchKeyword.value) {
    const keyword = searchKeyword.value.toLowerCase()
    filtered = filtered.filter(m => 
      m.title.toLowerCase().includes(keyword) ||
      m.ftitle.toLowerCase().includes(keyword)
    )
  }

  merchantList.value = filtered
}

// 下拉刷新
const onRefresh = () => {
  loadMerchants()
}

// 加载更多
const onLoad = () => {
  if (allMerchants.value.length === 0) {
    loadMerchants()
  }
}

// 搜索
const onSearch = () => {
  filterMerchants()
}

// 切换分类
const onCategoryChange = () => {
  filterMerchants()
}

// 获取分类名称
const getCategoryName = (typeid) => {
  const categoryMap = {
    'k3': '快3',
    'ssc': '时时彩',
    '11x5': '11选5',
    'pk10': 'PK10'
  }
  return categoryMap[typeid] || typeid
}

// 格式化营业时间
const formatTime = (startTime, endTime) => {
  if (!startTime || !endTime) return '全天'
  return `${startTime.slice(0, 5)}-${endTime.slice(0, 5)}`
}

// 进入游戏
const goToGame = (merchant) => {
  if (!merchant.isopen) {
    showToast('商家休息中')
    return
  }

  // 根据typeid跳转不同游戏
  if (merchant.typeid === 'k3') {
    router.push({
      path: `/game/${merchant.typeid}/${merchant.name}`,
      query: { title: merchant.title }
    })
  } else {
    showToast('该游戏暂未开放')
  }
}

onMounted(() => {
  loadMerchants()
})
</script>

<style scoped lang="scss">
.merchants-page {
  min-height: 100vh;
  background: #f7f8fa;
  padding-bottom: 60px;
}

.search-bar {
  background: white;
  padding: 0 0 8px;
}

.merchant-list {
  padding: 10px;
}

.merchant-card {
  background: white;
  border-radius: 12px;
  padding: 15px;
  margin-bottom: 12px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
  transition: all 0.3s;

  &:active {
    transform: scale(0.98);
  }

  .merchant-header {
    display: flex;
    align-items: flex-start;
    margin-bottom: 15px;

    .merchant-logo {
      width: 60px;
      height: 60px;
      border-radius: 12px;
      overflow: hidden;
      margin-right: 12px;
      flex-shrink: 0;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);

      img {
        width: 100%;
        height: 100%;
        object-fit: cover;
      }
    }

    .merchant-info {
      flex: 1;
      min-width: 0;

      .merchant-name {
        font-size: 17px;
        font-weight: 600;
        color: #323233;
        margin-bottom: 6px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
      }

      .merchant-desc {
        font-size: 13px;
        color: #969799;
        margin-bottom: 8px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
      }

      .merchant-tags {
        display: flex;
        gap: 6px;
      }
    }

    .merchant-status {
      flex-shrink: 0;
      margin-left: 8px;
    }
  }

  .merchant-stats {
    display: flex;
    justify-content: space-around;
    padding: 15px 0;
    border-top: 1px solid #f0f0f0;
    border-bottom: 1px solid #f0f0f0;
    margin-bottom: 15px;

    .stat-item {
      text-align: center;

      .stat-value {
        font-size: 16px;
        font-weight: 600;
        color: #323233;
        margin-bottom: 4px;
      }

      .stat-label {
        font-size: 12px;
        color: #969799;
      }
    }
  }

  .merchant-action {
    :deep(.van-button) {
      height: 38px;
      font-size: 15px;
      font-weight: 500;
    }
  }
}
</style>
