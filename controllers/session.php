<?php
// controllers/session.php
// ============================================================
// GESTIONNAIRE DE SESSION CENTRALISÉ - FreelaSkill
// À inclure dans TOUTES les pages qui nécessitent une session.
// Les autres équipes doivent faire :
//   require_once __DIR__ . '/../../controllers/session.php';
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// -------------------------------------------------------
// Helpers globaux (utilisables par TOUTES les équipes)
// -------------------------------------------------------

/**
 * Vérifie si l'utilisateur est connecté.
 */
function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']);
}

/**
 * Retourne l'ID de l'utilisateur connecté, ou null.
 */
function getCurrentUserId(): ?int {
    return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
}

/**
 * Retourne le rôle de l'utilisateur connecté ('freelancer', 'client'), ou null.
 */
function getCurrentUserRole(): ?string {
    return $_SESSION['user_role'] ?? null;
}

/**
 * Retourne le prénom de l'utilisateur connecté, ou null.
 */
function getCurrentUserPrenom(): ?string {
    return $_SESSION['user_prenom'] ?? null;
}

/**
 * Retourne le nom complet (prénom + nom) de l'utilisateur connecté.
 */
function getCurrentUserFullName(): string {
    $prenom = $_SESSION['user_prenom'] ?? '';
    $nom    = $_SESSION['user_nom']    ?? '';
    return trim("$prenom $nom");
}

/**
 * Redirige vers la page de login si l'utilisateur n'est pas connecté.
 * @param string $loginUrl URL de la page de login
 */
function requireLogin(string $loginUrl = '/views/frontoffice/login.php'): void {
    if (!isLoggedIn()) {
        header('Location: ' . $loginUrl);
        exit;
    }
}

/**
 * Vérifie que l'utilisateur est admin (role = 'admin').
 * Redirige vers accueil sinon.
 */
function requireAdmin(string $redirectUrl = '/views/frontoffice/discover.php'): void {
    if (($_SESSION['user_role'] ?? '') !== 'admin') {
        header('Location: ' . $redirectUrl);
        exit;
    }
}

/**
 * Crée la session utilisateur après login réussi.
 * Appelé uniquement par AuthController.
 */
function createUserSession(array $userData): void {
    $_SESSION['user_id']     = $userData['id'];
    $_SESSION['user_nom']    = $userData['nom'];
    $_SESSION['user_prenom'] = $userData['prenom'];
    $_SESSION['user_role']   = $userData['role'];
}

/**
 * Détruit la session (logout).
 */
function destroySession(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']
        );
    }
    session_destroy();
}
