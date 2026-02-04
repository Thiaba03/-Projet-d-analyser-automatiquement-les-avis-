<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const isDarkMode = ref(false)
const activeTab = ref('personal')
const loading = ref(false)

//  DONNÉES UTILISATEUR AVEC RÔLE 
const user = reactive({
  id: '',
  name: '',
  email: '',
  phone: '',
  jobTitle: '',
  department: '',
  bio: '',
  role: 'user',  
  location: 'Paris, Île-de-France, FR (CET)',
  avatar: ''
})

//  JOURNAL D'ACTIVITÉ
const activityLog = reactive([
  { id: 1, action: 'Connexion réussie', date: 'Aujourd\'hui, 14:20', icon: 'login', color: 'text-blue-500' },
  { id: 2, action: 'Modification du profil', date: 'Hier, 09:15', icon: 'edit', color: 'text-orange-500' },
  { id: 3, action: 'Nouveau mot de passe généré', date: '12 Déc. 2023', icon: 'key', color: 'text-emerald-500' },
  { id: 4, action: 'Tentative de connexion bloquée', date: '05 Déc. 2023', icon: 'gpp_bad', color: 'text-red-500' }
])

//  NOTIFICATIONS
const notifSettings = reactive({
  emailReviews: true,
  pushAlerts: true
})

//  GESTION MOT DE PASSE
const passwordForm = reactive({
  current: '',
  new: '',
  confirm: ''
})
const showCurrent = ref(false)
const showNew = ref(false)
const showConfirm = ref(false)
const isUpdatingPassword = ref(false)
const passwordMessage = ref('')
const passwordSuccess = ref(false)

//  VALIDATION MOT DE PASSE FORT
const isStrongPassword = (password) => {
  return password.length >= 8 && /[A-Z]/.test(password) && /\d/.test(password)
}

const isPasswordValid = () => {
  return passwordForm.current.length > 0 && 
         passwordForm.new.length > 0 && 
         passwordForm.confirm.length > 0 &&
         passwordForm.new === passwordForm.confirm &&
         isStrongPassword(passwordForm.new)
}

//  CHARGEMENT PROFIL - AVEC RÔLE 
const fetchProfile = async () => {
  loading.value = true
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

    if (!response.ok) {
      localStorage.removeItem('token')
      router.push('/login')
      return
    }

    const userData = await response.json()
    Object.assign(user, {
      id: userData.id || '',
      name: userData.name || '',
      email: userData.email || '',
      phone: userData.phone || '',
      jobTitle: userData.job_title || '',
      department: userData.department || '',
      bio: userData.bio || '',
      role: userData.role || 'user',  
      location: userData.location || 'Paris, Île-de-France, FR (CET)',
      avatar: userData.id ? `https://api.dicebear.com/7.x/avataaars/svg?seed=${userData.id}` : ''
    })
  } catch (error) {
    console.error('Erreur chargement profil:', error)
    localStorage.removeItem('token')
    router.push('/login')
  } finally {
    loading.value = false
  }
}

//  SAUVEGARDE PROFIL
const saveProfile = async () => {
  loading.value = true
  try {
    const token = localStorage.getItem('token')
    if (!token) {
      alert(' Token manquant')
      return
    }

    const response = await fetch('/api/user', {
      method: 'PATCH',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        name: user.name || null,
        email: user.email || null,
        phone: user.phone || null,
        job_title: user.jobTitle || null,
        department: user.department || null,
        bio: user.bio || null
      })
    })

    if (response.ok) {
      alert(' Profil mis à jour !')
      await fetchProfile()
    } else {
      const errorText = await response.text()
      alert(' Erreur: ' + errorText.slice(0, 100))
    }
  } catch (error) {
    console.error('Erreur sauvegarde:', error)
    alert(' Erreur réseau')
  } finally {
    loading.value = false
  }
}

