<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// États réactifs
const name = ref('')
const email = ref('')
const password = ref('')
const terms = ref(false)
const isDark = ref(false)
const isLoading = ref(false)

/**
 * LOGIQUE DU MODE SOMBRE (Auto + Persistance)
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

const handleSignup = async () => {
  if (!name.value || !email.value || !password.value || !terms.value) {
    alert('Remplis tous les champs !')
    return
  }

  isLoading.value = true
  try {
    const response = await fetch('/api/register', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        name: name.value,
        email: email.value,
        password: password.value
      })
    })

    const data = await response.json()

    if (response.ok) {
      //  Token sauvé → Dashboard
      localStorage.setItem('token', data.token)
      router.push('/dashboard')
    } else {
      alert(' ' + (data.message || 'Erreur inscription'))
    }
  } catch (error) {
    console.error('Erreur register:', error)
    alert(' Erreur réseau')
  } finally {
    isLoading.value = false
  }
}

</script>

<template>
  <div class="signup-page">
    <button class="theme-btn" @click="toggleTheme" type="button" :aria-label="isDark ? 'Mode clair' : 'Mode sombre'">
      <span class="material-symbols-outlined">{{ isDark ? 'light_mode' : 'dark_mode' }}</span>
    </button>

    <div class="glow-container">
      <div class="glow glow-orange"></div>
      <div class="glow glow-blue"></div>
    </div>

    <div class="signup-card">
      <div class="card-header">
        <div class="icon-box">
          <span class="material-symbols-outlined">person_add</span>
        </div>
        <h1>Créer un <span>compte</span></h1>
        <p>Rejoignez ReviewPro 2025 et commencez votre aventure</p>
      </div>

      <div class="social-grid">
        <button class="social-btn" type="button" title="Google">
          <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google">
        </button>
        <button class="social-btn" type="button" title="GitHub">
          <img src="https://www.svgrepo.com/show/512317/github-142.svg" alt="GitHub">
        </button>
        <button class="social-btn" type="button" title="LinkedIn">
          <img src="https://www.svgrepo.com/show/448234/linkedin.svg" alt="LinkedIn">
        </button>
      </div>

      <div class="divider">
        <span>ou utilisez votre e-mail</span>
      </div>

      <form @submit.prevent="handleSignup" class="signup-form">
        <div class="input-group">
          <label for="reg-name">NOM COMPLET</label>
          <div class="input-wrapper">
            <span class="material-symbols-outlined icon-left">person</span>
            <input id="reg-name" type="text" v-model="name" placeholder="Jean Dupont" required>
          </div>
        </div>

        <div class="input-group">
          <label for="reg-email">E-MAIL</label>
          <div class="input-wrapper">
            <span class="material-symbols-outlined icon-left">mail</span>
            <input id="reg-email" type="email" v-model="email" placeholder="nom@entreprise.com" required>
          </div>
        </div>

        <div class="input-group">
          <label for="reg-pass">MOT DE PASSE</label>
          <div class="input-wrapper">
            <span class="material-symbols-outlined icon-left">lock</span>
            <input id="reg-pass" type="password" v-model="password" placeholder="••••••••" required>
          </div>
        </div>

        <div class="form-footer">
          <label class="checkbox-container">
            <input type="checkbox" v-model="terms" required>
            <span class="checkmark"></span>
            <span class="terms-text">J'accepte les <a href="#" class="terms-link">Conditions Générales</a></span>
          </label>
        </div>

        <button type="submit" class="submit-btn" :disabled="isLoading">
          <template v-if="!isLoading">
            Créer un compte
            <span class="material-symbols-outlined">rocket_launch</span>
          </template>
          <div v-else class="loader"></div>
        </button>

        <p class="login-link">
          Déjà un compte ? <router-link to="/login">Se connecter</router-link>
        </p>
      </form>
    </div>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0');

:root {
  --primary: #ea580c;
  --primary-hover: #c2410c;
  --text-main: #0f172a;
  --text-muted: #64748b;
  --bg-page: #f8fafc;
  --card-bg: rgba(255, 255, 255, 0.85);
  --input-bg: #ffffff;
  --border-color: #e2e8f0;
  --shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05);
}

:root.dark {
  --bg-page: #020617;
  --card-bg: rgba(15, 23, 42, 0.7);
  --text-main: #f8fafc;
  --text-muted: #94a3b8;
  --input-bg: #1e293b;
  --border-color: #334155;
  --shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
}

.signup-page {
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


/* --- CARD --- */
.signup-card {
  position: relative; z-index: 10; width: 92%; max-width: 440px; padding: 40px;
  background: var(--card-bg); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);
  border: 1px solid var(--border-color); border-radius: 28px; box-shadow: var(--shadow);
  animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}

