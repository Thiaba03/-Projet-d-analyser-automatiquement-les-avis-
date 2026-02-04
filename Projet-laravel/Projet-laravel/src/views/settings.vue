<script setup lang="ts">
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

/* ------------------ TYPES ------------------ */
type Tab = 'general' | 'ai' | 'notifs' | 'security' | null

/* ------------------ STATE ------------------ */
const loading = ref(false)
const saving = ref(false)
const saved = ref(false)
const error = ref('')
const activeTab = ref<Tab>(null)

const isDark = ref(false)
const userRole = ref('user')

//  SETTINGS PERSISTANTS API
const settings = reactive({
  username: '',
  email: '',
  language: 'fr',
  timezone: 'Europe/Paris',
  notifications_email: true,
  notifications_push: true,
  ai_precision: 85,
  ai_auto_analysis: true,
  ai_topics_detection: true,
  password_current: '',
  password_new: '',
  password_confirm: ''
})

//  VALIDATION
const validationErrors = reactive({
  password: '',
  email: ''
})

/* ------------------ SECTIONS ------------------ */
const sections = [
  { id: 'general' as const, icon: 'account_circle', title: 'Mon Profil', desc: 'Compte et préférences' },
  { id: 'ai' as const, icon: 'auto_awesome', title: 'IA Avancée', desc: 'Configuration analyse' },
  { id: 'notifs' as const, icon: 'notifications', title: 'Notifications', desc: 'Emails et push' },
  { id: 'security' as const, icon: 'shield', title: 'Sécurité', desc: 'Mot de passe et 2FA' }
]

/* ------------------ API CALLS ------------------ */
// Charger settings utilisateur
const fetchSettings = async () => {
  loading.value = true
  try {
    const token = localStorage.getItem('token')
    const response = await fetch('/api/user/settings', {
      headers: { 
        'Accept': 'application/json', 
        'Authorization': `Bearer ${token}` 
      }
    })
    
    if (response.ok) {
      const data = await response.json()
      Object.assign(settings, data)
    }
  } catch (err) {
    error.value = 'Erreur chargement'
  } finally {
    loading.value = false
  }
}

// Sauvegarder settings
const saveSettings = async () => {
  if (!validateForm()) return
  
  saving.value = true
  saved.value = false
  error.value = ''
  
  try {
    const token = localStorage.getItem('token')
    const body = { 
      ...settings,
      // Nettoie mots de passe vides
      ...(settings.password_new && { 
        password_current: settings.password_current,
        password_new: settings.password_new 
      })
    }
    
    const response = await fetch('/api/user/settings', {
      method: 'POST',
      headers: { 
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}` 
      },
      body: JSON.stringify(body)
    })
    
    if (response.ok) {
      saved.value = true
      setTimeout(() => saved.value = false, 3000)
      closeSection()
    } else {
      const err = await response.json()
      error.value = err.message || 'Erreur sauvegarde'
    }
  } catch (err) {
    error.value = 'Erreur réseau'
  } finally {
    saving.value = false
  }
}

// Charger user + role
const fetchUser = async () => {
  try {
    const token = localStorage.getItem('token')
    const response = await fetch('/api/user', {
      headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${token}` }
    })
    
    if (response.ok) {
      const data = await response.json()
      userRole.value = data.role || 'user'
    }
  } catch {}
}

/* ------------------ VALIDATION ------------------ */
const validateForm = () => {
  validationErrors.password = ''
  validationErrors.email = ''
  
  let valid = true
  
  // Email valide
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!emailRegex.test(settings.email)) {
    validationErrors.email = 'Email invalide'
    valid = false
  }
  
  // Mot de passe
  if (settings.password_new) {
    if (settings.password_new.length < 8) {
      validationErrors.password = '8 caractères minimum'
      valid = false
    } else if (settings.password_new !== settings.password_confirm) {
      validationErrors.password = 'Confirmation différente'
      valid = false
    }
  }
  
  return valid
}

/* ------------------ ACTIONS ------------------ */
const openSection = (tab: Tab) => activeTab.value = tab
const closeSection = () => activeTab.value = null

