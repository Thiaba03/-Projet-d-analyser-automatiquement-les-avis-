<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

interface Review {
  id: number
  author: string
  avatar: string
  comment: string
  sentiment: string
  rating: number
  topic: string
  date: string
  score: number
  fullContent: string
}

const reviews = ref<Review[]>([])
const loading = ref(false)
const searchQuery = ref('')
const currentPage = ref(1)
const itemsPerPage = 5
const isDark = ref(false)
const userRole = ref('user')
const deletingReview = ref<number | null>(null)

//  CHARGEMENT USER + REVIEWS
const fetchUserAndReviews = async () => {
  const token = localStorage.getItem('token')
  if (!token) {
    router.push('/login')
    return
  }

  loading.value = true
  try {
    // 1. Récupère USER (rôle)
    const userResponse = await fetch('/api/user', {
      headers: {
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`
      }
    })
    
    if (userResponse.ok) {
      const userData = await userResponse.json()
      userRole.value = userData.role || 'user'
    }

    // 2. Récupère REVIEWS
    const reviewsResponse = await fetch('/api/reviews', {
      headers: {
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`
      }
    })
    
    if (reviewsResponse.ok) {
      const data = await reviewsResponse.json()
      reviews.value = Array.isArray(data) ? data.map((r: any) => ({
        id: r.id,
        author: r.user?.name || 'Anonyme',
        avatar: `https://i.pravatar.cc/150?u=${r.id}`,
        comment: (r.content || '').substring(0, 80) + ((r.content || '').length > 80 ? '...' : ''),
        sentiment: (r.sentiment || 'neutre').charAt(0).toUpperCase() + (r.sentiment || 'neutre').slice(1),
        rating: Math.round((r.score || 0) / 20),
        topic: Array.isArray(r.topics) ? r.topics.join(', ') : 'Général',
        date: r.created_at ? new Date(r.created_at).toLocaleDateString('fr-FR') : 'N/A',
        score: r.score || 0,
        fullContent: r.content || ''
      })) : []
    }
  } catch (error) {
    console.error('Erreur:', error)
  } finally {
    loading.value = false
  }
}

// Stats DYNAMIQUES
const stats = computed(() => {
  const reviewCount = reviews.value.length
  const total = Math.max(1, reviewCount)
  
  return [
    { label: 'Total Avis', value: reviewCount.toLocaleString(), growth: '+12%', icon: 'analytics', color: 'orange' },
    { label: 'Positifs', value: `${Math.round(reviews.value.filter(r => r.sentiment.toLowerCase() === 'positive').length / total * 100)}%`, growth: '+5%', icon: 'add_reaction', color: 'emerald' },
    { label: 'Neutres', value: `${Math.round(reviews.value.filter(r => r.sentiment.toLowerCase() === 'neutral').length / total * 100)}%`, growth: '0%', icon: 'face', color: 'amber' },
    { label: 'Négatifs', value: `${Math.round(reviews.value.filter(r => r.sentiment.toLowerCase() === 'negative').length / total * 100)}%`, growth: '-1%', icon: 'mood_bad', color: 'rose' }
  ]
})

// Pagination/Filtres 
const filteredReviews = computed(() => {
  const query = searchQuery.value.toLowerCase().trim()
  return reviews.value.filter(r => 
    (r.author || '').toLowerCase().includes(query) || 
    (r.topic || '').toLowerCase().includes(query) ||
    (r.comment || '').toLowerCase().includes(query)
  )
})

const totalPages = computed(() => Math.ceil((filteredReviews.value?.length || 0) / itemsPerPage))
const paginatedReviews = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  return filteredReviews.value.slice(start, start + itemsPerPage)
})


