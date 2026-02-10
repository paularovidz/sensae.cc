import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  {
    path: '/login',
    name: 'login',
    component: () => import('@/views/LoginView.vue'),
    meta: { guest: true }
  },
  {
    path: '/auth/verify/:token',
    name: 'auth-verify',
    component: () => import('@/views/AuthVerifyView.vue'),
    meta: { guest: true }
  },
  {
    path: '/',
    component: () => import('@/components/layout/DashboardLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'dashboard',
        component: () => import('@/views/DashboardView.vue')
      },
      {
        path: 'expenses',
        name: 'expenses',
        component: () => import('@/views/ExpensesView.vue')
      },
      {
        path: 'expenses/new',
        name: 'expense-create',
        component: () => import('@/views/ExpenseFormView.vue')
      },
      {
        path: 'expenses/:id/edit',
        name: 'expense-edit',
        component: () => import('@/views/ExpenseFormView.vue')
      },
      {
        path: 'recurring',
        name: 'recurring',
        component: () => import('@/views/RecurringExpensesView.vue')
      },
      {
        path: 'recurring/new',
        name: 'recurring-create',
        component: () => import('@/views/RecurringExpenseFormView.vue')
      },
      {
        path: 'recurring/:id/edit',
        name: 'recurring-edit',
        component: () => import('@/views/RecurringExpenseFormView.vue')
      },
      {
        path: 'categories',
        name: 'categories',
        component: () => import('@/views/CategoriesView.vue')
      },
      {
        path: 'vendor-mappings',
        name: 'vendor-mappings',
        component: () => import('@/views/VendorMappingsView.vue')
      },
      {
        path: 'import',
        name: 'import',
        component: () => import('@/views/ImportView.vue')
      }
    ]
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/'
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Navigation guards
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/login')
  } else if (to.meta.guest && authStore.isAuthenticated) {
    next('/')
  } else {
    next()
  }
})

export default router
