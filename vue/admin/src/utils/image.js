import { getResourceUrl } from '../../env'

/**
 * 图片URL处理工具
 */

/**
 * 获取完整的图片URL
 * @param {string} path - 图片路径（可能是相对路径或完整URL）
 * @returns {string} 完整的图片URL
 */
export function getImageUrl(path) {
  return getResourceUrl(path)
}

/**
 * 获取彩票图标URL（用于img标签显示）
 * @param {string} logo - logo路径
 * @returns {string} 图标URL
 */
export function getLotteryLogoUrl(logo) {
  return getImageUrl(logo)
}

