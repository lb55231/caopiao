import request from './request'

/**
 * 获取彩票列表
 */
export function getLotteryList() {
  return request({
    url: '/lottery/list',
    method: 'get'
  })
}

/**
 * 获取最新公告
 */
export function getNotice() {
  return request({
    url: '/lottery/notice',
    method: 'get'
  })
}

/**
 * 获取收益排行榜
 */
export function getRanking() {
  return request({
    url: '/lottery/ranking',
    method: 'get'
  })
}