//  MISE À JOUR MOT DE PASSE
const updatePassword = async () => {
  if (!isPasswordValid()) {
    passwordMessage.value = 'Vérifiez votre formulaire'
    passwordSuccess.value = false
    return
  }

  isUpdatingPassword.value = true
  passwordMessage.value = ''

  try {
    const token = localStorage.getItem('token')
    if (!token) {
      passwordMessage.value = ' Session expirée'
      router.push('/login')
      return
    }

    const response = await fetch('/api/user/password', {
      method: 'PATCH',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        current_password: passwordForm.current,
        password: passwordForm.new,
        password_confirmation: passwordForm.confirm
      })
    })

    if (response.ok) {
      passwordMessage.value = 'Bravo Mot de passe mis à jour !'
      passwordSuccess.value = true
      Object.assign(passwordForm, { current: '', new: '', confirm: '' })
      setTimeout(() => { passwordMessage.value = '' }, 5000)
    } else {
      const errorText = await response.text()
      passwordMessage.value = ' ' + errorText.slice(0, 100)
      passwordSuccess.value = false
    }
  } catch (error) {
    console.error('Erreur:', error)
    passwordMessage.value = ' Erreur réseau'
    passwordSuccess.value = false
  } finally {
    isUpdatingPassword.value = false
  }
}

//  INIT
onMounted(() => {
  const savedTheme = localStorage.getItem('theme')
  isDarkMode.value = savedTheme === 'dark' || document.documentElement.classList.contains('dark')
  if (isDarkMode.value) document.documentElement.classList.add('dark')
  fetchProfile()
})

const toggleDarkMode = () => {
  isDarkMode.value = !isDarkMode.value
  document.documentElement.classList.toggle('dark', isDarkMode.value)
  localStorage.setItem('theme', isDarkMode.value ? 'dark' : 'light')
}
</script>


