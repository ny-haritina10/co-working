import { createRouter, createWebHistory } from 'vue-router'
import AdminLogin from '../components/auth/AdminLogin.vue'
import Dashboard from '../components/panel/Dashboard.vue'
import ImportEspaces from '../components/import/ImportEspaces.vue'
import ImportReservations from '../components/import/ImportReservations.vue'
import ImportOptions from '../components/import/ImportOptions.vue'
import ImportPaiements from '../components/import/ImportPaiements.vue'

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: AdminLogin,
    meta: { requiresAuth: false } // Public route
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: Dashboard,
    meta: { requiresAuth: true } 
  },
  {
    path: '/import-espaces',
    name: 'ImportEspaces',
    component: ImportEspaces,
    meta: { requiresAuth: true } 
  },
  {
    path: '/import-reservations',
    name: 'ImportReservations',
    component: ImportReservations,
    meta: { requiresAuth: true } 
  },
  {
    path: '/import-options',
    name: 'ImportOptions',
    component: ImportOptions,
    meta: { requiresAuth: true } 
  },
  {
    path: '/import-paiements',
    name: 'ImportPaiements',
    component: ImportPaiements,
    meta: { requiresAuth: true } 
  },
  {
    path: '/',
    redirect: '/login'
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: { template: '<div>404 - Page Not Found</div>' }
  }
]

// Create router instance
const router = createRouter({
  history: createWebHistory(),
  routes
})

// Navigation guard
router.beforeEach((to, from, next) => {
  const isAuthenticated = !!localStorage.getItem('adminToken') 
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth)

  if (requiresAuth && !isAuthenticated) {
    next({ name: 'Login' })
  }

  else if (isAuthenticated && to.name === 'Login') {
    next({ name: 'Dashboard' })
  }

  else {
    next()
  }
})

export default router