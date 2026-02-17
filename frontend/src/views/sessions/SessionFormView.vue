<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useSessionsStore } from '@/stores/sessions'
import { usePersonsStore } from '@/stores/persons'
import { useProposalsStore } from '@/stores/proposals'
import { promoCodesApi, prepaidPacksApi, usersApi } from '@/services/api'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import AlertMessage from '@/components/ui/AlertMessage.vue'

const route = useRoute()
const router = useRouter()
const sessionsStore = useSessionsStore()
const personsStore = usePersonsStore()
const proposalsStore = useProposalsStore()

const isEdit = computed(() => !!route.params.id && route.name === 'session-edit')
const loading = ref(true)
const saving = ref(false)
const error = ref('')
const proposalSearch = ref('')
const showProposalModal = ref(false)
const newProposal = ref({ title: '', type: 'tactile', description: '' })
const proposalCreating = ref(false)
const proposalError = ref('')
const personSearch = ref('')

// User/Client selection (for associations)
const users = ref([])
const userSearch = ref('')
const showUserDropdown = ref(false)
const selectedUser = ref(null)
const loadingUsers = ref(false)
let userSearchTimeout = null

// Promo codes
const availablePromoCodes = ref([])
const promoCodeSearch = ref('')
const showPromoCodeDropdown = ref(false)
const selectedPromoCode = ref(null)
const sessionUserId = ref(null)
const originalPrice = ref(null) // Prix avant remise

// Quick promo creation
const showQuickPromo = ref(false)
const quickPromoType = ref('percentage') // 'percentage' or 'fixed_amount'
const quickPromoValue = ref(null)
const quickPromoCreating = ref(false)
const quickPromoError = ref('')

// Prepaid packs
const availablePrepaidPacks = ref([])
const selectedPrepaidPack = ref(null)
const prepaidPackLabels = ref({})

// Get current date/time in local timezone for datetime-local input
function getLocalDateTime() {
  const now = new Date()
  const offset = now.getTimezoneOffset()
  const local = new Date(now.getTime() - offset * 60 * 1000)
  return local.toISOString().slice(0, 16)
}

const form = ref({
  user_id: '',
  person_id: route.params.personId || '',
  session_date: getLocalDateTime(),
  duration_minutes: 45,
  duration_type: 'regular',
  with_accompaniment: true,
  behavior_start: '',
  proposal_origin: '',
  attitude_start: '',
  position: '',
  communication: [],
  session_end: '',
  behavior_end: '',
  wants_to_return: null,
  professional_notes: '',
  person_expression: '',
  next_session_proposals: '',
  proposals: [],
  price: null,
  promo_code_id: null,
  prepaid_pack_id: null,
  is_invoiced: false,
  is_paid: false,
})

// Session types
const allDurationTypes = [
  { value: 'discovery', label: 'Séance découverte', duration: 75 },
  { value: 'regular', label: 'Séance classique', duration: 45 },
  { value: 'half_day', label: 'Privatisation demi-journée', duration: 240, isGroup: true },
  { value: 'full_day', label: 'Privatisation journée', duration: 480, isGroup: true }
]

const isSelectedUserAssociation = computed(() => {
  return selectedUser.value?.client_type === 'association'
})

// For associations: show all types including group sessions
// For individuals: only show discovery and regular
const availableDurationTypes = computed(() => {
  if (isSelectedUserAssociation.value) {
    return allDurationTypes
  }
  return allDurationTypes.filter(t => !t.isGroup)
})

const isGroupSession = computed(() => {
  return form.value.duration_type === 'half_day' || form.value.duration_type === 'full_day'
})

// Update duration_minutes when duration_type changes
watch(() => form.value.duration_type, (newType) => {
  const type = allDurationTypes.find(t => t.value === newType)
  if (type) {
    form.value.duration_minutes = type.duration
  }
  // Clear person_id for group sessions
  if (newType === 'half_day' || newType === 'full_day') {
    form.value.person_id = ''
    personSearch.value = ''
  }
})

const loyaltyWarning = ref(null)

const showPersonDropdown = ref(false)

// User search functions
async function searchUsers() {
  if (!userSearch.value.trim()) {
    users.value = []
    return
  }
  loadingUsers.value = true
  try {
    const response = await usersApi.getAll({
      search: userSearch.value,
      limit: 10,
      is_active: true
    })
    users.value = response.data.data.users
  } catch (e) {
    console.error('Error searching users:', e)
  } finally {
    loadingUsers.value = false
  }
}

watch(userSearch, () => {
  if (userSearchTimeout) clearTimeout(userSearchTimeout)
  if (!userSearch.value.trim()) {
    users.value = []
    return
  }
  userSearchTimeout = setTimeout(searchUsers, 300)
})

function selectUser(user) {
  selectedUser.value = user
  form.value.user_id = user.id
  sessionUserId.value = user.id
  userSearch.value = ''
  users.value = []
  showUserDropdown.value = false

  // Reset session type when user changes
  if (user.client_type !== 'association') {
    // Reset to regular for non-associations
    if (isGroupSession.value) {
      form.value.duration_type = 'regular'
    }
  }

  // Load prepaid packs for this user
  fetchAvailablePrepaidPacks()
}

function clearUser() {
  selectedUser.value = null
  form.value.user_id = ''
  sessionUserId.value = null
  form.value.person_id = ''
  // Reset to regular session type
  form.value.duration_type = 'regular'
  availablePrepaidPacks.value = []
  selectedPrepaidPack.value = null
}

function hideUserDropdown() {
  setTimeout(() => {
    showUserDropdown.value = false
  }, 200)
}

