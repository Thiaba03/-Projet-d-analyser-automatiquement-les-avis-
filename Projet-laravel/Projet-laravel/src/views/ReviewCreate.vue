<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router' 

const router = useRouter() 

const form = reactive({
  rating: 5,
  content: '',
  useAI: true
})

const isSubmitting = ref(false)
const isDark = ref(false)
const errorMessage = ref('')

// Synchronisation au chargement avec la racine HTML
onMounted(() => {
  isDark.value = document.documentElement.classList.contains('dark')
})

const toggleTheme = () => {
  isDark.value = !isDark.value
  document.documentElement.classList.toggle('dark', isDark.value)
  localStorage.setItem('theme', isDark.value ? 'dark' : 'light')
}

// Changement dynamique de couleur selon la note (purement visuel pour l'utilisateur)
const sentimentTheme = computed(() => {
  if (form.rating <= 2) return { color: '#FF4D4D', label: 'Déçu', icon: 'sentiment_dissatisfied' }
  if (form.rating <= 3) return { color: '#FFB347', label: 'Correct', icon: 'sentiment_neutral' }
  return { color: '#ea580c', label: 'Adoré', icon: 'sentiment_very_satisfied' }
})

const setRating = (val) => { form.rating = val }

const handleSubmit = async () => {
  if (form.content.length < 10) {
    errorMessage.value = 'Votre avis doit faire au moins 10 caractères'
    return
  }
  
  const token = localStorage.getItem('token')
  if (!token) {
    errorMessage.value = 'Veuillez vous connecter d\'abord'
    router.push('/login')
    return
  }
  
  isSubmitting.value = true
  errorMessage.value = ''
  
  try {
    // APPEL API : On envoie seulement le contenu. 
    // L'IA (ReviewAnalyzer.php) détectera les services (topics) automatiquement.
    const response = await fetch('http://localhost:8000/api/reviews', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify({ 
        content: form.content 
      })
    })
    
    const data = await response.json()
    
    if (response.ok) {
      alert(` Succès ! L'IA a détecté un sentiment ${data.sentiment} et les thèmes : ${data.topics.join(', ')}`)
      router.push('/dashboard') 
    } else {
      errorMessage.value = data.message || 'Erreur création avis'
    }
  } catch (error) {
    console.error('Erreur:', error)
    errorMessage.value = 'Erreur réseau : vérifiez que votre backend Laravel est lancé.'
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <div class="app-shell" :class="{ 'dark': isDark }" :style="{ '--accent-dynamic': sentimentTheme.color }">
    <main class="main-content">
      <header class="form-header animate-slide-down">
        <button @click="toggleTheme" class="action-btn" type="button">
          <span class="material-symbols-outlined">{{ isDark ? 'light_mode' : 'dark_mode' }}</span>
        </button>
        
        <div class="header-center">
          <div class="sentiment-pill" :style="{ color: sentimentTheme.color, borderColor: sentimentTheme.color + '40' }">
            <span class="material-symbols-outlined">{{ sentimentTheme.icon }}</span>
            <span class="pill-text">{{ sentimentTheme.label }}</span>
          </div>
          <h1>Nouvel Avis</h1>
        </div>

        <router-link to="/dashboard" class="action-btn">
          <span class="material-symbols-outlined">close</span>
        </router-link>
      </header>

      <form @submit.prevent="handleSubmit" class="modern-card animate-scale-up">
        <div v-if="errorMessage" class="error-box">
          {{ errorMessage }}
        </div>

        <div class="rating-section">
          <label>Votre note globale</label>
          <div class="rating-group">
            <button v-for="i in 5" :key="i" type="button" 
              @click="setRating(i)" 
              class="star-pill"
              :class="{ 'active': i <= form.rating }"
            >
              <span class="material-symbols-outlined">star</span>
            </button>
          </div>
        </div>

        <div class="input-group">
          <div class="label-row">
            <label>Votre expérience</label>
            <span class="char-count" :class="{ 'limit': form.content.length > 450 }">
              {{ form.content.length }} / 500
            </span>
          </div>
          <div class="textarea-container">
            <textarea 
              v-model="form.content" 
              placeholder="Dites-nous ce que vous avez pensé du produit, du prix ou de la livraison..."
              maxlength="500"
              required
            ></textarea>
          </div>
          <p class="helper-text">L'IA détectera automatiquement les sujets abordés dans votre texte.</p>
        </div>

        <div class="ai-module active">
          <div class="ai-info">
            <div class="ai-icon">
              <span class="material-symbols-outlined">auto_awesome</span>
            </div>
            <div>
              <h3>Analyse Intelligente</h3>
              <p>Classification automatique par IA activée</p>
            </div>
          </div>
          <div class="ai-status">ON</div>
        </div>

        <button type="submit" class="btn-submit" :disabled="isSubmitting || form.content.length < 10">
          <span v-if="!isSubmitting">Publier l'avis</span>
          <span v-else class="loader"></span>
        </button>
      </form>
    </main>
  </div>
</template>


<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

.app-shell {
  --accent: var(--accent-dynamic, #ea580c);
  --bg: #F8FAFC;
  --surface: #FFFFFF;
  --text-main: #0F172A;
  --text-sub: #64748B;
  --border-light: #F1F5F9;
  --shadow: 0 20px 50px rgba(0,0,0,0.05);

  min-height: 100vh;
  background: var(--bg);
  color: var(--text-main);
  font-family: 'Plus Jakarta Sans', sans-serif;
  display: flex;
  justify-content: center;
  padding: 3rem 1.5rem;
  transition: all 0.4s ease;
}

/* Changement : Cible .dark au lieu de .dark-theme */
.app-shell.dark {
  --bg: #0F172A;
  --surface: #1E293B;
  --text-main: #F8FAFC;
  --text-sub: #94A3B8;
  --border-light: #334155;
  --shadow: 0 20px 50px rgba(0,0,0,0.3);
}

.main-content { width: 100%; max-width: 540px; }

/* Header */
.form-header {
  display: flex; justify-content: space-between; align-items: center; margin-bottom: 2.5rem;
}

.header-center { text-align: center; }
.header-center h1 { font-size: 1.5rem; font-weight: 800; margin-top: 0.75rem; letter-spacing: -0.5px; }

.sentiment-pill {
  display: inline-flex; align-items: center; gap: 8px; padding: 6px 16px; 
  border-radius: 100px; border: 1.5px solid; font-weight: 700; font-size: 0.85rem;
  transition: all 0.3s ease;
}

.action-btn {
  width: 48px; height: 48px; border-radius: 14px; border: none;
  background: var(--surface); color: var(--text-main); cursor: pointer;
  display: grid; place-items: center; box-shadow: var(--shadow); transition: 0.2s;
}
.action-btn:hover { transform: scale(1.05); }

/* Card */
.modern-card {
  background: var(--surface); padding: 2.5rem; border-radius: 30px;
  box-shadow: var(--shadow); display: flex; flex-direction: column; gap: 2rem;
}

.rating-section label, .input-group label {
  display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-sub);
  margin-bottom: 1rem; text-transform: uppercase; letter-spacing: 0.5px;
}

.rating-group { display: flex; gap: 10px; justify-content: center; }
.star-pill {
  background: var(--bg); border: none; width: 50px; height: 50px; border-radius: 15px;
  cursor: pointer; color: var(--text-sub); transition: all 0.3s ease;
}
.star-pill.active { background: #ea580c; color: #FFFFFF; transform: scale(1.1); box-shadow: 0 10px 20px rgba(255, 215, 0, 0.3); }

/* Inputs */
.select-wrapper, .textarea-container {
  position: relative; background: var(--bg); border-radius: 16px; transition: 0.3s;
}

select, textarea {
  width: 100%; background: transparent; border: 2px solid transparent; 
  padding: 1rem 1.25rem; font-family: inherit; font-size: 1rem; color: var(--text-main);
  outline: none; border-radius: 16px;
}

select:focus, textarea:focus { border-color: var(--accent); }

textarea { min-height: 120px; }

.icon-chevron { position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); pointer-events: none; }

/* AI Module */
.ai-module {
  display: flex; justify-content: space-between; align-items: center;
  padding: 1.25rem; border-radius: 20px; background: var(--bg);
  border: 1px solid transparent; transition: 0.3s;
}
.ai-module.active { border-color: var(--accent); background: linear-gradient(145deg, var(--surface), var(--bg)); }

.ai-info { display: flex; gap: 15px; align-items: center; }
.ai-icon { color: var(--accent); }
.ai-info h3 { font-size: 0.9rem; font-weight: 700; margin: 0; }
.ai-info p { font-size: 0.75rem; color: var(--text-sub); margin: 0; }

/* Switch */
.modern-switch { position: relative; width: 48px; height: 26px; }
.modern-switch input { opacity: 0; width: 0; height: 0; }
.slider {
  position: absolute; cursor: pointer; inset: 0; background: #CBD5E1;
  transition: .4s; border-radius: 34px;
}
.slider:before {
  position: absolute; content: ""; height: 18px; width: 18px; left: 4px; bottom: 4px;
  background: white; transition: .4s; border-radius: 50%;
}
input:checked + .slider { background: var(--accent); }
input:checked + .slider:before { transform: translateX(22px); }

/* Button */
.btn-submit {
  background: var(--accent); color: white; border: none; padding: 1.25rem;
  border-radius: 18px; font-weight: 800; font-size: 1rem; cursor: pointer;
  transition: all 0.3s ease; box-shadow: 0 15px 30px rgba(234, 88, 12, 0.2);
}
.btn-submit:hover:not(:disabled) { transform: translateY(-3px); box-shadow: 0 20px 40px rgba(234, 88, 12, 0.3); }
.btn-submit:disabled { opacity: 0.4; cursor: not-allowed; }

/* Utils */
.label-row { display: flex; justify-content: space-between; align-items: center; }
.char-count { font-size: 0.7rem; font-weight: 700; color: var(--text-sub); }
.char-count.limit { color: #EF4444; }

.loader {
  width: 20px; height: 20px; border: 3px solid rgba(255,255,255,0.3);
  border-top-color: #fff; border-radius: 50%; animation: spin 0.8s linear infinite; display: inline-block;
}

/* Animation sélecteurs */
@keyframes spin { to { transform: rotate(360deg); } }
@keyframes slideDown { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
@keyframes scaleUp { from { opacity: 0; transform: scale(0.98); } to { opacity: 1; transform: scale(1); } }
.animate-slide-down { animation: slideDown 0.5s ease-out; }
.animate-scale-up { animation: scaleUp 0.4s ease-out; }

</style>