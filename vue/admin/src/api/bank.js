import request from '@/utils/request'

// ==================== 提款银行 ====================
/**
 * 获取提款银行列表
 */
export function getSysBankList(params) {
  return request.get('/admin/sysbank/list', { params })
}

/**
 * 添加提款银行
 */
export function addSysBank(data) {
  return request.post('/admin/sysbank/add', data)
}

/**
 * 更新提款银行
 */
export function updateSysBank(data) {
  return request.put('/admin/sysbank/update', data)
}

/**
 * 删除提款银行
 */
export function deleteSysBank(id) {
  return request.delete(`/sysbank/delete?id=${id}`)
}

/**
 * 切换提款银行状态
 */
export function toggleSysBankStatus(data) {
  return request.post('/admin/sysbank/toggle-status', data)
}

// ==================== 存款方式配置 ====================
/**
 * 获取存款方式列表
 */
export function getLineBankList(params) {
  return request.get('/admin/linebank/list', { params })
}

/**
 * 添加存款方式
 */
export function addLineBank(data) {
  return request.post('/admin/linebank/add', data)
}

/**
 * 更新存款方式
 */
export function updateLineBank(data) {
  return request.put('/admin/linebank/update', data)
}

/**
 * 删除存款方式
 */
export function deleteLineBank(id) {
  return request.delete(`/linebank/delete?id=${id}`)
}

/**
 * 切换存款方式状态
 */
export function toggleLineBankStatus(data) {
  return request.post('/admin/linebank/toggle-status', data)
}

// ==================== 存款方式配置（payset表）====================
/**
 * 获取存款方式配置列表
 */
export function getPaySetList() {
  return request.get('/admin/payset/list')
}

/**
 * 添加存款方式
 */
export function addPaySet(data) {
  return request.post('/admin/payset/add', data)
}

/**
 * 更新存款方式
 */
export function updatePaySet(data) {
  return request.put('/admin/payset/update', data)
}

/**
 * 删除存款方式
 */
export function deletePaySet(id) {
  return request.delete(`/payset/delete?id=${id}`)
}

/**
 * 切换存款方式状态
 */
export function togglePaySetStatus(data) {
  return request.post('/admin/payset/toggle-status', data)
}

/**
 * 批量更新排序
 */
export function updatePaySetOrder(data) {
  return request.post('/admin/payset/listorder', data)
}

// ==================== 充值记录 ====================
/**
 * 获取充值记录列表
 */
export function getRechargeList(params) {
  return request.get('/admin/recharge/list', { params })
}

/**
 * 审核充值记录
 */
export function auditRecharge(data) {
  return request.post('/admin/recharge/audit/' + data.id, data)
}

/**
 * 删除充值记录
 */
export function deleteRecharge(id) {
  return request.delete(`/recharge/delete?id=${id}`)
}

// ==================== 提现记录 ====================
/**
 * 获取提现记录列表
 */
export function getWithdrawList(params) {
  return request.get('/admin/withdraw/list', { params })
}

/**
 * 审核提现记录
 */
export function auditWithdraw(data) {
  return request.post('/admin/withdraw/audit/' + data.id, data)
}

/**
 * 删除提现记录
 */
export function deleteWithdraw(id) {
  return request.delete(`/withdraw/delete?id=${id}`)
}

