<script setup>
import { ref } from 'vue'


// Initialisé à false : le mode clair est actif au chargement
const isDark = ref(false)

const toggleTheme = () => {
  isDark.value = !isDark.value
  // Ajoute 'dark' seulement si isDark est vrai, sinon l'enlève
  document.documentElement.classList.toggle('dark', isDark.value)
}
const commentaires = [
  { id: 1, auteur: "Sarah Jenkins", texte: "L'interface est époustouflante. J'ai l'impression d'utiliser un outil de 2030.", note: 5, img: "https://i.pravatar.cc/100?u=sarah" },
  { id: 2, auteur: "Marcus Ray", texte: "Profondeur de données incroyable, l'IA prédit tout parfaitement.", note: 4, img: "https://i.pravatar.cc/100?u=marcus" },
  { id: 3, auteur: "Elena Rodriguez", texte: "Le support client a résolu mon problème en moins de 4 minutes.", note: 5, img: "https://i.pravatar.cc/100?u=elena" }
]
// ... vos autres variables
const partenaires = [
  { name: 'Alpha', icon: 'deployed_code' },
  { name: 'Nexus', icon: 'hub' },
  { name: 'Vertex', icon: 'Tsunami' },
  { name: 'Orion', icon: 'Rocket' },
  { name: 'Nebula', icon: 'Cloud' },
  { name: 'Quantum', icon: 'Pbolt' },
]
// Dans votre <script setup>
const showModal = ref(false)
const videoUrl ="./assets/images/video.mp4" // Remplacez par votre lien
</script>

<template>
  <div class="page-wrapper">
    <nav class="navbar">
      <div class="nav-container">
        <div class="logo-section">
      <div class="logo-icon-bg shadow-orange">
        <span class="material-symbols-outlined text-white text-xl">rate_review</span>
      </div>
      <h1 class="logo-text" style="font-weight: 900;">Review <span style="color: #ea580c;">Pro</span> </h1>
    </div>

        <div class="nav-links-desktop">
          <a href="#fonctions">Fonctions</a>
          <a href="#temoignages">Avis</a>
          <a href="#tarifs">Tarifs</a>
          <div class="auth-buttons">
            <router-link to="/login" class="login-link">Se connecter</router-link>
             <router-link to="/inscrire" class="btn-nav" style="color: white;">S'inscrire</router-link>
          </div>
          <button @click="toggleTheme" class="theme-toggle">
            <span class="material-symbols-outlined">{{ isDark ? 'light_mode' : 'dark_mode' }}</span>
          </button>
        </div>
      </div>
    </nav>

    <main class="main-content">
      <div class="glow-bg"></div>

      <section class="hero container">
        <div class="hero-left">
          <div class="badge"><span class="pulse"></span> ReviewPro v2.0 Live</div>
          <h2 class="title">Données Utilisateur, <br><span class="gradient-text">Réimaginées.</span></h2>
          <p class="description">Le standard 2025 pour l'analyse des retours et la gestion des flux. Découvrez le futur de l'interface dès aujourd'hui.</p>
          
          <div class="hero-btns">
            <button class="btn-main">Commencer l'essai <span class="material-symbols-outlined">arrow_forward</span></button>
            <button class="btn-outline" @click="showModal = true">
  <span class="material-symbols-outlined">play_circle</span> Voir la démo
