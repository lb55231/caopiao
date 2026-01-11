import request from './request'

/**
 * 用户登录
 */
export function login(data) {
  return request({
    url: '/user/login',
    method: 'post',
    data
  })
}

/**
 * 用户注册
 */
export function register(data) {
  return request({
    url: '/user/register',
    method: 'post',
    data
  })
}

/**
 * 获取用户信息
 */
export function getUserInfo() {
  return request({
    url: '/user/info',
    method: 'get'
  })
}

/**
 * 更新用户信息
 */
export function updateUserInfo(data) {
  return request({
    url: '/user/update',
    method: 'post',
    data
  })
}

/**
 * 获取账变记录
 */
export function getAccountLogs(params) {
  return request({
    url: '/account/logs',
    method: 'get',
    params
  })
}

/**
 * 充值
 */
export function recharge(data) {
  return request({
    url: '/account/recharge',
    method: 'post',
    data
  })
}

/**
 * 提现
 */
export function withdraw(data) {
  return request({
    url: '/account/withdraw',
    method: 'post',
    data
  })
}

// ==================== 充值相关 ====================
/**
 * 获取支付方式列表
 */
export function getPaySets() {
  return request.get('/user/payset/list')
}

/**
 * 提交充值申请
 */
export function submitRecharge(data) {
  return request.post('/user/recharge/add', data)
}

// ==================== 提现相关 ====================
/**
 * 提交提现申请
 */
export function submitWithdraw(data) {
  return request.post('/user/withdraw/add', data)
}

// ==================== 银行卡管理 ====================
/**
 * 获取银行卡列表
 */
export function getBankList() {
  return request.get('/user/bank/list')
}

/**
 * 添加银行卡
 */
export function addBank(data) {
  return request.post('/user/bank/add', data)
}

/**
 * 删除银行卡
 */
export function deleteBank(id) {
  return request.delete(`/user/bank/delete?id=${id}`)
}

/**
 * 设置默认银行卡
 */
export function setDefaultBank(id) {
  return request.post('/user/bank/set-default', { id })
}

/**
 * 获取用户资料
 */
export function getUserProfile() {
  return request.get('/user/profile')
}

/**
 * 更新用户资料
 */
export function updateUserProfile(data) {
  return request.put('/user/profile', data)
}

/**
 * 修改密码
 */
export function changePassword(data) {
  return request.post('/user/change-password', data)
}

/**
 * 获取投注记录
 */
export function getBetRecords(params) {
  return request({
    url: '/user/bet_records',
    method: 'get',
    params
  })
}

/**
 * 获取账变记录
 */
export function getAccountRecords(params) {
  return request({
    url: '/user/account_records',
    method: 'get',
    params
  })
}
