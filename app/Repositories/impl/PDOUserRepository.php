<?php

namespace App\Repositories\impl;

use App\Models\User;
use App\Repositories\UserRepository;
use DateTime;
use PDO;

class PDOUserRepository extends PDORepository implements UserRepository
{
    public function findAll()
    {
        $pdo = self::getConnection();
        $sql = 'SELECT * FROM al_users';

        $stmt = $pdo->query($sql);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $users = [];

        foreach ($rows as $row)
        {
            $user = self::createUserFromRow($row);
            $users[] = $user;
        }

        return $users;
    }

    public function findById($id)
    {
        $pdo = self::getConnection();
        $sql = "SELECT * FROM al_users WHERE id = :id LIMIT 1";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $user = null;
        if ($row != null)
        {
            $user = self::createUserFromRow($row);
        }

        return $user;
    }

    public function findByEmail($email)
    {
        $pdo = self::getConnection();
        $sql = "SELECT * FROM al_users WHERE id = :email LIMIT 1";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['email' => $email]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $user = null;
        if ($row != null)
        {
            $user = self::createUserFromRow($row);
        }

        return $user;
    }

    public function matchEmailWithPassword($email, $password)
    {
        $pdo = self::getConnection();
        $sql = "SELECT * FROM al_users WHERE email = :email AND password = :password LIMIT 1";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['email' => $email, 'password' => $password]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $user = null;
        if ($row != null)
        {
            $user = self::createUserFromRow($row);
        }

        return $user;
    }

    public function existsByEmail($email)
    {
        $pdo = self::getConnection();
        $sql = "SELECT COUNT(*) FROM al_users WHERE email = :email";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['email' => $email]);
        
        return $stmt->fetchColumn() > 0;
    }

    public function save($entity)
    {
        $pdo = self::getConnection();

        if (isset($entity->id)) {
            $sql = "UPDATE al_users SET username = :username, email = :email, hash = :hash;";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'username' => $entity->username,
                'email' => $entity->email,
                'hash' => $entity->hash,
                'id' => $entity->id
            ]);
        } else {
            $sql = "INSERT INTO al_users (username, email, hash) VALUES (:username, :email, :hash)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'username' => $entity->username,
                'email' => $entity->email,
                'hash' => $entity->hash
            ]);
            $entity->id = $pdo->lastInsertId();
        }

        return $this->findById($entity->id);
    }

    public function delete($entity)
    {
        $pdo = self::getConnection();
        $sql = "DELETE FROM al_users WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $entity->id]);
    }

    private static function createUserFromRow($row): User
    {
        $createdAt = new DateTime($row['created_at']);
        $updatedAt = new DateTime($row['updated_at']);

        $user = new User;
        $user->id = $row['id'];
        $user->email = $row['email'];
        $user->createdAt = $createdAt;
        $user->updatedAt = $updatedAt;

        return $user;
    }
}