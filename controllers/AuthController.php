<?php
// controllers/AuthController.php

require_once __DIR__ . '/UserController.php';

class AuthController extends UserController
{
    public function handleLogin()
    {
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $errors;
        }

        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if ($email === '') {
            $this->addFieldError($errors, 'email', "L'email est requis.");
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->addFieldError($errors, 'email', 'Email invalide.');
        }
        if ($password === '') {
            $this->addFieldError($errors, 'password', 'Le mot de passe est requis.');
        }

        if (!empty($errors)) {
            return $errors;
        }

        $user = $this->userModel()->login($email, $password);

        if (!$user) {
            return ['_global' => 'Email ou mot de passe incorrect.'];
        }

        if ($user->getStatus() === 'banned') {
            return ['_global' => "Votre compte a ete suspendu. Contactez l'administrateur."];
        }

        $_SESSION['user_id'] = $user->getId();
        $_SESSION['user_nom'] = $user->getNom();
        $_SESSION['user_prenom'] = $user->getPrenom();
        $_SESSION['user_role'] = $user->getRole();

        if ($user->getRole() === 'freelancer' && $user->getGithubUrl() === '' && $user->getLinkedinUrl() === '') {
            header('Location: onboarding_links.php');
        } else {
            header('Location: profile.php');
        }

        exit;
    }

    public function handleRegister()
    {
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $errors;
        }

        $user = (new User())
            ->setNom($_POST['nom'] ?? '')
            ->setPrenom($_POST['prenom'] ?? '')
            ->setEmail($_POST['email'] ?? '')
            ->setPassword($_POST['password'] ?? '')
            ->setRole($_POST['role'] ?? '')
            ->setBio($_POST['bio'] ?? '')
            ->setAvatar('')
            ->setStatus('active');

        $confirmPassword = trim($_POST['confirm_password'] ?? '');
        $errors = $this->validateRegistrationUser($user, $confirmPassword);

        $userModel = $this->userModel();
        if (empty($errors) && $userModel->emailExists($user->getEmail())) {
            $this->addFieldError($errors, 'email', 'Cet email est deja utilise.');
        }

        if (!empty($errors)) {
            return $errors;
        }

        $id = $userModel->create($user);

        $_SESSION['user_id'] = $id;
        $_SESSION['user_nom'] = $user->getNom();
        $_SESSION['user_prenom'] = $user->getPrenom();
        $_SESSION['user_role'] = $user->getRole();

        if ($user->getRole() === 'freelancer') {
            header('Location: onboarding_links.php');
        } else {
            header('Location: profile.php');
        }

        exit;
    }
}
