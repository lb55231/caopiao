import request from '@/utils/request'

/**
 * 管理员登录
 */
export function login(data) {
  return request.post('/admin/login', data)
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
  return request.get('/admin/admin/list', { params })
}

/**
 * 添加管理员
 */
export function addAdmin(data) {
  return request.post('/admin/admin/add', data)
}

/**
 * 更新管理员
 */
export function updateAdmin(data) {
  return request.put('/admin/admin/update', data)
}

/**
 * 删除管理员
 */
export function deleteAdmin(id) {
  return request.delete(`/admin/admin/delete?id=${id}`)
}

/**
 * 锁定/解锁管理员
 */
export function toggleAdminLock(data) {
  return request.post('/admin/admin/toggle-lock', data)
}

/**
 * 获取管理组列表
 */
export function getAdminGroups() {
  return request.get('/admin/admin/groups')
}

/**
 * 修改密码
 */
export function changePassword(data) {
  return request.post('/admin/change-password', data)
}

/**
 * 修改安全码
 */
export function changeSafecode(data) {
  return request.post('/admin/change-safecode', data)
}