</button>
          </div>

          <div class="social-proof">
            <div class="avatars">
              <img src="https://i.pravatar.cc/100?u=1" alt=""><img src="https://i.pravatar.cc/100?u=2" alt=""><img src="https://i.pravatar.cc/100?u=3" alt="">
              <div class="more">+2k</div>
            </div>
            <span>Approuvé par +2000 leaders de la tech</span>
          </div>
        </div>

        <div class="hero-right">
          <div class="visual-wrapper">
            <img src="https://images.unsplash.com/photo-1460925895917-afdab827c52f?q=80&w=1000" alt="Dashboard" class="hero-img">
            <div class="widget float-1 floating">
              <span class="material-symbols-outlined text-green">trending_up</span>
              <div><small>CROISSANCE</small><strong>+128%</strong></div>
            </div>
            <div class="widget float-2 floating-delayed">
              <div class="widget-header"><span>Sentiment IA</span> <span class="tag">Élevé</span></div>
              <div class="progress"><div class="bar"></div></div>
            </div>
          </div>
        </div>
      </section>
 <h1 style="font-size: 26px;font-weight: 900;text-align: center;padding-top: 6rem;padding-bottom: 3rem;">Nos Entreprise <span style="color: #ea580c;">partenaires</span> </h1>
      <section class="partners-section">
  <div class="marquee-container">
    <div class="marquee-content">
      <div v-for="i in 2" :key="i" class="marquee-group">
        <div v-for="p in partenaires" :key="p.name" class="partner-logo">
          <span class="material-symbols-outlined">{{ p.icon }}</span>
          <span class="partner-name">{{ p.name }}</span>
        </div>
      </div>
    </div>
  </div>
</section>

      <section id="fonctions" class="features container">
        <div class="section-title">
          <h1 style="font-size: 30px;font-weight: 900;text-align: center;">Conçu pour la <span class="primary-text">Nouvelle Ère</span></h1>
        </div>
        <div class="feature-grid">
          <div class="f-card large">
            <span class="material-symbols-outlined f-icon">auto_awesome</span>
            <h3>Analyse Neural Engine</h3>
            <p>Notre modèle IA propriétaire déchiffre le sentiment utilisateur avec une précision de 99,8%.</p>
          </div>
          <div class="f-card primary-bg tall">
            <span class="material-symbols-outlined f-icon ">bolt</span>
            <h3>Synchro Temps Réel</h3>
            <p>Mises à jour instantanées sur toute votre infrastructure.</p>
          </div>
          <div class="f-card">
            <span class="material-symbols-outlined f-icon">shield</span>
            <h3>Sécurité Entreprise</h3>
            <p>Chiffrement de grade bancaire pour chaque donnée.</p>
          </div>
          <div class="f-card">
            <span class="material-symbols-outlined f-icon">api</span>
            <h3>API First</h3>
            <p>Architecture Headless pour une flexibilité totale.</p>
          </div>
        </div>
      </section>

      <section id="temoignages" class="sentiment-feed container">
        <div class="sentiment-box">
          <div class="sb-header">
            <h1 style="font-size: 28px;font-weight: 900;text-align: center;">Retours <span class="primary-text">d'expérience</span></h1>
            <div class="live-badge"><span class="dot"></span> LIVE</div>
          </div>
          <div class="scroll-container">
            <div v-for="c in commentaires" :key="c.id" class="comment-card">
              <div class="c-user">
                <img :src="c.img" alt="">
                <div><strong>{{ c.auteur }}</strong><div class="stars">★★★★★</div></div>
              </div>
              <p>"{{ c.texte }}"</p>
            </div>
          </div>
        </div>
      </section>

      <section id="tarifs" class="pricing container">
        <div class="section-title"><h1 style="font-size: 30px;font-weight: 900;text-align: center;">Valeur <span class="primary-text">Transparente</span></h1></div>
        <div class="price-grid">
          <div class="p-card">
            <h4>Starter</h4>
            <div class="price">0€<span>/mois</span></div>
            <ul><li>✔ 1,000 Utilisateurs</li><li>✔ Analyses de base</li></ul>
            <button class="btn-p">Choisir Starter</button>
          </div>
          <div class="p-card featured">
            <div class="pop-tag">RECOMMANDÉ</div>
            <h4>Professionnel</h4>
            <div class="price">49€<span>/mois</span></div>
            <ul><li>✔ 50,000 Utilisateurs</li><li>✔ Prédictions IA Neural</li></ul>
            <button class="btn-p main">Commencer Pro</button>
          </div>
          <div class="p-card">
            <h4>Entreprise</h4>
            <div class="price">199€<span>/mois</span></div>
            <ul><li>✔ Illimité</li><li>✔ Modèles IA sur mesure</li></ul>
            <button class="btn-p">Contacter Ventes</button>
          </div>
        </div>
      </section>

      <section id="download" class="download-section container">
  <div class="bento-download">
    <div class="bento-main">
      <div class="glass-badge">
        <span class="material-symbols-outlined">install_desktop</span>
        Multi-plateforme
      </div>
      <h2 class="bento-title">La puissance de ReviewPro, <br><span class="gradient-text">dans votre poche.</span></h2>
      <p class="bento-description">
        Ne perdez jamais le fil. Notre application mobile offre une latence ultra-faible pour monitorer vos flux IA en temps réel, même en déplacement.
      </p>
      
      <div class="button-group">
        <a href="#" class="app-btn apple">
          <i class="fab fa-apple"></i>
          <span>App Store</span>
        </a>
        <a href="#" class="app-btn google">
          <i class="fab fa-google-play"></i>
          <span>Google Play</span>
        </a>
      </div>
    </div>

    <div class="bento-visual">
      <div class="phone-container">
        <div class="phone-screen">
          <img src="https://images.unsplash.com/photo-1616348436168-de43ad0db179?q=80&w=1000" alt="Interface App">
        </div>
        <div class="mini-widget top-right floating">
          <span class="material-symbols-outlined">query_stats</span>
          <strong>99.9%</strong>
        </div>
        <div class="mini-widget bottom-left floating-delayed">
          <span class="material-symbols-outlined">verified_user</span>
          <strong>Secure</strong>
        </div>
      </div>
    </div>
  </div>
