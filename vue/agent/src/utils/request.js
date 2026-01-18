import axios from 'axios'
import { ElMessage } from 'element-plus'
import router from '@/router'
import env from '@/config/env'

// 创建 axios 实例
const service = axios.create({
  baseURL: env.baseURL,
  timeout: 30000,
  headers: {
    'Content-Type': 'application/json'
  }
})

// 请求拦截器
service.interceptors.request.use(
  config => {
    // 添加 token
    const token = localStorage.getItem('token')
    if (token) {
      config.headers['Authorization'] = `Bearer ${token}`
    }
    return config
  },
  error => {
    console.error('请求错误：', error)
    return Promise.reject(error)
  }
)

// 响应拦截器
service.interceptors.response.use(
  response => {
    const res = response.data
    
    // 后端返回格式: {code: 0/1, msg: '', data: {}}
    // code: 1-成功, 0-失败
    if (res.code !== 1 && res.code !== 200) {
      const message = res.msg || res.message || '请求失败'
      ElMessage.error(message)
      
      // 401: 未授权，跳转到登录页
      if (res.code === 401 || res.code === -1) {
        localStorage.removeItem('token')
        localStorage.removeItem('userInfo')
        router.push('/login')
      }
      
      return Promise.reject(new Error(message))
    }
    
    // 返回 data 部分，方便使用
    return res.data
  },
  error => {
    console.error('响应错误：', error)
    
    let message = '请求失败'
    
    if (error.response) {
      switch (error.response.status) {
        case 400:
          message = '请求参数错误'
          break
        case 401:
          message = '未授权，请重新登录'
          localStorage.removeItem('token')
          localStorage.removeItem('userInfo')
          router.push('/login')
          break
        case 403:
          message = '拒绝访问'
          break
        case 404:
          message = '请求资源不存在'
          break
        case 500:
          message = '服务器内部错误'
          break
        case 502:
          message = '网关错误'
          break
        case 503:
          message = '服务不可用'
          break
        case 504:
          message = '网关超时'
          break
        default:
          message = error.response.data?.message || '请求失败'
      }
    } else if (error.request) {
      message = '网络错误，请检查网络连接'
    }
    
    ElMessage.error(message)
    return Promise.reject(error)
  }
)

export default service
