<?php

declare(strict_types=1);

namespace App\Models;

use Matrac\Framework\Model;

class Auth extends Model
{

    public static function getUserRecord(string $username): array|false
    {

        // Fetch user data
        $stmt = self::query(
            "SELECT `user_id`, `password_hash`, `username`, `email`, `first_name`, `last_name`, `role`
         FROM `users`
         WHERE `username` = ? AND `active` = 1
         LIMIT 1",
            [$username]
        );

        return $stmt->fetch();
    }

    public static function updateUserLastLogin(int $userId): void
    {

        // Update last login (optional - requires DB column)
        static::query(
            "UPDATE `users` SET `last_login` = NOW() WHERE `user_id` = ?",
            [$userId]
        );
    }

    public static function getCurrentUser(array $user): array
    {

        $stmt = static::query(
            "SELECT `user_id`, `username`, `email`, `first_name`, `last_name`, `role`
                FROM `users` WHERE `user_id` = ? AND `email` = ? AND `active` = ? LIMIT 1",
            [$user['id'], $user['email'], 1]
        );

        return $stmt->fetch();
    }
}
