import request from '@/utils/request'

/**
 * 获取账变记录
 * @param {Object} params 查询参数
 */
export function getAccountChangeList(params) {
  return request({
    url: '/agent/finance/account-change',
    method: 'get',
    params: {
      page: params.page || 1,
      page_size: params.pageSize || 20,
      type: params.type || '',
      start_time: params.startTime || 0,
      end_time: params.endTime || 0
    }
  })
}

/**
 * 获取充值记录
 * @param {Object} params 查询参数
 */
export function getRechargeList(params) {
  return request({
    url: '/agent/finance/recharge',
    method: 'get',
    params: {
      page: params.page || 1,
      page_size: params.pageSize || 20,
      state: params.state !== undefined ? params.state : ''
    }
  })
}

/**
 * 充值审核
 * @param {Object} data 审核数据
 */
export function auditRecharge(data) {
  return request({
    url: '/admin/recharge/audit',
    method: 'post',
    data: {
      id: data.id,
      state: data.state, // 1-通过, -1-拒绝
      remark: data.remark || ''
    }
  })
}

/**
 * 删除充值记录
 * @param {Number} id 充值记录ID
 */
export function deleteRecharge(id) {
  return request({
    url: `/admin/recharge/delete/${id}`,
    method: 'delete'
  })
}

/**
 * 获取提现记录
 * @param {Object} params 查询参数
 */
export function getWithdrawList(params) {
  return request({
    url: '/agent/finance/withdraw',
    method: 'get',
    params: {
      page: params.page || 1,
      page_size: params.pageSize || 20,
      state: params.state !== undefined ? params.state : ''
    }
  })
}

/**
 * 提现审核
 * @param {Object} data 审核数据
 */
export function auditWithdraw(data) {
  return request({
    url: '/admin/withdraw/audit',
    method: 'post',
    data: {
      id: data.id,
      state: data.state, // 1-通过, 2-退回
      remark: data.remark || ''
    }
  })
}

/**
 * 删除提现记录
 * @param {Number} id 提现记录ID
 */
export function deleteWithdraw(id) {
  return request({
    url: `/admin/withdraw/delete/${id}`,
    method: 'delete'
  })
}

/**
 * 获取收益报表
 * @param {Object} params 查询参数
 */
export function getProfitList(params) {
  return request({
    url: '/agent/finance/profit',
    method: 'get',
    params: {
      page: params.page || 1,
      page_size: params.pageSize || 20,
      start_time: params.startTime || 0,
      end_time: params.endTime || 0
    }
  })
}
