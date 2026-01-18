/**
 * 玩法管理相关接口
 */
import request from '../utils/request'

/**
 * 获取玩法列表
 */
export function getPlayList(params) {
  return request.get('/admin/play/list', { params })
}

/**
 * 更新玩法
 */
export function updatePlay(id, data) {
  return request.post('/admin/play/update', { id, ...data })
}

/**
 * 切换玩法状态
 */
export function togglePlayStatus(id) {
  return request.post('/admin/play/toggle-status', { id })
}

/**
 * 批量调整赔率
 */
export function batchUpdateRate(data) {
  return request.post('/admin/play/batch-rate', data)
}
