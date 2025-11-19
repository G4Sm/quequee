<?php
// File: Event.php

class Event {
    public function __construct(
        public int $id_event,
        public string $judul,
        public string $tanggal,
        public int $pelihat,
        public string $kategori,
        public ?string $gambar, // Bisa null
        public int $crop_y
    ) {}
}