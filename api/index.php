<?php
// Pastikan folder cache ada di /tmp
if (!is_dir('/tmp/storage/framework/views')) {
    mkdir('/tmp/storage/framework/views', 0777, true);
}

// Jalankan Laravel lewat jembatan public
require __DIR__ . '/../public/index.php';
