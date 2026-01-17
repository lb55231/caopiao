/**
 * Mobile 移动端环境配置文件
 */

// 开发环境配置
const development = {
  // API基础路径
  baseURL: 'http://127.0.0.1:8000/api',
  // 静态资源基础路径（图片、文件等）
  resourceURL: 'http://127.0.0.1:8000',
  // 超时时间
  timeout: 10000
}

// 生产环境配置
const production = {
  // API基础路径 - 生产环境请修改为实际的域名
  baseURL: '/api',
  // 静态资源基础路径
  resourceURL: '',
  // 超时时间
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
