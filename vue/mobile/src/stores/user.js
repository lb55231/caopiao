import { defineStore } from 'pinia'
import { login, getUserInfo } from '@/api/user'

export const useUserStore = defineStore('user', {
  state: () => ({
    token: localStorage.getItem('token') || '',
    userInfo: JSON.parse(localStorage.getItem('userInfo') || '{}')
  }),

  getters: {
    isLogin: (state) => !!state.token,
    balance: (state) => state.userInfo?.balance || '0.00'
  },

  actions: {
    // 登录
    async login(data) {
      try {
        const res = await login(data)
        if (res.code === 200) {
          this.token = res.data.token
          this.userInfo = res.data.userInfo
          localStorage.setItem('token', this.token)
          localStorage.setItem('userInfo', JSON.stringify(this.userInfo))
          return { success: true }
        } else {
          return { success: false, message: res.msg }
        }
      } catch (error) {
        return { success: false, message: error.message }
      }
    },

    // 退出登录
    logout() {
      this.token = ''
      this.userInfo = {}
      localStorage.removeItem('token')
      localStorage.removeItem('userInfo')
    },

    // 获取用户信息
    async getUserInfo() {
      try {
        const res = await getUserInfo()
        if (res.code === 200) {
          this.userInfo = res.data
          localStorage.setItem('userInfo', JSON.stringify(this.userInfo))
        }
      } catch (error) {
        console.error('获取用户信息失败:', error)
      }
    },

    // 更新用户信息（本地）
    updateUserInfo(info) {
      this.userInfo = { ...this.userInfo, ...info }
      localStorage.setItem('userInfo', JSON.stringify(this.userInfo))
    },

    // 更新余额
    updateBalance(balance) {
      this.userInfo = { ...this.userInfo, balance: balance }
      localStorage.setItem('userInfo', JSON.stringify(this.userInfo))
    }
  }
})

