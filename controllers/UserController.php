<?php
// controllers/UserController.php

require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../config.php';

class UserController {

    protected $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    // -------------------------------------------------------
    // Helper: map a DB record (assoc array) to a User object
    // -------------------------------------------------------
    private function mapRecordToUser($record) {
        if (!$record) {
            return null;
        }
        $user = new User(
            $record['nom'] ?? '',
            $record['prenom'] ?? '',
            $record['email'] ?? '',
            $record['password'] ?? '',
            $record['role'] ?? 'client',
            $record['bio'] ?? '',
            $record['avatar'] ?? '',
            $record['status'] ?? 'active',
            $record['github_url'] ?? '',
            $record['linkedin_url'] ?? '',
            $record['cv_url'] ?? null,
            $record['portfolio_url'] ?? null
        );
        $user->setId($record['id'] ?? null);
        $user->setCreatedAt($record['created_at'] ?? null);
        $user->setFaceDescriptor($record['face_descriptor'] ?? null);
        return $user;
    }

    private function mapRecordsToUsers(array $records) {
        $users = [];
        foreach ($records as $record) {
            $users[] = $this->mapRecordToUser($record);
        }
        return $users;
    }

    // -------------------------------------------------------
    // Base de données : CRUD
    // -------------------------------------------------------
    public function getAll() {
        $sql = "SELECT * FROM users WHERE status != 'rejected' ORDER BY created_at DESC";
        $stmt = $this->pdo->query($sql);
        return $this->mapRecordsToUsers($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function getById($id) {
        $sql = "SELECT * FROM users WHERE id = ? LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $this->mapRecordToUser($stmt->fetch(PDO::FETCH_ASSOC));
    }

    public function getByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = ? LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$email]);
        return $this->mapRecordToUser($stmt->fetch(PDO::FETCH_ASSOC));
    }

    public function getByRole($role) {
        $sql = "SELECT * FROM users WHERE role = ? ORDER BY created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$role]);
        return $this->mapRecordsToUsers($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function getByStatus($status) {
        $sql = "SELECT * FROM users WHERE status = ? ORDER BY created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$status]);
        return $this->mapRecordsToUsers($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function create(User $user) {
        $sql = "INSERT INTO users (nom, prenom, email, password, role, bio, avatar, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $user->getNom(),
            $user->getPrenom(),
            $user->getEmail(),
            password_hash($user->getPassword(), PASSWORD_DEFAULT),
            $user->getRole(),
            $user->getBio(),
            $user->getAvatar(),
            $user->getStatus()
        ]);
        return $this->pdo->lastInsertId();
    }

    public function update(User $user) {
        $sql = "UPDATE users SET nom = ?, prenom = ?, email = ?, bio = ?, avatar = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $user->getNom(),
            $user->getPrenom(),
            $user->getEmail(),
            $user->getBio(),
            $user->getAvatar(),
            $user->getId()
        ]);
    }

    public function updateFull(User $user) {
        $sql = "UPDATE users SET nom = ?, prenom = ?, email = ?, bio = ?, role = ?, status = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $user->getNom(),
            $user->getPrenom(),
            $user->getEmail(),
            $user->getBio(),
            $user->getRole(),
            $user->getStatus(),
            $user->getId()
        ]);
    }

    public function updatePassword($id, $newPassword) {
        $sql = "UPDATE users SET password = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), $id]);
    }

    public function updateStatus($id, $status) {
        $sql = "UPDATE users SET status = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$status, $id]);
    }

    public function updateFaceDescriptor($id, $faceDescriptor) {
        $sql = "UPDATE users SET face_descriptor = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$faceDescriptor, $id]);
    }

    public function delete($id) {
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
    }

    public function updateLinks(User $user) {
        $sql = "UPDATE users SET github_url = ?, linkedin_url = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$user->getGithubUrl(), $user->getLinkedinUrl(), $user->getId()]);
    }

    public function updateFiles(User $user) {
        if ($user->getCvUrl() !== null && $user->getPortfolioUrl() !== null) {
            $sql = "UPDATE users SET cv_url = ?, portfolio_url = ? WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$user->getCvUrl(), $user->getPortfolioUrl(), $user->getId()]);
            return;
        }
        if ($user->getCvUrl() !== null) {
            $sql = "UPDATE users SET cv_url = ? WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$user->getCvUrl(), $user->getId()]);
        }
        if ($user->getPortfolioUrl() !== null) {
            $sql = "UPDATE users SET portfolio_url = ? WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$user->getPortfolioUrl(), $user->getId()]);
        }
    }

    public function clearLink($id, $field) {
        $allowed = ['github_url', 'linkedin_url'];
        if (!in_array($field, $allowed, true)) {
            return;
        }
        $sql = "UPDATE users SET {$field} = '' WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
    }

    public function clearFile($id, $field) {
        $allowed = ['cv_url', 'portfolio_url'];
        if (!in_array($field, $allowed, true)) {
            return;
        }
        $sql = "UPDATE users SET {$field} = NULL WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
    }

    public function login($email, $password) {
        $user = $this->getByEmail($email);
        if ($user && password_verify($password, $user->getPassword())) {
            return $user;
        }
        return false;
    }

    public function emailExists($email) {
        $sql = "SELECT COUNT(*) FROM users WHERE email = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetchColumn() > 0;
    }

    public function countAll() {
        return $this->pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    }

    public function countByRole($role) {
        $sql = "SELECT COUNT(*) FROM users WHERE role = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$role]);
        return $stmt->fetchColumn();
    }

    public function countByStatus($status) {
        $sql = "SELECT COUNT(*) FROM users WHERE status = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$status]);
        return $stmt->fetchColumn();
    }

    public function search($query) {
        $like = '%' . $query . '%';
        $sql = "SELECT * FROM users WHERE nom LIKE ? OR prenom LIKE ? OR email LIKE ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$like, $like, $like]);
        return $this->mapRecordsToUsers($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function filter($search, $role, $status) {
        $sql = "SELECT * FROM users WHERE 1 = 1";
        $params = [];

        if ($search !== '') {
            $like = '%' . $search . '%';
            $sql .= " AND (nom LIKE ? OR prenom LIKE ? OR email LIKE ?)";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }
        if ($role !== '') {
            $sql .= " AND role = ?";
            $params[] = $role;
        }
        if ($status !== '') {
            $sql .= " AND status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $this->mapRecordsToUsers($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // -------------------------------------------------------
    // Validation
    // -------------------------------------------------------
    protected function addFieldError(array &$errors, $field, $message) {
        if (!isset($errors[$field])) {
            $errors[$field] = $message;
        }
    }

    protected function mergeErrors(array $errors, array $newErrors) {
        foreach ($newErrors as $field => $message) {
            $this->addFieldError($errors, $field, $message);
        }
        return $errors;
    }

    protected function validateRegistrationUser(User $user, $confirmPassword) {
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

    protected function validateProfileUser(User $user) {
        $errors = [];

        if (trim($user->getNom()) === '') {
            $this->addFieldError($errors, 'nom', 'Le nom est requis.');
        }
        if (trim($user->getPrenom()) === '') {
            $this->addFieldError($errors, 'prenom', 'Le prenom est requis.');
        }
        if (trim($user->getEmail()) === '') {
            $this->addFieldError($errors, 'email', "L'email est requis.");
        } elseif (!filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL)) {
            $this->addFieldError($errors, 'email', 'Email invalide.');
        }

        return $errors;
    }

    protected function validatePasswordChange($password, $confirmPassword) {
        $errors = $this->validatePasswordRules($password, 'Le nouveau mot de passe est requis.', 'new_password');

        if ($password !== $confirmPassword) {
            $this->addFieldError($errors, 'confirm_password', 'Les mots de passe ne correspondent pas.');
        }

        return $errors;
    }

    protected function validatePasswordRules($password, $requiredMessage, $field = 'password') {
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

    protected function validateAdminCreate(User $user) {
        $errors = $this->validateProfileUser($user);
        $errors = $this->mergeErrors($errors, $this->validatePasswordRules($user->getPassword(), 'Le mot de passe est requis.', 'password'));

        if (!in_array($user->getRole(), ['freelancer', 'client'], true)) {
            $this->addFieldError($errors, 'role', 'Role invalide.');
        }

        return $errors;
    }

    protected function validateAdminUpdate(User $user) {
        $errors = $this->validateProfileUser($user);

        if (!in_array($user->getRole(), ['freelancer', 'client'], true)) {
            $this->addFieldError($errors, 'role', 'Role invalide.');
        }
        if (!in_array($user->getStatus(), ['active', 'banned', 'pending', 'rejected'], true)) {
            $this->addFieldError($errors, 'status', 'Statut invalide.');
        }

        return $errors;
    }

    // -------------------------------------------------------
    // File uploads
    // -------------------------------------------------------
    protected function handleAvatarUpload(&$user, &$success) {
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

        $updatedUser = new User(
            $user->getNom(),
            $user->getPrenom(),
            $user->getEmail(),
            '',
            $user->getRole(),
            $user->getBio(),
            $relativeAvatarPath,
            $user->getStatus()
        );
        $updatedUser->setId($user->getId());
        $this->update($updatedUser);

        $user = $this->getById($user->getId());
        $success = true;

        return $errors;
    }

    protected function handleDocumentsUpload(&$user, &$success) {
        $result = $this->processDocumentUploads($user);

        if (!empty($result['errors'])) {
            return $result['errors'];
        }

        if ($result['cv_path'] === null && $result['portfolio_path'] === null) {
            $targetField = $_POST['file_target'] ?? '_global';
            return [$targetField => 'Veuillez selectionner au moins un fichier.'];
        }

        $fileUser = new User();
        $fileUser->setId($user->getId());
        $fileUser->setCvUrl($result['cv_path']);
        $fileUser->setPortfolioUrl($result['portfolio_path']);
        $this->updateFiles($fileUser);

        $user = $this->getById($user->getId());
        $success = true;

        return [];
    }

    protected function processDocumentUploads(User $user) {
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

    // -------------------------------------------------------
    // Utilitaires
    // -------------------------------------------------------
    protected function ensureDirectory($path) {
        return is_dir($path) || (mkdir($path, 0777, true) && is_dir($path));
    }

    protected function uploadsPath($relativePath = '') {
        $relativePath = trim(str_replace('\\', '/', $relativePath), '/');
        $path = $this->projectPath('uploads');

        if ($relativePath !== '') {
            $path .= DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        }

        return $path;
    }

    protected function projectPath($relativePath = '') {
        $basePath = dirname(__DIR__);
        $relativePath = trim(str_replace('\\', '/', $relativePath), '/');

        if ($relativePath === '') {
            return $basePath;
        }

        return $basePath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    }
}
