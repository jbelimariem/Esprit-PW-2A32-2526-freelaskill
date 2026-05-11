<?php
// api/upload_pdf.php
header('Content-Type: application/json');

require_once __DIR__ . '/../secrets.php';
require_once __DIR__ . '/../controllers/CloudinaryService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

if (!isset($_FILES['pdf'])) {
    echo json_encode(['ok' => false, 'error' => 'No PDF file provided']);
    exit;
}

$file = $_FILES['pdf'];
$cloudinary = new CloudinaryService();

// Upload to Cloudinary as 'raw' (since it's a PDF)
$result = $cloudinary->uploadRaw($file['tmp_name'], 'freelaskill/reports');

if ($result['ok']) {
    echo json_encode([
        'ok' => true,
        'url' => $result['url']
    ]);
} else {
    echo json_encode([
        'ok' => false,
        'error' => $result['error']
    ]);
}
