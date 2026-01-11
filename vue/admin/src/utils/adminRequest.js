import axios from 'axios'
import { ElMessage } from 'element-plus'
import router from '@/router'
import env from '../../env'

// 管理后台专用的request实例
const adminRequest = axios.create({
  baseURL: env.adminBaseURL,
  timeout: env.timeout
})

// 请求拦截器
adminRequest.interceptors.request.use(
  config => {
    const token = localStorage.getItem('admin_token')
    if (token) {
      config.headers['Authorization'] = `Bearer ${token}`
      config.headers['Token'] = token
    }
    return config
  },
  error => {
    return Promise.reject(error)
  }
)

// 响应拦截器
adminRequest.interceptors.response.use(
  response => {
    const res = response.data
    
    if (res.code === 401) {
      ElMessage.error('登录已过期，请重新登录')
      localStorage.removeItem('admin_token')
      localStorage.removeItem('admin_info')
      router.push('/login')
      return Promise.reject(new Error('未授权'))
    }
    
    return res
  },
  error => {
    ElMessage.error(error.message || '请求失败')
    return Promise.reject(error)
  }
)

export default adminRequest
