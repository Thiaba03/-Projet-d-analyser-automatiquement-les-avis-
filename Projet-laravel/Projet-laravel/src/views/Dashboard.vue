<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const isDark = ref(false)
const searchQuery = ref('')
const sortBy = ref<'recent' | 'rating'>('recent')
const loading = ref(false)
const themeData = ref<{ label: string; percent: number }[]>([])

const generatingViz = ref(false) 

// --- FONCTION EXPORT CSV CORRIGÉE ---
const generateSeabornAudit = () => {
  generatingViz.value = true
  
  try {
    // Ajout de \ufeff pour que Excel reconnaisse l'encodage UTF-8 (accents)
    let csvContent = "\ufeffID;DATE;AUTEUR;SENTIMENT;NOTE;CONTENU\n";

    reviews.value.forEach(r => {
      // On s'assure que chaque valeur existe, sinon on met "N/A"
      const id = r.id || '0';
      const date = r.date || 'Date inconnue';
      const author = r.author || 'Anonyme';
      const status = r.status || 'Neutre';
      const rating = r.rating || 0;
      const content = r.content ? r.content.replace(/"/g, '""') : '';

      const row = `${id};${date};${author};${status};${rating};"${content}"`;
      csvContent += row + "\n";
    });

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement("a");
    const url = URL.createObjectURL(blob);
    
    link.setAttribute("href", url);
    link.setAttribute("download", `AUDIT_AVIS_${new Date().toLocaleDateString().replace(/\//g, '-')}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

  } catch (error) {
    console.error("Erreur export CSV:", error);
  } finally {
    setTimeout(() => { generatingViz.value = false }, 500);
  }
}

// --- CHARGEMENT DES DONNÉES CORRIGÉ ---
const fetchDashboardData = async () => {
  loading.value = true
  try {
    const token = localStorage.getItem('token')
    if (!token) return

    const reviewsResponse = await fetch('/api/reviews', {
      headers: { 'Authorization': `Bearer ${token}` }
    })
    const allReviews = await reviewsResponse.json()

    // 1. Calcul des thèmes
    const allTopics: string[] = allReviews.flatMap((r: any) => r.topics || [])
    const topicCounts: Record<string, number> = {}
    allTopics.forEach(t => { if(t) topicCounts[t] = (topicCounts[t] || 0) + 1 })
    
    const sortedTopics = Object.entries(topicCounts).sort((a, b) => b[1] - a[1])
    const totalTopics = allTopics.length || 1
    
    themeData.value = [
      { label: sortedTopics[0]?.[0] || 'Aucun thème', percent: Math.round((sortedTopics[0]?.[1] || 0) / totalTopics * 100) },
      { label: sortedTopics[1]?.[0] || '—', percent: Math.round((sortedTopics[1]?.[1] || 0) / totalTopics * 100) },
      { label: sortedTopics[2]?.[0] || '—', percent: Math.round((sortedTopics[2]?.[1] || 0) / totalTopics * 100) }
    ]

    // 2. Transformation des avis 
    reviews.value = allReviews.map((r: any) => {
      
      const dateObj = r.created_at ? new Date(r.created_at) : null;
      const formattedDate = (dateObj && !isNaN(dateObj.getTime())) 
        ? dateObj.toLocaleDateString('fr-FR') 
        : 'Date inconnue';

      return {
        id: r.id,
        author: r.user?.name || 'Anonyme',
        content: r.content || '',
        rating: Math.round((r.score || 0) / 20),
        status: r.sentiment === 'positive' ? 'Positif' : 
                r.sentiment === 'negative' ? 'Negatif' : 'Neutre',
        date: formattedDate,
        avatar: `https://api.dicebear.com/7.x/avataaars/svg?seed=${r.id}`
      }
    })

    // 3. Stats
    const totalReviews = allReviews.length
    const avgScore = totalReviews > 0 
      ? (allReviews.reduce((sum: number, r: any) => sum + (r.score || 0), 0) / totalReviews / 20).toFixed(1)
      : "0.0"

    stats.value = [
      { label: 'Nbre avis', value: totalReviews.toLocaleString(), growth: '+5.2%', icon: 'group', trend: 'up' },
      { label: 'Note Moyenne', value: avgScore, growth: '0.1%', icon: 'star', trend: 'up' },
    ]

  } catch (error) {
    console.error('Erreur dashboard:', error)
  } finally {
    loading.value = false
  }
}

