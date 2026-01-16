/**
 * 验证手机号
 * @param {String} phone 手机号
 * @returns {Boolean}
 */
export function isValidPhone(phone) {
  return /^1[3-9]\d{9}$/.test(phone)
}

/**
 * 验证邮箱
 * @param {String} email 邮箱
 * @returns {Boolean}
 */
export function isValidEmail(email) {
  return /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/.test(email)
}

/**
 * 验证身份证号
 * @param {String} idCard 身份证号
 * @returns {Boolean}
 */
export function isValidIdCard(idCard) {
  return /(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/.test(idCard)
}

/**
 * 验证银行卡号
 * @param {String} cardNo 银行卡号
 * @returns {Boolean}
 */
export function isValidBankCard(cardNo) {
  return /^\d{16,19}$/.test(cardNo)
}

/**
 * 验证密码强度
 * @param {String} password 密码
 * @returns {Object} {valid: Boolean, strength: String}
 */
export function validatePassword(password) {
  if (!password || password.length < 6) {
    return { valid: false, strength: 'weak', message: '密码至少6位' }
  }
  
  let strength = 0
  
  // 包含数字
  if (/\d/.test(password)) strength++
  // 包含小写字母
  if (/[a-z]/.test(password)) strength++
  // 包含大写字母
  if (/[A-Z]/.test(password)) strength++
  // 包含特殊字符
  if (/[^a-zA-Z0-9]/.test(password)) strength++
  
  const strengthMap = {
    1: 'weak',
    2: 'medium',
    3: 'strong',
    4: 'very-strong'
  }
  
  return {
    valid: strength >= 2,
    strength: strengthMap[strength] || 'weak',
    message: strength >= 2 ? '密码强度合格' : '密码强度太弱'
  }
}

/**
 * 验证 URL
 * @param {String} url URL 地址
 * @returns {Boolean}
 */
export function isValidURL(url) {
  try {
    new URL(url)
    return true
  } catch {
    return false
  }
}

/**
 * 验证 IP 地址
 * @param {String} ip IP 地址
 * @returns {Boolean}
 */
export function isValidIP(ip) {
  return /^(\d{1,3}\.){3}\d{1,3}$/.test(ip) && 
    ip.split('.').every(num => parseInt(num) >= 0 && parseInt(num) <= 255)
}

/**
 * 验证数字
 * @param {*} value 值
 * @returns {Boolean}
 */
export function isNumber(value) {
  return !isNaN(parseFloat(value)) && isFinite(value)
}

/**
 * 验证整数
 * @param {*} value 值
 * @returns {Boolean}
 */
export function isInteger(value) {
  return Number.isInteger(Number(value))
}

/**
 * 验证正整数
 * @param {*} value 值
 * @returns {Boolean}
 */
export function isPositiveInteger(value) {
  return isInteger(value) && Number(value) > 0
}

/**
 * 验证金额（最多两位小数）
 * @param {*} value 值
 * @returns {Boolean}
 */
export function isValidAmount(value) {
  return /^(0|[1-9]\d*)(\.\d{1,2})?$/.test(value)
}
