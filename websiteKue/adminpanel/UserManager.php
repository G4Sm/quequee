<?php
// File: UserManager.php

require_once 'Database.php';
require_once 'User.php';

class UserManager {
    /**
     * Mengambil data pengguna berdasarkan username.
     * @param string $username
     * @return User|null
     */
    public function getUserByUsername(string $username): ?User {
        $sql = "SELECT id, profile_image, profile_crop_y, role FROM users WHERE username = ?";
        
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        $currentImage = $row['profile_image'] ?? 'uploads/profiles/default.png';
        $currentCropY = intval($row['profile_crop_y'] ?? 50);
        
        // Memastikan crop_y dalam batas 0-100
        $currentCropY = max(0, min(100, $currentCropY));
        
        return new User(
            id: $row['id'],
            username: $username,
            profile_image: htmlspecialchars($currentImage),
            profile_crop_y: $currentCropY,
            role: $row['role'] ?? 'Pengguna'
        );
    }
}