</section>
<Teleport to="body">
  <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
    <div class="modal-content">
      <button class="close-modal" @click="showModal = false">
        <span class="material-symbols-outlined">close</span>
      </button>
      <div class="video-container">
        <iframe 
          :src="videoUrl" 
          frameborder="0" 
          allow="autoplay; fullscreen; picture-in-picture" 
          allowfullscreen>
        </iframe>
      </div>
    </div>
  </div>
</Teleport>
    </main>
  </div>
  <footer class="footer">
      <div class="container">
        <div class="footer-top">
          <div class="footer-brand">
            <div class="logo">
              <div class="logo-box">
 <span class="material-symbols-outlined text-white text-xl">rate_review</span>
              </div>
              <h1>ReviewPro</h1>
            </div>
            <p class="footer-tagline">Conçu pour le futur.</p>
          </div>

          <div class="footer-grid">
            <div class="footer-col">
              <h3>Plateforme</h3>
              <ul>
                <li><a href="#">Fonctionnalités</a></li>
                <li><a href="#">Intégrations</a></li>
                <li><a href="#">API</a></li>
              </ul>
            </div>
            <div class="footer-col">
              <h3>Entreprise</h3>
              <ul>
                <li><a href="#">À propos</a></li>
                <li><a href="#">Blog</a></li>
                <li><a href="#">Carrières</a></li>
              </ul>
            </div>
            <div class="footer-col">
              <h3>Légal</h3>
              <ul>
                <li><a href="#">Confidentialité</a></li>
                <li><a href="#">Conditions</a></li>
              </ul>
            </div>
          </div>
        </div>

        <div class="footer-bottom">
          <p class="copyright">© 2025 ReviewPro Inc.</p>
          <div class="footer-socials">
            <a href="#">Twitter</a>
            <a href="#">GitHub</a>
            <a href="#">LinkedIn</a>
          </div>
        </div>
      </div>
    </footer>

</template>

<style>
/* --- RESET GLOBAL CRITIQUE --- */
*, *::before, *::after {
  box-sizing: border-box;
}

:root {
  --primary: #ea580c;       /* Orange vif */
  --primary-hover: #c2410c;
  --bg: #fff;
  --surface: #FFFFFF;
  --text: #111827;
  --text-light: #6B7280;
  --border: rgba(0, 0, 0, 0.08);
  --nav-bg: rgba(255, 255, 255, 0.8);
}

