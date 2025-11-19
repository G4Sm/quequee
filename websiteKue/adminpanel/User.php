<?php
// File: User.php

class User {
    public function __construct(
        public int $id,
        public string $username,
        public string $profile_image,
        public int $profile_crop_y,
        public string $role
    ) {}

    // Metode untuk mengembalikan data dalam format array lama (untuk kompatibilitas UI)
    public function toArray(): array {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'profile_image' => $this->profile_image,
            'profile_crop_y' => $this->profile_crop_y,
            'role' => $this->role,
        ];
    }
}