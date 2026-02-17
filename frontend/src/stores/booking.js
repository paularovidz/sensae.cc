import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { publicBookingApi, publicPromoCodesApi, publicPrepaidApi } from '@/services/api'
import { useAuthStore } from '@/stores/auth'

export const useBookingStore = defineStore('booking', () => {
  const authStore = useAuthStore()
  // ========================================
  // STATE
  // ========================================

  // Wizard state
  const currentStep = ref(1)
  const totalSteps = 5

  // Step 1: Client type
  const isNewClient = ref(null) // true = first visit, false = returning

  // Step 2: Person selection
  const existingPersons = ref([])
  const selectedPersonId = ref(null)
  const newPerson = ref({
    firstName: '',
    lastName: ''
  })

  // Step 3: Date/Time
  const selectedDate = ref(null)
  const selectedTime = ref(null)
  const durationType = ref('regular') // 'discovery', 'regular', 'half_day', 'full_day'
  const withAccompaniment = ref(true) // For group sessions (half_day, full_day)
  const associationSessionCategory = ref(null) // 'individual' or 'privatization' - for step 2 sub-navigation
  const availableDates = ref([])
  const availableSlots = ref([])
  const currentMonth = ref(new Date().getMonth() + 1)
  const currentYear = ref(new Date().getFullYear())

  // Step 4: Contact info
  const clientInfo = ref({
    email: '',
    phone: '',
    firstName: '',
    lastName: '',
    clientType: 'personal', // 'personal' or 'professional'
    companyName: '',
    siret: ''
  })
  const gdprConsent = ref(false)
  const cgrConsent = ref(false) // Conditions Générales de Réservation
  const captchaToken = ref(null)

  // Existing client info (masked for security)
  const existingClientInfo = ref(null) // { email_masked, phone_masked, has_phone, gdpr_already_accepted, first_name, last_name, client_type, company_name }

  // Step 5: Confirmation
  const bookingResult = ref(null)

  // Schedule info
  const scheduleInfo = ref(null)
  const durationLabels = ref({})
  const prices = ref({ discovery: 55, regular: 45 }) // default prices for personal
  const pricesByClientType = ref({
    personal: { discovery: 55, regular: 45 },
    association: {
      discovery: 50,
      regular: 40,
      half_day_with: 200,
      half_day_without: 120,
      full_day_with: 350,
      full_day_without: 200
    }
  })

  // Group session types (half_day, full_day) - associations only
  const groupTypes = ref(['half_day', 'full_day'])
  const bookingDelays = ref({ personal: 60, association: 90 })
  const emailConfirmationRequired = ref(false)

  // Prepaid credits
  const prepaidBalance = ref(null) // { total_credits: number, packs: array }
  const usePrepaid = ref(false) // Whether to use prepaid credit for this booking
  const prepaidLoading = ref(false)

  // Promo codes
  const hasManualPromoCodes = ref(false)
  const promoCodeInput = ref('')
  const appliedPromo = ref(null) // { id, code, name, discount_type, discount_value, discount_label }
  const promoPricing = ref(null) // { original_price, discount_amount, final_price }
  const promoError = ref(null)
  const promoLoading = ref(false)

  // Loading states
  const loading = ref(false)
  const error = ref(null)

  // ========================================
  // GETTERS
  // ========================================

  const canGoNext = computed(() => {
    switch (currentStep.value) {
      case 1:
        return isNewClient.value !== null
      case 2:
        // Group sessions (half_day, full_day) don't require person selection
        if (isGroupSession.value) {
          return true
        }
        if (isNewClient.value) {
          return newPerson.value.firstName.trim() && newPerson.value.lastName.trim()
        }
        return selectedPersonId.value !== null || (newPerson.value.firstName.trim() && newPerson.value.lastName.trim())
      case 3:
        return selectedDate.value && selectedTime.value
      case 4:
        return (
          clientInfo.value.email.trim() &&
          clientInfo.value.firstName.trim() &&
          clientInfo.value.lastName.trim() &&
          gdprConsent.value &&
          cgrConsent.value
        )
      case 5:
        return true
      default:
        return false
    }
  })

  const personInfo = computed(() => {
    if (selectedPersonId.value) {
      const person = existingPersons.value.find(p => p.id === selectedPersonId.value)
      if (person) {
        return {
          firstName: person.first_name,
          lastName: person.last_name,
          id: person.id
        }
      }
    }
    return {
      firstName: newPerson.value.firstName,
      lastName: newPerson.value.lastName,
      id: null
    }
  })

  const bookingData = computed(() => {
    const data = {
      session_date: selectedDate.value && selectedTime.value
        ? `${selectedDate.value} ${selectedTime.value}:00`
        : null,
      duration_type: durationType.value,
      with_accompaniment: withAccompaniment.value,
      client_email: clientInfo.value.email.trim().toLowerCase(),
      client_phone: clientInfo.value.phone.trim() || null,
      client_first_name: clientInfo.value.firstName.trim(),
      client_last_name: clientInfo.value.lastName.trim(),
      gdpr_consent: gdprConsent.value,
      client_type: clientInfo.value.clientType || 'personal'
    }

    // Person info only for individual sessions
    if (!isGroupSession.value) {
      data.person_first_name = personInfo.value.firstName
      data.person_last_name = personInfo.value.lastName
      data.person_id = personInfo.value.id
    }

    // Add professional info if association client
    if (clientInfo.value.clientType === 'association') {
      data.company_name = clientInfo.value.companyName.trim() || null
      data.siret = clientInfo.value.siret.replace(/\s/g, '').trim() || null
    }

    // Add captcha token if present
    if (captchaToken.value) {
      data.captcha_token = captchaToken.value
    }

    // Add prepaid flag if using prepaid credit (takes priority over promo)
    if (willUsePrepaid.value) {
      data.use_prepaid = true
    }
    // Add promo code if applied (only if not using prepaid)
    else if (appliedPromo.value) {
      if (appliedPromo.value.code) {
        data.promo_code = appliedPromo.value.code
      } else {
        data.promo_code_id = appliedPromo.value.id
      }
    }

    return data
  })

  const durationInfo = computed(() => {
    const type = durationType.value
    const price = currentPrice.value

    // Use dynamic values from scheduleInfo if available
    if (scheduleInfo.value?.durations?.[type]) {
      const info = scheduleInfo.value.durations[type]
      const labels = {
        discovery: 'Séance découverte (1h15)',
        regular: 'Séance classique (45min)',
        half_day: 'Privatisation demi-journée (4h)',
        full_day: 'Privatisation journée (8h)'
      }
      return {
        display: info.display,
        blocked: info.blocked,
        label: labels[type] || type,
        price,
        isGroup: info.is_group || false
      }
    }
    // Fallback defaults
    const defaults = {
      discovery: { display: 75, blocked: 90, label: 'Séance découverte (1h15)' },
      regular: { display: 45, blocked: 65, label: 'Séance classique (45min)' },
      half_day: { display: 240, blocked: 240, label: 'Demi-journée (4h)', isGroup: true },
      full_day: { display: 480, blocked: 480, label: 'Journée complète (8h)', isGroup: true }
    }
    const d = defaults[type] || defaults.regular
    return { ...d, price }
  })

  // Get the current client type (from existingClientInfo or clientInfo)
  const currentClientType = computed(() => {
    return existingClientInfo.value?.client_type || clientInfo.value.clientType || 'personal'
  })

  // Check if user is admin (admins have no booking limits)
  const isAdminUser = computed(() => {
    return existingClientInfo.value?.is_admin === true
  })

  // Get max advance days for booking
  const maxAdvanceDays = computed(() => {
    if (isAdminUser.value) return 365
    const clientType = currentClientType.value
    return bookingDelays.value[clientType] || 60
  })

  // Get prices for the current client type
  const pricesForCurrentClient = computed(() => {
    const clientType = currentClientType.value
    return pricesByClientType.value[clientType] || prices.value
  })

  // Check if current duration type is a group session
  const isGroupSession = computed(() => {
    return groupTypes.value.includes(durationType.value)
  })

  // Get available session types for the current client type
  const availableSessionTypes = computed(() => {
    const isAssociation = currentClientType.value === 'association'

    // Individual types are always available
    const types = [
      { value: 'discovery', label: 'Séance découverte (1h15)', description: 'Première séance pour découvrir Snoezelen' },
      { value: 'regular', label: 'Séance classique (45min)', description: 'Séance de suivi régulier' }
    ]

    // Privatization types only for associations
    if (isAssociation) {
      types.push(
        { value: 'half_day', label: 'Privatisation demi-journée (4h)', description: 'Privatisation de l\'espace - demi-journée', isGroup: true },
        { value: 'full_day', label: 'Privatisation journée (8h)', description: 'Privatisation de l\'espace - journée entière', isGroup: true }
      )
    }

    return types
  })

  const currentPrice = computed(() => {
    // Priority: 1. Free session promo, 2. Prepaid credit, 3. Other promos
    // If free session promo is applied, it has priority
    if (hasFreeSessionPromo.value) {
      return promoPricing.value.final_price // Should be 0
    }
    // If using prepaid credit, price is 0
    if (willUsePrepaid.value) {
      return 0
    }
    // If other promo is applied, return the final price
    if (promoPricing.value) {
      return promoPricing.value.final_price
    }

    // Calculate price based on duration type and accompaniment (for group sessions)
    const clientPrices = pricesForCurrentClient.value

    // For group sessions, price depends on accompaniment
    if (isGroupSession.value) {
      const suffix = withAccompaniment.value ? '_with' : '_without'
      const priceKey = `${durationType.value}${suffix}`
      return clientPrices[priceKey] || 0
    }

    return clientPrices[durationType.value] || (durationType.value === 'discovery' ? 55 : 45)
  })

  const originalPrice = computed(() => {
    const clientPrices = pricesForCurrentClient.value

    // For group sessions, price depends on accompaniment
    if (isGroupSession.value) {
      const suffix = withAccompaniment.value ? '_with' : '_without'
      const priceKey = `${durationType.value}${suffix}`
      return clientPrices[priceKey] || 0
    }

    return clientPrices[durationType.value] || (durationType.value === 'discovery' ? 55 : 45)
  })

  const hasPromoApplied = computed(() => {
    return appliedPromo.value !== null && promoPricing.value !== null
  })

  // Check if applied promo is a "free session" type (has priority over prepaid)
  const hasFreeSessionPromo = computed(() => {
    return hasPromoApplied.value && appliedPromo.value?.discount_type === 'free_session'
  })

  const hasPrepaidCredits = computed(() => {
    return prepaidBalance.value && prepaidBalance.value.total_credits > 0
  })

  // Can use prepaid only if no free session promo is applied
  const canUsePrepaid = computed(() => {
    return hasPrepaidCredits.value && !hasFreeSessionPromo.value
  })

  const willUsePrepaid = computed(() => {
    return canUsePrepaid.value && usePrepaid.value
  })

  // ========================================
  // ACTIONS
  // ========================================

  async function fetchScheduleInfo() {
    try {
      const response = await publicBookingApi.getSchedule()
      scheduleInfo.value = response.data.data
      durationLabels.value = response.data.data.duration_types
      prices.value = response.data.data.prices || { discovery: 55, regular: 45 }
      pricesByClientType.value = response.data.data.prices_by_client_type || {
        personal: { discovery: 55, regular: 45 },
        association: {
          discovery: 50,
          regular: 40,
          half_day_with: 200,
          half_day_without: 120,
          full_day_with: 350,
          full_day_without: 200
        }
      }
      bookingDelays.value = response.data.data.booking_delays || { personal: 60, association: 90 }
      emailConfirmationRequired.value = response.data.data.email_confirmation_required || false
      // Update group types from server if provided
      if (response.data.data.group_types) {
        groupTypes.value = response.data.data.group_types
      }
    } catch (err) {
      console.error('Failed to fetch schedule:', err)
    }
  }

  // ========================================
  // PROMO CODE ACTIONS
  // ========================================

  async function checkHasManualPromoCodes() {
    try {
      const response = await publicPromoCodesApi.hasManualCodes()
      hasManualPromoCodes.value = response.data.data.has_manual_codes
    } catch (err) {
      console.error('Failed to check manual promo codes:', err)
      hasManualPromoCodes.value = false
    }
  }

  async function validatePromoCode(code) {
    if (!code || !code.trim()) {
      promoError.value = 'Veuillez entrer un code'
      return false
    }

    promoLoading.value = true
    promoError.value = null

    try {
      const response = await publicPromoCodesApi.validate(
        code.trim(),
        durationType.value,
        clientInfo.value.email?.trim() || null,
        clientInfo.value.clientType || 'personal'
      )

      const data = response.data.data
      appliedPromo.value = data.promo
      promoPricing.value = data.pricing
      promoCodeInput.value = code.trim().toUpperCase()

      return true
    } catch (err) {
      promoError.value = err.response?.data?.message || 'Code promo invalide'
      appliedPromo.value = null
      promoPricing.value = null
      return false
    } finally {
      promoLoading.value = false
    }
  }

  async function checkAutomaticPromo() {
    promoLoading.value = true

    try {
      const response = await publicPromoCodesApi.getAutomatic(
        durationType.value,
        clientInfo.value.email?.trim() || null,
        clientInfo.value.clientType || 'personal'
      )

      const data = response.data.data
      if (data.has_automatic_promo) {
        appliedPromo.value = data.promo
        promoPricing.value = data.pricing
        return true
      }
    } catch (err) {
      console.error('Failed to check automatic promo:', err)
    } finally {
      promoLoading.value = false
    }

    return false
  }

  function clearPromoCode() {
    promoCodeInput.value = ''
    appliedPromo.value = null
    promoPricing.value = null
    promoError.value = null
  }

  // ========================================
  // PREPAID CREDITS ACTIONS
  // ========================================

  async function checkPrepaidBalance(email) {
    if (!email || !email.trim()) {
      prepaidBalance.value = null
      usePrepaid.value = false
      return null
    }

    prepaidLoading.value = true

    try {
      const response = await publicPrepaidApi.check(email.trim(), durationType.value)
      prepaidBalance.value = response.data.data
      // Auto-enable if credits available
      if (prepaidBalance.value?.total_credits > 0) {
        usePrepaid.value = true
      }
      return prepaidBalance.value
    } catch (err) {
      console.error('Failed to check prepaid balance:', err)
      prepaidBalance.value = null
      usePrepaid.value = false
      return null
    } finally {
      prepaidLoading.value = false
    }
  }

  function setUsePrepaid(value) {
    usePrepaid.value = value
    // If enabling prepaid, clear promo code (mutually exclusive)
    if (value && appliedPromo.value) {
      clearPromoCode()
    }
  }

  function clearPrepaidState() {
    prepaidBalance.value = null
    usePrepaid.value = false
  }

  async function checkEmail(email) {
    loading.value = true
    error.value = null

    try {
      const response = await publicBookingApi.checkEmail(email)
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors de la vérification'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function fetchPersonsByEmail(email) {
    loading.value = true
    error.value = null

    try {
      const response = await publicBookingApi.getPersonsByEmail(email)
      const data = response.data.data

      existingPersons.value = data.persons || []

      // Store existing client info if available
      if (data.existing_client && data.client_info) {
        existingClientInfo.value = data.client_info
        // Pre-fill client info with the real names (not masked)
        clientInfo.value.firstName = data.client_info.first_name
        clientInfo.value.lastName = data.client_info.last_name
        // Pre-fill client type and company info
        clientInfo.value.clientType = data.client_info.client_type || 'personal'
        clientInfo.value.companyName = data.client_info.company_name || ''
        // Auto-accept GDPR for existing clients (they already have an account)
        gdprConsent.value = true
      } else {
        existingClientInfo.value = null
      }

      return existingPersons.value
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors de la récupération des personnes'
      existingPersons.value = []
      existingClientInfo.value = null
      throw err
    } finally {
      loading.value = false
    }
  }

  async function fetchAvailableDates(year = currentYear.value, month = currentMonth.value) {
    loading.value = true
    error.value = null

    try {
      const clientType = currentClientType.value
      const email = clientInfo.value.email?.trim() || null
      const response = await publicBookingApi.getAvailableDates(year, month, durationType.value, clientType, email, withAccompaniment.value)
      availableDates.value = response.data.data.available_dates || []
      currentYear.value = year
      currentMonth.value = month
      return availableDates.value
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors de la récupération des dates'
      availableDates.value = []
      throw err
    } finally {
      loading.value = false
    }
  }

  async function fetchAvailableSlots(date) {
    loading.value = true
    error.value = null

    try {
      const response = await publicBookingApi.getAvailableSlots(date, durationType.value, withAccompaniment.value)
      availableSlots.value = response.data.data.slots || []
      return availableSlots.value
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors de la récupération des créneaux'
      availableSlots.value = []
      throw err
    } finally {
      loading.value = false
    }
  }

  async function createBooking() {
    loading.value = true
    error.value = null

    try {
      const response = await publicBookingApi.createBooking(bookingData.value)
      bookingResult.value = response.data.data

      // Refresh user data if authenticated (to get updated phone, etc.)
      if (authStore.isAuthenticated) {
        try {
          await authStore.fetchCurrentUser()
        } catch (e) {
          // Ignore refresh errors, booking was successful
        }
      }

      // Clear existingClientInfo cache so next booking fetches fresh data
      existingClientInfo.value = null

      return bookingResult.value
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors de la création de la réservation'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function confirmBooking(token) {
    loading.value = true
    error.value = null

    try {
      const response = await publicBookingApi.confirmBooking(token)
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors de la confirmation'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function cancelBooking(token) {
    loading.value = true
    error.value = null

    try {
      const response = await publicBookingApi.cancelBooking(token)
      return response.data.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Erreur lors de l\'annulation'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function getBookingByToken(token) {
    loading.value = true
    error.value = null

    try {
      const response = await publicBookingApi.getBookingByToken(token)
      return response.data.data.booking
    } catch (err) {
      error.value = err.response?.data?.message || 'Réservation non trouvée'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Navigation
  function nextStep() {
    if (currentStep.value < totalSteps && canGoNext.value) {
      currentStep.value++
      saveToStorage()
    }
  }

  function prevStep() {
    if (currentStep.value > 1) {
      currentStep.value--
      saveToStorage()
    }
  }

  function goToStep(step) {
    if (step >= 1 && step <= totalSteps) {
      currentStep.value = step
      saveToStorage()
    }
  }

  // Storage persistence
  function saveToStorage() {
    const state = {
      currentStep: currentStep.value,
      isNewClient: isNewClient.value,
      selectedPersonId: selectedPersonId.value,
      newPerson: newPerson.value,
      selectedDate: selectedDate.value,
      selectedTime: selectedTime.value,
      durationType: durationType.value,
      clientInfo: clientInfo.value,
      gdprConsent: gdprConsent.value,
      existingPersons: existingPersons.value,
      existingClientInfo: existingClientInfo.value
    }
    try {
      localStorage.setItem('booking_wizard_state', JSON.stringify(state))
    } catch (e) {
      console.warn('Failed to save booking state:', e)
    }
  }

  function restoreFromStorage() {
    try {
      const saved = localStorage.getItem('booking_wizard_state')
      if (saved) {
        const state = JSON.parse(saved)
        currentStep.value = state.currentStep || 1
        isNewClient.value = state.isNewClient
        selectedPersonId.value = state.selectedPersonId
        newPerson.value = state.newPerson || { firstName: '', lastName: '' }
        selectedDate.value = state.selectedDate
        selectedTime.value = state.selectedTime
        durationType.value = state.durationType || 'regular'
        clientInfo.value = state.clientInfo || { email: '', phone: '', firstName: '', lastName: '', clientType: 'personal', companyName: '', siret: '' }
        gdprConsent.value = state.gdprConsent || false
        existingPersons.value = state.existingPersons || []
        existingClientInfo.value = state.existingClientInfo || null
        return true
      }
    } catch (e) {
      console.warn('Failed to restore booking state:', e)
    }
    return false
  }

  function clearStorage() {
    localStorage.removeItem('booking_wizard_state')
  }

  // Reset
  function resetWizard() {
    currentStep.value = 1
    isNewClient.value = null
    existingPersons.value = []
    selectedPersonId.value = null
    newPerson.value = { firstName: '', lastName: '' }
    selectedDate.value = null
    selectedTime.value = null
    durationType.value = 'regular'
    withAccompaniment.value = true
    associationSessionCategory.value = null
    availableDates.value = []
    availableSlots.value = []
    clientInfo.value = { email: '', phone: '', firstName: '', lastName: '', clientType: 'personal', companyName: '', siret: '' }
    gdprConsent.value = false
    cgrConsent.value = false
    captchaToken.value = null
    existingClientInfo.value = null
    bookingResult.value = null
    error.value = null
    // Reset promo state
    promoCodeInput.value = ''
    appliedPromo.value = null
    promoPricing.value = null
    promoError.value = null
    // Reset prepaid state
    prepaidBalance.value = null
    usePrepaid.value = false
    clearStorage()
  }

  // Set captcha token
  function setCaptchaToken(token) {
    captchaToken.value = token
  }

  // Set duration type (changes availability)
  function setDurationType(type) {
    if (type !== durationType.value) {
      const wasGroupSession = groupTypes.value.includes(durationType.value)
      const isGroupSession = groupTypes.value.includes(type)
      durationType.value = type
      // Reset accompaniment to true only when switching FROM individual TO group session
      // Keep the choice when switching between group types (half_day <-> full_day)
      if (isGroupSession && !wasGroupSession) {
        withAccompaniment.value = true
      }
      // Reset date/time selection as slots change
      selectedDate.value = null
      selectedTime.value = null
      availableDates.value = []
      availableSlots.value = []
      // Clear promo as it might be specific to duration type
      clearPromoCode()
    }
  }

  // Set accompaniment (for group sessions)
  function setWithAccompaniment(value) {
    withAccompaniment.value = value
  }

  // Set association session category (for step 2 sub-navigation)
  function setAssociationSessionCategory(category) {
    associationSessionCategory.value = category
    if (category === 'privatization') {
      // Default to half_day for privatization
      setDurationType('half_day')
      setWithAccompaniment(true)
      // Clear person selection for privatization
      selectedPersonId.value = null
      newPerson.value = { firstName: '', lastName: '' }
    } else if (category === 'individual') {
      // Reset to regular for individual sessions
      setDurationType('regular')
    }
  }

  // Reset association session category (back to category selection)
  function resetAssociationSessionCategory() {
    associationSessionCategory.value = null
    setDurationType('regular')
  }

  // Reset date/time selection (step 3)
  function resetDateTimeSelection() {
    selectedDate.value = null
    selectedTime.value = null
    availableDates.value = []
    availableSlots.value = []
  }

  // Reset contact info (step 4) - for when changing client/identity
  function resetContactInfo() {
    clientInfo.value = {
      email: clientInfo.value.email, // Keep email as it's used for lookup
      phone: '',
      firstName: '',
      lastName: '',
      clientType: 'personal',
      companyName: '',
      siret: ''
    }
    gdprConsent.value = false
    cgrConsent.value = false
    existingClientInfo.value = null
  }

  // Reset all steps following person selection (steps 3, 4, 5)
  function resetFollowingSteps() {
    resetDateTimeSelection()
    resetContactInfo()
    bookingResult.value = null
  }

  return {
    // State
    currentStep,
    totalSteps,
    isNewClient,
    existingPersons,
    selectedPersonId,
    newPerson,
    selectedDate,
    selectedTime,
    durationType,
    withAccompaniment,
    associationSessionCategory,
    groupTypes,
    availableDates,
    availableSlots,
    currentMonth,
    currentYear,
    clientInfo,
    gdprConsent,
    cgrConsent,
    captchaToken,
    existingClientInfo,
    bookingResult,
    scheduleInfo,
    durationLabels,
    prices,
    pricesByClientType,
    bookingDelays,
    currentPrice,
    originalPrice,
    currentClientType,
    isAdminUser,
    maxAdvanceDays,
    pricesForCurrentClient,
    emailConfirmationRequired,
    isGroupSession,
    availableSessionTypes,
    loading,
    error,

    // Prepaid state
    prepaidBalance,
    usePrepaid,
    prepaidLoading,
    hasPrepaidCredits,
    canUsePrepaid,
    willUsePrepaid,

    // Promo state
    hasManualPromoCodes,
    promoCodeInput,
    appliedPromo,
    promoPricing,
    promoError,
    promoLoading,
    hasPromoApplied,
    hasFreeSessionPromo,

    // Getters
    canGoNext,
    personInfo,
    bookingData,
    durationInfo,

    // Actions
    fetchScheduleInfo,
    checkEmail,
    fetchPersonsByEmail,
    fetchAvailableDates,
    fetchAvailableSlots,
    createBooking,
    confirmBooking,
    cancelBooking,
    getBookingByToken,
    nextStep,
    prevStep,
    goToStep,
    saveToStorage,
    restoreFromStorage,
    clearStorage,
    resetWizard,
    setDurationType,
    setWithAccompaniment,
    setAssociationSessionCategory,
    resetAssociationSessionCategory,
    setCaptchaToken,
    resetDateTimeSelection,
    resetContactInfo,
    resetFollowingSteps,

    // Prepaid actions
    checkPrepaidBalance,
    setUsePrepaid,
    clearPrepaidState,

    // Promo actions
    checkHasManualPromoCodes,
    validatePromoCode,
    checkAutomaticPromo,
    clearPromoCode
  }
})
