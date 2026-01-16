import request from '@/utils/request'

/**
 * 获取彩票列表
 * @param {Object} params 查询参数
 */
export function getLotteryList(params) {
  return request({
    url: '/agent/lottery/types',
    method: 'get',
    params
  })
}

/**
 * 获取彩票详情
 * @param {Number} id 彩票ID
 */
export function getLotteryDetail(id) {
  return request({
    url: `/lottery/detail/${id}`,
    method: 'get'
  })
}

/**
 * 更新彩票状态
 * @param {Number} id 彩票ID
 * @param {Number} status 状态
 */
export function updateLotteryStatus(id, status) {
  return request({
    url: `/lottery/status/${id}`,
    method: 'post',
    data: { status }
  })
}

/**
 * 获取开奖记录
 * @param {Object} params 查询参数
 */
export function getDrawList(params) {
  return request({
    url: '/agent/lottery/results',
    method: 'get',
    params
  })
}

/**
 * 获取开奖详情
 * @param {Number} id 开奖记录ID
 */
export function getDrawDetail(id) {
  return request({
    url: `/lottery/draw/${id}`,
    method: 'get'
  })
}

/**
 * 设置开奖
 * @param {Object} data 开奖信息
 */
export function setDraw(data) {
  return request({
    url: '/lottery/draw/set',
    method: 'post',
    data
  })
}

/**
 * 获取当前期号
 * @param {Number} lotteryId 彩票ID
 */
export function getCurrentIssue(lotteryId) {
  return request({
    url: `/lottery/issue/current/${lotteryId}`,
    method: 'get'
  })
}

/**
 * 获取注单列表（下级用户的）
 * @param {Object} params 查询参数
 */
export function getOrderList(params) {
  return request({
    url: '/agent/lottery/bet-records',
    method: 'get',
    params
  })
}

/**
 * 获取注单详情
 * @param {Number} id 注单ID
 */
export function getOrderDetail(id) {
  return request({
    url: `/lottery/orders/${id}`,
    method: 'get'
  })
}

/**
 * 撤销注单
 * @param {Number} id 注单ID
 */
export function cancelOrder(id) {
  return request({
    url: `/lottery/orders/cancel/${id}`,
    method: 'post'
  })
}

/**
 * 获取注单统计
 * @param {Object} params 查询参数
 */
export function getOrderStatistics(params) {
  return request({
    url: '/lottery/orders/statistics',
    method: 'get',
    params
  })
}