const filteredReviews = computed(() => {
  const query = searchQuery.value.toLowerCase().trim()
  let result = reviews.value.filter(r => 
    r.author.toLowerCase().includes(query) || 
    r.content.toLowerCase().includes(query)
  )
  return sortBy.value === 'rating' ? 
    result.sort((a, b) => b.rating - a.rating) : 
    result.sort((a, b) => b.id - a.id)
})

const stats = ref<any[]>([])
const reviews = ref<any[]>([])

onMounted(() => {
  isDark.value = document.documentElement.classList.contains('dark')
  fetchDashboardData()
})

const toggleTheme = (dark: boolean) => {
  isDark.value = dark
  document.documentElement.classList.toggle('dark', dark)
  localStorage.setItem('theme', dark ? 'dark' : 'light')
}

const currentPage = ref(1)
const itemsPerPage = 5
const paginatedReviews = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  return filteredReviews.value.slice(start, start + itemsPerPage)
})
const hasNextPage = computed(() => currentPage.value * itemsPerPage < filteredReviews.value.length)
</script>

<template>
  <div :class="{ 'dark': isDark }" class="dashboard-root">
    <div class="app-wrapper">
      
      <div class="bg-glow"></div>

      <header class="neo-header">
        <div class="header-content">
          <div class="brand">
            <h1 class="brand-title">Tableau <span class="accent">de Bord</span></h1>
          </div>

          <div class="search-bar">
            <span class="material-symbols-outlined search-icon">search</span>
            <input v-model="searchQuery" type="text" placeholder="Rechercher des analyses..." />
          </div>

          <div class="nav-actions">
           <div class="theme-switch">
  <button @click="toggleTheme(false)" :class="{ 'active': !isDark }">
    <span class="material-symbols-outlined">light_mode</span>
  </button>
  
  <button @click="toggleTheme(true)" :class="{ 'active': isDark }">
    <span class="material-symbols-outlined">dark_mode</span>
  </button>
</div>
            <div class="notification-btn">
              <span class="material-symbols-outlined">notifications</span>
              <span class="dot"></span>
            </div>
            <button 
  @click="generateSeabornAudit" 
  class="btn-create" 
  :disabled="generatingViz"
>
  <span class="material-symbols-outlined">
    {{ generatingViz ? 'sync' : 'monitoring' }}
  </span>
  {{ generatingViz ? 'Génération...' : 'All avis' }}