html.dark {
  /* Le bleu nuit profond de l'image */
  --bg: #0b0e14;            
  /* Les cartes légèrement plus claires pour l'effet "layer" */
  --surface: #161b22;       
  --text: #f0f6fc;
  --text-light: #8b949e;
  /* Bordures très subtiles comme sur la capture */
  --border: rgba(255, 255, 255, 0.05); 
  --nav-bg: rgba(11, 14, 20, 0.8);
}body {
  margin: 0;
  background-color: var(--bg);
  color: var(--text);
  transition: background-color 0.3s ease, color 0.3s ease;
  overflow-x: hidden; /* Empêche le scroll horizontal sur toute la page */
  width: 100%;
}
</style>

<style scoped>
.page-wrapper { 
  min-height: 100vh; 
  font-family: 'Plus Jakarta Sans', sans-serif; 
  overflow-x: hidden; /* Double sécurité */
  width: 100%;
}

.container { 
  max-width: 1200px; 
  margin: 0 auto; 
  padding: 0 1.5rem; 
  width: 100%; 
}

/* NAVBAR */
.navbar { position: fixed; top: 0; width: 100%; background: var(--nav-bg); backdrop-filter: blur(10px); border-bottom: 1px solid var(--border); z-index: 100; }
.nav-container { max-width: 1200px; margin: 0 auto; padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center; }
.logo { display: flex; align-items: center; gap: 10px; }
.logo-box { background: var(--primary); color: white; padding: 6px; border-radius: 10px; display: flex; }
.logo h1 { font-family: 'Space Grotesk', sans-serif; font-size: 1.3rem; margin: 0; color: var(--text); }
.primary-text { color: var(--primary); }
.logo-section { display: flex; align-items: center; gap: 1rem; margin-bottom: 0.8rem; padding-left: 0.5rem; margin-top: 20px;}
.logo-icon-bg { background-color: #ea580c; padding: 0.5rem; border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; }
.shadow-orange { box-shadow: 0 10px 15px -3px rgba(234, 88, 12, 0.2); }
.logo-text { font-size: 1.125rem; font-weight: 900; letter-spacing: -0.025em; text-transform: uppercase; color: #1e293b; }
.nav-links-desktop { display: flex; align-items: center; gap: 2rem; }
.nav-links-desktop a { text-decoration: none; color: var(--text); font-weight: 600; font-size: 0.9rem; }
.auth-buttons { display: flex; align-items: center; gap: 1.5rem; }
.btn-nav { background: var(--primary); color: white; border: none; padding: 8px 18px; border-radius: 10px; font-weight: bold; cursor: pointer; }

.theme-toggle { background: none; border: none; color: var(--text); cursor: pointer; display: flex; padding: 5px; }

/* MAIN CONTENT & HERO */
.main-content { padding-top: 80px; position: relative; overflow-x: hidden; width: 100%; }
.glow-bg { position: absolute; top: 0; left: 0; width: 100%; height: 800px; background: radial-gradient(circle at top left, rgba(217, 94, 22, 0.1), transparent); pointer-events: none; }

.hero { display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; align-items: center; padding: 6rem 1.5rem; }
.badge { display: inline-flex; align-items: center; gap: 8px; background: var(--surface); border: 1px solid var(--border); padding: 5px 15px; border-radius: 20px; font-weight: bold; font-size: 0.8rem; margin-bottom: 2rem; }
.pulse { width: 8px; height: 8px; background: var(--primary); border-radius: 50%; animation: pulse 2s infinite; }
.title { font-family: 'Space Grotesk', sans-serif; font-size: clamp(2.5rem, 5vw, 4rem); line-height: 1.1; margin-bottom: 1.5rem; color: var(--text); }
.gradient-text { background: linear-gradient(to right, var(--primary), #FF8C4B); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
.description { font-size: 1.1rem; color: var(--text-light); line-height: 1.6; margin-bottom: 2.5rem; }

.hero-btns { display: flex; gap: 1rem; margin-bottom: 3rem; }
.btn-main { background: var(--primary); color: white; border: none; padding: 18px 30px; border-radius: 15px; font-weight: bold; cursor: pointer; display: flex; align-items: center; gap: 10px; }
.btn-outline { background: var(--surface); border: 1px solid var(--border); padding: 18px 30px; border-radius: 15px; font-weight: bold; cursor: pointer; display: flex; align-items: center; gap: 10px; color: var(--text); }

.social-proof { display: flex; align-items: center; gap: 15px; color: var(--text-light); font-size: 0.85rem; }
.avatars { display: flex; }
.avatars img { width: 32px; height: 32px; border-radius: 50%; border: 2px solid var(--surface); margin-left: -10px; }
.more { background: var(--primary); color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: bold; margin-left: -10px; border: 2px solid var(--surface); }

/* VISUALS & WIDGETS */
.visual-wrapper { position: relative; width: 100%; }
.hero-img { width: 100%; max-width: 100%; border-radius: 24px; box-shadow: 0 30px 60px rgba(0,0,0,0.12); border: 1px solid var(--border); display: block; }
.widget { position: absolute; background: var(--surface); padding: 15px; border-radius: 18px; border: 1px solid var(--border); display: flex; gap: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); color: var(--text); z-index: 10; }
.float-1 { top: 20px; left: 0; } 
.float-2 { bottom: 30px; right: 10px; flex-direction: column; min-width: 160px; }
.text-green { color: #16a34a; }
.progress { height: 6px; background: var(--border); border-radius: 3px; overflow: hidden; }
.bar { width: 85%; height: 100%; background: var(--primary); }

/* --- SECTION PARTENAIRES MARQUEE --- */
.partners-section {
  padding: 5rem 0;
  background: var(--surface);
 border: 1px solid rgba(0, 0, 0, 0.08);
  overflow: hidden;
  margin: 2rem 0;
}

.marquee-container {
  display: flex;
  overflow: hidden;
  user-select: none;
  mask-image: linear-gradient(to right, transparent, black 15%, black 85%, transparent);
}

.marquee-content {
  display: flex;
  flex-shrink: 0;
  gap: 4rem;
  min-width: 100%;
  animation: scroll-marquee 25s linear infinite;
}

.marquee-group {
  display: flex;
  align-items: center;
  justify-content: space-around;
  gap: 4rem;
  min-width: 100%;
}

.partner-logo {
  display: flex;
  align-items: center;
  gap: 12px;
  color: var(--text-light);
  filter: grayscale(1);
  opacity: 0.7;
  transition: 0.3s;
}

.partner-logo:hover {
  filter: grayscale(0);
  opacity: 1;
  color: var(--primary);
}

.partner-logo .material-symbols-outlined {
  font-size: 2rem;
}

.partner-name {
  font-family: 'Space Grotesk', sans-serif;
  font-weight: 700;
  font-size: 1.2rem;
  text-transform: uppercase;
  letter-spacing: 1px;
}

/* Animation de défilement */
@keyframes scroll-marquee {
  from { transform: translateX(0); }
  to { transform: translateX(-100%); }
}

/* Pause au survol pour l'accessibilité */
.marquee-container:hover .marquee-content {
  animation-play-state: paused;
}
/* FEATURES */
.features { padding: 6rem 1.5rem; }
.section-title { text-align: center; margin-bottom: 4rem; }
.section-title h2 { font-size: 2.5rem; font-family: 'Space Grotesk'; color: var(--text); }
.feature-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; }
.f-card { background: var(--surface); border: 1px solid var(--border); padding: 2.5rem; border-radius: 24px; transition: 0.3s; }
.f-card.large { grid-column: span 2; }
.f-card.primary-bg { background: var(--primary); color: white; border: none; }
.f-icon { font-size: 2.5rem; color: var(--primary); margin-bottom: 1rem; }
.primary-bg .f-icon { color: white; }

/* TESTIMONIALS */
.sentiment-box { background:#F2F4F8 ; border-radius: 32px; padding: 3rem; width: 100%; }
.sb-header { display: flex; justify-content: space-between; margin-bottom: 2rem; }
.scroll-container { display: flex; gap: 1.5rem; overflow-x: auto; padding-bottom: 1rem; -webkit-overflow-scrolling: touch; }
.comment-card { min-width: 300px; background: var(--surface); padding: 1.5rem; border-radius: 20px; }
.c-user { display: flex; gap: 12px; margin-bottom: 1rem; }
.c-user img { width: 44px; height: 44px; border-radius: 50%; }
.stars{
    color: #ea580c;
}
/* Modification de la grille pour accepter le remplissage automatique */
.feature-grid { 
  display: grid; 
  grid-template-columns: repeat(3, 1fr); 
  grid-auto-flow: dense; /* Crucial : permet aux petites cartes de remplir les vides sous la grande */
  gap: 1.5rem; 
}

/* Ciblage spécifique de la carte haute */
.f-card.tall { 
  grid-row: span 2; 
  display: flex;
  flex-direction: column;
  justify-content: center;
  min-height: 400px; /* Optionnel : pour bien marquer la hauteur */
}

/* PRICING */
.pricing { padding: 6rem 1.5rem; }
.price-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; }
.p-card { background: var(--surface); border: 1px solid var(--border); padding: 3rem 2rem; border-radius: 24px; text-align: center; position: relative; }
.p-card.featured { transform: scale(1.05); background: #111827; color: white; border: none; z-index: 2; }
.dark .p-card.featured { background: #FFFFFF; color: #111827; }
.pop-tag { position: absolute; top: -15px; left: 50%; transform: translateX(-50%); background: var(--primary); color: white; padding: 5px 15px; border-radius: 20px; font-size: 0.7rem; font-weight: bold; }
.price { font-size: 3rem; font-weight: 800; margin: 1.5rem 0; }
.p-card ul { list-style: none; padding: 0; margin-bottom: 2rem; text-align: left; }
.btn-p { width: 100%; padding: 12px; border-radius: 10px; border: 1px solid var(--border); background: none; color: inherit; font-weight: bold; cursor: pointer; }
.btn-p.main { background: var(--primary); color: white; border: none; }
/* --- BENTO DOWNLOAD SECTION --- */
.download-section {
  padding: 8rem 1.5rem;
}

.bento-download {
  background: linear-gradient(145deg, var(--surface), var(--bg));
  border: 1px solid var(--border);
  border-radius: 48px;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
  padding: 5rem;
  position: relative;
  overflow: hidden;
  box-shadow: 0 40px 100px -20px rgba(0,0,0,0.05);
}

.dark .bento-download {
  background: rgba(255, 255, 255, 0.02);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.05);
}

/* Glass Badge */
.glass-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: rgba(234, 88, 12, 0.1);
  color: var(--primary);
  padding: 8px 16px;
  border-radius: 100px;
  font-weight: 700;
  font-size: 0.85rem;
  margin-bottom: 2rem;
}

.bento-title {
  font-family: 'Space Grotesk', sans-serif;
  font-size: clamp(2rem, 4vw, 3.5rem);
  line-height: 1;
  color: var(--text);
  margin-bottom: 1.5rem;
}

.bento-description {
  color: var(--text-light);
  font-size: 1.1rem;
  line-height: 1.6;
  max-width: 500px;
  margin-bottom: 3rem;
}

/* Boutons Modernes */
.button-group {
  display: flex;
  gap: 1rem;
}

.app-btn {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 28px;
  border-radius: 18px;
  text-decoration: none;
  font-weight: 700;
  transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.app-btn.apple { background: var(--text); color: var(--surface); }
.app-btn.google { background: var(--surface); color: var(--text); border: 1px solid var(--border); }

.app-btn:hover {
  transform: translateY(-5px) scale(1.02);
  box-shadow: 0 20px 40px rgba(0,0,0,0.1);
}

/* Visual Phone et Widgets */
.bento-visual {
  display: flex;
  justify-content: center;
  align-items: center;
  position: relative;
}

.phone-container {
  width: 280px;
  height: 560px;
  background: #111827;
  border: 12px solid #1f2937;
  border-radius: 40px;
  overflow: visible;
  position: relative;
  box-shadow: 0 50px 100px rgba(0,0,0,0.4);
}

.phone-screen {
  width: 100%;
  height: 100%;
  border-radius: 28px;
  overflow: hidden;
}

.phone-screen img { width: 100%; height: 100%; object-fit: cover; }

.mini-widget {
  position: absolute;
  background: var(--surface);
  padding: 12px 20px;
  border-radius: 16px;
  border: 1px solid var(--border);
  display: flex;
  align-items: center;
  gap: 10px;
  box-shadow: 0 15px 30px rgba(0,0,0,0.1);
  z-index: 5;
}
.page-wrapper { 
  min-height: 100vh; 
  font-family: 'Plus Jakarta Sans', sans-serif; 
  overflow-x: hidden;
  width: 100%;
  /* AJOUTE CECI : */
  background-color: var(--bg); 
  position: relative;
  z-index: 1; /* S'assure de passer devant d'éventuels éléments de App.vue */
}

.top-right { top: 10%; right: -30%; }
.bottom-left { bottom: 15%; left: -30%; }
/* --- FOOTER STYLES --- */
.footer {
  padding-top: 5rem;
  padding-bottom: 3rem;
  border-top: 1px solid var(--border);
  background-color: #fff;
}

.footer-top {
  display: flex;
  justify-content: space-between;
  margin-bottom: 5rem;
  gap: 4rem;
}

.footer-brand {
  flex: 1;
}

.footer-tagline {
  margin-top: 1.5rem;
  color: var(--text-light);
  font-size: 1rem;
}

.footer-grid {
  display: flex;
  gap: 5rem;
  flex: 2;
  justify-content: flex-end;
}

.footer-col h3 {
  font-family: 'Space Grotesk', sans-serif;
  font-size: 1.1rem;
  font-weight: 700;
  margin-bottom: 1.5rem;
  color: var(--text);
}

.footer-col ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.footer-col ul li {
  margin-bottom: 0.8rem;
}

.footer-col ul li a {
  text-decoration: none;
  color: var(--text-light);
  font-size: 0.95rem;
  transition: color 0.2s ease;
}

.footer-col ul li a:hover {
  color: var(--primary);
}

/* Footer Bottom */
.footer-bottom {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 2rem;
  border-top: 1px solid var(--border);
}

.copyright {
  color: var(--text-light);
  font-size: 0.9rem;
}

.footer-socials {
  display: flex;
  gap: 2rem;
}

.footer-socials a {
  text-decoration: none;
  color: var(--text-light);
  font-size: 0.9rem;
  font-weight: 500;
  transition: color 0.2s ease;
}

.footer-socials a:hover {
  color: var(--text);
}

/* Responsive Footer */
@media (max-width: 968px) {
  .footer-top {
    flex-direction: column;
    gap: 3rem;
  }
  
  .footer-grid {
    justify-content: flex-start;
    flex-wrap: wrap;
    gap: 3rem;
  }
  
  .footer-bottom {
    flex-direction: column-reverse;
    gap: 1.5rem;
    text-align: center;
  }
}
/* Mobile Adjustments */
@media (max-width: 968px) {
  .bento-download { grid-template-columns: 1fr; padding: 3rem 1.5rem; text-align: center; }
  .bento-description { margin: 0 auto 2rem; }
  .button-group { justify-content: center; }
  .phone-container { transform: scale(0.8); margin-top: 2rem; }
  .top-right, .bottom-left { right: 0; left: 0; margin: auto; width: fit-content; }
}
/* ANIMATIONS */
@keyframes pulse { 0% { transform: scale(1); opacity: 1; } 50% { transform: scale(1.5); opacity: 0.4; } 100% { transform: scale(1); opacity: 1; } }
@keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-15px); } }
.floating { animation: float 4s ease-in-out infinite; }
.floating-delayed { animation: float 4s ease-in-out infinite 1s; }

/* RESPONSIVE */
@media (max-width: 1024px) {
  .hero { grid-template-columns: 1fr; text-align: center; gap: 3rem; }
  .hero-btns, .social-proof { justify-content: center; }
  .feature-grid, .price-grid { grid-template-columns: 1fr; }
  .f-card.large { grid-column: span 1; }
  .nav-links-desktop { display: none; }
  .p-card.featured { transform: scale(1); }
  .float-1 { left: 50%; transform: translateX(-50%); top: -30px; }
}
</style>