const filteredPersons = computed(() => {
  if (!personSearch.value) return personsStore.persons
  const search = personSearch.value.toLowerCase()
  return personsStore.persons.filter(p =>
    p.first_name.toLowerCase().includes(search) ||
    p.last_name.toLowerCase().includes(search)
  )
})

const selectedPersonName = computed(() => {
  if (!form.value.person_id) return ''
  const person = personsStore.persons.find(p => p.id === form.value.person_id)
  return person ? `${person.first_name} ${person.last_name}` : ''
})

function selectPerson(person) {
  form.value.person_id = person.id
  personSearch.value = ''
  showPersonDropdown.value = false
}

function clearPerson() {
  form.value.person_id = ''
  personSearch.value = ''
}

function hidePersonDropdown() {
  setTimeout(() => {
    showPersonDropdown.value = false
  }, 200)
}

const labels = {
  behavior_start: [
    { value: 'calm', label: 'Calme' },
    { value: 'agitated', label: 'Agité' },
    { value: 'defensive', label: 'Défensif' },
    { value: 'anxious', label: 'Inquiet' },
    { value: 'passive', label: 'Passif (apathique)' }
  ],
  behavior_end: [
    { value: 'calm', label: 'Calme' },
    { value: 'agitated', label: 'Agité' },
    { value: 'tired', label: 'Fatigué' },
    { value: 'defensive', label: 'Défensif' },
    { value: 'anxious', label: 'Inquiet' },
    { value: 'passive', label: 'Passif (apathique)' }
  ],
  proposal_origin: [
    { value: 'person', label: 'La personne elle-même' },
    { value: 'relative', label: 'Un proche' }
  ],
  attitude_start: [
    { value: 'accepts', label: 'Accepte la séance' },
    { value: 'indifferent', label: 'Indifférente' },
    { value: 'refuses', label: 'Refuse' }
  ],
  position: [
    { value: 'standing', label: 'Debout' },
    { value: 'lying', label: 'Allongée' },
    { value: 'sitting', label: 'Assise' },
    { value: 'moving', label: 'Se déplace' }
  ],
  communication: [
    { value: 'body', label: 'Corporelle' },
    { value: 'verbal', label: 'Verbale' },
    { value: 'vocal', label: 'Vocale' }
  ],
  session_end: [
    { value: 'accepts', label: 'Accepte' },
    { value: 'refuses', label: 'Refuse' },
    { value: 'interrupts', label: 'Interrompt la séance' }
  ],
  appreciation: [
    { value: 'negative', label: 'Apprécié négativement' },
    { value: 'neutral', label: 'Neutralité' },
    { value: 'positive', label: 'Apprécié positivement' }
  ]
}

onMounted(async () => {
  try {
    await Promise.all([
      personsStore.fetchPersons({ limit: 100 }),
      proposalsStore.fetchTypes(),
      proposalsStore.fetchProposals({ limit: 500 })
    ])

    if (isEdit.value) {
      const session = await sessionsStore.fetchSession(route.params.id)
      sessionUserId.value = session.user_id || null

      // Load the user info for display
      if (session.user_id) {
        selectedUser.value = {
          id: session.user_id,
          first_name: session.user_first_name || session.client_first_name || '',
          last_name: session.user_last_name || session.client_last_name || '',
          email: session.user_email || session.client_email || '',
          company_name: session.company_name || null,
          client_type: session.client_type || 'personal'
        }
      }

      form.value = {
        user_id: session.user_id || '',
        person_id: session.person_id || '',
        session_date: session.session_date?.replace(' ', 'T').slice(0, 16) || '',
        duration_minutes: session.duration_minutes || 45,
        duration_type: session.duration_type || 'regular',
        with_accompaniment: session.with_accompaniment !== false,
        behavior_start: session.behavior_start || '',
        proposal_origin: session.proposal_origin || '',
        attitude_start: session.attitude_start || '',
        position: session.position || '',
        communication: session.communication || [],
        session_end: session.session_end || '',
        behavior_end: session.behavior_end || '',
        wants_to_return: session.wants_to_return,
        professional_notes: session.professional_notes || '',
        person_expression: session.person_expression || '',
        next_session_proposals: session.next_session_proposals || '',
        proposals: (session.proposals || []).map(p => ({
          sensory_proposal_id: p.id,
          title: p.title,
          type: p.type,
          appreciation: p.appreciation || ''
        })),
        price: session.price ?? null,
        promo_code_id: session.promo_code_id || null,
        prepaid_pack_id: session.prepaid_pack_id || null,
        is_invoiced: session.is_invoiced || false,
        is_paid: session.is_paid || false
      }

      // Charger le code promo actuel si présent
      if (session.promo_code_id && session.promo_code) {
        selectedPromoCode.value = session.promo_code
        // Stocker le prix original (avant remise) si disponible
        if (session.original_price !== null && session.original_price !== undefined) {
          originalPrice.value = Number(session.original_price)
        }
      }

      // Charger le pack prépayé actuel si présent
      if (session.prepaid_pack_id && session.prepaid_pack) {
        selectedPrepaidPack.value = session.prepaid_pack
      }

      // Charger les packs prépayés disponibles pour cet utilisateur
      if (sessionUserId.value) {
        await fetchAvailablePrepaidPacks()
      }
    }

    // Charger les codes promo disponibles
    await fetchAvailablePromoCodes()
  } catch (e) {
    console.error('Error loading form data:', e)
  } finally {
    loading.value = false
  }
})

async function fetchAvailablePromoCodes() {
  try {
    const params = {}
    if (sessionUserId.value) {
      params.user_id = sessionUserId.value
    }
    // En mode édition, exclure la session actuelle du comptage des utilisations
    // pour que le code promo déjà appliqué reste visible
    if (isEdit.value && route.params.id) {
      params.exclude_session_id = route.params.id
    }
    const response = await promoCodesApi.getAvailable(params)
    availablePromoCodes.value = response.data.data.promo_codes || []
  } catch (e) {
    console.error('Error fetching promo codes:', e)
  }
}