</button>
          </div>
        </div>
      </header>

      <main class="main-container">
        <div class="max-width-limit">

          <section class="stats-grid">
            <div v-for="stat in stats" :key="stat.label" class="neo-card stat-card group">
              <div class="stat-icon-bg">
                <span class="material-symbols-outlined">{{ stat.icon }}</span>
              </div>
              <div class="stat-info">
                <p class="stat-label">{{ stat.label }}</p>
                <div class="stat-value-row">
                  <h3 class="stat-number">{{ stat.value }}</h3>
                  <span :class="['trend-tag', stat.trend]">
                    <span class="material-symbols-outlined">{{ stat.trend === 'up' ? 'trending_up' : 'trending_down' }}</span>
                    {{ stat.growth }}
                  </span>
                </div>
                <div class="stat-chart-deco">
                  <div v-if="stat.icon === 'group'" class="health-monitor">
                    <div class="pulse-dot"></div>
                    <div class="activity-bars">
                      <div v-for="i in 6" :key="i" class="bar"></div>
                    </div>
                  </div>
                  
                  <div v-else-if="stat.icon === 'star'" class="star-rating">
                    <span v-for="i in 5" :key="i" class="material-symbols-outlined icon-filled">star</span>
                  </div>
                  <div v-else class="progress-track">
                    <div class="progress-fill" style="width: 45%"></div>
                  </div>
                </div>
              </div>
            </div>
          </section>

          <section class="charts-layout">
            <div class="neo-card main-chart">
              <div class="chart-header">
                <div class="title-group">
                  <h3>Engagement Utilisateur</h3>
                  <div class="stats-mini">
                    <span class="val">24.5k</span> <span class="lab">Sessions</span>
                  </div>
                </div>
                <select class="neo-select">
                  <option>30 derniers jours</option>
                  <option>7 derniers jours</option>
                </select>
              </div>
              <div class="chart-body">
                <svg viewBox="0 0 500 150" class="spline-svg">
                  <defs>
                    <linearGradient id="chartFill" x1="0" x2="0" y1="0" y2="1">
                      <stop offset="0%" stop-color="#ea580c" stop-opacity="0.2"></stop>
                      <stop offset="100%" stop-color="#ea580c" stop-opacity="0"></stop>
                    </linearGradient>
                  </defs>
                  <path d="M0,120 Q50,100 100,110 T200,80 T300,90 T400,40 T500,60 V150 H0 Z" fill="url(#chartFill)"></path>
                  <path d="M0,120 Q50,100 100,110 T200,80 T300,90 T400,40 T500,60" fill="none" stroke="#ea580c" stroke-width="3" stroke-linecap="round"></path>
                  <circle cx="400" cy="40" r="5" fill="white" stroke="#ea580c" stroke-width="3"></circle>
                </svg>
                <div class="x-axis">
                  <span>Lun</span><span>Mar</span><span>Mer</span><span>Jeu</span><span>Ven</span><span>Sam</span><span>Dim</span>
                </div>
              </div>
            </div>

            <div class="neo-card donut-chart-card">
          <h3>Thèmes de Retours</h3>
          <div class="donut-container">
            <svg viewBox="0 0 100 100" class="donut-svg">
              <circle cx="50" cy="50" r="40" class="base" />
              <circle cx="50" cy="50" r="40" class="segment primary" style="stroke-dasharray: 150.8 251.3;" />
              <circle cx="50" cy="50" r="40" class="segment secondary" style="stroke-dasharray: 62.8 251.3; stroke-dashoffset: -150.8;" />
              <circle cx="50" cy="50" r="40" class="segment tertiary" style="stroke-dasharray: 37.7 251.3; stroke-dashoffset: -213.6;" />
            </svg>
            
            <! CENTRE DONUT = THÈME #1 >
            <div class="donut-center">
              <span class="percent">{{ themeData[0]?.label || 'Aucun thème' }}</span>
              <span class="label">{{ themeData[0]?.percent || 0 }}%</span>
            </div>
          </div>
          
          <! LISTE TOP 3 >
          <div class="theme-list">
            <div v-for="(t, idx) in themeData" :key="t.label + idx" class="theme-row">
              <div class="theme-label-group">
                <span :class="['dot', idx === 0 ? 'p' : idx === 1 ? 's' : 't']"></span>
                {{ t.label }}
              </div>
              <strong>{{ t.percent }}%</strong>
            </div>
          </div>
        </div>
</section>

          <section class="neo-card table-wrapper">
            <div class="table-header" style="display: flex;justify-content: space-between;">
              <h3 style="font-weight: 500;">Avis Récents</h3>
              <div class="actions">
                <select v-model="sortBy" class="neo-select-sm">
                  <option value="recent" style="font-weight: 600;">Récents</option>
                  <option value="rating" style="font-weight: 600;">Mieux notés</option>
                </select>
                <button class="btn-link">Voir tout</button>
              </div>
            </div>
            <div class="scroll-area">
              <table class="neo-table">
                <thead>
                  <tr style="background-color: #F1F5F9;">
                    <th>Utilisateur</th><th>Note</th><th>Avis</th><th>Date</th><th>Statut</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="r in paginatedReviews" :key="r.id">
                    <td class="user-cell">
                      <img :src="r.avatar" /> <span>{{ r.author }}</span>
                    </td>
                    <td>
                      <div class="stars">
                        <span v-for="i in 5" :key="i" class="material-symbols-outlined" :class="{'icon-filled': i <= r.rating}">star</span>
                      </div>
                    </td>
                    <td class="review-text">"{{ r.content }}"</td>
                    <td class="date-text">{{ r.date }}</td>
                    <td><span :class="['status-badge', r.status.toLowerCase()]">{{ r.status }}</span>
                    </td>
                  </tr>
                </tbody>
              </table>
              
              <div class="dashboard-pagination">
                <button
                  class="btn-page"
                  :disabled="currentPage === 1"
                  @click="currentPage--">
                  Précédent
                </button>

                <button
                  class="btn-page"
                  :disabled="!hasNextPage"
                  @click="currentPage++">
                  Suivant
                </button>
              </div>
            </div>
          
          </section>  


          <footer class="app-footer">
            © 2025 NEO UI Project • Version 2.0.1
          </footer>
        </div>
      </main>
    </div>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap');

