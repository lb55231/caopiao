/**
 * 注单异常检查相关接口
 */
import request from '../utils/request'

/**
 * 获取异常注单列表
 */
export function getAbnormalBets(params) {
  return request.get('/admin/bet/check', { params })
}

/**
 * 撤单
 */
export function cancelBet(id) {
  return request.post('/admin/bet/cancel', { id })
}

/**
 * 修改投注号码
 */
export function updateBetCode(trano, tzcode) {
  return request.post('/admin/bet/update-code', { trano, tzcode })
}