async function fetchAvailablePrepaidPacks() {
  if (!sessionUserId.value) return

  try {
    const response = await prepaidPacksApi.getByUser(sessionUserId.value)
    const data = response.data.data || {}
    prepaidPackLabels.value = data.labels || {}

    // Filtrer pour n'avoir que les packs avec des crédits restants
    const allPacks = data.packs || []
    availablePrepaidPacks.value = allPacks.filter(pack => {
      const remaining = pack.sessions_total - pack.sessions_used
      return pack.is_active && remaining > 0 && !pack.is_expired
    })
  } catch (e) {
    console.error('Error fetching prepaid packs:', e)
  }
}

function selectPrepaidPack(pack) {
  selectedPrepaidPack.value = pack
  form.value.prepaid_pack_id = pack.id
  form.value.price = 0 // Séance prépayée = 0€

  // Désélectionner le code promo si un pack est sélectionné
  if (selectedPromoCode.value) {
    clearPromoCode()
  }
}

function clearPrepaidPack() {
  selectedPrepaidPack.value = null
  form.value.prepaid_pack_id = null
  // Le prix sera remis à la valeur normale par l'utilisateur
}

const showProposalDropdown = ref(false)

const filteredProposals = computed(() => {
  // Exclure les propositions déjà ajoutées
  const addedIds = new Set(form.value.proposals.map(p => p.sensory_proposal_id))
  let available = proposalsStore.proposals.filter(p => !addedIds.has(p.id))

  if (!proposalSearch.value) return available

  const search = proposalSearch.value.toLowerCase()
  return available.filter(p =>
    p.title.toLowerCase().includes(search) ||
    (p.description && p.description.toLowerCase().includes(search)) ||
    proposalsStore.getTypeLabel(p.type).toLowerCase().includes(search)
  )
})

function hideProposalDropdown() {
  setTimeout(() => {
    showProposalDropdown.value = false
  }, 200)
}

function addProposal(proposal) {
  if (!form.value.proposals.find(p => p.sensory_proposal_id === proposal.id)) {
    form.value.proposals.push({
      sensory_proposal_id: proposal.id,
      title: proposal.title,
      type: proposal.type,
      appreciation: ''
    })
  }
  proposalSearch.value = ''
  showProposalDropdown.value = false
}

function removeProposal(index) {
  form.value.proposals.splice(index, 1)
}

async function createNewProposal() {
  proposalError.value = ''
  proposalCreating.value = true

  try {
    const proposal = await proposalsStore.createProposal(newProposal.value)
    addProposal(proposal)
    showProposalModal.value = false
    newProposal.value = { title: '', type: 'tactile', description: '' }
  } catch (e) {
    console.error('Error creating proposal:', e)
    proposalError.value = e.response?.data?.message || 'Erreur lors de la création de la proposition'
  } finally {
    proposalCreating.value = false
  }
}

function toggleCommunication(value) {
  const index = form.value.communication.indexOf(value)
  if (index === -1) {
    form.value.communication.push(value)
  } else {
    form.value.communication.splice(index, 1)
  }
}

// Promo code functions
const filteredPromoCodes = computed(() => {
  if (!promoCodeSearch.value) return availablePromoCodes.value
  const search = promoCodeSearch.value.toLowerCase()
  return availablePromoCodes.value.filter(p =>
    p.name.toLowerCase().includes(search) ||
    (p.code && p.code.toLowerCase().includes(search)) ||
    (p.discount_label && p.discount_label.toLowerCase().includes(search))
  )
})

function calculateDiscountedPrice(promo, price) {
  if (!promo || price === null || price === undefined) return price

  let discountAmount = 0
  const discountValue = Number(promo.discount_value)

  switch (promo.discount_type) {
    case 'percentage':
      discountAmount = price * (discountValue / 100)
      break
    case 'fixed_amount':
      discountAmount = discountValue
      break
    case 'free_session':
      discountAmount = price
      break
  }

  return Math.max(0, price - discountAmount)
}

function selectPromoCode(promo) {
  // Désélectionner le pack prépayé si un code promo est sélectionné
  if (selectedPrepaidPack.value) {
    clearPrepaidPack()
  }

  // Sauvegarder le prix original si pas encore fait (avant toute remise)
  if (originalPrice.value === null && form.value.price !== null) {
    originalPrice.value = form.value.price
  }

  selectedPromoCode.value = promo
  form.value.promo_code_id = promo.id
  promoCodeSearch.value = ''
  showPromoCodeDropdown.value = false

  // Appliquer la remise depuis le prix original (pour gérer le changement de promo)
  if (originalPrice.value !== null) {
    form.value.price = Math.round(calculateDiscountedPrice(promo, originalPrice.value) * 100) / 100
  }
}

function clearPromoCode() {
  selectedPromoCode.value = null
  form.value.promo_code_id = null
  promoCodeSearch.value = ''

  // Restaurer le prix original
  if (originalPrice.value !== null) {
    form.value.price = originalPrice.value
    originalPrice.value = null
  }
}

function hidePromoCodeDropdown() {
  setTimeout(() => {
    showPromoCodeDropdown.value = false
  }, 200)
}

function formatPrice(price) {
  if (price === null || price === undefined) return '0'
  return Number(price).toFixed(2).replace('.', ',')
}

