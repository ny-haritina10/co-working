// src/router/index.js
import { createRouter, createWebHistory } from 'vue-router'
import AdminLogin from '../components/auth/AdminLogin.vue'
import Dashboard from '../components/panel/Dashboard.vue'
import ImportEspaces from '../components/import/ImportEspaces.vue'
import ImportReservations from '../components/import/ImportReservations.vue'
import ImportOptions from '../components/import/ImportOptions.vue'
import ImportPaiements from '../components/import/ImportPaiements.vue'
import NotFound from '../components/errors/NotFound.vue'
import PaiementsList from '../components/paiements/PaiementsList.vue'

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: AdminLogin,
    meta: { requiresAuth: false }
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: Dashboard,
    meta: { requiresAuth: true }
  },
  {
    path: '/paiements',
    name: 'PaiementsList',
    component: PaiementsList,
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
    component: NotFound 
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to, from, next) => {
  const isAuthenticated = !!localStorage.getItem('adminToken')
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth)

  if (requiresAuth && !isAuthenticated) {
    next({ name: 'Login' })
  } else if (isAuthenticated && to.name === 'Login') {
    next({ name: 'Dashboard' })
  } else {
    next()
  }
})

export default router