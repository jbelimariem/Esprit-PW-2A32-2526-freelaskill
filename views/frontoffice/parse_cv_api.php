<?php
// views/frontoffice/parse_cv_api.php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../controllers/CvParseController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['cv_file'])) {
    $file = $_FILES['cv_file'];

    // Vérifications basiques
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['status' => 'error', 'message' => "Erreur lors de l'upload du fichier."]);
        exit;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ext !== 'pdf') {
        echo json_encode(['status' => 'error', 'message' => "Seuls les fichiers PDF sont supportés par l'IA pour le moment."]);
        exit;
    }

    if ($file['size'] > 5 * 1024 * 1024) { // 5 MB max pour Gemini
        echo json_encode(['status' => 'error', 'message' => "Le fichier est trop volumineux (5 Mo max)."]);
        exit;
    }

    $controller = new CvParseController();
    $extractedData = $controller->parsePdfCv($file['tmp_name']);

    if ($extractedData && is_array($extractedData)) {
        echo json_encode([
            'status' => 'success',
            'data' => $extractedData
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => "Impossible d'extraire les informations du CV."]);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Requête invalide']);
exit;
