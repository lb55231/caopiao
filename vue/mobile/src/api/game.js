import request from './request'

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
 * 获取玩法配置
 */
export function getPlays() {
  return request({
    url: '/game/plays',
    method: 'get'
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
 * 查询投注记录
 */
export function getBetRecords(params) {
  return request({
    url: '/game/records',
    method: 'get',
    params
  })
}

