<script setup>
import { onMounted } from 'vue'
import { useRoute } from 'vue-router'
import Sidebar from '@/components/Navbar.vue' 

const route = useRoute()

onMounted(() => {
  const savedTheme = localStorage.getItem('theme')
  const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches

  if (savedTheme === 'dark' || (!savedTheme && systemPrefersDark)) {
    document.documentElement.classList.add('dark')
  } else {
    document.documentElement.classList.remove('dark')
  }
})
</script>

<template>
  <div class="app-layout">
    <Sidebar v-if="!route.meta.hideSidebar" />
    <main :class="['main-viewport', 'custom-scrollbar', { 'full-width': route.meta.hideSidebar }]">
      <router-view />
    </main>
  </div>
</template>

<style>
/* --- RESET GLOBAL (SANS TOUCHER À LA POLICE) --- */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body, html {
  height: 100%;
  width: 100%;
  overflow: hidden;
  /* Font-family supprimé ici pour garder ta police d'origine */
}

/* --- STRUCTURE --- */
.app-layout {
  display: flex;
  height: 100vh;
  width: 100vw;
  background-color: #ffffff;
}

.main-viewport {
  flex: 1;
  height: 100vh;
  overflow-y: auto;
  position: relative;
  background-color: #f1f4f9 !important; 
  background-image: radial-gradient(#d1d5db 0.8px, transparent 0.8px);
  background-size: 30px 30px;
  transition: all 0.3s ease;
}

/* --- MODE SOMBRE GLOBAL --- */

.dark .app-layout {
  background-color: #0f1831;
}

/* 1. Sidebar : Fond et Logo */
.dark aside, 
.dark .sidebar-container {
  background-color: #1e293b!important;
  border-right: 1px solid rgba(255, 255, 255, 0.05)   !important;
}

/* Texte du logo REVIEWPRO en blanc */
.dark .logo-text {
  color: #fff !important;
}

/* 2. ÉLÉMENT ACTIF (contraste sur bouton blanc) */
.dark .nav-item-active {
  background-color:  rgba(234, 88, 12, 0.1)!important;
  color:  #ea580c  !important; 
}

.dark .nav-item-active .material-symbols-outlined {
  color: #ea580c !important;
}

/* 3. ÉLÉMENTS NON-ACTIFS */
.dark .nav-item:not(.nav-item-active) {
  color: #cbd5e1 !important;
}
.dark .nav-item:not(.nav-item-active):hover {
  color: #ea580c !important;
}
.dark .nav-item:not(.nav-item-active) .material-symbols-outlined {
  color: #cbd5e1 !important;
}
.dark .nav-item:not(.nav-item-active) .material-symbols-outlined {
  color: #cbd5e1 !important;
}

.dark .logout-text {
  color: #ea580c !important;
}

/* 4. TEXTES SECONDAIRES & PIED DE PAGE */
.dark .nav-category {
  color: #475569 !important;
}

.dark .user-name {
  color: #ffffff !important;
}

.dark .user-role {
  color: #94a3b8 !important;
}

.dark .sidebar-footer {
  background-color: #242b3d !important;
  border-top: 1px solid #2d3748 !important;
}

/* 5. ZONE DE CONTENU SOMBRE */
.dark .main-viewport {
  background-color: #0b0f1a !important;
  background-image: radial-gradient(#1e293b 1px, transparent 1px) !important;
}

/* --- SCROLLBAR --- */
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
.dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; }
</style>