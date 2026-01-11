import axios from 'axios'
import { showToast } from 'vant'
import { useUserStore } from '@/stores/user'
import router from '@/router'
import env from '../../env'

const request = axios.create({
  baseURL: env.baseURL,
  timeout: env.timeout
})

// 请求拦截器
request.interceptors.request.use(
  config => {
    const userStore = useUserStore()
    if (userStore.token) {
      config.headers['token'] = userStore.token
    }
    return config
  },
  error => {
    return Promise.reject(error)
  }
)

// 响应拦截器
request.interceptors.response.use(
  response => {
    const res = response.data
    
    // 如果返回的状态码为200，说明接口请求成功
    if (res.code === 200) {
      return res
    }
    
    // 401: 未登录或token失效
    if (res.code === 401) {
      showToast(res.msg || 'Token已失效，请重新登录')
      const userStore = useUserStore()
      userStore.logout()
      router.push('/login')
      return Promise.reject(new Error(res.msg || 'Error'))
    }
    
    // 其他错误
    showToast(res.msg || '请求失败')
    return Promise.reject(new Error(res.msg || 'Error'))
  },
  error => {
    console.error('请求错误:', error)
    showToast(error.message || '网络错误')
    return Promise.reject(error)
  }
)

export default request