// Thème global
const toggleDarkMode = () => {
  isDark.value = !isDark.value
  if (isDark.value) {
    document.documentElement.classList.add('dark')
    localStorage.setItem('theme', 'dark')
  } else {
    document.documentElement.classList.remove('dark')
    localStorage.setItem('theme', 'light')
  }
}

// Reset mot de passe
const resetPassword = () => {
  settings.password_current = ''
  settings.password_new = ''
  settings.password_confirm = ''
}

/* ------------------ COMPUTED ------------------ */
const langOptions = [
  { value: 'fr', label: 'Français' },
  { value: 'en', label: 'English' },
  { value: 'es', label: 'Español' }
]

const tzOptions = [
  { value: 'Europe/Paris', label: 'Paris (CET)' },
  { value: 'America/New_York', label: 'New York (EST)' },
  { value: 'Asia/Tokyo', label: 'Tokyo (JST)' }
]

/* ------------------ LIFECYCLE ------------------ */
const handleKey = (e: KeyboardEvent) => {
  if (e.key === 'Escape') closeSection()
}

onMounted(() => {
  window.addEventListener('keydown', handleKey)
  isDark.value = document.documentElement.classList.contains('dark')
  fetchUser()
  fetchSettings()
})

onUnmounted(() => window.removeEventListener('keydown', handleKey))
</script>

