<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const isMenuOpen = ref(false)
const loadingUser = ref(true)
const user = reactive({
  name: 'Chargement...',
  email: '',
  avatar: '',
  role: 'user'  //  DEFAULT
})

//  CHARGEMENT USER API - FIX RÔLE
const fetchUser = async () => {
  try {
    const token = localStorage.getItem('token')
    if (!token) {
      router.push('/login')
      return
    }

    const response = await fetch('/api/user', {
      headers: { 
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })

    if (response.ok) {
      const userData = await response.json()
      user.name = userData.name || 'Utilisateur'
      user.email = userData.email || ''
      
      //  FIX : VRAI RÔLE DE LA BASE
      user.role = userData.role || 'user'
      
      user.avatar = userData.id 
        ? `https://api.dicebear.com/7.x/avataaars/svg?seed=${userData.id}`
        : 'https://api.dicebear.com/7.x/avataaars/svg?seed=user'
    } else {
      localStorage.removeItem('token')
      router.push('/login')
    }
  } catch (error) {
    console.error('Erreur fetch user:', error)
    localStorage.removeItem('token')
    router.push('/login')
  } finally {
    loadingUser.value = false
  }
}

//  DÉCONNEXION FIXÉE
const logout = async () => {
  try {
    const token = localStorage.getItem('token')
    if (token) {
      await fetch('/api/user/logout', {
        method: 'POST',
        headers: { 
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        }
      }).catch(() => {}) // Ignore logout errors
    }
  } catch (error) {
    console.error('Erreur logout:', error)
  } finally {
    localStorage.removeItem('token')
    router.push('/')
  }
}

const toggleMenu = () => {
  isMenuOpen.value = !isMenuOpen.value
}

//  INIT
onMounted(() => {
  fetchUser()
})
</script>



<template>
  <button class="mobile-toggle" @click="toggleMenu">
    <span class="material-symbols-outlined">{{ isMenuOpen ? 'close' : 'menu' }}</span>
  </button>

  <div v-if="isMenuOpen" class="sidebar-overlay" @click="isMenuOpen = false"></div>

  <aside class="sidebar-container custom-scrollbar" :class="{ 'is-open': isMenuOpen }">
    <div class="logo-section">
      <div class="logo-icon-bg shadow-orange">
        <span class="material-symbols-outlined text-white text-xl">rate_review</span>
      </div>
      <h1 class="logo-text">ReviewPro</h1>
    </div>

    <nav class="nav-container">
      <p class="nav-category">Menu</p>
      
      <router-link to="/dashboard" active-class="nav-item-active" class="nav-item group" @click="isMenuOpen = false">
        <span class="material-symbols-outlined icon-size">dashboard</span> 
        <span class="text-sm font-bold">Tableau de bord</span>
      </router-link>
      
      <router-link to="/reviews" active-class="nav-item-active" class="nav-item group" @click="isMenuOpen = false">
        <span class="material-symbols-outlined icon-size">format_list_bulleted</span> 
        <span class="text-sm font-bold">Liste des Avis</span>
      </router-link>

      <router-link to="/review-create" active-class="nav-item-active" class="nav-item group" @click="isMenuOpen = false">
        <span class="material-symbols-outlined icon-size">add_circle</span> 
        <span class="text-sm font-bold">Rédiger un avis</span>
      </router-link>
      
      <div class="nav-divider"></div>
      
      <p class="nav-category">Authentification & Compte</p>
      
      <router-link to="/profile" active-class="nav-item-active" class="nav-item group" @click="isMenuOpen = false">
        <span class="material-symbols-outlined icon-size">account_circle</span> 
        <span class="text-sm font-bold">Paramètres</span>
      </router-link>

      <!-- <router-link to="/settings" active-class="nav-item-active" class="nav-item group" @click="isMenuOpen = false">
        <span class="material-symbols-outlined icon-size">settings</span> 
        <span class="text-sm font-bold">Paramètres</span>
      </router-link> -->

      <button @click="logout" class="nav-item logout-button">
        <span class="material-symbols-outlined icon-size">logout</span>
        <span class="text-sm font-bold">Déconnexion</span>
      </button>
    </nav>

   <div class="sidebar-footer group">
  <div class="footer-content">
    <img :src="user.avatar" class="user-avatar shadow-sm" />
    <div class="user-info">
      <p class="user-name truncate">{{ user.name }}</p>
      <!--  FIX DYNAMIQUE RÔLE -->
      <p class="user-role uppercase" :class="{ 'text-orange-500 font-bold': user.role === 'admin' }">
        {{ user.role === 'admin' ? ' ADMINISTRATEUR' : 'Utilisateur' }}
      </p>
    </div>
  </div>
  <span class="material-symbols-outlined footer-arrow">chevron_right</span>
</div>
  </aside>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200');

/* --- STRUCTURE DE BASE --- */
.sidebar-container {
  width: 18rem;
  flex: none;
  background-color: white;
  border-right: 1px solid #e2e8f0;
  display: flex;
  flex-direction: column;
  padding: 1.5rem;
  height: 100vh;
  position: sticky;
  top: 0;
  z-index: 50; /* Augmenté pour passer devant l'overlay */
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s ease, left 0.3s ease;
}

/* --- MOBILE TOGGLE & OVERLAY --- */
.mobile-toggle {
  display: none; /* Caché par défaut */
  position: fixed;
  top: 1rem;
  left: 1rem;
  z-index: 60;
  background: #ea580c;
  color: white;
  border: none;
  padding: 0.5rem;
  border-radius: 0.5rem;
  cursor: pointer;
}

.sidebar-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 40;
}

