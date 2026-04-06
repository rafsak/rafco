# ATFP Chatbot — Orientation Professionnelle

Chatbot intelligent d'orientation professionnelle pour les jeunes tunisiens, développé en PHP natif + MySQL + JS vanilla.

## Fonctionnalités

- **Chatbot bilingue** (Français / Arabe) avec interface messenger
- **Analyse du niveau scolaire** et des centres d'intérêt
- **Recommandation de spécialités** ATFP adaptées au profil
- **Recherche de centres** par gouvernorat
- **Parcours d'orientation** guidé en 3 étapes (niveau → intérêts → localisation)
- **Panel admin** avec statistiques, gestion des conversations, centres, spécialités
- **Intents configurables** via `intents.json`
- **Logique IA** par scoring de mots-clés

## Prérequis

- XAMPP (Apache + MySQL + PHP 7.4+)
- Navigateur moderne

## Installation

1. Copier le dossier dans `htdocs/atfp-chatbot/`
2. Démarrer Apache et MySQL dans XAMPP
3. Ouvrir `http://localhost/atfp-chatbot/install.php` dans le navigateur
4. C'est prêt ! Ouvrir `http://localhost/atfp-chatbot/`

## Structure

```
├── index.html          # Interface chatbot (messenger UI)
├── chat.php            # API interne du chatbot
├── config.php          # Configuration + PDO + helpers
├── admin.php           # Panel d'administration
├── install.php         # Script d'installation automatique
├── intents.json        # Intentions configurables (FR/AR)
├── database.sql        # Schéma + données de test
└── assets/
    ├── css/style.css   # Styles messenger
    └── js/app.js       # Logique front-end (fetch)
```

## Admin Panel

- URL : `http://localhost/atfp-chatbot/admin.php`
- Login : `admin` / `admin123`
- Fonctionnalités : statistiques, conversations, gestion centres/spécialités, éditeur d'intents

## Sécurité

- PDO avec requêtes préparées (protection SQL injection)
- Échappement XSS (`htmlspecialchars`)
- Mots de passe hashés avec `password_hash` (bcrypt)
- Validation des entrées côté serveur

## Personnalisation

Modifier `intents.json` pour ajouter/modifier les intentions du chatbot. Chaque intent contient :
- `tag` : identifiant unique
- `patterns` : mots-clés déclencheurs (FR + AR)
- `responses` : réponses possibles (FR + AR)
- `action` : action à exécuter (optionnel)

## Base de données

- 24 gouvernorats tunisiens
- 12 secteurs de formation
- 12 centres ATFP
- 20 spécialités (CAP, BTP, BTS)
- Système de conversations et profils utilisateurs
