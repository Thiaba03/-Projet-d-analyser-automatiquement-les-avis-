  Plateforme d'Analyse Automatisée d'Avis Clients (IA)
Ce projet est une solution Full-Stack dédiée à la collecte et à l'analyse sémantique automatique d'avis clients. 
Il a été développé dans le cadre du cursus Bachelor 3 Data & IA.

  Objectifs du Projet
L'enjeu est de transformer des données textuelles brutes en indicateurs décisionnels (KPIs) exploitables par une entreprise.
Centralisation : Stockage sécurisé des avis via une API REST.
Intelligence Artificielle : Analyse automatique (Sentiment, Scoring, Topics) à chaque soumission d'avis.
Visualisation : Dashboard interactif pour le suivi des performances de satisfaction client.

  Stack Technique
Backend : Laravel 12 (Architecture 100% API REST).
Frontend : Vue 3 (Vite, Axios, Vue Router) ou JavaScript moderne.
Base de Données : MySQL avec schéma relationnel optimisé.
IA/NLP : Intégration d'un service de Traitement du Langage Naturel (Rule-based ou API pré-entraînée)

  Composante IA (Moteur d'Analyse)
Le système expose un endpoint d'analyse (/api/analyze) capable de traiter le texte pour extraire :
Sentiment : Classification (Positif, Neutre, Négatif).
Satisfaction Score : Note automatisée de 0 à 100 basée sur la ponctuation et la sémantique.
Topic Detection : Identification des thèmes clés (ex: "Livraison", "Qualité", "Prix").

  Installation
Cloner le dépôt :

Bash
git clone https://github.com/Thiaba03/-Projet-d-analyser-automatiquement-les-avis-
Configuration du Backend :

Bash
cd avis-ia
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
Lancement du Frontend :

Bash
cd ../frontend
npm install
npm run dev

   Compétences Validées
Conception d'architectures de données évolutives.
Développement de microservices IA intégrés en Backend.
Consommation d'API RESTful via un Frontend réactif.
Utilisation de Git pour le versioning et la collaboration.
Auteurs : Groupe d'étudiants B3 Data & IA - ECE Paris (Février 2026).
