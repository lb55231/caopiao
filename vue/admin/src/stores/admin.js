import { defineStore } from 'pinia'
import { login as loginApi, getAdminInfo } from '@/api/admin'

export const useAdminStore = defineStore('admin', {
  state: () => ({
    token: localStorage.getItem('admin_token') || '',
    adminInfo: JSON.parse(localStorage.getItem('admin_info') || '{}')
  }),

  getters: {
    isLogin: (state) => !!state.token,
    username: (state) => state.adminInfo.username || '',
    groupname: (state) => state.adminInfo.groupname || '管理员'
  },

  actions: {
    async login(loginData) {
      // 开发阶段：先使用真实API，如果失败则fallback到mock
      try {
        const res = await loginApi(loginData)
        if (res.code === 200) {
          this.token = res.data.token
          this.adminInfo = res.data.adminInfo
          localStorage.setItem('admin_token', this.token)
          localStorage.setItem('admin_info', JSON.stringify(this.adminInfo))
          return { success: true }
        } else {
          return { success: false, message: res.msg }
        }
      } catch (error) {
        // API调用失败，使用mock登录（方便开发）
        console.warn('API登录失败，使用mock登录:', error.message)
        
        // Mock登录：admin / admin123 / 123456
        if (loginData.username === 'admin' && 
            loginData.password === 'admin123' && 
            loginData.safecode === '123456') {
          this.token = 'mock_admin_token_' + Date.now()
          this.adminInfo = { 
            id: 1, 
            username: 'admin', 
            groupid: 1,
            groupname: '超级管理员'
          }
          localStorage.setItem('admin_token', this.token)
          localStorage.setItem('admin_info', JSON.stringify(this.adminInfo))
          return { success: true }
        } else {
          return { success: false, message: '用户名、密码或安全码错误' }
        }
      }
    },

    logout() {
      this.token = ''
      this.adminInfo = {}
      localStorage.removeItem('admin_token')
      localStorage.removeItem('admin_info')
    },

    async getInfo() {
      try {
        const res = await getAdminInfo()
        if (res.code === 200) {
          this.adminInfo = res.data
          localStorage.setItem('admin_info', JSON.stringify(this.adminInfo))
        }
      } catch (error) {
        console.error('获取管理员信息失败:', error)
      }
    }
  }
})