<template>
  <!-- TON TEMPLATE EXACTEMENT IDENTIQUE -->
  <div :class="{ 'dark': isDarkMode }" class="profile-page-container">
    <header class="top-nav-modern">
      <div class="breadcrumb-container">
        <span>App</span> <span class="material-symbols-outlined sep">chevron_right</span>
        <span>Utilisateurs</span> <span class="material-symbols-outlined sep">chevron_right</span>
        <span class="current-page">Paramètres</span>
      </div>
      <button @click="toggleDarkMode" class="theme-btn-neo shadow-sm">
        <span class="material-symbols-outlined">{{ isDarkMode ? 'light_mode' : 'dark_mode' }}</span>
      </button>
    </header>

    <main class="profile-scroll-view custom-scrollbar text-left">
      <div class="profile-content-layout animate-up">
        
        <!-- BANNER UTILISATEUR -->
        <section class="banner-card shadow-premium">
          <div class="banner-inner">
            <div class="avatar-stack">
              <img :src="user.avatar || '/default-avatar.png'" :alt="user.name" class="profile-img shadow-lg" />
              <div class="camera-pill shadow-orange">
                <span class="material-symbols-outlined">photo_camera</span>
              </div>
            </div>
            
            <div class="user-identity">
              <div class="name-status-row">
                <h2 class="user-h2">{{ loading ? 'Chargement...' : (user.name || 'N/C') }}</h2>
                <span class="status-pill-neo">
                  <span class="pulse-dot"></span> Statut Actif
                </span>
              </div>
              <p class="user-job-sub">
                {{ user.jobTitle || 'N/C' }}
                <span v-if="user.department" class="dept-badge">• {{ user.department }}</span>
              </p>
              
              <div class="user-meta-row">
                <div class="meta-box">
                  <span class="material-symbols-outlined text-orange-500">location_on</span>
                  {{ user.location }}
                </div>
                <div class="meta-box">
                  <span class="material-symbols-outlined text-orange-500">mail</span>
                  {{ user.email || 'N/C' }}
                </div>
                <div class="meta-box">
                  <span class="material-symbols-outlined text-orange-500">badge</span>
                  ID: {{ user.id || 'N/C' }}
                </div>
              </div>
            </div>
            
            <button @click="saveProfile" :disabled="loading" class="btn-save-modern shadow-orange">
              <span class="material-symbols-outlined">save</span>
              {{ loading ? 'Sauvegarde...' : 'Enregistrer le profil' }}
            </button>
          </div>

          <div class="tabs-nav-neo">
            <button @click="activeTab = 'personal'" :class="{ active: activeTab === 'personal' }">
              Infos Personnelles
            </button>
            <button @click="activeTab = 'security'" :class="{ active: activeTab === 'security' }">
              Sécurité
            </button>
            <button @click="activeTab = 'notifications'" :class="{ active: activeTab === 'notifications' }">
              Notifications
            </button>
            <button @click="activeTab = 'activity'" :class="{ active: activeTab === 'activity' }">
              Journal d'activité
            </button>
          </div>
        </section>

        <!-- FORMULAIRE PLEIN ÉCRAN -->
        <div class="w-full">
          <div class="content-card-neo shadow-premium animate-fade">
            
            <!-- INFO PERSONNELLES -->
            <div v-if="activeTab === 'personal'">
              <h3 class="card-title-neo italic">Informations de contact</h3>
              <div class="form-grid-neo">
                <div class="input-wrap">
                  <label>Nom complet</label>
                  <input v-model="user.name" class="neo-input" required />
                </div>
                <div class="input-wrap">
                  <label>Adresse E-mail</label>
                  <input v-model="user.email" type="email" class="neo-input" required />
                </div>
                <div class="input-wrap">
                  <label>Téléphone</label>
                  <input v-model="user.phone" type="tel" class="neo-input" placeholder="+33 6 12 34 56 78" />
                </div>
                <div class="input-wrap">
                  <label>Poste occupé</label>
                  <input v-model="user.jobTitle" class="neo-input" placeholder="Développeur Fullstack" />
                </div>
                <div class="input-wrap">
                  <label>Département</label>
                  <input v-model="user.department" class="neo-input" placeholder="Technique, Marketing, RH..." />
                </div>
                <div class="input-wrap full">
                  <label>Bio</label>
                  <textarea v-model="user.bio" rows="4" class="neo-textarea" placeholder="Décrivez-vous..."></textarea>
                </div>
              </div>
            </div>

            <!-- SÉCURITÉ FONCTIONNELLE -->
            <div v-else-if="activeTab === 'security'" class="content-card-neo shadow-premium animate-fade">
              <h3 class="card-title-neo italic">Sécurité du compte</h3>
              
              <form @submit.prevent="updatePassword" class="space-y-4">
                <div class="input-wrap">
                  <label>Mot de passe actuel</label>
                  <div class="relative">
                    <input 
                      v-model="passwordForm.current" 
                      :type="showCurrent ? 'text' : 'password'" 
                      class="neo-input" 
                      placeholder="••••••••"
                      required
                    />
                    <button 
                      type="button"
                      @click="showCurrent = !showCurrent"
                      class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                    >
                      <span class="material-symbols-outlined text-sm">
                        {{ showCurrent ? 'visibility_off' : 'visibility' }}
                      </span>
                    </button>
                  </div>
                </div>

                <div class="input-wrap">
                  <label>Nouveau mot de passe</label>
                  <div class="relative">
                    <input 
                      v-model="passwordForm.new" 
                      :type="showNew ? 'text' : 'password'" 
                      class="neo-input" 
                      placeholder="••••••••"
                      required
                    />
                    <button 
                      type="button"
                      @click="showNew = !showNew"
                      class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                    >
                      <span class="material-symbols-outlined text-sm">
                        {{ showNew ? 'visibility_off' : 'visibility' }}
                      </span>
                    </button>
                  </div>
                  <div v-if="passwordForm.new.length > 0" class="mt-2 text-xs space-y-1">
                    <div :class="['flex items-center', { 'text-green-500': isStrongPassword(passwordForm.new), 'text-red-500': !isStrongPassword(passwordForm.new) }]">
                      <span class="material-symbols-outlined text-xs mr-1">check_circle</span>
                      {{ isStrongPassword(passwordForm.new) ? 'Mot de passe fort' : 'Min. 8 caractères, 1 majuscule, 1 chiffre' }}
                    </div>
                  </div>
                </div>

                <div class="input-wrap">
                  <label>Confirmer nouveau mot de passe</label>
                  <div class="relative">
                    <input 
                      v-model="passwordForm.confirm" 
                      :type="showConfirm ? 'text' : 'password'" 
                      class="neo-input" 
                      placeholder="••••••••"
                      required
                    />
                    <button 
                      type="button"
                      @click="showConfirm = !showConfirm"
                      class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                    >
                      <span class="material-symbols-outlined text-sm">
                        {{ showConfirm ? 'visibility_off' : 'visibility' }}
                      </span>
                    </button>
                  </div>
                  <div v-if="passwordForm.confirm.length > 0 && passwordForm.new !== passwordForm.confirm" class="mt-2 text-red-500 text-xs flex items-center">
                    <span class="material-symbols-outlined text-xs mr-1">error</span>
                    Les mots de passe ne correspondent pas
                  </div>
                </div>

                <!-- BOUTON ORANGE CENTRÉ -->
