# FreelaSkill — Plateforme Freelance

Projet Web — ESPRIT 2A32 | 2025-2026

## Description

FreelaSkill est une plateforme de mise en relation entre freelances et clients. Elle permet la gestion des utilisateurs, des offres d'emploi, des contrats, des produits et de la messagerie.

## Technologies

- **Backend** : PHP (PDO — MySQLi interdit)
- **Base de données** : MySQL / MariaDB
- **Frontend** : HTML, CSS, JavaScript
- **Architecture** : MVC (Model-View-Controller)
- **Serveur local** : XAMPP

## Architecture MVC

```
projet2222/
├── Models/                  # Modèles (classes métier)
│   └── User.php
├── controllers/             # Contrôleurs (logique)
│   ├── AuthController.php
│   ├── ProfileController.php
│   ├── BackofficeController.php
│   ├── UserController.php
│   ├── PasswordResetController.php
│   ├── ChatbotController.php
│   ├── session.php          # Gestionnaire de sessions centralisé
│   └── config.php           # Configuration BD + PDO
├── views/
│   ├── frontoffice/         # Pages utilisateur
│   └── backoffice/          # Pages admin
├── uploads/                 # Fichiers uploadés
└── index.php                # Point d'entrée
```

## Installation

1. Cloner le dépôt dans `xampp/htdocs/` :
```bash
git clone https://github.com/jbelimariem/Esprit-PW-2A32-2526-freelaskill.git projet2222
```

2. Importer la base de données :
   - Ouvrir phpMyAdmin : `http://localhost/phpmyadmin`
   - Créer la base `freelaskill`
   - Importer `freelaskill.sql`

3. Configurer la connexion locale :
```bash
cp controllers/config.local.example.php controllers/config.local.php
```
Modifier `config.local.php` avec vos identifiants.

4. Accéder au projet :
```
http://localhost/projet2222/
```

## Sessions — Standard Groupe

Toutes les pages utilisent `controllers/session.php` :

```php
require_once __DIR__ . '/../../controllers/session.php';

// Vérifier si connecté
isLoggedIn();           // bool
getCurrentUserId();     // int|null
getCurrentUserRole();   // 'freelancer' | 'client' | null
requireLogin();         // redirige si non connecté
```

Variables de session standardisées :
- `$_SESSION['user_id']`
- `$_SESSION['user_nom']`
- `$_SESSION['user_prenom']`
- `$_SESSION['user_role']`

## Membres de l'équipe — 2A32

| Membre | Tâche | Branche Git |
|--------|-------|-------------|
| Mariem Tounsi | Gestion Utilisateurs (Auth, Profil, Admin) | `user` |
| ... | ... | ... |
| ... | ... | ... |
| ... | ... | ... |
| ... | ... | ... |

## Branches Git

- `main` — branche d'intégration commune
- `user` — module utilisateurs (Mariem Tounsi)
- Autres branches selon les équipes

## Règles d'intégration

- ✅ Utiliser **PDO uniquement** (MySQLi interdit)
- ✅ Respecter l'architecture **MVC**
- ✅ Merger dans `main` via Git
- ✅ Inclure `controllers/session.php` pour la gestion des sessions
