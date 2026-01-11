<template>
  <div class="home-page">
    <!-- 轮播图 -->
    <van-swipe :autoplay="3000" indicator-color="white" class="banner-swipe">
      <van-swipe-item>
        <img src="/images/banner/1.jpg" alt="banner">
      </van-swipe-item>
      <van-swipe-item>
        <img src="/images/banner/2.jpg" alt="banner">
      </van-swipe-item>
    </van-swipe>

    <!-- 公告栏 -->
    <div class="home_notice">
      <van-notice-bar
        left-icon="volume-o"
        :text="noticeText"
        @click="showNoticeDetail"
      />
    </div>

    <!-- 空白区域 -->
    <div class="my_operation_money"></div>

    <!-- 热门商家 -->
    <div class="credit">
      <div class="hot">
        <img src="/images/index/icon-hot.png" alt="" class="hot-img">
        热门商家
      </div>
      
      <ul class="lottery-grid">
        <li
          v-for="item in lotteryList"
          :key="item.id"
          class="home_main_list"
          @click="goToGame(item)"
        >
          <div>
            <i class="iconfont">
              <img :src="getImageUrl(item.logo)" style="width:50px" />
            </i>
            <div class="hot-periods">
              <span style="font-size: 14px;color: #333333;">{{ item.title }}</span>
            </div>
          </div>
        </li>
      </ul>
    </div>

    <!-- 收益专栏 -->
    <div class="winning-box">
      <div class="news-title">
        <img src="/app/rank.png" style="margin: 10px;width: 16px;">
        <h2 class="news-tit"><strong>收益专栏</strong></h2>
      </div>
      <div class="rankBg"></div>
      <div class="news-content myScroll">
        <ul class="news-scroll" ref="scrollList">
          <li v-for="(item, index) in rankingList" :key="index">
            {{ item.username }}
            <b>{{ item.k3name }}</b>
            <em>收益 <em style="color: #fe4365;">{{ item.okamount }}</em>元</em>
          </li>
        </ul>
      </div>
    </div>

    <!-- 底部导航 -->
    <van-tabbar v-model="active" route>
      <van-tabbar-item to="/" icon="wap-home-o">首页</van-tabbar-item>
      <van-tabbar-item to="/hall" icon="apps-o">大厅</van-tabbar-item>
      <van-tabbar-item to="/user" icon="user-o">我的</van-tabbar-item>
    </van-tabbar>
  </div>
</template>

<script>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { showToast } from 'vant'
import { getLotteryList, getNotice, getRanking } from '@/api/lottery'
import { getImageUrl } from '@/utils/image'

export default {
  name: 'HomePage',
  setup() {
    const router = useRouter()
    const active = ref(0)
    const noticeText = ref('欢迎光临！祝您游戏愉快！')
    const noticeId = ref(0)
    const lotteryList = ref([])
    const rankingList = ref([])
    const scrollTimer = ref(null)
    const scrollList = ref(null)

    // 加载彩票列表
    const loadLotteryList = async () => {
      try {
        const res = await getLotteryList()
        lotteryList.value = res.data || []
      } catch (error) {
        console.error('加载彩票列表失败:', error)
      }
    }

    // 加载公告
    const loadNotice = async () => {
      try {
        const res = await getNotice()
        if (res.data) {
          noticeText.value = res.data.title
          noticeId.value = res.data.id
        }
      } catch (error) {
        console.error('加载公告失败:', error)
      }
    }

    // 加载收益排行
    const loadRanking = async () => {
      try {
        const res = await getRanking()
        rankingList.value = res.data || []
        // 加载完成后启动滚动
        setTimeout(() => {
          startAutoScroll()
        }, 500)
      } catch (error) {
        console.error('加载收益排行失败:', error)
      }
    }

    // 自动滚动效果（与原项目一致）
    const startAutoScroll = () => {
      if (!scrollList.value) return
      
      const ul = scrollList.value
      if (!ul.querySelector('li')) return
      
      const liHeight = 58 // li的高度
      let currentScroll = 0

      scrollTimer.value = setInterval(() => {
        // 每次都重新获取第一个li，避免引用失效
        const firstLi = ul.querySelector('li')
        if (!firstLi) return
        
        currentScroll += 1
        
        if (currentScroll >= liHeight) {
          currentScroll = 0
          // 将第一个li移到最后
          const firstClone = firstLi.cloneNode(true)
          ul.appendChild(firstClone)
          ul.removeChild(firstLi)
        }
        
        ul.style.transform = `translateY(-${currentScroll}px)`
      }, 40) // 速度40ms
    }

    // 显示公告详情
    const showNoticeDetail = () => {
      if (noticeId.value) {
        showToast('查看公告详情')
        // router.push(`/notice/${noticeId.value}`)
      }
    }

    // 进入游戏
    const goToGame = (item) => {
      // 根据typeid跳转不同游戏
      if (item.typeid === 'k3') {
        router.push({
          path: `/game/${item.typeid}/${item.name}`,
          query: { title: item.title }
        })
      } else {
        showToast('该游戏暂未开放')
      }
    }

    onMounted(() => {
      loadLotteryList()
      loadNotice()
      loadRanking()
    })

    onUnmounted(() => {
      if (scrollTimer.value) {
        clearInterval(scrollTimer.value)
      }
    })

    return {
      active,
      noticeText,
      lotteryList,
      rankingList,
      scrollList,
      showNoticeDetail,
      goToGame,
      getImageUrl
    }
  }
}
</script>

