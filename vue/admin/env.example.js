/**
 * Admin 后台环境配置示例文件
 * 
 * 使用方法：
 * 1. 复制此文件为 env.js
 * 2. 修改配置为您的实际环境配置
 * 3. 如果需要，可以将 env.js 添加到 .gitignore，避免提交个人配置
 */

// 开发环境配置
const development = {
  // API基础路径 - 修改为您的后端API地址
  baseURL: 'http://127.0.0.1:8000/api',
  
  // 管理后台API基础路径
  adminBaseURL: 'http://127.0.0.1:8000/api/admin',
  
  // 静态资源基础路径（图片、文件等） - 修改为您的资源服务器地址
  resourceURL: 'http://127.0.0.1:8000',
  
  // 请求超时时间（毫秒）
  timeout: 10000
}

// 生产环境配置
const production = {
  // API基础路径 - 生产环境请修改为实际的域名
  baseURL: 'https://api.your-domain.com/api',
  
  // 管理后台API基础路径
  adminBaseURL: 'https://api.your-domain.com/api/admin',
  
  // 静态资源基础路径 - 可以使用CDN地址
  resourceURL: 'https://cdn.your-domain.com',
  
  // 请求超时时间（毫秒）
  timeout: 30000
}

// 根据当前环境选择配置
const env = import.meta.env.MODE === 'production' ? production : development

/**
 * 获取完整的资源URL
 * @param {string} path - 资源路径
 * @returns {string} 完整的资源URL
 */
export function getResourceUrl(path) {
  if (!path) {
    return ''
  }
  
  // 如果已经是完整URL，直接返回
  if (path.startsWith('http://') || path.startsWith('https://')) {
    return path
  }
  
  // 确保路径以 / 开头
  const normalizedPath = path.startsWith('/') ? path : `/${path}`
  
  return env.resourceURL + normalizedPath
}

export default env