/* --- MEDIA QUERIES --- */
@media (max-width: 1024px) {
  .mobile-toggle {
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .sidebar-container {
    position: fixed;
    left: -18rem; /* Caché à gauche */
    top: 0;
  }

  .sidebar-container.is-open {
    left: 0; /* Affiché */
    transform: translateX(0);
  }
}

/* --- RESTE DU STYLE (Inchangé) --- */
.logo-section { display: flex; align-items: center; gap: 1rem; margin-bottom: 2.5rem; padding-left: 0.5rem; }
.logo-icon-bg { background-color: #ea580c; padding: 0.5rem; border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; }
.shadow-orange { box-shadow: 0 10px 15px -3px rgba(234, 88, 12, 0.2); }
.logo-text { font-size: 1.125rem; font-weight: 900; letter-spacing: -0.025em; text-transform: uppercase; color: #1e293b; }
.nav-container { flex: 1; display: flex; flex-direction: column; gap: 0.25rem; text-align: left; overflow-y: auto; }
.nav-category { padding: 0 1rem; font-size: 10px; font-weight: 900; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 1rem; }
.nav-item { width: 100%; display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; border-radius: 0.75rem; color: #64748b; text-decoration: none; transition: all 0.2s ease; border: none; background: transparent; cursor: pointer; }
.nav-item:hover { background-color: #f8fafc; color: #ea580c; }
.nav-item-active { background-color: #fff7ed; color: #ea580c !important; font-weight: 700; border-right: 4px solid #ea580c; }
.logout-button { margin-top: auto; color: #ef4444; }
.logout-button:hover { background-color: #fef2f2; color: #dc2626; }
.nav-divider { margin: 1.5rem 0; border-top: 1px solid #f1f5f9; }
.sidebar-footer { margin-top: 1.5rem; padding: 1rem; background-color: #f8fafc; border-radius: 1rem; border: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; }
.user-name { font-size: 11px; font-weight: 700; color: #1e293b; }
.user-role { font-size: 9px; color: #94a3b8; font-weight: 700; }
.user-avatar { width: 2rem; height: 2rem; border-radius: 9999px; }
.icon-size { font-size: 20px; }

/* --- MODE SOMBRE --- */
:global(.dark) .sidebar-container { background-color: #0F172A!important; border-right-color: #2d1f14; }
:global(.dark) .logo-text { color: #ffffff; }
:global(.dark) .nav-item { color: #fff; }
:global(.dark) .nav-item:hover { background-color: rgba(255, 255, 255, 0.05); color: #fb923c; }
:global(.dark) .nav-item-active { background-color: rgba(234, 88, 12, 0.1); }
:global(.dark) .nav-divider { border-top-color: #2d1f14; }
:global(.dark) .sidebar-footer { background-color: #241a11; border-color: #2d1f14; }
:global(.dark) .user-name { color: #ffff; }
:global(.dark) .logout-button:hover { background-color: rgba(239, 68, 68, 0.1); }

</style>