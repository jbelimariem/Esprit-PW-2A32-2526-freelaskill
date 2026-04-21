<?php
// Models/UserModel.php

require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/User.php';

class UserModel extends Model
{
    private $table = 'users';

    private function mapRecordToUser($record)
    {
        return $record ? new User($record) : null;
    }

    private function mapRecordsToUsers(array $records)
    {
        $users = [];

        foreach ($records as $record) {
            $users[] = new User($record);
        }

        return $users;
    }

    public function getAll()
    {
        return $this->mapRecordsToUsers($this->findAllRecords($this->table, 'created_at DESC'));
    }

    public function getById($id)
    {
        return $this->mapRecordToUser($this->findRecordById($this->table, $id));
    }

    public function getByEmail($email)
    {
        $statement = $this->query("SELECT * FROM {$this->table} WHERE email = ? LIMIT 1", [$email]);
        return $this->mapRecordToUser($statement->fetch(PDO::FETCH_ASSOC));
    }

    public function getByRole($role)
    {
        $statement = $this->query(
            "SELECT * FROM {$this->table} WHERE role = ? ORDER BY created_at DESC",
            [$role]
        );

        return $this->mapRecordsToUsers($statement->fetchAll(PDO::FETCH_ASSOC));
    }

    public function getByStatus($status)
    {
        $statement = $this->query(
            "SELECT * FROM {$this->table} WHERE status = ? ORDER BY created_at DESC",
            [$status]
        );

        return $this->mapRecordsToUsers($statement->fetchAll(PDO::FETCH_ASSOC));
    }

    public function create(User $user)
    {
        $this->query(
            "INSERT INTO {$this->table} (nom, prenom, email, password, role, bio, avatar, status, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())",
            [
                $user->getNom(),
                $user->getPrenom(),
                $user->getEmail(),
                password_hash($user->getPassword(), PASSWORD_DEFAULT),
                $user->getRole(),
                $user->getBio(),
                $user->getAvatar(),
                $user->getStatus(),
            ]
        );

        return self::$db->lastInsertId();
    }

    public function update(User $user)
    {
        $this->query(
            "UPDATE {$this->table} SET nom = ?, prenom = ?, email = ?, bio = ?, avatar = ? WHERE id = ?",
            [
                $user->getNom(),
                $user->getPrenom(),
                $user->getEmail(),
                $user->getBio(),
                $user->getAvatar(),
                $user->getId(),
            ]
        );
    }

    public function updateFull(User $user)
    {
        $this->query(
            "UPDATE {$this->table} SET nom = ?, prenom = ?, email = ?, bio = ?, role = ?, status = ? WHERE id = ?",
            [
                $user->getNom(),
                $user->getPrenom(),
                $user->getEmail(),
                $user->getBio(),
                $user->getRole(),
                $user->getStatus(),
                $user->getId(),
            ]
        );
    }

    public function updatePassword($id, $newPassword)
    {
        $this->query(
            "UPDATE {$this->table} SET password = ? WHERE id = ?",
            [password_hash($newPassword, PASSWORD_DEFAULT), $id]
        );
    }

    public function updateStatus($id, $status)
    {
        $this->query(
            "UPDATE {$this->table} SET status = ? WHERE id = ?",
            [$status, $id]
        );
    }

    public function delete($id)
    {
        $this->query("DELETE FROM {$this->table} WHERE id = ?", [$id]);
    }

    public function updateLinks(User $user)
    {
        $this->query(
            "UPDATE {$this->table} SET github_url = ?, linkedin_url = ? WHERE id = ?",
            [$user->getGithubUrl(), $user->getLinkedinUrl(), $user->getId()]
        );
    }

    public function updateFiles(User $user)
    {
        if ($user->getCvUrl() !== null && $user->getPortfolioUrl() !== null) {
            $this->query(
                "UPDATE {$this->table} SET cv_url = ?, portfolio_url = ? WHERE id = ?",
                [$user->getCvUrl(), $user->getPortfolioUrl(), $user->getId()]
            );
            return;
        }

        if ($user->getCvUrl() !== null) {
            $this->query(
                "UPDATE {$this->table} SET cv_url = ? WHERE id = ?",
                [$user->getCvUrl(), $user->getId()]
            );
        }

        if ($user->getPortfolioUrl() !== null) {
            $this->query(
                "UPDATE {$this->table} SET portfolio_url = ? WHERE id = ?",
                [$user->getPortfolioUrl(), $user->getId()]
            );
        }
    }

    public function clearLink($id, $field)
    {
        $allowed = ['github_url', 'linkedin_url'];
        if (!in_array($field, $allowed, true)) {
            return;
        }

        $this->query("UPDATE {$this->table} SET {$field} = '' WHERE id = ?", [$id]);
    }

    public function clearFile($id, $field)
    {
        $allowed = ['cv_url', 'portfolio_url'];
        if (!in_array($field, $allowed, true)) {
            return;
        }

        $this->query("UPDATE {$this->table} SET {$field} = NULL WHERE id = ?", [$id]);
    }

    public function login($email, $password)
    {
        $user = $this->getByEmail($email);

        if ($user && password_verify($password, $user->getPassword())) {
            return $user;
        }

        return false;
    }

    public function emailExists($email)
    {
        $statement = $this->query("SELECT COUNT(*) FROM {$this->table} WHERE email = ?", [$email]);
        return $statement->fetchColumn() > 0;
    }

    public function countAll()
    {
        return $this->query("SELECT COUNT(*) FROM {$this->table}")->fetchColumn();
    }

    public function countByRole($role)
    {
        $statement = $this->query("SELECT COUNT(*) FROM {$this->table} WHERE role = ?", [$role]);
        return $statement->fetchColumn();
    }

    public function countByStatus($status)
    {
        $statement = $this->query("SELECT COUNT(*) FROM {$this->table} WHERE status = ?", [$status]);
        return $statement->fetchColumn();
    }

    public function search($query)
    {
        $like = '%' . $query . '%';
        $statement = $this->query(
            "SELECT * FROM {$this->table} WHERE nom LIKE ? OR prenom LIKE ? OR email LIKE ?",
            [$like, $like, $like]
        );

        return $this->mapRecordsToUsers($statement->fetchAll(PDO::FETCH_ASSOC));
    }

    public function filter($search, $role, $status)
    {
        $sql = "SELECT * FROM {$this->table} WHERE 1 = 1";
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
        $statement = $this->query($sql, $params);

        return $this->mapRecordsToUsers($statement->fetchAll(PDO::FETCH_ASSOC));
    }
}
