import adminRequest from '@/utils/adminRequest'

/**
 * 获取彩种列表
 */
export function getLotteryTypes(params) {
  return adminRequest({
    url: '/lottery/types',
    method: 'get',
    params
  })
}

/**
 * 添加彩种
 */
export function addLotteryType(data) {
  return adminRequest({
    url: '/lottery/type/add',
    method: 'post',
    data
  })
}

/**
 * 更新彩种
 */
export function updateLotteryType(data) {
  return adminRequest({
    url: '/lottery/type/update/' + data.id,
    method: 'put',
    data
  })
}

/**
 * 删除彩种
 */
export function deleteLotteryType(data) {
  return adminRequest({
    url: '/lottery/type/delete/' + data.id,
    method: 'delete',
    data
  })
}

/**
 * 切换彩种状态
 */
export function toggleLotteryStatus(data) {
  return adminRequest({
    url: '/lottery/toggle-status',
    method: 'post',
    data
  })
}

/**
 * 保存彩种排序
 */
export function saveLotteryOrder(data) {
  return adminRequest({
    url: '/lottery/save-order',
    method: 'post',
    data
  })
}

// ==================== 开奖管理 API ====================

/**
 * 获取开奖记录列表
 */
export function getLotteryResults(params) {
  return adminRequest({
    url: '/lottery/results',
    method: 'get',
    params
  })
}

/**
 * 添加开奖记录（手动开奖）
 */
export function addLotteryResult(data) {
  return adminRequest({
    url: '/lottery/result/add',
    method: 'post',
    data
  })
}

/**
 * 删除开奖记录
 */
export function deleteLotteryResult(data) {
  return adminRequest({
    url: '/lottery/result/delete/' + data.id,
    method: 'delete',
    data
  })
}

// ==================== 游戏记录 API ====================

/**
 * 获取投注记录列表
 */
export function getBetRecords(params) {
  return adminRequest({
    url: '/bet/records',
    method: 'get',
    params
  })
}

// ==================== 设置开奖 API ====================

/**
 * 获取预开奖列表
 */
export function getYukaijiang(params) {
  return adminRequest({
    url: '/yukaijiang',
    method: 'get',
    params
  })
}

/**
 * 保存预开奖号码
 */
export function saveYkj(data) {
  return adminRequest({
    url: '/ykjbaocun',
    method: 'post',
    data
  })
}

