// src/router/index.js
import { createRouter, createWebHistory } from 'vue-router'
import AdminLogin from '../components/auth/AdminLogin.vue'
import Dashboard from '../components/panel/Dashboard.vue'

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: AdminLogin
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: Dashboard
  },
  {
    path: '/',
    redirect: '/login'
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router