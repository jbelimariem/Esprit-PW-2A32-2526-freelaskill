# 🚀 FreelaSkill — Guide de setup pour l'équipe

> **Lis ce fichier EN PREMIER avant de lancer le projet.**  
> Les APIs ne marcheront pas sans les étapes ci-dessous.

---

## 📋 Prérequis

- PHP 8.x + extension **cURL** activée
- MySQL (XAMPP recommandé)
- Navigateur moderne

---

## ⚙️ Installation rapide

### 1. Cloner le projet

```bash
git clone https://github.com/jbelimariem/Esprit-PW-2A32-2526-freelaskill.git
cd Esprit-PW-2A32-2526-freelaskill
```

### 2. Créer la base de données

- Ouvre **phpMyAdmin** → Crée une base nommée `freelaskill`
- Importe le fichier SQL du projet (si disponible) ou lance le projet une première fois

### 3. Configurer les APIs locales

Les clés API ne sont **jamais committées** dans git (`.gitignore`).  
Tu dois créer tes propres fichiers locaux à partir des templates :

```bash
# Dans le dossier controllers/
copy controllers\config.local.example.php controllers\config.local.php
copy controllers\email_config.local.example.php controllers\email_config.local.php
```

Ensuite **édite ces 2 fichiers** avec tes propres clés (voir sections ci-dessous).

---

## 🤖 API Groq (IA — GRATUIT)

> Utilisée pour : chatbot, suggestions de bio, modération de contenu, suggestions de mots de passe, traduction

**Fichier à éditer :** `controllers/config.local.php`

### Obtenir une clé Groq
1. Va sur [https://console.groq.com](https://console.groq.com)
2. Crée un compte gratuit
3. Va dans **API Keys** → **Create API Key**
4. Copie la clé (commence par `gsk_...`)

### Remplir le fichier

```php
return [
    'groq_api_key' => 'gsk_VOTRE_CLE_ICI',   // ← ta clé Groq
    'groq_api_url' => 'https://api.groq.com/openai/v1/chat/completions',
    'groq_model'   => 'llama-3.1-8b-instant',
];
```

---

## 📧 API Brevo / Email (GRATUIT jusqu'à 300 emails/jour)

> Utilisée pour : emails de bienvenue, reset de mot de passe, notifications de sécurité

**Fichier à éditer :** `controllers/email_config.local.php`

### Obtenir une clé Brevo
1. Va sur [https://app.brevo.com](https://app.brevo.com)
2. Crée un compte gratuit
3. Va dans **SMTP & API** → **API Keys** → **Generate a new API key**
4. Copie la clé (commence par `xkeysib-...`)
5. Va dans **Senders & IP** → **Senders** → Vérifie ton adresse email

### Remplir le fichier

```php
return [
    'provider'   => 'brevo',
    'api_url'    => 'https://api.brevo.com/v3/smtp/email',
    'api_key'    => 'xkeysib-VOTRE_CLE_ICI',   // ← ta clé Brevo
    'from_email' => 'ton@email.com',             // ← email vérifié dans Brevo
    'from_name'  => 'FreelaSkill',
];
```

> ⚠️ L'email `from_email` doit être **vérifié** dans Brevo sinon les envois seront rejetés.

---

## ☁️ Cloudinary (uploads avatar/CV/portfolio)

> Utilisé pour stocker les fichiers uploadés (photos de profil, CVs, portfolios)

La clé Cloudinary est configurée dans `controllers/config.php` via la constante `CLOUDINARY_URL`.  
Contacte le responsable du projet pour obtenir les credentials du compte partagé.

---

## 💳 Stripe (paiements marketplace)

> Utilisé pour les paiements dans la section marketplace

Les clés Stripe **test** sont déjà dans `controllers/config.php`.  
Elles fonctionnent sans setup supplémentaire pour les tests.

Pour tester un paiement, utilise la carte : `4242 4242 4242 4242` (exp: n'importe quelle date future, CVC: 123)

---

## 🔐 Google OAuth (connexion avec Google)

> Utilisé pour la connexion et l'inscription via compte Google

Le **Client ID** Google est déjà hardcodé dans les vues.  
Pour que ça fonctionne en local, l'URL `http://localhost/projet2222/Views/Frontoffice/google_callback.php` doit être enregistrée dans la Google Console.

Contacte le responsable du projet si tu as des problèmes avec la redirection Google.

---

## ✅ Vérification — Tester que tout marche

Lance ces URLs dans le navigateur après setup :

| Test | URL |
|------|-----|
| Page d'accueil | `http://localhost/projet2222/` |
| Login | `http://localhost/projet2222/Views/Frontoffice/login.php` |
| Chatbot IA | Se connecter puis tester le widget chatbot |
| Email | Se connecter et vérifier la réception d'email |

---

## ❓ Problèmes fréquents

### "Groq n'est pas configuré"
→ Le fichier `controllers/config.local.php` est absent ou la clé est vide.

### "Clé API email manquante"
→ Le fichier `controllers/email_config.local.php` est absent ou la clé Brevo est vide.

### Page blanche / 404 après login
→ Vérifie que les chemins utilisent bien `Views/Frontoffice/` (avec majuscules).

### cURL error
→ Active l'extension cURL dans `php.ini` (décommenter `extension=curl`).

---

## 📁 Structure des fichiers de config

```
controllers/
├── config.php                      ← Config principale (DB, Cloudinary, Stripe)
├── config.local.php                ← ⛔ IGNORÉ PAR GIT — clé Groq locale
├── config.local.example.php        ← ✅ Template à copier
├── email_config.php                ← Config email principale
├── email_config.local.php          ← ⛔ IGNORÉ PAR GIT — clé Brevo locale
└── email_config.local.example.php  ← ✅ Template à copier
```

---

*Mis à jour le 10 mai 2026 — Branche `main`*