const deleteReview = async (reviewId: number) => {
  const reviewAuthor = paginatedReviews.value.find(r => r.id === reviewId)?.author || 'cet utilisateur'
  if (!confirm(`Supprimer l'avis de ${reviewAuthor} ?`)) return
  
  deletingReview.value = reviewId
  const token = localStorage.getItem('token')
  
  try {
    const response = await fetch(`/api/reviews/${reviewId}`, {
      method: 'DELETE',
      headers: {
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`
      }
    })
    
    if (response.ok) {
      reviews.value = reviews.value.filter(r => r.id !== reviewId)
      alert(' Avis supprimé avec succès !')
    } else {
      const error = await response.json()
      alert(` Erreur: ${error.message || 'Suppression échouée'}`)
    }
  } catch (error) {
    alert(' Erreur réseau')
  } finally {
    deletingReview.value = null
  }
}

watch(searchQuery, () => { 
  currentPage.value = 1 
})

const exportCSV = () => {
  const safeReviews = Array.isArray(filteredReviews.value) ? filteredReviews.value : []
  const headers = ['Auteur', 'Date', 'Sujet', 'Commentaire', 'Score', 'Sentiment']
  const rows = safeReviews.map(r => [
    r.author || '', 
    r.date || '', 
    r.topic || '', 
    `"${r.fullContent || ''}"`, 
    r.score || 0, 
    r.sentiment || ''
  ])
  const content = [headers, ...rows].map(e => e.join(",")).join("\n")
  const blob = new Blob([content], { type: 'text/csv;charset=utf-8;' })
  const link = document.createElement("a")
  link.href = URL.createObjectURL(blob)
  link.download = `avis_${new Date().toISOString().split('T')[0]}.csv`
  link.click()
}

onMounted(() => {
  isDark.value = document.documentElement.classList.contains('dark')
  fetchUserAndReviews()
})

const toggleTheme = () => { 
  isDark.value = !isDark.value
  if (isDark.value) {
    document.documentElement.classList.add('dark')
    localStorage.setItem('theme', 'dark')
  } else {
    document.documentElement.classList.remove('dark')
    localStorage.setItem('theme', 'light')
  }
}
</script>

<template>
  <div class="dashboard-wrapper" :class="{ 'dark': isDark }">
    <!-- Loading spinner -->
    <div v-if="loading" class="loading-overlay">
      <div class="loader">Chargement des avis...</div>
    </div>
    
    <header class="main-header">
      <div class="header-content">
        <h1 class="main-title">Liste<span class="accent"> des Avis</span></h1>
        <div class="top-actions">
          <button @click="toggleTheme" class="action-circle" title="Changer de thème">
            <span class="material-symbols-outlined">{{ isDark ? 'light_mode' : 'dark_mode' }}</span>
          </button>
          <router-link to="/review-create" class="btn-export desktop-only" style="text-decoration: none;">
            <span class="material-symbols-outlined">add</span>
            <span>Ajouter un avis</span>
          </router-link>
        </div>
      </div>
    </header>

    <div class="content-container">
      <!-- Stats -->
      <section class="stats-grid">
        <div v-for="stat in stats" :key="stat.label" class="stat-item">
          <div class="stat-header">
            <span class="stat-label">{{ stat.label }}</span>
            <div class="icon-circle" :class="stat.color">
              <span class="material-symbols-outlined">{{ stat.icon }}</span>
            </div>
          </div>
          <div class="stat-content">
            <h2 class="stat-number">{{ stat.value }}</h2>
            <span class="stat-badge" :class="stat.growth.includes('+') ? 'up' : 'down'">{{ stat.growth }}</span>
          </div>
        </div>
      </section>

      <!-- Filtres -->
      <div class="filter-container">
        <div class="search-wrapper">
          <span class="material-symbols-outlined">search</span>
          <input v-model="searchQuery" type="text" placeholder="Rechercher un auteur ou un avis..." />
        </div>
        <div class="filter-group">
          <button @click="exportCSV" class="btn-export mobile-full">
            <span class="material-symbols-outlined">download</span>
            <span class="btn-text">Exporter</span>
          </button>
          <button class="btn-filter-alt mobile-full">
            <span class="material-symbols-outlined">filter_list</span> 
            <span class="btn-text">Filtres</span>
          </button>
        </div>
      </div>

      <!-- Tableau -->
      <section class="table-section">
        <div v-if="!loading && reviews.length === 0" class="empty-state">
          <span class="material-symbols-outlined">reviews</span>
          <h3>Aucun avis</h3>
          <router-link to="/review-create" class="btn-empty">Créer le premier avis</router-link>
        </div>
        
        <div v-else class="table-responsive">
          <table class="lector-table">
            <thead>
              <tr>
                <th class="desktop-only" style="width: 40px"></th>
                <th>AUTEUR</th>
                <th>COMMENTAIRE</th>
                <th>SENTIMENT</th>
                <th>NOTE</th>
                <th>SUJET</th>
                <th class="desktop-only">DATE</th>
                <th class="text-right">ACTIONS</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="rev in paginatedReviews" :key="rev.id" class="lector-row">
                <td class="desktop-only"><input type="checkbox" /></td>
                <td data-label="AUTEUR" class="user-cell">
                  <img :src="rev.avatar" class="avatar-img" alt="avatar" />
                  <span class="name">{{ rev.author }}</span>
                </td>
                <td data-label="COMMENTAIRE" class="comment-cell">
                  <p class="comment-text" :title="rev.fullContent">{{ rev.comment }}</p>
                </td>
                <td data-label="SENTIMENT">
                  <span class="sentiment-tag" :class="rev.sentiment.toLowerCase()">
                    {{ rev.sentiment }}
                  </span>
                </td>
                <td data-label="NOTE">
                  <div class="rating-box">
                    <span class="material-symbols-outlined star-icon">star</span>
                    <span>{{ rev.rating }}/5 ({{ rev.score }}/100)</span>
                  </div>
                </td>
                <td data-label="SUJET"><span class="topic-pill">{{ rev.topic }}</span></td>
                <td data-label="DATE" class="desktop-only">{{ rev.date }}</td>
                
                <!--  ACTIONS ADMIN SEULE -->
                <td class="text-right action-cell">
                  <button 
                    v-if="userRole === 'admin'" 
                    @click="deleteReview(rev.id)" 
                    :disabled="deletingReview === rev.id"
                    class="btn-icon-danger"
                    title="Supprimer cet avis (Admin)"
                  >
                    <span v-if="deletingReview === rev.id" class="material-symbols-outlined">hourglass_empty</span>
                    <span v-else class="material-symbols-outlined">delete</span>
                  </button>
                  
                  
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- PAGINATION TERMINALE - ZÉRO ERREUR -->
<div class="pagination-footer" v-if="totalPages > 0">
  <p class="pagination-info">
    Affichage de 
    <strong>{{ Math.max(1, (currentPage - 1) * itemsPerPage + 1) }}</strong> à 
    <strong>{{ Math.min(currentPage * itemsPerPage, filteredReviews.length || 0) }}</strong>
    sur <strong>{{ filteredReviews.length || 0 }}</strong>
  </p>
  <div class="pagination-controls">
    <!-- Précédent -->
    <button 
      class="btn-page" 
      :disabled="currentPage <= 1" 
      @click="currentPage = Math.max(1, currentPage - 1)"
    >
      <span class="material-symbols-outlined">chevron_left</span>
    </button>
    
    <!-- Pages -->
    <button 
      v-for="p in totalPages" 
      :key="p" 
      class="btn-page" 
      :class="{ 'active': currentPage === p }" 
      @click="currentPage = p"
    >
      {{ p }}
    </button>
    
    <!-- Suivant -->
    <button 
      class="btn-page" 
      :disabled="currentPage >= totalPages" 
      @click="currentPage = Math.min(totalPages, currentPage + 1)"
    >
      <span class="material-symbols-outlined">chevron_right</span>
    </button>
  </div>
</div>

      </section>
    </div>
  </div>
</template>





<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap');


.btn-icon-danger {
  color: #ef4444;
  padding: 4px 8px;
  border-radius: 6px;
  transition: all 0.2s;
  margin-right: 4px;
}
.btn-icon-danger:hover:not(:disabled) {
  background: #ef4444 !important;
  color: white !important;
}
.btn-icon-danger:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* --- VARIABLES ET THÈMES --- */
.dashboard-wrapper {
  --orange: #EA580C; --emerald: #10B981; --amber: #F59E0B; --rose: #F43F5E;
  --bg-color: #F1F5F9; --card-bg: #FFFFFF; --text-main: #1E293B; --border: #E2E8F0;
  --text-muted: #64748B;
  min-height: 100vh; background-color: var(--bg-color); font-family: 'Roboto', sans-serif; transition: all 0.3s ease; color: var(--text-main);
}
.dashboard-wrapper.dark { 
  --bg-color:  #0000; --card-bg:linear-gradient(145deg, #111827, #0f172a); --text-main: #F8FAFC; --border: #334155; --text-muted: #94A3B8;
}
/* Dans votre style */
.dashboard-wrapper.dark { 
  --bg-color: #0f172a; /* Bleu très foncé au lieu de transparent */
  --card-bg: #1e293b;   /* Couleur de carte distincte */
  --text-main: #F8FAFC; 
  --border: #1e293b; 
  --text-muted: #f8fafc;
}


/* --- HEADER --- */
.main-header { width: 100%; background: var(--card-bg); border-bottom: 1px solid transparent; position: sticky; top: 0; z-index: 100; }
.header-content { max-width: 1400px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; padding: 1rem 2rem; }
.main-title { font-size: 1.4rem; font-weight: 800; letter-spacing: -1px; margin: 0; }
.accent { color: var(--orange); }
.top-actions { display: flex; gap: 1rem; align-items: center; }
.action-circle { width: 44px; height: 44px; border-radius: 12px; border: 1px solid transparent; background: var(--card-bg); cursor: pointer; color: var(--text-main); display: flex; align-items: center; justify-content: center; transition: 0.2s; }
.action-circle:hover { background: var(--bg-color); }

/* --- GRILLE DE STATS --- */
.content-container { max-width: 1400px; margin: 0 auto; padding: 2rem; }
.stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; margin-bottom: 2rem; }
.stat-item { background: var(--card-bg); padding: 1.5rem; border-radius: 24px; border: 1px solid transparent; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
.stat-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem; }
.stat-label { font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; }
.icon-circle { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; }
.icon-circle.orange { background: rgba(234, 88, 12, 0.1); color: var(--orange); }
.icon-circle.emerald { background: rgba(16, 185, 129, 0.1); color: var(--emerald); }
.icon-circle.amber { background: rgba(245, 158, 11, 0.1); color: var(--amber); }
.icon-circle.rose { background: rgba(244, 63, 94, 0.1); color: var(--rose); }
.stat-number { font-size: 1.8rem; font-weight: 800; margin: 4px 0; }
.stat-badge { font-size: 0.75rem; font-weight: 800; padding: 4px 8px; border-radius: 6px; }
.stat-badge.up { background: #DCFCE7; color: #166534; }
.stat-badge.down { background: #FEE2E2; color: #991B1B; }

/* --- FILTRES --- */
.filter-container { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; gap: 1rem; }
.search-wrapper { background: var(--card-bg); border: 1px solid transparent; border-radius: 12px; padding: 10px 16px; display: flex; align-items: center; gap: 10px; flex: 1; max-width: 450px; }
.search-wrapper input { background: transparent; border: none; outline: none; width: 100%; font-size: 0.9rem; color: var(--text-main); }
.search-wrapper span { color: var(--text-muted); }
.filter-group { display: flex; gap: 10px; align-items: center; }
.btn-export { background: var(--orange); color: white; border: none; padding: 10px 18px; border-radius: 12px; display: flex; align-items: center; gap: 8px; font-size: 0.85rem; font-weight: 700; cursor: pointer; }
.btn-filter-alt { background: var(--card-bg); border: 1px solid transparent; padding: 10px 16px; border-radius: 12px; display: flex; align-items: center; gap: 8px; font-size: 0.85rem; cursor: pointer; color: var(--text-main); font-weight: 500; }

/* --- TABLEAU --- */
.table-section { background: var(--card-bg); padding: 1.5rem; border-radius: 24px; border: 1px solid transparent; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
.table-responsive { width: 100%; overflow-x: auto; }
.lector-table { width: 100%; border-collapse: collapse; }
.lector-table th { text-align: left; padding: 1rem; font-size: 0.75rem; color: var(--text-muted); font-weight: 800; border-bottom: 1px solid var(--border); }
.lector-table td { padding: 1.2rem 1rem; border-bottom: 1px solid transparent; font-size: 0.9rem; }
.user-cell { display: flex; align-items: center; gap: 12px; font-weight: 700; }
.avatar-img { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; }
.comment-text { color: var(--text-muted); margin: 0; max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.sentiment-tag { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 999px; font-size: 0.75rem; font-weight: 700; }
.sentiment-tag.positif { background: #f0fdf4; color: #166534; }
.sentiment-tag.négatif { background: #fef2f2; color: #991b1b; }
.sentiment-tag.neutre { background: #fffbeb; color: #92400e; }

.rating-box { display: flex; align-items: center; gap: 4px; font-weight: 800; }
.star-icon { color: #facc15; font-size: 18px !important; font-variation-settings: 'FILL' 1; }
.topic-pill { background: var(--bg-color); padding: 4px 10px; border-radius: 8px; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); }

.btn-icon-more { background: transparent; border: none; color: var(--text-muted); cursor: pointer; border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; }
.btn-icon-more:hover { background: var(--bg-color); }

/* --- PAGINATION --- */
.pagination-footer { display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem; }
.pagination-info { font-size: 0.85rem; color: var(--text-muted); }
.pagination-controls { display: flex; gap: 6px; }
.btn-page { min-width: 36px; height: 36px; border-radius: 8px; border: 1px solid var(--border); background: var(--card-bg); color: var(--text-main); font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s; }
.btn-page:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-page.active { background: var(--orange); color: white; border-color: var(--orange); }

/* --- RESPONSIVE --- */
@media (max-width: 1024px) {
  .stats-grid { grid-template-columns: repeat(2, 1fr); }
  .desktop-only { display: none !important; }
}

@media (max-width: 768px) {
  .content-container { padding: 1rem; }
  .stats-grid { grid-template-columns: 1fr; }
  .filter-container { flex-direction: column; align-items: stretch; }
  .search-wrapper { max-width: none; }
  .filter-group { display: grid; grid-template-columns: 1fr 1fr; }
  .btn-text { display: none; }
  .btn-export, .btn-filter-alt { justify-content: center; }

  /* Mode Cards sur mobile */
  .lector-table thead { display: none; }
  .lector-table, .lector-table tbody, .lector-table tr, .lector-table td { display: block; width: 100%; }
  .lector-row { background: var(--card-bg); border: 1px solid var(--border); border-radius: 16px; margin-bottom: 1rem; padding: 0.5rem; }
  .lector-table td { display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1rem; border-bottom: 1px dotted var(--border); text-align: right; }
  .lector-table td:last-child { border-bottom: none; }
  .lector-table td[data-label]::before { content: attr(data-label); font-weight: 800; color: var(--text-muted); font-size: 0.7rem; text-transform: uppercase; float: left; }
  .comment-text { max-width: 180px; white-space: normal; }
  .pagination-footer { flex-direction: column; gap: 1rem; text-align: center; }
}
/* --- MODIFICATIONS POUR LE MODE SOMBRE GLOBAL --- */

/* On remplace .dashboard-wrapper.dark par .dark .dashboard-wrapper */

/* On force le header à suivre aussi */
:global(.dark) .main-header {
  background: var(--card-bg);
  border-bottom: 1px solid #ea580c ;
}

/* Amélioration du contraste des textes secondaires en mode sombre */
:global(.dark) .stat-label, 
:global(.dark) .comment-text,
:global(.dark) .pagination-info {
  color: #cbd5e1 !important; /* Un gris très clair, presque blanc */
}

/* Couleurs spécifiques pour les tags de sentiment en mode sombre */
:global(.dark) .sentiment-tag.positif { background: rgba(22, 163, 74, 0.2); color: #4ade80; }
:global(.dark) .sentiment-tag.négatif { background: rgba(239, 68, 68, 0.2); color: #f87171; }
:global(.dark) .sentiment-tag.neutre { background: rgba(251, 191, 36, 0.2); color: #fbbf24; }

/*  les Inputs en mode sombre */
:global(.dark) .search-wrapper {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid var(--border);
}
</style>