<div class="password-btn-container">
  <button 
    type="submit" 
    :disabled="isUpdatingPassword || !isPasswordValid()"
    class="group relative overflow-hidden btn-password-orange transform-gpu"
  >
    <!-- GRADIENT ORANGE INTENSE -->
    <div class="absolute inset-0 bg-gradient-to-r from-orange-500 to-orange-600 opacity-95 group-hover:from-orange-400 group-hover:to-orange-500 transition-all duration-500"></div>
    
    <!-- EFFET SHINE ORANGE -->
    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-orange-200/30 to-transparent skew-x-12 transform -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
    
    <!-- ICÔNE ANIMÉE -->
    <div class="relative flex items-center justify-center gap-3 px-8 py-4 font-semibold text-lg">
      <span class="material-symbols-outlined text-xl group-hover:scale-110 transition-transform duration-300 text-white">
        {{ isUpdatingPassword ? 'hourglass_empty' : 'lock_reset' }}
      </span>
      <span class="relative z-10 text-white font-bold tracking-wide">
        {{ isUpdatingPassword ? 'Mise à jour...' : 'Mettre à jour le mot de passe' }}
      </span>
    </div>
    
    <!-- BORDER GLOW ORANGE -->
    <div class="absolute inset-0 border-2 border-orange-300/60 rounded-2xl group-hover:border-orange-200/90 transition-all duration-500 scale-105"></div>
    
    <!-- PARTICULES ORANGE -->
    <div class="absolute inset-0 overflow-hidden">
      <div class="absolute -top-2 -right-2 w-20 h-20 bg-orange-300/20 rounded-full blur-xl animate-pulse group-hover:animate-ping delay-500"></div>
      <div class="absolute -bottom-2 -left-2 w-16 h-16 bg-orange-400/30 rounded-full blur-lg animate-bounce delay-1000"></div>
    </div>
  </button>
