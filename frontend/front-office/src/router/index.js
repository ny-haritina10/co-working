import { createRouter, createWebHistory } from 'vue-router'
import Login from '../components/auth/Login.vue'
import Home from '../components/home/Home.vue'
import EspaceSituation from '../components/espace/EspaceSituation.vue'

const routes = [
  {
    path: '/',
    name: 'Login',
    component: Login
  },
  {
    path: '/home',
    name: 'Home',
    component: Home
  },
  {
    path: '/espace',
    name: 'Espaces',
    component: EspaceSituation
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router