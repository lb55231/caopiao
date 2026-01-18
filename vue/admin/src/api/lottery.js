import request from '@/utils/request'

/**
 * 获取彩种列表
 */
export function getLotteryTypes(params) {
  return request({
    url: '/admin/lottery/types',
    method: 'get',
    params
  })
}

/**
 * 添加彩种
 */
export function addLotteryType(data) {
  return request({
    url: '/admin/lottery/type/add',
    method: 'post',
    data
  })
}

/**
 * 更新彩种
 */
export function updateLotteryType(data) {
  return request({
    url: '/admin/lottery/type/update/' + data.id,
    method: 'put',
    data
  })
}

/**
 * 删除彩种
 */
export function deleteLotteryType(data) {
  return request({
    url: '/admin/lottery/type/delete/' + data.id,
    method: 'delete',
    data
  })
}

/**
 * 切换彩种状态
 */
export function toggleLotteryStatus(data) {
  return request({
    url: '/admin/lottery/toggle-status',
    method: 'post',
    data
  })
}

/**
 * 保存彩种排序
 */
export function saveLotteryOrder(data) {
  return request({
    url: '/admin/lottery/save-order',
    method: 'post',
    data
  })
}

// ==================== 开奖管理 API ====================

/**
 * 获取开奖记录列表
 */
export function getLotteryResults(params) {
  return request({
    url: '/admin/lottery/results',
    method: 'get',
    params
  })
}

/**
 * 添加开奖记录（手动开奖）
 */
export function addLotteryResult(data) {
  return request({
    url: '/admin/lottery/result/add',
    method: 'post',
    data
  })
}

/**
 * 删除开奖记录
 */
export function deleteLotteryResult(data) {
  return request({
    url: '/admin/lottery/result/delete/' + data.id,
    method: 'delete',
    data
  })
}

// ==================== 游戏记录 API ====================

/**
 * 获取投注记录列表
 */
export function getBetRecords(params) {
  return request({
    url: '/admin/bet/records',
    method: 'get',
    params
  })
}

// ==================== 设置开奖 API ====================

/**
 * 获取预开奖列表
 */
export function getYukaijiang(params) {
  return request({
    url: '/admin/yukaijiang',
    method: 'get',
    params
  })
}

/**
 * 保存预开奖号码
 */
export function saveYkj(data) {
  return request({
    url: '/admin/ykjbaocun',
    method: 'post',
    data
  })
}