/* ÉTOILES DASHBOARD */
.stars {
  color: #facc15 !important; /* Jaune doré */
  display: flex;
  gap: 2px;
}

/* ÉTOILE VIDE */
.stars .material-symbols-outlined {
  font-variation-settings: 'FILL' 0, 'wght' 400 !important;
  color: #d1d5db !important; /* Gris clair */
}

/* ÉTOILE REMPLIE */
.stars .icon-filled {
  font-variation-settings: 'FILL' 1, 'wght' 600 !important;
  color: #facc15 !important; /* Jaune doré */
}


/* REMPLACE TOUT ton CSS status-badge par ça */
.status-badge {
  font-size: 0.65rem;
  font-weight: 700;
  text-transform: uppercase;
  padding: 4px 10px;
  border-radius: 8px !important;
}

/* POSITIF */
.status-badge[class*="posit"] {
  background:  #166534 !important;
  color: #dcfce7 !important;
  border: 1px solid #aae9c1 !important;
}

/* NÉGATIF */
.status-badge[class*="negat"] {
  background: #f34a4a !important;
  color: #f9f0f0 !important;
  border: 1px solid #f59494 !important;
}

/* NEUTRE */
.status-badge[class*="neut"] {
  background: #92400e !important;
  color: #fef3c7 !important;
  border: 1px solid #f59e0b !important;
}




.dashboard-pagination {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  margin-top: 8px;
}

.btn-page {
  min-width: 80px;
  height: 32px;
  padding: 0 12px;
  border-radius: 999px;
  border: 1px solid #e2e8f0;
  background: #ffffff;
  font-size: 0.8rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.15s ease;
}

.btn-page:hover:not(:disabled) {
  background: #f8fafc;
  border-color: #cbd5e1;
}

.btn-page:disabled {
  opacity: 0.5;
  cursor: default;
}

.dark .btn-page {
  background: #0f172a;
  border-color: #1e293b;
  color: #e2e8f0;
}

.dark .btn-page:hover:not(:disabled) {
  background: #020617;
}



.dark .status-badge.positive {
  background: rgba(34, 197, 94, 0.2);
  color: #4ade80;
  border-color: rgba(34, 197, 94, 0.4);
}

.dark .status-badge.negatif {
  background: rgba(239, 68, 68, 0.2);
  color: #f87171;
  border-color: rgba(239, 68, 68, 0.4);
}

.dark .status-badge.neutre {
  background: rgba(251, 191, 36, 0.2);
  color: #fbbf24;
  border-color: rgba(251, 191, 36, 0.4);
}



.dashboard-root {
  --primary: #ea580c;
  --secondary: #fb923c;
  --tertiary: #fdba74;
  --bg-light:  #F1F5F9;
  --card-light: #ffffff;
  --border-light: rgba(0, 0, 0, 0.05);
  --text-light: #1e293b;
  font-family: 'Roboto', sans-serif;
}
.dark .dashboard-root {
  --bg-dark: #0f172a;         
  --card-dark: #1e293b;       
  --border-dark: #1e293b;     
  --primary:#ea580c;         
  --text-dark: #f8fafc;
  --text-dim: #94a3b8;
}
.health-monitor { display: flex; align-items: center; gap: 12px; height: 50px;width: 200px; }
.pulse-dot { 
  width: 8px; height: 8px; background: #16a34a; border-radius: 50%; 
  box-shadow: 0 0 0 rgba(22, 163, 74, 0.4); animation: pulse 2s infinite; 
}
@keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(22, 163, 74, 0.7); } 70% { box-shadow: 0 0 0 10px rgba(22, 163, 74, 0); } 100% { box-shadow: 0 0 0 0 rgba(22, 163, 74, 0); } }
.activity-bars { display: flex; align-items: flex-end; gap: 2px; height: 20px; }
.bar { width: 3px; background: var(--primary); border-radius: 2px; animation: dance 1s infinite alternate; }
.bar:nth-child(2) { animation-delay: 0.1s; }
.bar:nth-child(3) { animation-delay: 0.3s; }
.bar:nth-child(4) { animation-delay: 0.2s; }
@keyframes dance { from { height: 20%; } to { height: 100%; } }

