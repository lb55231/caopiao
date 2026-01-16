import dayjs from 'dayjs'

/**
 * 格式化金额
 * @param {Number|String} amount 金额
 * @param {Number} decimals 小数位数
 * @returns {String}
 */
export function formatMoney(amount, decimals = 2) {
  if (!amount && amount !== 0) return '0.00'
  
  const num = parseFloat(amount)
  if (isNaN(num)) return '0.00'
  
  return num.toLocaleString('zh-CN', {
    minimumFractionDigits: decimals,
    maximumFractionDigits: decimals
  })
}

/**
 * 格式化日期时间
 * @param {String|Date} date 日期
 * @param {String} format 格式
 * @returns {String}
 */
export function formatDateTime(date, format = 'YYYY-MM-DD HH:mm:ss') {
  if (!date) return ''
  return dayjs(date).format(format)
}

/**
 * 格式化日期
 * @param {String|Date} date 日期
 * @returns {String}
 */
export function formatDate(date) {
  return formatDateTime(date, 'YYYY-MM-DD')
}

/**
 * 格式化时间
 * @param {String|Date} date 日期
 * @returns {String}
 */
export function formatTime(date) {
  return formatDateTime(date, 'HH:mm:ss')
}

/**
 * 格式化百分比
 * @param {Number|String} value 值
 * @param {Number} decimals 小数位数
 * @returns {String}
 */
export function formatPercent(value, decimals = 2) {
  if (!value && value !== 0) return '0%'
  
  const num = parseFloat(value)
  if (isNaN(num)) return '0%'
  
  return (num * 100).toFixed(decimals) + '%'
}

/**
 * 格式化手机号
 * @param {String} phone 手机号
 * @returns {String}
 */
export function formatPhone(phone) {
  if (!phone) return ''
  return phone.replace(/(\d{3})\d{4}(\d{4})/, '$1****$2')
}

/**
 * 格式化银行卡号
 * @param {String} cardNo 银行卡号
 * @returns {String}
 */
export function formatBankCard(cardNo) {
  if (!cardNo) return ''
  return cardNo.replace(/(\d{4})\d+(\d{4})/, '$1 **** **** $2')
}

/**
 * 格式化文件大小
 * @param {Number} size 文件大小（字节）
 * @returns {String}
 */
export function formatFileSize(size) {
  if (!size || size === 0) return '0 B'
  
  const units = ['B', 'KB', 'MB', 'GB', 'TB']
  const k = 1024
  const i = Math.floor(Math.log(size) / Math.log(k))
  
  return (size / Math.pow(k, i)).toFixed(2) + ' ' + units[i]
}

/**
 * 数字千分位格式化
 * @param {Number|String} num 数字
 * @returns {String}
 */
export function formatNumber(num) {
  if (!num && num !== 0) return '0'
  
  const n = parseFloat(num)
  if (isNaN(n)) return '0'
  
  return n.toLocaleString('zh-CN')
}
