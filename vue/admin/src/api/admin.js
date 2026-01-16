import adminRequest from '@/utils/adminRequest'
import request from '@/utils/request'
import env from '../../env'

/**
 * 管理员登录
 */
export function login(data) {
  return request.post(env.baseURL + '/admin/login', data)
}

/**
 * 获取当前管理员信息
 */
export function getAdminInfo() {
  // 返回当前存储的管理员信息
  const adminInfo = JSON.parse(localStorage.getItem('admin_info') || '{}')
  return Promise.resolve({
    code: 200,
    msg: '获取成功',
    data: adminInfo
  })
}

/**
 * 获取管理员列表
 */
export function getAdminList(params) {
  return adminRequest.get('/admin/list', { params })
}

/**
 * 添加管理员
 */
export function addAdmin(data) {
  return adminRequest.post('/admin/add', data)
}

/**
 * 更新管理员
 */
export function updateAdmin(data) {
  return adminRequest.put('/admin/update', data)
}

/**
 * 删除管理员
 */
export function deleteAdmin(id) {
  return adminRequest.delete(`/admin/delete?id=${id}`)
}

/**
 * 锁定/解锁管理员
 */
export function toggleAdminLock(data) {
  return adminRequest.post('/admin/toggle-lock', data)
}

/**
 * 获取管理组列表
 */
export function getAdminGroups() {
  return adminRequest.get('/admin/groups')
}

/**
 * 修改密码
 */
export function changePassword(data) {
  return adminRequest.post('/change-password', data)
}

/**
 * 修改安全码
 */
export function changeSafecode(data) {
  return adminRequest.post('/change-safecode', data)
}
