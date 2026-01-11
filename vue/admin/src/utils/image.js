/**
 * 图片URL处理工具
 */

/**
 * 获取完整的图片URL
 * @param {string} path - 图片路径（可能是相对路径或完整URL）
 * @returns {string} 完整的图片URL
 */
export function getImageUrl(path) {
  if (!path) {
    return ''
  }
  
  // 如果已经是完整URL，直接返回
  if (path.startsWith('http://') || path.startsWith('https://')) {
    return path
  }
  
  // 使用当前页面的协议和主机，替换端口为8000
  // 例如: http://192.168.21.101:5173 -> http://192.168.21.101:8000
  const currentHost = window.location.hostname
  const backendUrl = `http://${currentHost}:8000`
  return backendUrl + path
}

/**
 * 获取彩票图标URL（用于img标签显示）
 * @param {string} logo - logo路径
 * @returns {string} 图标URL
 */
export function getLotteryLogoUrl(logo) {
  return getImageUrl(logo)
}