<template>
  <div class="settings-wrapper" :class="{ 'dark': isDark }">
    
    <!-- Header -->
    <header class="settings-header">
      <div class="header-container">
        <div class="brand">
          <span class="material-symbols-outlined settings-icon">settings</span>
          <h1>Paramètres Avancés</h1>
          <span class="subtitle" v-if="userRole === 'admin'">Mode Administrateur</span>
        </div>
        <div class="header-actions">
          <button class="icon-btn" @click="toggleDarkMode" title="Thème">
            <span class="material-symbols-outlined">{{ isDark ? 'light_mode' : 'dark_mode' }}</span>
          </button>
          <router-link to="/reviews" class="btn-secondary">
            <span class="material-symbols-outlined">arrow_back</span>
            Avis
          </router-link>
        </div>
      </div>
    </header>

    <!-- Loading -->
    <div v-if="loading" class="loading-overlay">
      <div class="loader">Chargement...</div>
    </div>

    <!-- Erreur -->
    <Transition name="slide-fade">
      <div v-if="error" class="error-banner">
        {{ error }}
        <button @click="error = ''" class="close-error">×</button>
      </div>
    </Transition>

    <main>
      <!-- Grille sections -->
      <Transition name="fade-scale" mode="out-in">
        <div v-if="!activeTab" key="grid" class="options-grid">
          <button
            v-for="s in sections"
            :key="s.id"
            class="option-card"
            :class="{ loading }"
            @click="openSection(s.id)"
          >
            <span class="material-symbols-outlined icon-lg">{{ s.icon }}</span>
            <div>
              <h3>{{ s.title }}</h3>
              <p>{{ s.desc }}</p>
            </div>
          </button>
        </div>
      </Transition>

      <!-- Modal section -->
      <div v-if="activeTab" class="modal-overlay" @click.self="closeSection">
        <div class="modal-content animate-zoom">
          <div class="modal-header">
            <button class="close-btn" @click="closeSection" title="Fermer">
              <span class="material-symbols-outlined">close</span>
            </button>
          </div>

          <!-- GENERAL -->
          <template v-if="activeTab === 'general'">
            <h2 class="modal-title"> Mon Profil</h2>
            <div class="field-group">
              <div class="field">
                <label>Nom d'utilisateur</label>
                <input v-model="settings.username" class="neo-input" />
              </div>
              <div class="field">
                <label>Email</label>
                <input v-model="settings.email" type="email" class="neo-input" />
                <span v-if="validationErrors.email" class="error-text">{{ validationErrors.email }}</span>
              </div>
              <div class="field-row">
                <div class="field">
                  <label>Langue</label>
                  <select v-model="settings.language" class="neo-input">
                    <option v-for="opt in langOptions" :key="opt.value" :value="opt.value">
                      {{ opt.label }}
                    </option>
                  </select>
                </div>
                <div class="field">
                  <label>Fuseau horaire</label>
                  <select v-model="settings.timezone" class="neo-input">
                    <option v-for="opt in tzOptions" :key="opt.value" :value="opt.value">
                      {{ opt.label }}
                    </option>
                  </select>
                </div>
              </div>
            </div>
          </template>

          <!--  IA -->
          <template v-if="activeTab === 'ai'">
            <h2 class="modal-title"> Intelligence Artificielle</h2>
            <div class="field">
              <label>Précision IA ({{ settings.ai_precision }}%)</label>
              <input 
                type="range" 
                min="50" max="100" step="5"
                v-model.number="settings.ai_precision"
                class="neo-range"
              />
              <div class="range-labels">
                <span>Basique 50%</span>
                <span>Expert 100%</span>
              </div>
            </div>
            <div class="toggle-grid">
              <div class="toggle-row">
                <strong>Analyse automatique</strong>
                <label class="switch">
                  <input type="checkbox" v-model="settings.ai_auto_analysis" />
                  <span class="slider"></span>
                </label>
              </div>
              <div class="toggle-row">
                <strong>Détection thématiques</strong>
                <label class="switch">
                  <input type="checkbox" v-model="settings.ai_topics_detection" />
                  <span class="slider"></span>
                </label>
              </div>
            </div>
          </template>

          <!--  NOTIFICATIONS -->
          <template v-if="activeTab === 'notifs'">
            <h2 class="modal-title"> Notifications</h2>
            <div class="toggle-grid">
              <div class="toggle-row">
                <strong>Emails (nouveaux avis)</strong>
                <label class="switch">
                  <input type="checkbox" v-model="settings.notifications_email" />
                  <span class="slider"></span>
                </label>
              </div>
              <div class="toggle-row">
                <strong>Notifications push</strong>
                <label class="switch">
                  <input type="checkbox" v-model="settings.notifications_push" />
                  <span class="slider"></span>
                </label>
              </div>
            </div>
          </template>

          <!--  SECURITY -->
          <template v-if="activeTab === 'security'">
            <h2 class="modal-title"> Sécurité</h2>
            <div class="field-group">
              <div class="field">
                <label>Mot de passe actuel</label>
                <input 
                  v-model="settings.password_current" 
                  type="password" 
                  class="neo-input" 
                  placeholder="Requis pour changer"
                />
              </div>
              <div class="field">
                <label>Nouveau mot de passe</label>
                <input 
                  v-model="settings.password_new" 
                  type="password" 
                  class="neo-input" 
                  placeholder="8+ caractères"
                />
              </div>
              <div class="field">
                <label>Confirmer</label>
                <input 
                  v-model="settings.password_confirm" 
                  type="password" 
                  class="neo-input" 
                />
                <span v-if="validationErrors.password" class="error-text">{{ validationErrors.password }}</span>
              </div>
              <button v-if="settings.password_new" @click="resetPassword" class="btn-link">
                Annuler changement
              </button>
            </div>
          </template>

          <!-- Boutons actions -->
          <div class="modal-actions">
            <button 
              class="btn-secondary" 
              @click="closeSection"
              :disabled="saving"
            >
              Annuler
            </button>
            <button 
              class="btn-primary" 
              @click="saveSettings"
              :disabled="saving || loading"
            >
              <span v-if="saving" class="material-symbols-outlined">hourglass_empty</span>
              {{ saved ? '✔ Enregistré !' : 'Enregistrer' }}
            </button>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>



<style scoped>
/* ------------------ THEME ------------------ */
.settings-wrapper {
  --bg: #f1f5f9;
  --card: #ffffff;
  --accent: #ea580c;
  --text: #0f172a;
  --text-card: #334155;

  min-height: 100vh;
  background: var(--bg);
  color: var(--text);
  font-family: sans-serif;
  transition: all 0.3s ease;
}

