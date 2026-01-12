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
 * 获取彩票类型列表
 */
export function getLotteryTypes(type) {
  return request({
    url: '/lottery/list',
    method: 'get',
    params: { type }
  })
}

/**
 * 获取彩票详情
 */
export function getLotteryDetail(id) {
  return request({
    url: `/lottery/${id}`,
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

/**
 * 获取当前期号
 */
export function getCurrentPeriod(cpname) {
  return request({
    url: '/game/period',
    method: 'get',
    params: { cpname }
  })
}

/**
 * 获取开奖历史
 */
export function getLotteryHistory(params) {
  return request({
    url: '/lottery/history',
    method: 'get',
    params
  })
}

/**
 * 获取玩法配置
 */
export function getPlays(cpname) {
  return request({
    url: '/game/plays',
    method: 'get',
    params: { cpname }
  })
}

/**
 * 提交投注
 */
export function submitBet(data) {
  return request({
    url: '/game/bet',
    method: 'post',
    data
  })
}

/**
 * 获取投注记录
 */
export function getBetList(params) {
  return request({
    url: '/user/bet_records',
    method: 'get',
    params
  })
}
