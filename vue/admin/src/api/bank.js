import adminRequest from '@/utils/adminRequest'

// ==================== 提款银行 ====================
/**
 * 获取提款银行列表
 */
export function getSysBankList(params) {
  return adminRequest.get('/sysbank/list', { params })
}

/**
 * 添加提款银行
 */
export function addSysBank(data) {
  return adminRequest.post('/sysbank/add', data)
}

/**
 * 更新提款银行
 */
export function updateSysBank(data) {
  return adminRequest.put('/sysbank/update', data)
}

/**
 * 删除提款银行
 */
export function deleteSysBank(id) {
  return adminRequest.delete(`/sysbank/delete?id=${id}`)
}

/**
 * 切换提款银行状态
 */
export function toggleSysBankStatus(data) {
  return adminRequest.post('/sysbank/toggle-status', data)
}

// ==================== 存款方式配置 ====================
/**
 * 获取存款方式列表
 */
export function getLineBankList(params) {
  return adminRequest.get('/linebank/list', { params })
}

/**
 * 添加存款方式
 */
export function addLineBank(data) {
  return adminRequest.post('/linebank/add', data)
}

/**
 * 更新存款方式
 */
export function updateLineBank(data) {
  return adminRequest.put('/linebank/update', data)
}

/**
 * 删除存款方式
 */
export function deleteLineBank(id) {
  return adminRequest.delete(`/linebank/delete?id=${id}`)
}

/**
 * 切换存款方式状态
 */
export function toggleLineBankStatus(data) {
  return adminRequest.post('/linebank/toggle-status', data)
}

// ==================== 存款方式配置（payset表）====================
/**
 * 获取存款方式配置列表
 */
export function getPaySetList() {
  return adminRequest.get('/payset/list')
}

/**
 * 添加存款方式
 */
export function addPaySet(data) {
  return adminRequest.post('/payset/add', data)
}

/**
 * 更新存款方式
 */
export function updatePaySet(data) {
  return adminRequest.put('/payset/update', data)
}

/**
 * 删除存款方式
 */
export function deletePaySet(id) {
  return adminRequest.delete(`/payset/delete?id=${id}`)
}

/**
 * 切换存款方式状态
 */
export function togglePaySetStatus(data) {
  return adminRequest.post('/payset/toggle-status', data)
}

/**
 * 批量更新排序
 */
export function updatePaySetOrder(data) {
  return adminRequest.post('/payset/listorder', data)
}

// ==================== 充值记录 ====================
/**
 * 获取充值记录列表
 */
export function getRechargeList(params) {
  return adminRequest.get('/recharge/list', { params })
}

/**
 * 审核充值记录
 */
export function auditRecharge(data) {
  return adminRequest.post('/recharge/audit/' + data.id, data)
}

/**
 * 删除充值记录
 */
export function deleteRecharge(id) {
  return adminRequest.delete(`/recharge/delete?id=${id}`)
}

// ==================== 提现记录 ====================
/**
 * 获取提现记录列表
 */
export function getWithdrawList(params) {
  return adminRequest.get('/withdraw/list', { params })
}

/**
 * 审核提现记录
 */
export function auditWithdraw(data) {
  return adminRequest.post('/withdraw/audit/' + data.id, data)
}

/**
 * 删除提现记录
 */
export function deleteWithdraw(id) {
  return adminRequest.delete(`/withdraw/delete?id=${id}`)
}

