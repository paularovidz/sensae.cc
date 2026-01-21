/**
 * Format a phone number for display
 * +33612345678 -> +33 6 12 34 56 78
 *
 * @param {string|null} phone - The phone number in international format
 * @returns {string|null} - The formatted phone number or null
 */
export function formatPhoneForDisplay(phone) {
  if (!phone || phone === '') {
    return null
  }

  // French format: +33 6 12 34 56 78
  if (phone.startsWith('+33') && phone.length === 12) {
    return '+33 ' + phone.substring(3, 4) + ' ' +
           phone.substring(4, 6) + ' ' +
           phone.substring(6, 8) + ' ' +
           phone.substring(8, 10) + ' ' +
           phone.substring(10, 12)
  }

  // Belgian format: +32 4 12 34 56 78
  if (phone.startsWith('+32') && phone.length >= 11) {
    const number = phone.substring(3)
    const groups = number.match(/.{1,2}/g) || []
    return '+32 ' + groups.join(' ')
  }

  // UK format: +44 7xxx xxx xxx
  if (phone.startsWith('+44') && phone.length >= 12) {
    const number = phone.substring(3)
    // UK mobile: 7xxx xxx xxx
    if (number.startsWith('7') && number.length === 10) {
      return '+44 ' + number.substring(0, 4) + ' ' +
             number.substring(4, 7) + ' ' +
             number.substring(7, 10)
    }
    // Generic UK: group by 3
    const groups = number.match(/.{1,3}/g) || []
    return '+44 ' + groups.join(' ')
  }

  // Swiss format: +41 7x xxx xx xx
  if (phone.startsWith('+41') && phone.length >= 11) {
    const number = phone.substring(3)
    const groups = number.match(/.{1,2}/g) || []
    return '+41 ' + groups.join(' ')
  }

  // Luxembourg format: +352 6xx xxx xxx
  if (phone.startsWith('+352') && phone.length >= 12) {
    const number = phone.substring(4)
    const groups = number.match(/.{1,3}/g) || []
    return '+352 ' + groups.join(' ')
  }

  // Generic format: group by 2 after country code
  if (phone.startsWith('+')) {
    const match = phone.match(/^\+(\d{1,3})(.*)$/)
    if (match) {
      const countryCode = match[1]
      const number = match[2]
      const groups = number.match(/.{1,2}/g) || []
      return '+' + countryCode + ' ' + groups.join(' ')
    }
  }

  return phone
}
