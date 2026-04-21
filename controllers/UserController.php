<?php
// controllers/UserController.php

require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/UserModel.php';

abstract class UserController
{
    public function getAll()
    {
        return $this->userModel()->getAll();
    }

    public function getById($id)
    {
        return $this->userModel()->getById($id);
    }

    public function filter($search, $role, $status)
    {
        return $this->userModel()->filter($search, $role, $status);
    }

    public function countAll()
    {
        return $this->userModel()->countAll();
    }

    public function countByRole($role)
    {
        return $this->userModel()->countByRole($role);
    }

    public function countByStatus($status)
    {
        return $this->userModel()->countByStatus($status);
    }

    protected function userModel()
    {
        return new UserModel();
    }

    protected function addFieldError(array &$errors, $field, $message)
    {
        if (!isset($errors[$field])) {
            $errors[$field] = $message;
        }
    }

    protected function mergeErrors(array $errors, array $newErrors)
    {
        foreach ($newErrors as $field => $message) {
            $this->addFieldError($errors, $field, $message);
        }

        return $errors;
    }

    protected function validateRegistrationUser(User $user, $confirmPassword)
    {
        $errors = $this->validateProfileUser($user);
        $errors = $this->mergeErrors($errors, $this->validatePasswordRules($user->getPassword(), 'Le mot de passe est requis.', 'password'));

        if ($user->getPassword() !== $confirmPassword) {
            $this->addFieldError($errors, 'confirm_password', 'Les mots de passe ne correspondent pas.');
        }

        if (!in_array($user->getRole(), ['freelancer', 'client'], true)) {
            $this->addFieldError($errors, 'role', 'Veuillez choisir un role.');
        }

        return $errors;
    }

    protected function validateProfileUser(User $user)
    {
        $errors = [];

        if ($user->getNom() === '') {
            $this->addFieldError($errors, 'nom', 'Le nom est requis.');
        }
        if ($user->getPrenom() === '') {
            $this->addFieldError($errors, 'prenom', 'Le prenom est requis.');
        }
        if ($user->getEmail() === '') {
            $this->addFieldError($errors, 'email', "L'email est requis.");
        } elseif (!filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL)) {
            $this->addFieldError($errors, 'email', 'Email invalide.');
        }

