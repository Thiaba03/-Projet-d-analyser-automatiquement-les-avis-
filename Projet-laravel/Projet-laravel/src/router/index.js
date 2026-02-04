import { createRouter, createWebHistory } from 'vue-router'

// Imports des composants
import SiteVitrine from '@/views/site_vitrine.vue' 
import Dashboard from '@/views/Dashboard.vue'
import ReviewsList from '@/views/ReviewsList.vue'
import ReviewCreate from '@/views/ReviewCreate.vue'
import Profile from '@/views/Profile.vue'
import Settings from '@/views/settings.vue'
import LoginView from '../views/Login.vue' // Import de votre fichier
import inscrire from '../views/inscrire.vue' // Import de votre fichier


const routes = [
  { 
    path: '/', 
    name: 'Home', 
    component: SiteVitrine,
    // On cache la sidebar pour le site vitrine
    meta: { hideSidebar: true } 
  },
  {
    path: '/login',
    name: 'login',
    component: LoginView,
     // On cache la sidebar pour le site vitrine
    meta: { hideSidebar: true }
  },
   {
    path: '/inscrire',
    name: 'inscrire',
    component: inscrire ,
     // On cache la sidebar pour le site vitrine
    meta: { hideSidebar: true }
  },
  
  { 
    path: '/dashboard', 
    name: 'Dashboard', 
    component: Dashboard 
  },
  { 
    path: '/reviews', 
    name: 'ReviewsList', 
    component: ReviewsList 
  },
  { 
    path: '/review-create', 
    name: 'ReviewCreate', 
    component: ReviewCreate 
  },
  { 
    path: '/profile', 
    name: 'Profile', 
    component: Profile 
  },
  { 
    path: '/settings', 
    name: 'Settings', 
    component: Settings 
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router