</div>



                <div v-if="passwordMessage" :class="['p-3 rounded-lg mt-4 text-sm', { 'bg-green-100 text-green-800 border border-green-200': passwordSuccess, 'bg-red-100 text-red-800 border border-red-200': !passwordSuccess }]">
                  {{ passwordMessage }}
                </div>
              </form>
            </div>

            <!-- JOURNAL -->
            <div v-else-if="activeTab === 'activity'">
              <h3 class="card-title-neo italic">Journal d'activité</h3>
              <div class="timeline-neo">
                <div v-for="log in activityLog" :key="log.id" class="timeline-row">
                  <div class="timeline-icon-box" :class="log.color.replace('text', 'bg').replace('500', '100')">
                    <span class="material-symbols-outlined" :class="log.color">{{ log.icon }}</span>
                  </div>
                  <div class="timeline-txt">
                    <p class="t-action">{{ log.action }}</p>
                    <p class="t-date uppercase">{{ log.date }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- NOTIFICATIONS -->
            <div v-else-if="activeTab === 'notifications'">
              <h3 class="card-title-neo italic">Alertes & Notifications</h3>
              <div class="notif-list-neo">
                <div v-for="(val, key) in notifSettings" :key="key" class="notif-row-neo">
                  <div class="flex items-center gap-4">
                    <div class="notif-icon-neo">
                      <span class="material-symbols-outlined">
                        {{ key === 'emailReviews' ? 'alternate_email' : 'notifications_active' }}
                      </span>
                    </div>
                    <p class="font-bold">
                      {{ key === 'emailReviews' ? 'Alertes par email' : 'Notifications Bureau' }}
                    </p>
                  </div>
                  <div @click="notifSettings[key] = !notifSettings[key]" class="switch-neo" :class="{ on: notifSettings[key] }">
                    <div class="switch-handle"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>




<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

/* --- DESIGN GLOBAL --- */
.profile-page-container {
  width: 100%;
  height: 100%;
  background-color: #f1f4f9 !important;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  transition: background-color 0.3s ease;
}

/* --- LOGIQUE MODE SOMBRE --- */
/* On cible .profile-page-container.dark pour le background principal */
.profile-page-container.dark {
  background-color: #0f172a!important;
}

.dark .top-nav-modern {
  background:#1e293b !important;
  border-bottom-color: #1e293b !important;
}

.dark .banner-card, 
.dark .content-card-neo,
.dark .perf-item-neo,
.dark .theme-btn-neo {
  background-color: #1e293b!important;
  border-color: #334155 !important;
}

.dark .user-h2, 
.dark .card-title-neo, 
.dark .p-val,
.dark .t-action,
.dark .breadcrumb-container span:not(.current-page) {
  color: #ffffff !important;
}

.dark .neo-input, 
.dark .neo-textarea,
.dark .notif-row-neo {
  background-color: #0f172a !important;
  border-color: #334155 !important;
  color: #f8fafc !important;
}

.dark .user-job-sub, 
.dark .p-lab, 
.dark .t-date,
.dark .meta-box,
.dark label {
  color: #94a3b8 !important;
}

/* --- RESTE DU DESIGN (Inchangé) --- */
.top-nav-modern {
  height: 64px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 40px;
  background: rgba(255,255,255,0.4);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(226, 232, 240, 0.6);
}
.breadcrumb-container { display: flex; align-items: center; gap: 8px; font-size: 10px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; }
.sep { font-size: 14px; }
.current-page { color: #ea580c; }
.theme-btn-neo { background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 6px; color: #64748b; cursor: pointer; }

.profile-scroll-view { flex: 1; overflow-y: auto; padding: 40px; }
.profile-content-layout { max-width: 1200px; margin: 0 auto; display: flex; flex-direction: column; gap: 32px; }

.banner-card { background: white; border-radius: 40px; padding: 40px; border: 1px solid rgba(255,255,255,0.8); transition: 0.3s; }
.banner-inner { display: flex; align-items: center; gap: 32px; flex-wrap: wrap; margin-bottom: 32px; }
.avatar-stack { position: relative; }
.profile-img { width: 128px; height: 128px; border-radius: 50%; border: 5px solid #f8fafc; }
.camera-pill { position: absolute; bottom: 0; right: 0; background: #ea580c; color: white; padding: 8px; border-radius: 50%; border: 3px solid white; display: flex; }

.user-h2 { font-size: 32px; font-weight: 900; color: #0f172a; margin: 0; letter-spacing: -1.5px; }
.user-job-sub { font-size: 16px; color: #64748b; font-weight: 600; margin-top: 4px; }
.status-pill-neo { background: #ecfdf5; color: #10b981; font-size: 9px; font-weight: 800; padding: 4px 12px; border-radius: 20px; text-transform: uppercase; display: inline-flex; align-items: center; gap: 6px; }
.pulse-dot { width: 6px; height: 6px; background: #10b981; border-radius: 50%; animation: pulse 2s infinite; }
.user-meta-row { display: flex; gap: 24px; margin-top: 20px; flex-wrap: wrap; }
.meta-box { display: flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; }

.btn-save-modern { background: #ea580c; color: white; border: none; padding: 14px 28px; border-radius: 18px; font-weight: 800; font-size: 12px; text-transform: uppercase; cursor: pointer; display: flex; align-items: center; gap: 8px; }

.tabs-nav-neo { display: flex; gap: 32px; border-top: 1px solid #f1f5f9; padding-top: 24px; }
.tabs-nav-neo button { background: none; border: none; font-size: 10px; font-weight: 900; text-transform: uppercase; color: #94a3b8; letter-spacing: 1px; cursor: pointer; padding-bottom: 12px; border-bottom: 2.5px solid transparent; transition: 0.3s; }
.tabs-nav-neo button.active { color: #ea580c; border-color: #ea580c; }

.bottom-grid-modern { display: grid; grid-template-columns: 1fr 340px; gap: 32px; align-items: start; }
.content-card-neo { background: white; border-radius: 35px; padding: 35px; border: 1px solid white; transition: 0.3s; }
.card-title-neo { font-size: 18px; font-weight: 900; color: #0f172a; margin-bottom: 32px; }

.form-grid-neo { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
.input-wrap { display: flex; flex-direction: column; gap: 8px; }
.input-wrap label { font-size: 9px; font-weight: 900; color: #94a3b8; text-transform: uppercase; margin-left: 12px; }
.input-wrap.full { grid-column: span 2; }
.neo-input, .neo-textarea { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 14px 18px; font-weight: 700; color: #1e293b; outline: none; }
.neo-input:focus { border-color: #ea580c; background: white; }

.timeline-neo { display: flex; flex-direction: column; gap: 24px; }
.timeline-row { display: flex; align-items: center; gap: 16px; }
.timeline-icon-box { width: 44px; height: 44px; border-radius: 14px; display: flex; align-items: center; justify-content: center; }
.t-action { font-size: 14px; font-weight: 800; color: #1e293b; margin: 0; }
.t-date { font-size: 10px; font-weight: 700; color: #94a3b8; margin: 0; }

.perf-title-neo { font-size: 11px; font-weight: 900; color: #94a3b8; letter-spacing: 2px; margin-bottom: 24px; }
.perf-stack { display: flex; flex-direction: column; gap: 12px; }
.perf-item-neo { display: flex; align-items: center; gap: 16px; padding: 14px; background: #fdfdfd; border-radius: 20px; border: 1px solid #f1f5f9; transition: 0.3s; }
.perf-item-neo:hover { transform: scale(1.02); border-color: #ea580c; }
.p-icon-box { background: #fff7ed; color: #ea580c; padding: 12px; border-radius: 14px; }
.p-lab { font-size: 9px; font-weight: 900; color: #94a3b8; text-transform: uppercase; margin: 0; }
.p-val { font-size: 18px; font-weight: 900; color: #1e293b; margin: 0; }

.notif-list-neo { display: flex; flex-direction: column; gap: 16px; }
.notif-row-neo { display: flex; align-items: center; justify-content: space-between; padding: 20px; background: #f8fafc; border-radius: 24px; }
.notif-icon-neo { background: white; color: #ea580c; padding: 10px; border-radius: 12px; display: flex; border: 1px solid #f1f5f9; }
.switch-neo { width: 48px; height: 26px; background: #e2e8f0; border-radius: 20px; position: relative; cursor: pointer; transition: 0.3s; }
.switch-neo.on { background: #ea580c; }
.switch-handle { width: 18px; height: 18px; background: white; border-radius: 50%; position: absolute; top: 4px; left: 4px; transition: 0.3s; }
.switch-neo.on .switch-handle { transform: translateX(22px); }

.shadow-premium { box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.05); }
.shadow-orange { box-shadow: 0 10px 20px rgba(234, 88, 12, 0.2); }
.animate-up { animation: slideUp 0.6s ease-out; }

@keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
@keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.5; } 100% { opacity: 1; } }

.scrollbar-hide::-webkit-scrollbar { display: none; }

/* 🔥 BOUTON ORANGE PREMIUM */
.btn-password-orange {
  background: linear-gradient(135deg, #f97316 0%, #ea580c 50%, #c2410c 100%) !important;
  backdrop-filter: blur(20px);
  border-radius: 24px !important;
  border: 1px solid rgba(251, 146, 60, 0.4);
  box-shadow: 
    0 20px 40px rgba(249, 115, 22, 0.4),
    0 8px 16px rgba(0, 0, 0, 0.15),
    inset 0 1px 0 rgba(255, 255, 255, 0.25);
  color: white !important;
  font-weight: 700 !important;
  font-size: 16px !important;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.15);
  min-height: 68px;
  position: relative;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  cursor: pointer;
  width: 100%;
  max-width: 420px;
}

.btn-password-orange:hover {
  transform: translateY(-4px) scale(1.02);
  box-shadow: 
    0 30px 60px rgba(249, 115, 22, 0.5),
    0 12px 24px rgba(0, 0, 0, 0.25),
    inset 0 1px 0 rgba(255, 255, 255, 0.35);
  background: linear-gradient(135deg, #fb923c 0%, #f59e0b 50%, #d97706 100%) !important;
}

.btn-password-orange:disabled {
  background: linear-gradient(135deg, #9ca3af 0%, #6b7280 100%) !important;
  opacity: 0.7;
  cursor: not-allowed;
  transform: none !important;
  box-shadow: 
    0 10px 20px rgba(0, 0, 0, 0.1),
    inset 0 1px 0 rgba(255, 255, 255, 0.1);
}

/* DARK MODE ORANGE */
.dark .btn-password-orange {
  background: linear-gradient(135deg, #ea580c 0%, #ea580c 50%, #ea580c 100%) !important;
  border-color: rgba(251, 146, 60, 0.6);
}

.dark .btn-password-orange:hover {
  box-shadow: 
    0 35px 70px rgba(251, 146, 60, 0.6),
    0 15px 30px rgba(0, 0, 0, 0.4);
}

/* CONTAINER CENTRE */
.password-btn-container {
  display: flex;
  justify-content: center;
  align-items: center;
  margin: 2.5rem 0 1.5rem 0;
  width: 100%;
}


</style>