<style scoped lang="scss">
.home-page {
  min-height: 100vh;
  background: #f5f5f5;
  padding-bottom: 60px;
}

/* 轮播图 */
.banner-swipe {
  width: 100%;
  
  img {
    width: 100%;
    display: block;
  }
}

/* 公告栏 */
.home_notice {
  width: 100%;
  text-align: center;
  position: relative;
  margin-top: -30px;
  padding: 0 10px;
  
  :deep(.van-notice-bar) {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 20px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  }
}

/* 空白区域 */
.my_operation_money {
  background: white;
  margin: 20px auto 10px;
  width: 95%;
  border-radius: 4px;
  box-shadow: 1px 1px 3px rgba(0, 0, 0, .1);
}

/* 热门商家 */
.credit {
  width: 95%;
  margin: 10px auto;
  background: #fff;
  border-radius: 4px;
  box-shadow: 1px 1px 3px rgba(0, 0, 0, .1);
  padding-bottom: 10px;
  
  .hot {
    height: 40px;
    line-height: 40px;
    font-size: 14px;
    padding: 0 10px;
    border-bottom: 1px solid #f2f6fc;
    
    .hot-img {
      width: 16px;
      vertical-align: middle;
      margin-right: 5px;
    }
  }
  
  .lottery-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
    padding: 10px;
    list-style: none;
    margin: 0;
    
    .home_main_list {
      text-align: center;
      padding: 10px 0;
      cursor: pointer;
      transition: all 0.3s;
      
      &:active {
        transform: scale(0.95);
      }
      
      .iconfont {
        display: block;
        margin-bottom: 5px;
        
        img {
          width: 50px;
          height: 50px;
          object-fit: contain;
        }
      }
      
      .hot-periods {
        span {
          font-size: 14px;
          color: #333;
        }
      }
    }
  }
}

/* 收益专栏 */
.winning-box {
  width: 95%;
  margin: 10px auto;
  border-radius: 4px;
  position: relative;
  background: #fff;
  box-shadow: 1px 1px 3px rgba(0, 0, 0, .1);
  
  .news-title {
    display: flex;
    align-items: center;
    height: 40px;
    padding: 0 10px;
    box-shadow: 0 2px 2px #f2f6fc;
    
    img {
      margin-right: 5px;
    }
    
    .news-tit {
      font-size: 14px;
      margin: 0;
      font-weight: bold;
    }
  }
  
  .rankBg {
    position: absolute;
    left: 0;
    right: 0;
    top: 0;
    bottom: 0;
    background: #fff url(/app/rankBg.png) no-repeat;
    background-position: 30px 70px;
    background-size: 110%;
    opacity: .1;
    pointer-events: none;
  }
  
  .news-content {
    height: 290px;
    padding: 0 10px;
    overflow: hidden;
    position: relative;
    
    .news-scroll {
      list-style: none;
      padding: 0;
      margin: 0;
      transition: transform 0.04s linear;
      
      li {
        line-height: 25px;
        height: 58px;
        width: 100%;
        border-bottom: 1px solid #f2f6fc;
        overflow: hidden;
        padding-left: 10px;
        position: relative;
        font-size: 14px;
        color: #333;
        
        b {
          color: #333;
          display: block;
          font-weight: normal;
        }
        
        em {
          display: inline-block;
          color: #999;
          position: absolute;
          right: 10px;
          top: 15px;
          font-style: normal;
        }
      }
    }
  }
}

:deep(.van-tabbar) {
  box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.1);
}
</style>
