<?php
session_start();
require_once __DIR__ . '/../../controllers/contratController.php';
require_once __DIR__ . '/../../controllers/fpdf/fpdf.php';

class PDF extends FPDF {
    // En-tête
    function Header() {
        $this->SetFont('Arial', 'B', 15);
        $this->SetTextColor(37, 99, 235); // Tech blue
        $this->Cell(0, 10, 'FreelaSkill - Administration', 0, 1, 'C');
        $this->SetFont('Arial', 'I', 10);
        $this->SetTextColor(128, 128, 128);
        $this->Cell(0, 5, 'Rapport de Contrat(s)', 0, 1, 'C');
        $this->Ln(10);
    }

    // Pied de page
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(128, 128, 128);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb} - Genere le ' . date('d/m/Y H:i'), 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

if (isset($_GET['id'])) {
    // Export d'un seul contrat
    $id = intval($_GET['id']);
    $contrat = getContratById($id);
    
    if (!$contrat) {
        die("Contrat introuvable.");
    }
    
    // Titre
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);
    // Supprimer les accents pour éviter les problèmes d'encodage FPDF par défaut
    $titre = iconv('UTF-8', 'windows-1252', $contrat['titre']);
    $pdf->Cell(0, 10, $titre, 0, 1, 'C');
    $pdf->Ln(5);
    
    // Informations de base
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(240, 240, 240);
    $pdf->Cell(0, 8, ' Informations Generales', 0, 1, 'L', true);
    
    $pdf->SetFont('Arial', '', 11);
    $pdf->Ln(2);
    $pdf->Cell(50, 7, 'Statut:', 0, 0);
    $pdf->Cell(0, 7, strtoupper($contrat['statut']), 0, 1);
    
    $pdf->Cell(50, 7, 'Budget:', 0, 0);
    $pdf->Cell(0, 7, $contrat['budget'] . ' DT', 0, 1);
    
    $pdf->Cell(50, 7, 'Delai:', 0, 0);
    $pdf->Cell(0, 7, $contrat['delai'] . ' jours', 0, 1);
    
    $pdf->Cell(50, 7, 'Cree le:', 0, 0);
    $pdf->Cell(0, 7, date('d/m/Y', strtotime($contrat['date_creation'])), 0, 1);
    
    $pdf->Ln(5);
    
    // Description
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 8, ' Description du Contrat', 0, 1, 'L', true);
    $pdf->Ln(2);
    $pdf->SetFont('Arial', '', 11);
    $desc = iconv('UTF-8', 'windows-1252', $contrat['description']);
    $pdf->MultiCell(0, 6, $desc);
    
    $pdf->Ln(10);
    
    // Règles associées
    require_once __DIR__ . '/../../controllers/ruleController.php';
    $rules = getRulesByContratId($id);
    
    if (!empty($rules)) {
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetFillColor(240, 240, 240);
        $pdf->Cell(0, 8, ' Regles Associees', 0, 1, 'L', true);
        $pdf->Ln(3);

        foreach ($rules as $index => $r) {
            // Numéro + titre de la règle
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->SetFillColor(230, 238, 255);
            $r_titre = iconv('UTF-8', 'windows-1252//TRANSLIT', ($index + 1) . '. ' . $r['titre']);
            $pdf->Cell(0, 8, $r_titre, 0, 1, 'L', true);

            // Type et valeur sur une ligne
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetFillColor(255, 255, 255);
            if (!empty($r['type'])) {
                $pdf->Cell(40, 6, 'Type :', 0, 0);
                $pdf->Cell(0, 6, iconv('UTF-8', 'windows-1252//TRANSLIT', $r['type']), 0, 1);
            }
            if (!empty($r['valeur'])) {
                $pdf->Cell(40, 6, 'Valeur :', 0, 0);
                $pdf->Cell(0, 6, iconv('UTF-8', 'windows-1252//TRANSLIT', $r['valeur']), 0, 1);
            }

            // Statut
            $pdf->Cell(40, 6, 'Statut :', 0, 0);
            $pdf->Cell(0, 6, ucfirst($r['statut']), 0, 1);

            // Description détaillée
            if (!empty($r['description'])) {
                $pdf->SetFont('Arial', 'I', 10);
                $pdf->SetTextColor(60, 60, 60);
                $pdf->Cell(40, 6, 'Description :', 0, 1);
                $r_desc = iconv('UTF-8', 'windows-1252//TRANSLIT', $r['description']);
                $pdf->MultiCell(0, 5, $r_desc, 0, 'L');
                $pdf->SetTextColor(0, 0, 0);
            }

            $pdf->Ln(4);
        }

        $pdf->Ln(4);
        $pdf->SetFillColor(240, 240, 240);
    } else {
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetFillColor(240, 240, 240);
        $pdf->Cell(0, 8, ' Regles Associees', 0, 1, 'L', true);
        $pdf->Ln(2);
        $pdf->SetFont('Arial', 'I', 10);
        $pdf->SetTextColor(128, 128, 128);
        $pdf->Cell(0, 7, 'Aucune regle associee a ce contrat.', 0, 1);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Ln(5);
        $pdf->SetFillColor(240, 240, 240);
    }

    // Intervenants et signatures
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(240, 240, 240);
    $pdf->Cell(0, 8, ' Intervenants & Signatures', 0, 1, 'L', true);
    $pdf->Ln(2);
    $pdf->SetFont('Arial', '', 11);
    $pdf->SetTextColor(0, 0, 0);

    $pdf->Cell(50, 7, 'Freelancer:', 0, 0);
    $pdf->Cell(0, 7, iconv('UTF-8', 'windows-1252//TRANSLIT', $contrat['freelance_info'] ?? ''), 0, 1);

    $pdf->Ln(8);

    // Fonction helper pour décoder et sauvegarder une signature base64 en fichier temp
    $saveSigToTemp = function($dataUrl) {
        if (empty($dataUrl) || strpos($dataUrl, 'data:image/') !== 0) return null;
        $parts = explode(',', $dataUrl, 2);
        if (count($parts) < 2) return null;
        $imgData = base64_decode($parts[1]);
        if (!$imgData) return null;
        $tmpFile = tempnam(sys_get_temp_dir(), 'sig_') . '.png';
        file_put_contents($tmpFile, $imgData);
        return $tmpFile;
    };

    // Titres des colonnes signatures
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(95, 7, 'Signature Client', 0, 0, 'C');
    $pdf->Cell(95, 7, 'Signature Freelancer', 0, 1, 'C');
    $pdf->Ln(2);

    $sigClientFile    = $saveSigToTemp($contrat['signature_client'] ?? '');
    $sigFreelanceFile = $saveSigToTemp($contrat['signature_freelance'] ?? '');

    $sigY = $pdf->GetY();
    $sigH = 35; // hauteur de la zone signature

    // Cadre client
    $pdf->Rect(10, $sigY, 90, $sigH);
    if ($sigClientFile) {
        $pdf->Image($sigClientFile, 12, $sigY + 2, 86, $sigH - 4);
        @unlink($sigClientFile);
    } else {
        $pdf->SetFont('Arial', 'I', 9);
        $pdf->SetTextColor(150, 150, 150);
        $pdf->SetXY(10, $sigY + ($sigH / 2) - 3);
        $pdf->Cell(90, 6, 'Non signe', 0, 0, 'C');
        $pdf->SetTextColor(0, 0, 0);
    }

    // Cadre freelancer
    $pdf->Rect(105, $sigY, 90, $sigH);
    if ($sigFreelanceFile) {
        $pdf->Image($sigFreelanceFile, 107, $sigY + 2, 86, $sigH - 4);
        @unlink($sigFreelanceFile);
    } else {
        $pdf->SetFont('Arial', 'I', 9);
        $pdf->SetTextColor(150, 150, 150);
        $pdf->SetXY(105, $sigY + ($sigH / 2) - 3);
        $pdf->Cell(90, 6, 'Non signe', 0, 0, 'C');
        $pdf->SetTextColor(0, 0, 0);
    }

    $pdf->SetY($sigY + $sigH + 5);
    $pdf->Ln(5);
    
    $pdf->Output('I', 'Contrat_' . $id . '.pdf');

} elseif (isset($_GET['action']) && $_GET['action'] === 'export_all') {
    // Export de la liste de tous les contrats
    $contrats = getAllContrats();
    
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 10, 'Liste Complete des Contrats', 0, 1, 'C');
    $pdf->Ln(5);
    
    // En-têtes du tableau
    $pdf->SetFillColor(37, 99, 235);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Arial', 'B', 10);
    
    $pdf->Cell(80, 8, 'Titre', 1, 0, 'C', true);
    $pdf->Cell(30, 8, 'Budget', 1, 0, 'C', true);
    $pdf->Cell(30, 8, 'Statut', 1, 0, 'C', true);
    $pdf->Cell(50, 8, 'Creation', 1, 1, 'C', true);
    
    // Données
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', '', 10);
    
    $fill = false;
    foreach ($contrats as $c) {
        $pdf->SetFillColor(245, 245, 245);
        $titre = substr(iconv('UTF-8', 'windows-1252//TRANSLIT', $c['titre']), 0, 40);
        
        $pdf->Cell(80, 8, $titre, 1, 0, 'L', $fill);
        $pdf->Cell(30, 8, $c['budget'] . ' DT', 1, 0, 'C', $fill);
        $pdf->Cell(30, 8, ucfirst($c['statut']), 1, 0, 'C', $fill);
        $pdf->Cell(50, 8, date('d/m/Y', strtotime($c['date_creation'])), 1, 1, 'C', $fill);
        
        $fill = !$fill;
    }
    
    $pdf->Output('I', 'Liste_Contrats.pdf');
} else {
    die("Action invalide.");
}
?>
