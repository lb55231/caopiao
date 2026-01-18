/**
 * 环境配置文件
 * 开发环境和生产环境的 API 地址配置
 */

// 开发环境
const dev = {
  baseURL: 'http://127.0.0.1:8000/api',
  wsURL: 'ws://127.0.0.1:8000'
}

// 生产环境
const prod = {
  baseURL: '/api',  // 生产环境使用相对路径
  wsURL: `ws://${window.location.host}`
}

// 根据当前环境导出配置
export default import.meta.env.MODE === 'development' ? dev : prod