async function createQuickPromo() {
  if (!quickPromoValue.value || quickPromoValue.value <= 0) {
    quickPromoError.value = 'Veuillez saisir une valeur valide'
    return
  }

  if (quickPromoType.value === 'percentage' && quickPromoValue.value > 100) {
    quickPromoError.value = 'Le pourcentage ne peut pas dépasser 100%'
    return
  }

  quickPromoError.value = ''
  quickPromoCreating.value = true

  try {
    const today = new Date().toISOString().split('T')[0]
    const discountLabel = quickPromoType.value === 'percentage'
      ? `-${quickPromoValue.value}%`
      : `-${quickPromoValue.value}€`

    const promoData = {
      name: `Remise ${discountLabel} - Séance`,
      discount_type: quickPromoType.value,
      discount_value: quickPromoValue.value,
      application_mode: 'automatic',
      max_uses_total: 1,
      target_user_id: sessionUserId.value || null,
      valid_from: today,
      is_active: true
    }

    const response = await promoCodesApi.create(promoData)
    const newPromo = response.data.data

    // Ajouter le discount_label
    newPromo.discount_label = quickPromoType.value === 'percentage'
      ? `-${Number(newPromo.discount_value).toFixed(0)}%`
      : `-${Number(newPromo.discount_value).toFixed(2).replace('.', ',')} €`

    // Sélectionner automatiquement le nouveau code
    selectPromoCode(newPromo)

    // Réinitialiser le formulaire
    showQuickPromo.value = false
    quickPromoValue.value = null
    quickPromoType.value = 'percentage'

    // Rafraîchir la liste des codes disponibles
    await fetchAvailablePromoCodes()
  } catch (e) {
    console.error('Error creating quick promo:', e)
    quickPromoError.value = e.response?.data?.message || 'Erreur lors de la création'
  } finally {
    quickPromoCreating.value = false
  }
}

function cancelQuickPromo() {
  showQuickPromo.value = false
  quickPromoValue.value = null
  quickPromoType.value = 'percentage'
  quickPromoError.value = ''
}

async function handleSubmit() {
  error.value = ''

  // Validate required fields
  if (!form.value.user_id) {
    error.value = 'Veuillez sélectionner un client'
    return
  }

  // person_id is required for individual sessions only
  if (!isGroupSession.value && !form.value.person_id) {
    error.value = 'Veuillez sélectionner une personne (bénéficiaire)'
    return
  }

  saving.value = true

  // Convert datetime-local format (2026-01-20T14:30) to API format (2026-01-20 14:30:00)
  const sessionDate = form.value.session_date.replace('T', ' ') + ':00'

  const data = {
    ...form.value,
    session_date: sessionDate,
    // For group sessions, clear clinical fields and person_id
    ...(isGroupSession.value ? {
      person_id: null,
      behavior_start: null,
      proposal_origin: null,
      attitude_start: null,
      position: null,
      communication: [],
      session_end: null,
      behavior_end: null,
      wants_to_return: null,
      professional_notes: null,
      person_expression: null,
      next_session_proposals: null,
      proposals: []
    } : {
      proposals: form.value.proposals.map((p, i) => ({
        sensory_proposal_id: p.sensory_proposal_id,
        appreciation: p.appreciation || null,
        order: i
      }))
    })
  }

  // Ajouter les infos de prix original si un code promo est appliqué
  if (selectedPromoCode.value && originalPrice.value !== null) {
    data.original_price = originalPrice.value
    data.discount_amount = originalPrice.value - (form.value.price || 0)
  }

  try {
    if (isEdit.value) {
      await sessionsStore.updateSession(route.params.id, data)
      router.push(`/app/sessions/${route.params.id}`)
    } else {
      const session = await sessionsStore.createSession(data)
      // Vérifier si une alerte fidélité est retournée
      if (session.loyalty_warning) {
        loyaltyWarning.value = session.loyalty_warning
      }
      router.push(`/app/sessions/${session.id}`)
    }
  } catch (e) {
    error.value = e.response?.data?.message || 'Une erreur est survenue'
    if (e.response?.data?.errors) {
      error.value = Object.values(e.response.data.errors).join(', ')
    }
  } finally {
    saving.value = false
  }
}

function cancel() {
  if (isEdit.value) {
    router.push(`/app/sessions/${route.params.id}`)
  } else {
    router.push('/app/sessions')
  }
}
</script>

