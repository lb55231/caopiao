import request from '@/utils/request'

// 会员组管理
export const getMemberGroups = () => request.get('/admin/membergroup/list')
export const addMemberGroup = (data) => request.post('/admin/membergroup/add', data)
export const updateMemberGroup = (id, data) => request.put(`/admin/membergroup/update/${id}`, data)
export const deleteMemberGroup = (id) => request.delete(`/admin/membergroup/delete/${id}`)
export const toggleMemberGroupStatus = (id, status) => 
  request.put(`/admin/membergroup/toggle_status/${id}`, { status })

// 会员管理
export const getMemberList = (params) => request.get('/admin/member/list', { params })
export const updateMember = (id, data) => request.put(`/admin/member/update/${id}`, data)
export const toggleMemberLock = (id) => request.put(`/admin/member/toggle_lock/${id}`)
export const changeMemberBalance = (data) => request.post('/admin/member/change_balance/' + data.id, data)
export const changeMemberPoint = (data) => request.post('/admin/member/change_point/' + data.id, data)
export const changeMemberXima = (data) => request.post('/admin/member/change_xima/' + data.id, data)
export const deleteMember = (id) => request.delete(`/admin/member/delete/${id}`)
export const kickOutMember = (id) => request.post(`/admin/member/kickout/${id}`)

// 会员记录统计
export const getMemberRechargeRecords = (uid, params) => 
  request.get('/admin/member/recharge_records', { params: { uid, ...params } })
export const getMemberWithdrawRecords = (uid, params) => 
  request.get('/admin/member/withdraw_records', { params: { uid, ...params } })
export const getMemberGameRecords = (uid, params) => 
  request.get('/admin/member/game_records', { params: { uid, ...params } })

// 同IP会员检测
export const getSameIpMembers = (params) => request.get('/admin/member/same_ip', { params })

// 账变记录
export const getFundRecords = (params) => request.get('/admin/fund/records', { params })

// 银行信息
export const getMemberBanks = (params) => request.get('/admin/member/banks', { params })

// 代理注册链接
export const getAgentLinks = (params) => request.get('/admin/agent/links', { params})
export const addAgentLink = (data) => request.post('/admin/agent/link/add', data)
export const deleteAgentLink = (id) => request.delete(`/admin/agent/link/delete/${id}`)

// 登录日志
export const getLoginLogs = (params) => request.get('/admin/loginlog/list', { params })