        return $errors;
    }

    protected function validatePasswordChange($password, $confirmPassword)
    {
        $errors = $this->validatePasswordRules($password, 'Le nouveau mot de passe est requis.', 'new_password');

        if ($password !== $confirmPassword) {
            $this->addFieldError($errors, 'confirm_password', 'Les mots de passe ne correspondent pas.');
        }

        return $errors;
    }

    protected function validatePasswordRules($password, $requiredMessage, $field = 'password')
    {
        $errors = [];

        if ($password === '') {
            $this->addFieldError($errors, $field, $requiredMessage);
            return $errors;
        }

        if (strlen($password) < 8) {
            $this->addFieldError($errors, $field, 'Le mot de passe doit contenir au moins 8 caracteres.');
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $this->addFieldError($errors, $field, 'Le mot de passe doit contenir au moins une lettre majuscule.');
        }
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $this->addFieldError($errors, $field, 'Le mot de passe doit contenir au moins un caractere special.');
        }

        return $errors;
    }

    protected function validateAdminCreate(User $user)
    {
        $errors = $this->validateProfileUser($user);
        $errors = $this->mergeErrors($errors, $this->validatePasswordRules($user->getPassword(), 'Le mot de passe est requis.', 'password'));

        if (!in_array($user->getRole(), ['freelancer', 'client'], true)) {
            $this->addFieldError($errors, 'role', 'Role invalide.');
        }

        return $errors;
    }

    protected function validateAdminUpdate(User $user)
    {
        $errors = $this->validateProfileUser($user);

        if (!in_array($user->getRole(), ['freelancer', 'client'], true)) {
            $this->addFieldError($errors, 'role', 'Role invalide.');
        }
        if (!in_array($user->getStatus(), ['active', 'banned', 'pending'], true)) {
            $this->addFieldError($errors, 'status', 'Statut invalide.');
        }

        return $errors;
    }

    protected function handleAvatarUpload(&$user, &$success)
    {
        $errors = [];
        $avatarFile = $_FILES['avatar_file'] ?? null;
        $maxSize = 3 * 1024 * 1024;
        $allowedTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
        ];

        if ($avatarFile === null || empty($avatarFile['name'])) {
            return ['avatar_file' => 'Veuillez selectionner une image.'];
        }
        if ($avatarFile['error'] !== UPLOAD_ERR_OK) {
            return ['avatar_file' => "Erreur lors de l'envoi de la photo."];
        }
        if ($avatarFile['size'] > $maxSize) {
            return ['avatar_file' => 'La photo ne doit pas depasser 3 MB.'];
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = $finfo ? finfo_file($finfo, $avatarFile['tmp_name']) : '';
        if ($finfo) {
            finfo_close($finfo);
        }

        if (!isset($allowedTypes[$mimeType])) {
            return ['avatar_file' => 'Format image non supporte. Utilisez JPG, PNG, WEBP ou GIF.'];
        }

        $uploadDir = $this->uploadsPath('avatar');
        if (!$this->ensureDirectory($uploadDir)) {
            return ['avatar_file' => "Impossible de preparer le dossier d'upload."];
        }

        $fileName = 'avatar_' . $user->getId() . '_' . time() . '.' . $allowedTypes[$mimeType];
        $relativeAvatarPath = 'uploads/avatar/' . $fileName;
        $targetPath = $this->uploadsPath('avatar/' . $fileName);

        if (!move_uploaded_file($avatarFile['tmp_name'], $targetPath)) {
            return ['avatar_file' => "Impossible d'enregistrer la photo."];
        }

        if ($user->getAvatar() !== '') {
            $oldAvatarPath = $this->projectPath($user->getAvatar());
            if (is_file($oldAvatarPath)) {
                @unlink($oldAvatarPath);
            }
        }

        $this->userModel()->update(
            (new User())
                ->setId($user->getId())
                ->setNom($user->getNom())
                ->setPrenom($user->getPrenom())
                ->setEmail($user->getEmail())
                ->setBio($user->getBio())
                ->setAvatar($relativeAvatarPath)
        );

        $user = $this->userModel()->getById($user->getId());
        $success = true;

        return $errors;
    }

    protected function handleDocumentsUpload(&$user, &$success)
    {
        $result = $this->processDocumentUploads($user);

        if (!empty($result['errors'])) {
            return $result['errors'];
        }

        if ($result['cv_path'] === null && $result['portfolio_path'] === null) {
            $targetField = $_POST['file_target'] ?? '_global';
            return [$targetField => 'Veuillez selectionner au moins un fichier.'];
        }

        $this->userModel()->updateFiles(
            (new User())
                ->setId($user->getId())
                ->setCvUrl($result['cv_path'])
                ->setPortfolioUrl($result['portfolio_path'])
        );

        $user = $this->userModel()->getById($user->getId());
        $success = true;

        return [];
    }

    protected function processDocumentUploads(User $user)
    {
        $errors = [];
        $maxSize = 5 * 1024 * 1024;
        $allowedCv = ['application/pdf'];
        $allowedPortfolio = [
            'application/pdf',
            'application/zip',
            'application/x-zip-compressed',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];

        $cvDirectory = $this->uploadsPath('cv');
        $portfolioDirectory = $this->uploadsPath('portfolio');
        $this->ensureDirectory($cvDirectory);
        $this->ensureDirectory($portfolioDirectory);

        $newCvPath = null;
        $newPortfolioPath = null;

        if (!empty($_FILES['cv_file']['name'])) {
            $file = $_FILES['cv_file'];

            if ($file['error'] !== UPLOAD_ERR_OK) {
                $this->addFieldError($errors, 'cv_file', 'Erreur lors du transfert du CV.');
            } elseif ($file['size'] > $maxSize) {
                $this->addFieldError($errors, 'cv_file', 'Le CV ne doit pas depasser 5 MB.');
            } elseif (!in_array($file['type'], $allowedCv, true)) {
                $this->addFieldError($errors, 'cv_file', 'Le CV doit etre un fichier PDF.');
            } else {
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $fileName = 'cv_' . $user->getId() . '_' . time() . '.' . $extension;
                $targetPath = $this->uploadsPath('cv/' . $fileName);

                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    if ($user->getCvUrl()) {
                        $oldFile = $this->projectPath($user->getCvUrl());
                        if (is_file($oldFile)) {
                            @unlink($oldFile);
                        }
                    }

                    $newCvPath = 'uploads/cv/' . $fileName;
                } else {
                    $this->addFieldError($errors, 'cv_file', 'Impossible de sauvegarder le CV.');
                }
            }
        }

        if (!empty($_FILES['portfolio_file']['name'])) {
            $file = $_FILES['portfolio_file'];

            if ($file['error'] !== UPLOAD_ERR_OK) {
                $this->addFieldError($errors, 'portfolio_file', 'Erreur lors du transfert du Portfolio.');
            } elseif ($file['size'] > $maxSize) {
                $this->addFieldError($errors, 'portfolio_file', 'Le Portfolio ne doit pas depasser 5 MB.');
            } elseif (!in_array($file['type'], $allowedPortfolio, true)) {
                $this->addFieldError($errors, 'portfolio_file', 'Format non supporte (PDF, ZIP ou DOCX).');
            } else {
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $fileName = 'portfolio_' . $user->getId() . '_' . time() . '.' . $extension;
                $targetPath = $this->uploadsPath('portfolio/' . $fileName);

                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    if ($user->getPortfolioUrl()) {
                        $oldFile = $this->projectPath($user->getPortfolioUrl());
                        if (is_file($oldFile)) {
                            @unlink($oldFile);
                        }
                    }

                    $newPortfolioPath = 'uploads/portfolio/' . $fileName;
                } else {
                    $this->addFieldError($errors, 'portfolio_file', 'Impossible de sauvegarder le Portfolio.');
                }
            }
        }

        return [
            'errors' => $errors,
            'cv_path' => $newCvPath,
            'portfolio_path' => $newPortfolioPath,
        ];
    }

    protected function ensureDirectory($path)
    {
        return is_dir($path) || (mkdir($path, 0777, true) && is_dir($path));
    }

    protected function uploadsPath($relativePath = '')
    {
        $relativePath = trim(str_replace('\\', '/', $relativePath), '/');
        $path = $this->projectPath('uploads');

        if ($relativePath !== '') {
            $path .= DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        }

        return $path;
    }

    protected function projectPath($relativePath = '')
    {
        $basePath = dirname(__DIR__);
        $relativePath = trim(str_replace('\\', '/', $relativePath), '/');

        if ($relativePath === '') {
            return $basePath;
        }

        return $basePath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    }
}