<template>
  <div class="max-w-4xl mx-auto">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-white">
        {{ isEdit ? 'Modifier la séance' : 'Nouvelle séance' }}
      </h1>
    </div>

    <LoadingSpinner v-if="loading" size="lg" class="py-12" />

    <form v-else @submit.prevent="handleSubmit" class="space-y-6">
      <AlertMessage v-if="loyaltyWarning" type="warning" class="mb-4">
        <strong>Attention:</strong> {{ loyaltyWarning.user_name }} - {{ loyaltyWarning.message }}
      </AlertMessage>

      <AlertMessage v-if="error" type="error" dismissible @dismiss="error = ''">
        {{ error }}
      </AlertMessage>

      <!-- Informations générales -->
      <div class="bg-gray-800 rounded-xl border border-gray-700">
        <div class="px-6 py-4 border-b border-gray-700">
          <h2 class="font-semibold text-white">Informations générales</h2>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Client/User selector -->
          <div class="md:col-span-2">
            <label class="label">Client *</label>
            <div v-if="selectedUser" class="flex items-center justify-between p-3 bg-primary-900/30 border border-primary-700 rounded-lg">
              <div>
                <div class="font-medium text-gray-100">
                  {{ selectedUser.first_name }} {{ selectedUser.last_name }}
                  <span v-if="selectedUser.company_name" class="text-primary-300 ml-1">({{ selectedUser.company_name }})</span>
                </div>
                <div class="text-sm text-gray-400">{{ selectedUser.email }}</div>
                <span
                  v-if="selectedUser.client_type === 'association'"
                  class="inline-block mt-1 px-2 py-0.5 text-xs rounded bg-indigo-500/20 text-indigo-300"
                >
                  Association
                </span>
              </div>
              <button
                v-if="!isEdit"
                type="button"
                @click="clearUser"
                class="text-gray-500 hover:text-red-400 transition-colors"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            </div>
            <div v-else-if="!isEdit" class="relative">
              <input
                v-model="userSearch"
                type="text"
                class="input"
                placeholder="Rechercher un client par nom, email ou association..."
                @focus="showUserDropdown = true"
                @blur="hideUserDropdown"
              />
              <div v-if="loadingUsers" class="absolute right-3 top-2.5">
                <LoadingSpinner size="sm" />
              </div>
              <div
                v-if="showUserDropdown && users.length > 0"
                class="absolute z-20 w-full mt-1 bg-gray-700 border border-gray-600 rounded-lg shadow-lg max-h-60 overflow-auto"
              >
                <button
                  v-for="user in users"
                  :key="user.id"
                  type="button"
                  @mousedown.prevent="selectUser(user)"
                  class="w-full px-4 py-3 text-left hover:bg-gray-600 border-b border-gray-600 last:border-0 transition-colors"
                >
                  <div class="font-medium text-gray-100">
                    {{ user.first_name }} {{ user.last_name }}
                    <span v-if="user.company_name" class="text-primary-300 ml-1">({{ user.company_name }})</span>
                  </div>
                  <div class="text-sm text-gray-400">{{ user.email }}</div>
                </button>
              </div>
            </div>
            <div v-else class="px-3 py-2 bg-gray-700 rounded-lg text-gray-300">
              {{ selectedUser?.first_name }} {{ selectedUser?.last_name }}
              <span v-if="selectedUser?.company_name" class="text-primary-300 ml-1">({{ selectedUser?.company_name }})</span>
            </div>
          </div>

          <!-- Session type selector (only after user selected, shows group options for associations) -->
          <div v-if="selectedUser" class="md:col-span-2">
            <label class="label">Type de séance *</label>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
              <button
                v-for="dtype in availableDurationTypes"
                :key="dtype.value"
                type="button"
                @click="form.duration_type = dtype.value"
                :class="[
                  'p-3 border-2 rounded-lg text-left transition',
                  form.duration_type === dtype.value
                    ? 'border-primary-500 bg-primary-900/30'
                    : 'border-gray-600 hover:border-gray-500 bg-gray-700/50'
                ]"
              >
                <div class="font-medium text-gray-100 text-sm">{{ dtype.label }}</div>
                <div class="text-xs text-gray-400">{{ dtype.duration }} min</div>
                <span v-if="dtype.isGroup" class="inline-block mt-1 px-1.5 py-0.5 text-xs rounded bg-purple-500/20 text-purple-300">Privatisation</span>
              </button>
            </div>
          </div>

          <!-- Accompaniment toggle (for group sessions) -->
          <div v-if="selectedUser && isGroupSession" class="md:col-span-2">
            <label class="label">Accompagnement</label>
            <div class="flex gap-4 mt-2">
              <label class="flex items-center cursor-pointer">
                <input
                  type="radio"
                  :value="true"
                  v-model="form.with_accompaniment"
                  class="w-4 h-4 text-primary-600 border-gray-600 bg-gray-700 focus:ring-primary-500"
                />
                <span class="ml-2 text-sm text-gray-300">Avec accompagnement (présence de Céline)</span>
              </label>
              <label class="flex items-center cursor-pointer">
                <input
                  type="radio"
                  :value="false"
                  v-model="form.with_accompaniment"
                  class="w-4 h-4 text-primary-600 border-gray-600 bg-gray-700 focus:ring-primary-500"
                />
                <span class="ml-2 text-sm text-gray-300">Sans accompagnement (accès libre)</span>
              </label>
            </div>
          </div>

          <!-- Person selector (hidden for group sessions) -->
          <div v-if="selectedUser && !isGroupSession">
            <label for="person_id" class="label">Personne (bénéficiaire) *</label>
            <div v-if="!isEdit" class="relative">
              <input
                v-model="personSearch"
                type="text"
                class="input"
                placeholder="Rechercher et sélectionner une personne..."
                @focus="showPersonDropdown = true"
                @blur="hidePersonDropdown"
              />
              <div
                v-if="showPersonDropdown && filteredPersons.length > 0"
                class="absolute z-20 w-full mt-1 bg-gray-700 border border-gray-600 rounded-lg shadow-lg max-h-60 overflow-auto"
              >
                <button
                  v-for="person in filteredPersons"
                  :key="person.id"
                  type="button"
                  @mousedown.prevent="selectPerson(person)"
                  class="w-full px-4 py-2 text-left text-gray-200 hover:bg-gray-600"
                  :class="{ 'bg-primary-900/50': form.person_id === person.id }"
                >
                  {{ person.first_name }} {{ person.last_name }}
                </button>
              </div>
              <input type="hidden" :value="form.person_id" required />
              <div v-if="selectedPersonName && form.person_id" class="mt-2 px-3 py-2 bg-primary-900/50 text-primary-300 rounded-lg flex items-center justify-between">
                <span>{{ selectedPersonName }}</span>
                <button type="button" @click="clearPerson" class="text-primary-400 hover:text-primary-200">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
            </div>
            <div v-else class="px-3 py-2 bg-gray-700 rounded-lg text-gray-300">
              {{ selectedPersonName || 'Personne non trouvée' }}
            </div>
          </div>

          <div v-if="selectedUser">
            <label for="session_date" class="label">Date et heure *</label>
            <input id="session_date" v-model="form.session_date" type="datetime-local" class="input" required />
          </div>

          <div v-if="selectedUser">
            <label for="duration_minutes" class="label">Durée (minutes)</label>
            <input id="duration_minutes" v-model.number="form.duration_minutes" type="number" min="1" class="input" required @wheel.prevent />
          </div>
        </div>
      </div>

      <!-- Début de séance (hidden for group sessions) -->
      <div v-if="!isGroupSession" class="bg-gray-800 rounded-xl border border-gray-700">
        <div class="px-6 py-4 border-b border-gray-700">
          <h2 class="font-semibold text-white">Début de séance</h2>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
          <div>
            <label class="label">Comportement</label>
            <select v-model="form.behavior_start" class="input">
              <option value="">Non renseigné</option>
              <option v-for="opt in labels.behavior_start" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
            </select>
          </div>

          <div>
            <label class="label">Proposition vient de</label>
            <select v-model="form.proposal_origin" class="input">
              <option value="">Non renseigné</option>
              <option v-for="opt in labels.proposal_origin" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
            </select>
          </div>

          <div>
            <label class="label">Attitude</label>
            <select v-model="form.attitude_start" class="input">
              <option value="">Non renseigné</option>
              <option v-for="opt in labels.attitude_start" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Pendant la séance (hidden for group sessions) -->
      <div v-if="!isGroupSession" class="bg-gray-800 rounded-xl border border-gray-700">
        <div class="px-6 py-4 border-b border-gray-700">
          <h2 class="font-semibold text-white">Pendant la séance</h2>
        </div>
        <div class="p-6 space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="label">Position</label>
              <select v-model="form.position" class="input">
                <option value="">Non renseigné</option>
                <option v-for="opt in labels.position" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
              </select>
            </div>

            <div>
              <label class="label">Communication</label>
              <div class="flex flex-wrap gap-2 mt-2">
                <button
                  v-for="opt in labels.communication"
                  :key="opt.value"
                  type="button"
                  @click="toggleCommunication(opt.value)"
                  :class="[
                    'px-3 py-1.5 rounded-lg text-sm font-medium transition-colors',
                    form.communication.includes(opt.value)
                      ? 'bg-primary-600 text-white'
                      : 'bg-gray-700 text-gray-300 hover:bg-gray-600'
                  ]"
                >
                  {{ opt.label }}
                </button>
              </div>
            </div>
          </div>

          <!-- Propositions sensorielles -->
          <div>
            <label class="label">Propositions sensorielles</label>
            <div class="relative">
              <input
                v-model="proposalSearch"
                type="text"
                class="input"
                placeholder="Rechercher ou sélectionner une proposition..."
                @focus="showProposalDropdown = true"
                @blur="hideProposalDropdown"
              />
              <div
                v-if="showProposalDropdown && filteredProposals.length > 0"
                class="absolute z-10 w-full mt-1 bg-gray-700 border border-gray-600 rounded-lg shadow-lg max-h-60 overflow-auto"
              >
                <button
                  v-for="proposal in filteredProposals"
                  :key="proposal.id"
                  type="button"
                  @mousedown.prevent="addProposal(proposal)"
                  class="w-full px-4 py-2 text-left text-gray-200 hover:bg-gray-600 flex items-center justify-between"
                >
                  <span>{{ proposal.title }}</span>
                  <span :class="proposalsStore.getTypeBadgeClass(proposal.type)">
                    {{ proposalsStore.getTypeLabel(proposal.type) }}
                  </span>
                </button>
              </div>
              <div
                v-if="showProposalDropdown && filteredProposals.length === 0 && proposalSearch"
                class="absolute z-10 w-full mt-1 bg-gray-700 border border-gray-600 rounded-lg shadow-lg"
              >
                <div class="px-4 py-3 text-gray-400 text-sm">
                  Aucune proposition trouvée pour "{{ proposalSearch }}"
                </div>
              </div>
            </div>
            <button type="button" @click="showProposalModal = true" class="mt-2 text-sm text-primary-400 hover:text-primary-300">
              + Créer une nouvelle proposition
            </button>

            <div v-if="form.proposals.length" class="mt-4 space-y-2">
              <div
                v-for="(proposal, index) in form.proposals"
                :key="index"
                class="flex items-center gap-4 p-3 bg-gray-700/50 rounded-lg"
              >
                <div class="flex-1">
                  <div class="font-medium text-gray-200">{{ proposal.title }}</div>
                  <span :class="proposalsStore.getTypeBadgeClass(proposal.type)">
                    {{ proposalsStore.getTypeLabel(proposal.type) }}
                  </span>
                </div>
                <select v-model="proposal.appreciation" class="input w-auto">
                  <option value="">Non évalué</option>
                  <option v-for="opt in labels.appreciation" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                </select>
                <button type="button" @click="removeProposal(index)" class="text-red-500 hover:text-red-700">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Fin de séance (hidden for group sessions) -->
      <div v-if="!isGroupSession" class="bg-gray-800 rounded-xl border border-gray-700">
        <div class="px-6 py-4 border-b border-gray-700">
          <h2 class="font-semibold text-white">Fin de séance</h2>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
          <div>
            <label class="label">Fin de séance</label>
            <select v-model="form.session_end" class="input">
              <option value="">Non renseigné</option>
              <option v-for="opt in labels.session_end" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
            </select>
          </div>

          <div>
            <label class="label">Comportement</label>
            <select v-model="form.behavior_end" class="input">
              <option value="">Non renseigné</option>
              <option v-for="opt in labels.behavior_end" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
            </select>
          </div>

          <div>
            <label class="label">Souhaite revenir</label>
            <select v-model="form.wants_to_return" class="input">
              <option :value="null">Non renseigné</option>
              <option :value="true">Oui</option>
              <option :value="false">Non</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Facturation -->
      <div class="bg-gray-800 rounded-xl border border-gray-700">
        <div class="px-6 py-4 border-b border-gray-700">
          <h2 class="font-semibold text-white">Facturation</h2>
        </div>
        <div class="p-6 space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="price" class="label">Prix (€)</label>
              <div class="flex items-center gap-3">
                <input
                  id="price"
                  v-model.number="form.price"
                  type="number"
                  min="0"
                  step="1"
                  class="input w-32"
                  placeholder="45"
                  :disabled="selectedPromoCode !== null"
                  @wheel.prevent
                />
                <!-- Affichage du prix original barré quand une promo est appliquée -->
                <div v-if="selectedPromoCode && originalPrice !== null" class="text-sm">
                  <span class="text-gray-500 line-through">{{ formatPrice(originalPrice) }} €</span>
                  <span class="text-green-400 ml-2">→ {{ formatPrice(form.price) }} €</span>
                </div>
              </div>
            </div>

            <!-- Pack prépayé -->
            <div v-if="sessionUserId">
              <label class="label">Séance prépayée</label>
              <div v-if="selectedPrepaidPack" class="px-3 py-2 bg-teal-900/30 border border-teal-700 text-teal-300 rounded-lg flex items-center justify-between">
                <div>
                  <span class="font-medium">{{ selectedPrepaidPack.label || prepaidPackLabels?.pack_type?.[selectedPrepaidPack.pack_type] || selectedPrepaidPack.pack_type }}</span>
                  <span v-if="selectedPrepaidPack.sessions_remaining !== undefined" class="ml-2 text-teal-200 text-sm">
                    ({{ selectedPrepaidPack.sessions_remaining }} crédit(s) restant(s))
                  </span>
                </div>
                <button type="button" @click="clearPrepaidPack" class="text-teal-400 hover:text-teal-200">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
              <div v-else-if="availablePrepaidPacks.length > 0" class="space-y-2">
                <div class="flex flex-wrap gap-2">
                  <button
                    v-for="pack in availablePrepaidPacks"
                    :key="pack.id"
                    type="button"
                    @click="selectPrepaidPack(pack)"
                    class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors bg-teal-900/30 border border-teal-700/50 text-teal-300 hover:bg-teal-800/50"
                  >
                    {{ prepaidPackLabels?.pack_type?.[pack.pack_type] || pack.pack_type }}
                    <span class="text-teal-400/70 ml-1">({{ pack.sessions_total - pack.sessions_used }} restant(s))</span>
                  </button>
                </div>
              </div>
              <div v-else class="text-sm text-gray-500">
                Aucune séance prépayée disponible
              </div>
            </div>

            <!-- Code promo (caché si pack prépayé sélectionné) -->
            <div v-if="!selectedPrepaidPack">
              <label class="label">Code promo</label>
              <div v-if="selectedPromoCode" class="px-3 py-2 bg-green-900/30 border border-green-700 text-green-300 rounded-lg flex items-center justify-between">
                <div>
                  <span class="font-medium">{{ selectedPromoCode.name }}</span>
                  <span v-if="selectedPromoCode.code" class="text-green-400 ml-2">({{ selectedPromoCode.code }})</span>
                  <span class="ml-2 text-green-200 font-semibold">{{ selectedPromoCode.discount_label }}</span>
                </div>
                <button type="button" @click="clearPromoCode" class="text-green-400 hover:text-green-200">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
              <div v-else-if="showQuickPromo" class="space-y-3">
                <!-- Formulaire rapide de création de promo -->
                <div class="p-3 bg-gray-700/50 rounded-lg border border-gray-600">
                  <div class="flex items-center gap-2 mb-3">
                    <select v-model="quickPromoType" class="input w-auto py-1.5 text-sm">
                      <option value="percentage">%</option>
                      <option value="fixed_amount">€</option>
                    </select>
                    <input
                      v-model.number="quickPromoValue"
                      type="number"
                      min="0"
                      :max="quickPromoType === 'percentage' ? 100 : undefined"
                      step="1"
                      class="input w-24 py-1.5 text-sm"
                      placeholder="Valeur"
                      @wheel.prevent
                      @keyup.enter="createQuickPromo"
                    />
                    <span class="text-gray-400 text-sm">de remise</span>
                  </div>
                  <div v-if="quickPromoError" class="text-red-400 text-xs mb-2">{{ quickPromoError }}</div>
                  <div class="flex gap-2">
                    <button
                      type="button"
                      @click="createQuickPromo"
                      :disabled="quickPromoCreating || !quickPromoValue"
                      class="btn-primary text-xs py-1.5 px-3"
                    >
                      <LoadingSpinner v-if="quickPromoCreating" size="sm" class="mr-1" />
                      Appliquer
                    </button>
                    <button
                      type="button"
                      @click="cancelQuickPromo"
                      :disabled="quickPromoCreating"
                      class="btn-secondary text-xs py-1.5 px-3"
                    >
                      Annuler
                    </button>
                  </div>
                </div>
              </div>
              <div v-else class="space-y-2">
                <div class="relative">
                  <input
                    v-model="promoCodeSearch"
                    type="text"
                    class="input"
                    placeholder="Rechercher un code promo..."
                    @focus="showPromoCodeDropdown = true"
                    @blur="hidePromoCodeDropdown"
                  />
                  <div
                    v-if="showPromoCodeDropdown && filteredPromoCodes.length > 0"
                    class="absolute z-20 w-full mt-1 bg-gray-700 border border-gray-600 rounded-lg shadow-lg max-h-60 overflow-auto"
                  >
                    <button
                      v-for="promo in filteredPromoCodes"
                      :key="promo.id"
                      type="button"
                      @mousedown.prevent="selectPromoCode(promo)"
                      class="w-full px-4 py-2 text-left text-gray-200 hover:bg-gray-600 flex items-center justify-between"
                    >
                      <div class="flex items-center gap-2">
                        <span class="font-medium">{{ promo.name }}</span>
                        <span v-if="promo.code" class="text-gray-400 text-sm">({{ promo.code }})</span>
                        <span v-if="promo.application_mode === 'automatic'" class="text-xs bg-indigo-600/50 text-indigo-200 px-1.5 py-0.5 rounded">Auto</span>
                      </div>
                      <span class="text-green-400 font-semibold">{{ promo.discount_label }}</span>
                    </button>
                  </div>
                  <div
                    v-if="showPromoCodeDropdown && filteredPromoCodes.length === 0 && promoCodeSearch"
                    class="absolute z-20 w-full mt-1 bg-gray-700 border border-gray-600 rounded-lg shadow-lg"
                  >
                    <div class="px-4 py-3 text-gray-400 text-sm">
                      Aucun code promo trouvé pour "{{ promoCodeSearch }}"
                    </div>
                  </div>
                  <div
                    v-if="showPromoCodeDropdown && availablePromoCodes.length === 0 && !promoCodeSearch"
                    class="absolute z-20 w-full mt-1 bg-gray-700 border border-gray-600 rounded-lg shadow-lg"
                  >
                    <div class="px-4 py-3 text-gray-400 text-sm">
                      Aucun code promo disponible
                    </div>
                  </div>
                </div>
                <button
                  type="button"
                  @click="showQuickPromo = true"
                  class="text-sm text-primary-400 hover:text-primary-300"
                >
                  + Créer une remise rapide
                </button>
              </div>
            </div>
          </div>
          <div class="flex flex-wrap gap-6">
            <label class="flex items-center cursor-pointer">
              <input
                type="checkbox"
                v-model="form.is_invoiced"
                class="w-4 h-4 text-primary-600 border-gray-600 bg-gray-700 rounded focus:ring-primary-500"
              />
              <span class="ml-2 text-sm text-gray-300">Facturée</span>
            </label>

            <label class="flex items-center cursor-pointer">
              <input
                type="checkbox"
                v-model="form.is_paid"
                class="w-4 h-4 text-primary-600 border-gray-600 bg-gray-700 rounded focus:ring-primary-500"
              />
              <span class="ml-2 text-sm text-gray-300">Payée</span>
            </label>
          </div>
        </div>
      </div>

      <!-- Notes privées (hidden for group sessions) -->
      <div v-if="!isGroupSession" class="bg-gray-800 rounded-xl border border-gray-700">
        <div class="px-6 py-4 border-b border-gray-700">
          <h2 class="font-semibold text-white">Notes privées</h2>
          <p class="text-sm text-gray-400">Ces notes sont chiffrées et confidentielles.</p>
        </div>
        <div class="p-6 space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="label">Impressions du professionnel</label>
              <textarea v-model="form.professional_notes" rows="4" class="input" placeholder="Vos observations et impressions..."></textarea>
            </div>

            <div>
              <label class="label">Expression de la personne</label>
              <textarea v-model="form.person_expression" rows="4" class="input" placeholder="Ce que la personne a exprimé..."></textarea>
            </div>
          </div>

          <div>
            <label class="label">Propositions pour une prochaine séance</label>
            <textarea v-model="form.next_session_proposals" rows="3" class="input" placeholder="Propositions sensorielles à envisager pour la prochaine séance..."></textarea>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex justify-end space-x-3">
        <button type="button" @click="cancel" class="btn-secondary">Annuler</button>
        <button type="submit" class="btn-primary" :disabled="saving">
          <LoadingSpinner v-if="saving" size="sm" class="mr-2" />
          {{ isEdit ? 'Enregistrer' : 'Créer la séance' }}
        </button>
      </div>
    </form>

    <!-- Modal création proposition -->
    <Teleport to="body">
      <div v-if="showProposalModal" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="fixed inset-0 bg-black/70" @click="showProposalModal = false" />
        <div class="flex min-h-full items-center justify-center p-4">
          <div class="relative bg-gray-800 rounded-xl shadow-xl max-w-md w-full p-6 border border-gray-700">
            <h3 class="text-lg font-semibold text-white mb-4">Nouvelle proposition sensorielle</h3>
            <AlertMessage v-if="proposalError" type="error" dismissible @dismiss="proposalError = ''" class="mb-4">
              {{ proposalError }}
            </AlertMessage>
            <div class="space-y-4">
              <div>
                <label class="label">Titre *</label>
                <input v-model="newProposal.title" type="text" class="input" required />
              </div>
              <div>
                <label class="label">Type *</label>
                <select v-model="newProposal.type" class="input">
                  <option v-for="(label, type) in proposalsStore.typeLabels" :key="type" :value="type">{{ label }}</option>
                </select>
              </div>
              <div>
                <label class="label">Description</label>
                <textarea v-model="newProposal.description" rows="3" class="input"></textarea>
              </div>
            </div>
            <div class="flex justify-end space-x-3 mt-6">
              <button type="button" @click="showProposalModal = false" class="btn-secondary" :disabled="proposalCreating">Annuler</button>
              <button type="button" @click="createNewProposal" class="btn-primary" :disabled="!newProposal.title || proposalCreating">
                <LoadingSpinner v-if="proposalCreating" size="sm" class="mr-2" />
                Créer
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>
