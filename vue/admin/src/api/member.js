import adminRequest from '@/utils/adminRequest'

// 会员组管理
export const getMemberGroups = () => adminRequest.get('/membergroup/list')
export const addMemberGroup = (data) => adminRequest.post('/membergroup/add', data)
export const updateMemberGroup = (id, data) => adminRequest.put(`/membergroup/update/${id}`, data)
export const deleteMemberGroup = (id) => adminRequest.delete(`/membergroup/delete/${id}`)
export const toggleMemberGroupStatus = (id, status) => 
  adminRequest.put(`/membergroup/toggle_status/${id}`, { status })

// 会员管理
export const getMemberList = (params) => adminRequest.get('/member/list', { params })
export const updateMember = (id, data) => adminRequest.put(`/member/update/${id}`, data)
export const toggleMemberLock = (id) => adminRequest.put(`/member/toggle_lock/${id}`)
export const changeMemberBalance = (data) => adminRequest.post('/member/change_balance/' + data.id, data)
export const changeMemberPoint = (data) => adminRequest.post('/member/change_point/' + data.id, data)
export const changeMemberXima = (data) => adminRequest.post('/member/change_xima/' + data.id, data)
export const deleteMember = (id) => adminRequest.delete(`/member/delete/${id}`)
export const kickOutMember = (id) => adminRequest.post(`/member/kickout/${id}`)

// 会员记录统计
export const getMemberRechargeRecords = (uid, params) => 
  adminRequest.get('/member/recharge_records', { params: { uid, ...params } })
export const getMemberWithdrawRecords = (uid, params) => 
  adminRequest.get('/member/withdraw_records', { params: { uid, ...params } })
export const getMemberGameRecords = (uid, params) => 
  adminRequest.get('/member/game_records', { params: { uid, ...params } })

// 同IP会员检测
export const getSameIpMembers = (params) => adminRequest.get('/member/same_ip', { params })

// 账变记录
export const getFundRecords = (params) => adminRequest.get('/fund/records', { params })

// 银行信息
export const getMemberBanks = (params) => adminRequest.get('/member/banks', { params })

// 代理注册链接
export const getAgentLinks = (params) => adminRequest.get('/agent/links', { params})
export const addAgentLink = (data) => adminRequest.post('/agent/link/add', data)
export const deleteAgentLink = (id) => adminRequest.delete(`/agent/link/delete/${id}`)

// 登录日志
export const getLoginLogs = (params) => adminRequest.get('/loginlog/list', { params })



