import request from '@/utils/request'

/**
 * 获取代理账户信息
 */
export function getAccountInfo() {
  return request({
    url: '/agent/account/info',
    method: 'get'
  })
}

/**
 * 更新账户信息
 * @param {Object} data 账户信息
 */
export function updateAccountInfo(data) {
  return request({
    url: '/account/update',
    method: 'post',
    data
  })
}

/**
 * 修改密码
 * @param {Object} data 密码信息
 */
export function changePassword(data) {
  return request({
    url: '/account/password',
    method: 'post',
    data
  })
}
