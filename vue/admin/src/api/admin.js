import adminRequest from '@/utils/adminRequest'

/**
 * 管理员登录
 */
export function login(data) {
  return adminRequest({
    url: '/login',
    method: 'post',
    data
  })
}

/**
 * 获取管理员信息
 */
export function getAdminInfo() {
  return adminRequest({
    url: '/info',
    method: 'get'
  })
}

/**
 * 获取统计数据
 */
export function getStatistics(params) {
  return adminRequest({
    url: '/statistics',
    method: 'get',
    params
  })
}

/**
 * 获取会员列表
 */
export function getMemberList(params) {
  return adminRequest({
    url: '/member/list',
    method: 'get',
    params
  })
}

/**
 * 获取会员详情
 */
export function getMemberDetail(id) {
  return adminRequest({
    url: `/member/${id}`,
    method: 'get'
  })
}

/**
 * 调整会员余额
 */
export function adjustBalance(data) {
  return adminRequest({
    url: '/member/change_balance/' + data.id,
    method: 'post',
    data
  })
}

/**
 * 获取投注记录
 */
export function getBetList(params) {
  return adminRequest({
    url: '/bets',
    method: 'get',
    params
  })
}

/**
 * 获取开奖记录
 */
export function getLotteryList(params) {
  return adminRequest({
    url: '/lottery/results',
    method: 'get',
    params
  })
}

/**
 * 添加开奖记录
 */
export function addLottery(data) {
  return adminRequest({
    url: '/lottery/result/add',
    method: 'post',
    data
  })
}

/**
 * 获取充值记录
 */
export function getRechargeList(params) {
  return adminRequest({
    url: '/recharge/list',
    method: 'get',
    params
  })
}

/**
 * 获取提现记录
 */
export function getWithdrawList(params) {
  return adminRequest({
    url: '/withdraw/list',
    method: 'get',
    params
  })
}

/**
 * 审核提现
 */
export function auditWithdraw(data) {
  return adminRequest({
    url: '/withdraw/audit/' + data.id,
    method: 'post',
    data
  })
}

/**
 * 获取账变记录
 */
export function getFinanceRecords(params) {
  return adminRequest({
    url: '/finance/records',
    method: 'get',
    params
  })
}