.app-wrapper { min-height: 100vh; background-color: #F1F5F9; color: var(--text-light); transition: all 0.4s ease; position: relative; overflow: hidden; }
.dark .app-wrapper { background-color: var(--bg-dark); color: var(--text-dark); }
.neo-header { height: 80px; background: white; backdrop-filter: blur(12px); border-bottom: 1px solid var(--border-light); position: sticky; top: 0; z-index: 50; }
.dark .neo-header { 
  background: var(--card-dark); 
  
 
  border-bottom: 1px solid var(--border-dark); 
  backdrop-filter: none; 
}.header-content { max-width: 1400px; margin: 0 auto; height: 100%; padding: 0 32px; display: flex; align-items: center; justify-content: space-between; }
.brand-title { font-size: 1.5rem; font-weight: 900; letter-spacing: -0.5px; }
.accent { color: var(--primary); }
.search-bar { max-width: 400px; position: relative; margin-left: auto; margin-right: 24px; }
.search-bar input { width: 100%; padding: 10px 16px 10px 44px; border-radius: 10px; border: none; background:#F1F5F9; font-size: 0.875rem; transition: all 0.2s; font-family: inherit; }
.dark .search-bar input { background: rgba(255, 255, 255, 0.05); color: white; }
.search-icon { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
.nav-actions { display: flex; align-items: center; gap: 16px; }
.theme-switch { display: flex; background: rgba(0,0,0,0.04); border-radius: 99px; padding: 4px; gap: 4px; }
.dark .theme-switch { background: rgba(255,255,255,0.05); }
.theme-switch button { width: 32px; height: 32px; border: none; background: transparent; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #94a3b8; cursor: pointer; }
.theme-switch button.active { background: white; color: var(--primary); box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
.dark .theme-switch button.active { background: var(--primary); color: white; }
.user-pill img { width: 36px; height: 36px; border-radius: 50%; border: 2px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
.main-container { padding: 32px; position: relative; z-index: 1; }
.max-width-limit { max-width: 1280px; margin: 0 auto; display: flex; flex-direction: column; gap: 32px; }
.page-heading { display: flex; justify-content: space-between; align-items: center; }
.page-heading h1 { font-size: 1.5rem;font-weight: 700;}
.page-heading p { color: #64748b; margin-top: 4px; font-weight: 400; }
.btn-create { background: var(--primary); color: white; padding: 12px 24px; border-radius: 12px; border: none; font-weight: 700; display: flex; align-items: center; gap: 8px; cursor: pointer; transition: transform 0.2s; font-family: inherit; }
.btn-create:hover { transform: translateY(-2px); }
.neo-card { background: var(--card-light); border: 1px solid var(--border-light); border-radius: 40px; padding:  20px; transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }
.dark .neo-card { background: var(--card-dark); border: 1px solid var(--border-dark); }
.neo-card:hover { transform: translateY(-4px); box-shadow: 0 10px 30px -5px rgba(234, 88, 12, 0.15); }
.neo-card.stat-card {
  padding: 20px 20px;
  border-radius: 25px;
}
.stat-card .stat-icon-bg {
  width: 38px;
  height: 38px;
  margin-bottom: 8px; 
}
.stat-card .stat-number {
  font-size: 1.5rem;
  font-weight: 800;
}
.stat-card .stat-chart-deco {
  height: 25px;
  margin-top: 10px;
}
.stat-card .health-monitor {
  height: 25px;
}
.stat-card .activity-bars {
  height: 15px;
}
.search-bar input {
  width: 100%;
  padding: 10px 16px 10px 44px;
  border-radius: 10px;
  border: 1px solid transparent; 
  background: #F1F5F9;
  font-size: 0.875rem;
  transition: all 0.2s;
  font-family: inherit;
}

.search-bar input:focus {
  outline: none; 
  border: 1px solid #cbd5e1; 
  background: #ffffff; 
}

.dark .search-bar input:focus {
  border-color: #475569; 
  background: rgba(255, 255, 255, 0.1);
}

.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; }
.stat-icon-bg { width: 48px; height: 48px; background: rgba(234, 88, 12, 0.1); color: var(--primary); border-radius: 14px; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; }
.stat-label { font-size: 0.875rem; font-weight: 500; color: #64748b; }
.stat-value-row { display: flex; align-items: baseline; gap: 12px; margin-top: 4px; }
.stat-number { font-size: 2.5rem; font-weight: 900; }
.trend-tag { font-size: 0.75rem; font-weight: 700; padding: 4px 10px; border-radius: 99px; display: flex; align-items: center; gap: 4px; }
.trend-tag.up { background: #f0fdf4; color: #16a34a; }
.trend-tag.down { background: #fef2f2; color: #dc2626; }
.stat-chart-deco { height: 40px; margin-top: 16px; }
.star-rating { color: #facc15; display: flex; gap: 2px; }
.progress-track { height: 6px; background: rgba(0,0,0,0.05); border-radius: 99px; margin-top: 20px; overflow: hidden; }
.progress-fill { height: 100%; background: var(--primary); }
.charts-layout { display: grid; grid-template-columns: 2fr 1fr; gap: 24px; }
.main-chart { display: flex; flex-direction: column; }
.chart-header { display: flex; justify-content: space-between; margin-bottom: 24px; }
.chart-header h3 { font-weight: 700; }
.stats-mini .val { font-size: 1.5rem; font-weight: 900; color: var(--primary); }
.stats-mini .lab { font-size: 0.875rem; color: #64748b; }
.neo-select { border: none; background: #f1f5f9; padding: 8px 12px; border-radius: 8px; font-weight: 500; font-family: inherit; }
.dark .neo-select { background: rgba(255,255,255,0.05); color: #cbd5e1; }
.chart-body { margin-top: auto; }
.spline-svg { width: 100%; height: 180px; overflow: visible; }
.x-axis { display: flex; justify-content: space-between; font-size: 0.75rem; color: #94a3b8; margin-top: 16px; padding: 0 10px; }
.donut-container { position: relative; width: 180px; margin: 30px auto; }
.donut-svg { transform: rotate(-90deg); width: 100%; height: 100%; }
.donut-svg circle { fill: none; stroke-width: 10; stroke-linecap: round; }
.donut-svg .base { stroke: rgba(0,0,0,0.04); }
.dark .donut-svg .base { stroke: rgba(255, 255, 255, 0.04); }
.donut-svg .segment.primary { stroke: var(--primary); }
.donut-svg .segment.secondary { stroke: var(--secondary); }
.donut-svg .segment.tertiary { stroke: var(--tertiary); }
.donut-center { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); display: flex; flex-direction: column; align-items: center; }
.donut-center .percent { font-size: 1.5rem; font-weight: 900; color: var(--text-light); }
.dark .donut-center .percent { color: white; }
.donut-center .label { font-size: 0.75rem; color: #94a3b8; font-weight: 500; }
.theme-list { display: flex; flex-direction: column; gap: 12px; margin-top: 20px; }
.theme-row { display: flex; align-items: center; font-size: 0.875rem; gap: 8px; color: #64748b; justify-content: space-between; }
.theme-label-group { display: flex; align-items: center; gap: 8px; font-weight: 500; }
.theme-row strong { color: var(--text-light); font-weight: 700; }
.dark .theme-row strong { color: white; }
.dot { width: 8px; height: 8px; border-radius: 50%; }
.dot.p { background: var(--primary); }
.dot.s { background: var(--secondary); }
.dot.t { background: var(--tertiary); }
.neo-table { width: 100%; border-collapse: collapse; }
.neo-table th { text-align: left; padding: 16px; font-size: 0.75rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; border-bottom: 1px solid var(--border-light); }
.neo-table td { padding: 20px 16px; border-bottom: 1px solid var(--border-light); font-size: 0.875rem; font-weight: 400; }
.dark .neo-table th, .dark .neo-table td { border-bottom-color: var(--border-dark); }
.user-cell { display: flex; align-items: center; gap: 12px; font-weight: 500; }
.user-cell img { width: 32px; height: 32px; border-radius: 50%; background: #f1f5f9; }
.stars { color: #facc15; }
.status-badge { font-size: 0.65rem; font-weight: 700; text-transform: uppercase; padding: 4px 10px; border-radius: 8px; }
.status-badge.approved { background: #dcfce7; color: #166534; }
.status-badge.pending { background: #fef3c7; color: #92400e; }
.app-footer { text-align: center; font-size: 0.75rem; font-weight: 500; color: #94a3b8; letter-spacing: 2px; padding: 40px 0; }
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400; }
.icon-filled { font-variation-settings: 'FILL' 1; }

.dark .neo-card {
  background: #1e293b;
  border: 1px solid var(--border-dark) !important;
  backdrop-filter: blur(8px);
}

.dark .neo-card:hover {
  border-color: rgba(249, 115, 22, 0.4) !important; 
  box-shadow: 0 0 20px rgba(249, 115, 22, 0.05);
}

.dark .neo-header {
  background: #1e293b!important;
  backdrop-filter: blur(12px);
  border-bottom: 1px solid var(--border-dark);
}
.dark .neo-table th {
  background: #1e293b;
  color: var(--text-dim);
}

.dark .neo-table td {
  border-bottom: 1px solid #1e293b;
}

.dark .status-badge.approved {
  background: rgba(34, 197, 94, 0.15);
  color: #4ade80;
  border: 1px solid rgba(34, 197, 94, 0.3);
}

.dark .status-badge.pending {
  background: rgba(234, 179, 8, 0.15);
  color: #fbbf24;
  border: 1px solid rgba(234, 179, 8, 0.3);
}

@media (max-width: 1024px) { .charts-layout { grid-template-columns: 1fr; } }

@media (max-width: 768px) {
  .header-content {
    padding: 0 16px;
    gap: 10px;
  }

  .btn-create span { margin: 0; }
  .btn-create {
    padding: 10px;
    border-radius: 50%; 
  }
  .btn-create { font-size: 0; } 
  .btn-create .material-symbols-outlined { font-size: 24px; }

  .search-bar {
    margin-right: 8px;
  }
  .search-bar input {
    width: 40px; 
    padding: 10px;
  }
  .search-bar input:focus {
    width: 150px;
  }
  .search-icon { position: static; display: none; } 
}
@media (max-width: 640px) {
  .main-container {
    padding: 16px; 
  }

  .stats-grid {
    grid-template-columns: 1fr; 
  }

  .charts-layout {
    grid-template-columns: 1fr; 
  }
  
  .stat-number {
    font-size: 1.8rem; 
  }
}
.scroll-area {
  width: 100%;
  overflow-x: auto; 
  -webkit-overflow-scrolling: touch;
}

@media (max-width: 600px) {
  .neo-table th:nth-child(4), 
  .neo-table td:nth-child(4),
  .neo-table th:nth-child(3), 
  .neo-table td:nth-child(3) {
    display: none;
  }
}@media (max-width: 480px) {
  .neo-card {
    border-radius: 20px; 
    padding: 16px;
  }
  
  .brand-title {
    font-size: 1.1rem;
  }
  @media (max-width: 768px) {
  .brand {
    display: flex;
    align-items: center;
    min-width: fit-content; 
  }

  .brand-title {
    font-size: 1rem; 
    white-space: nowrap; 
  }
}
@media (max-width: 600px) {
  .search-bar {
    display: none; 
  }
  
  .search-icon-mobile {
    display: block;
    color: var(--text-light);
  }
}@media (max-width: 600px) {
  .nav-actions {
    gap: 8px; 
  }

 .theme-switch {
    background: transparent;
  }
  
  .theme-switch button:not(.active) {
    display: none; 
  }

  .btn-create {
    width: 40px;
    height: 40px;
    justify-content: center;
    border-radius: 50%;
    padding: 0;
  }
}
}

.btn-audit-executive {
  background: #1e293b; /* Dark Slate */
  color: #f8fafc;
  border: 2px solid #334155;
  padding: 12px 24px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  gap: 16px;
  cursor: pointer;
  transition: all 0.3s;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.btn-audit-executive:hover {
  background: #0f172a;
  border-color: #38bdf8; /* Accent bleu cyan */
  transform: translateY(-2px);
}

.btn-text {
  display: flex;
  flex-direction: column;
  text-align: left;
}

.title {
  font-weight: 800;
  font-size: 0.95rem;
  letter-spacing: 0.05em;
}

.subtitle {
  font-size: 0.75rem;
  color: #94a3b8;
}
</style>