.card-header { text-align: center; margin-bottom: 24px; }
.icon-box {
  width: 52px; height: 52px; margin: 0 auto 15px; background: var(--primary);
  color: white; border-radius: 14px; display: flex; align-items: center; justify-content: center;
  box-shadow: 0 10px 15px -3px rgba(234, 88, 12, 0.3);
}

.card-header h1 { font-size: 26px; font-weight: 800; color: var(--text-main); }
.card-header h1 span { color: var(--primary); margin-left: 6px; }
.card-header p { color: var(--text-muted); font-size: 14px; margin-top: 6px; }

.social-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 24px; }
.social-btn {
  height: 50px; background: var(--input-bg); border: 1.5px solid var(--border-color);
  border-radius: 14px; display: flex; align-items: center; justify-content: center;
  cursor: pointer; transition: all 0.2s ease;
}
.social-btn:hover { border-color: var(--primary); transform: translateY(-2px); background: transparent; }
.social-btn img { width: 22px; height: 22px; }

.divider { display: flex; align-items: center; gap: 10px; margin-bottom: 24px; color: var(--text-muted); font-size: 12px; font-weight: 700; text-transform: uppercase; }
.divider::before, .divider::after { content: ""; flex: 1; height: 1px; background: var(--border-color); }

/* --- INPUTS --- */
.input-group { margin-bottom: 18px; }
.input-group label { display: block; font-size: 12px; font-weight: 700; color: var(--text-main); margin-bottom: 6px; margin-left: 4px; }
.input-wrapper { position: relative; display: flex; align-items: center; }
.input-wrapper input {
  width: 100%; padding: 14px 16px 14px 46px; background: var(--input-bg);
  border: 1.5px solid var(--border-color); border-radius: 14px; color: var(--text-main);
  font-size: 15px; transition: all 0.2s ease;
}
.input-wrapper input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 4px rgba(234, 88, 12, 0.15); }
.icon-left { position: absolute; left: 15px; color: var(--text-muted); font-size: 20px; }

/* --- CHECKBOX & FOOTER --- */
.form-footer { margin-bottom: 25px; }
.checkbox-container { display: flex; align-items: flex-start; cursor: pointer; font-size: 13px; color: var(--text-muted); }
.checkbox-container input { display: none; }
.checkmark { width: 19px; height: 19px; border: 2px solid var(--border-color); border-radius: 6px; margin-right: 10px; flex-shrink: 0; transition: 0.2s; }
.checkbox-container input:checked + .checkmark { background: var(--primary); border-color: var(--primary); }
.terms-text { line-height: 1.4; }
.terms-link { color: var(--primary); font-weight: 700; text-decoration: none; }

.submit-btn {
  width: 100%; padding: 16px; background: var(--primary); color: white; border: none;
  border-radius: 14px; font-weight: 700; font-size: 16px; cursor: pointer;
  display: flex; align-items: center; justify-content: center; gap: 10px; transition: 0.3s;
}
.submit-btn:hover:not(:disabled) { background: var(--primary-hover); transform: translateY(-2px); box-shadow: 0 10px 20px -5px rgba(234, 88, 12, 0.4); }
.submit-btn:disabled { opacity: 0.6; cursor: not-allowed; }

.login-link { text-align: center; margin-top: 25px; font-size: 14px; color: var(--text-muted); }
.login-link a { color: var(--primary); font-weight: 700; text-decoration: none; }

.theme-btn {
  position: absolute; top: 25px; right: 25px; width: 48px; height: 48px; border-radius: 14px;
  border: 1px solid var(--border-color); background: var(--card-bg); color: var(--text-main);
  cursor: pointer; z-index: 100; display: flex; align-items: center; justify-content: center; transition: 0.3s;
}
.theme-btn:hover { border-color: var(--primary); transform: rotate(15deg); }

.loader {
  width: 22px; height: 22px; border: 3px solid rgba(255,255,255,0.3); border-top-color: white;
  border-radius: 50%; animation: spin 0.8s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }
@keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
</style>