.dark-mode {
  --bg: #0f172a;
  --card: #1e293b;
  --text: #f8fafc;
  --text-card: #cbd5e1;
}

/* ------------------ HEADER ------------------ */
.settings-header {
  background: var(--card);
  border-bottom: 1px solid rgba(226, 232, 240, 0.1);
  padding: 1.5rem 2rem;
}

.header-container {
  max-width: 1000px;
  margin: auto;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.brand { display: flex; gap: 1rem; align-items: center; }
.settings-icon { color: var(--accent); font-size: 2rem; }
.icon-btn { background: none; border: none; cursor: pointer; color: var(--text); display: flex; align-items: center; }

.back-link {
  text-decoration: none;
  color: var(--accent);
  font-weight: 700;
  font-size: 0.9rem;
}

/* ------------------ GRID ------------------ */
.options-grid {
  max-width: 1000px;
  margin: 4rem auto;
  display: grid;
  gap: 2rem;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  padding: 0 2rem;
}

.option-card {
  all: unset;
  background: var(--card);
  border-radius: 24px;
  padding: 2.5rem;
  text-align: center;
  cursor: pointer;
  transition: 0.3s;
  box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.05);
  color: var(--text-card);
}

.option-card h3 { color: var(--text); margin: 1rem 0 0.5rem 0; }
.option-card p { font-size: 0.9rem; opacity: 0.8; }

.option-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 20px 40px -15px rgba(234, 88, 12, 0.25);
}

.icon-lg { font-size: 3rem; color: var(--accent); }

/* ------------------ MODAL ------------------ */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.8);
  backdrop-filter: blur(8px);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: var(--card);
  border-radius: 32px;
  padding: 3rem;
  width: 90%;
  max-width: 500px;
  position: relative;
  color: var(--text);
}

.close-btn {
  position: absolute;
  top: 1.5rem;
  right: 1.5rem;
  background: none;
  border: none;
  cursor: pointer;
  color: var(--text);
}

.modal-title { font-size: 1.5rem; font-weight: 800; margin-bottom: 2rem; }

/* ------------------ FORM ------------------ */
.field { margin-bottom: 1.5rem; display: flex; flex-direction: column; gap: 0.5rem; }
.field label { font-size: 0.85rem; font-weight: 700; opacity: 0.7; }
.field-label { display: block; margin-bottom: 1rem; font-weight: 600; }

.neo-input {
  width: 100%;
  padding: 1rem;
  border-radius: 12px;
  border: 1px solid rgba(226, 232, 240, 0.2);
  background: var(--bg);
  color: var(--text);
  outline: none;
}

.neo-range {
  width: 100%;
  accent-color: var(--accent);
  margin-bottom: 1.5rem;
}

.toggle-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin: 2rem 0;
}

/* SWITCH */
.switch { position: relative; width: 44px; height: 24px; }
.switch input { opacity: 0; width: 0; height: 0; }
.slider {
  position: absolute; inset: 0;
  background: #94a3b8;
  border-radius: 999px;
  transition: 0.3s;
  cursor: pointer;
}
.slider::before {
  content: '';
  position: absolute;
  height: 18px; width: 18px;
  left: 3px; bottom: 3px;
  background: white;
  border-radius: 50%;
  transition: 0.3s;
}
input:checked + .slider { background: var(--accent); }
input:checked + .slider::before { transform: translateX(20px); }

/* BUTTON */
.btn-primary {
  width: 100%;
  background: var(--accent);
  color: white;
  border: none;
  padding: 1rem;
  border-radius: 14px;
  font-weight: 700;
  cursor: pointer;
  transition: 0.2s;
}
.btn-primary:hover { opacity: 0.9; }
.btn-primary:active { transform: scale(0.98); }

/* ANIMATIONS */
.animate-zoom { animation: zoomIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }
.fade-scale-enter-active, .fade-scale-leave-active { transition: 0.25s; }
.fade-scale-enter-from, .fade-scale-leave-to { opacity: 0; transform: scale(0.95); }

@keyframes zoomIn {
  from { transform: scale(0.85); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}
</style>