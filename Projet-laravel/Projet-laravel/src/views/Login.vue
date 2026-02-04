<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// États réactifs
const email = ref('')
const password = ref('')
const rememberMe = ref(false)
const isDark = ref(false) 
const showPassword = ref(false)
const isLoading = ref(false)
const errorMessage = ref('')  

/**
 * LOGIQUE DU MODE SOMBRE PAR DÉFAUT
 */
const applyTheme = (dark) => {
  isDark.value = dark
  if (dark) {
    document.documentElement.classList.add('dark')
  } else {
    document.documentElement.classList.remove('dark')
  }
}

onMounted(() => {
  const savedTheme = localStorage.getItem('theme')
  const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
  
  if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
    applyTheme(true)
  } else {
    applyTheme(false)
  }
})

const toggleTheme = () => {
  const newStatus = !isDark.value
  applyTheme(newStatus)
  localStorage.setItem('theme', newStatus ? 'dark' : 'light')
}

//  NOUVELLE FONCTION - Remplace complètement l'ancienne
const handleLogin = async () => {
  // Reset erreur
  errorMessage.value = ''
  isLoading.value = true
  
  try {
    const response = await fetch('/api/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        email: email.value,
        password: password.value
      })
    })
    
    const data = await response.json()
    
    if (response.ok) {
      // SAUVEGARDE TON TOKEN (testé Postman !)
      localStorage.setItem('token', data.token)
      localStorage.setItem('user', JSON.stringify(data.user))
      
      // Redirection vers tes avis (pas dashboard)
      router.push('/dashboard')
    } else {
      // Erreur Laravel (401, etc.)
      errorMessage.value = data.message || 'Erreur de connexion'
    }
  } catch (error) {
    console.error('Erreur login:', error)
    errorMessage.value = 'Erreur réseau. Vérifiez votre connexion.'
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div class="login-container">
    <button class="theme-toggle" @click="toggleTheme" type="button" :title="isDark ? 'Passer au clair' : 'Passer au sombre'">
      <span class="material-symbols-outlined">{{ isDark ? 'light_mode' : 'dark_mode' }}</span>
    </button>

    <div class="bg-glows">
      <div class="glow glow-primary"></div>
      <div class="glow glow-secondary"></div>
    </div>

    <div class="login-card">
      <div class="card-content">
        <header class="header">
          <div class="logo-box">
            <span class="material-symbols-outlined">shield_person</span>
          </div>
          <h1>REVIEW<span>PRO</span></h1>
          <p>Heureux de vous revoir !</p>
        </header>

        <form @submit.prevent="handleLogin" class="form">
          <!--   ERREUR AFFICHAGE -->
          <div v-if="errorMessage" class="error-message">
            {{ errorMessage }}
          </div>

          <div class="input-group">
            <label for="email">Adresse E-mail</label>
            <div class="field-wrapper">
              <span class="material-symbols-outlined field-icon">mail</span>
              <input 
                id="email" 
                type="email" 
                v-model="email" 
                placeholder="nom@entreprise.com" 
                required
              >
            </div>
          </div>

          <div class="input-group">
            <label for="password">Mot de passe</label>
            <div class="field-wrapper">
              <span class="material-symbols-outlined field-icon">lock</span>
              <input 
                id="password" 
                :type="showPassword ? 'text' : 'password'" 
                v-model="password" 
                placeholder="••••••••" 
                required
              >
              <button type="button" class="password-toggle" @click="showPassword = !showPassword">
                <span class="material-symbols-outlined">{{ showPassword ? 'visibility' : 'visibility_off' }}</span>
              </button>
            </div>
          </div>

          <div class="form-footer">
            <label class="checkbox-container">
              <input type="checkbox" v-model="rememberMe">
              <span class="checkmark"></span>
              Rester connecté
            </label>
            <a href="#" class="forgot-password">Oublié ?</a>
          </div>

          <button type="submit" class="submit-btn" :disabled="isLoading">
            <template v-if="!isLoading">
              Se connecter
              <span class="material-symbols-outlined">login</span>
            </template>
            <div v-else class="loader"></div>
          </button>

          <div class="divider">
            <span>ou</span>
          </div>

          <button type="button" class="google-signin">
            <img src="https://www.google.com/favicon.ico" alt="Google">
            Continuer avec Google
          </button>

          <p class="signup-prompt">
            Pas de compte ? <router-link to="/inscrire">S'inscrire</router-link>
          </p>
        </form>
      </div>
    </div>
  </div>
</template>


<style>
/* Importation globale des ressources */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0');

:root {
  /* Mode Clair par défaut */
  --primary: #ea580c;
  --primary-hover: #c2410c;
  --bg-page: #f1f5f9;
  --card-bg: rgba(255, 255, 255, 0.8);
  --text-main: #0f172a;
  --text-muted: #64748b;
  --input-bg: #ffffff;
  --border-color: #e2e8f0;
  --shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
}

:root.dark {
  /* Mode Sombre */
  --bg-page: #020617;
  --card-bg: rgba(15, 23, 42, 0.75);
  --text-main: #f8fafc;
  --text-muted: #94a3b8;
  --input-bg: #1e293b;
  --border-color: #334155;
  --shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
}

.login-container {
  font-family: 'Inter', sans-serif;
  min-height: 100vh;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: var(--bg-page);
  position: relative;
  overflow: hidden;
  transition: background-color 0.4s ease;
  color: var(--text-main);
}



/* CARTE GLASSMORPHISM */
.login-card {
  position: relative;
  z-index: 10;
  width: 92%;
  max-width: 420px;
  background: var(--card-bg);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border: 1px solid var(--border-color);
  border-radius: 28px;
  box-shadow: var(--shadow);
  padding: 40px;
  transition: transform 0.3s ease, background-color 0.4s ease;
}

/* HEADER */
.header { text-align: center; margin-bottom: 35px; }
.logo-box {
  width: 54px; height: 54px; background: var(--primary);
  color: white; border-radius: 14px;
  display: flex; align-items: center; justify-content: center;
  margin: 0 auto 16px;
  box-shadow: 0 10px 20px -5px rgba(234, 88, 12, 0.4);
}
.header h1 { font-size: 26px; font-weight: 800; letter-spacing: -0.5px; }
.header h1 span { color: var(--primary); }
.header p { color: var(--text-muted); font-size: 15px; margin-top: 6px; }

/* FORMULAIRE & INPUTS */
.input-group { margin-bottom: 22px; }
.input-group label { display: block; font-size: 14px; font-weight: 600; margin-bottom: 8px; margin-left: 4px; }
.field-wrapper { position: relative; display: flex; align-items: center; }
.field-icon { position: absolute; left: 16px; color: var(--text-muted); font-size: 20px; }
.field-wrapper input {
  width: 100%; padding: 14px 16px 14px 48px;
  background: var(--input-bg); border: 1.5px solid var(--border-color);
  border-radius: 14px; color: var(--text-main); font-size: 15px;
  transition: all 0.2s ease;
}
.field-wrapper input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 4px rgba(234, 88, 12, 0.15); }

