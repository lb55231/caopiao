/**
 * 玩法管理相关接口
 */
import request from '../utils/request'
import env from '../../env'

/**
 * 获取玩法列表
 */
export function getPlayList(params) {
  return request.get(env.baseURL + '/admin/play/list', { params })
}

/**
 * 更新玩法
 */
export function updatePlay(id, data) {
  return request.post(env.baseURL + '/admin/play/update', { id, ...data })
}

/**
 * 切换玩法状态
 */
export function togglePlayStatus(id) {
  return request.post(env.baseURL + '/admin/play/toggle-status', { id })
}

/**
 * 批量调整赔率
 */
export function batchUpdateRate(data) {
  return request.post(env.baseURL + '/admin/play/batch-rate', data)
}