.password-toggle {
  position: absolute; right: 14px; background: none; border: none;
  color: var(--text-muted); cursor: pointer; display: flex;
}

/* OPTIONS FOOTER */
.form-footer { display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; font-size: 14px; }
.checkbox-container { display: flex; align-items: center; cursor: pointer; color: var(--text-muted); }
.checkbox-container input { display: none; }
.checkmark { width: 19px; height: 19px; border: 2px solid var(--border-color); border-radius: 6px; margin-right: 10px; transition: 0.2s; }
.checkbox-container input:checked + .checkmark { background: var(--primary); border-color: var(--primary); }
.forgot-password { color: var(--primary); font-weight: 700; text-decoration: none; }

/* BOUTONS */
.submit-btn {
  width: 100%; padding: 16px; background: var(--primary); color: white;
  border: none; border-radius: 14px; font-weight: 700; font-size: 16px;
  cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 12px;
  transition: 0.3s ease;
}
.submit-btn:hover:not(:disabled) { background: var(--primary-hover); transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(234, 88, 12, 0.3); }
.submit-btn:disabled { opacity: 0.6; cursor: not-allowed; }

.divider { margin: 25px 0; display: flex; align-items: center; color: var(--text-muted); font-size: 13px; font-weight: 600; text-transform: uppercase; }
.divider::before, .divider::after { content: ""; flex: 1; height: 1px; background: var(--border-color); margin: 0 12px; }

.google-signin {
  width: 100%; padding: 12px; background: transparent; border: 1.5px solid var(--border-color);
  border-radius: 14px; color: var(--text-main); font-weight: 600;
  display: flex; align-items: center; justify-content: center; gap: 12px; cursor: pointer; transition: 0.2s;
}
.google-signin:hover { background: var(--input-bg); }
.google-signin img { width: 18px; }

/* BOUTON THÈME */
.theme-toggle {
  position: absolute; top: 25px; right: 25px; z-index: 100;
  width: 48px; height: 48px; border-radius: 14px; border: 1px solid var(--border-color);
  background: var(--card-bg); color: var(--text-main); cursor: pointer;
  display: flex; align-items: center; justify-content: center; transition: 0.3s;
}
.theme-toggle:hover { transform: rotate(15deg); border-color: var(--primary); }

.signup-prompt { margin-top: 25px; text-align: center; font-size: 14px; color: var(--text-muted); }
.signup-prompt a { color: var(--primary); font-weight: 700; text-decoration: none; }

/* CHARGEMENT */
.loader {
  width: 22px; height: 22px; border: 3px solid rgba(255,255,255,0.3);
  border-top-color: white; border-radius: 50%; animation: spin 0.8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
</style>