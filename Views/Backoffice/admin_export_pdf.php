<?php
session_start();
require_once __DIR__ . '/../../controllers/contratController.php';
require_once __DIR__ . '/../../controllers/ruleController.php';
require_once __DIR__ . '/../../controllers/fpdf/fpdf.php';

// ── Helper : encode UTF-8 → windows-1252 pour FPDF ──────────────────────
function enc($str) {
    if (empty($str)) return '';
    return iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $str) ?: $str;
}

// ── Helper : décode signature base64 → fichier PNG temporaire ────────────
function sigToTemp($dataUrl) {
    if (empty($dataUrl) || strpos($dataUrl, 'data:image/') !== 0) return null;
    $parts = explode(',', $dataUrl, 2);
    if (count($parts) < 2) return null;
    $imgData = base64_decode($parts[1], true);
    if (!$imgData || strlen($imgData) < 100) return null;
    $isPng = (substr($imgData, 0, 8) === "\x89PNG\r\n\x1a\n");
    $isJpg = (substr($imgData, 0, 2) === "\xFF\xD8");
    if (!$isPng && !$isJpg) return null;
    $tmpFile = tempnam(sys_get_temp_dir(), 'sig_') . ($isPng ? '.png' : '.jpg');
    if (file_put_contents($tmpFile, $imgData) === false) return null;
    $info = @getimagesize($tmpFile);
    if (!$info || $info[0] < 2) { @unlink($tmpFile); return null; }
    return $tmpFile;
}

// ── Classe PDF avec header/footer professionnels ─────────────────────────
class PDF extends FPDF {

    public $docTitle = '';

    function Error($msg) {
        throw new Exception('FPDF: ' . $msg);
    }

    function Header() {
        // Bande bleue en haut
        $this->SetFillColor(37, 99, 235);
        $this->Rect(0, 0, 210, 22, 'F');

        // Logo texte
        $this->SetFont('Arial', 'B', 14);
        $this->SetTextColor(255, 255, 255);
        $this->SetXY(10, 5);
        $this->Cell(80, 12, 'FreelaSkill', 0, 0, 'L');

        // Sous-titre à droite
        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(180, 210, 255);
        $this->SetXY(100, 5);
        $this->Cell(100, 6, enc('Plateforme de gestion de contrats'), 0, 1, 'R');
        $this->SetXY(100, 11);
        $this->Cell(100, 6, 'Genere le ' . date('d/m/Y a H:i'), 0, 0, 'R');

        // Ligne de séparation sous le header
        $this->SetDrawColor(37, 99, 235);
        $this->SetLineWidth(0.5);
        $this->Line(10, 25, 200, 25);

        $this->Ln(10);
        $this->SetTextColor(0, 0, 0);
        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(0.2);
    }

    function Footer() {
        $this->SetY(-14);
        // Ligne de séparation
        $this->SetDrawColor(200, 200, 200);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(2);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(150, 150, 150);
        $this->Cell(95, 6, 'FreelaSkill - Document confidentiel', 0, 0, 'L');
        $this->Cell(95, 6, 'Page ' . $this->PageNo() . ' / {nb}', 0, 0, 'R');
    }

    // ── Section title bar ──────────────────────────────────────────────
    function SectionTitle($icon, $title) {
        $this->SetFillColor(37, 99, 235);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 10);
        $this->SetX(10);
        $this->Cell(190, 8, '  ' . enc($icon . '  ' . $title), 0, 1, 'L', true);
        $this->SetTextColor(0, 0, 0);
        $this->Ln(3);
    }

    // ── Ligne label / valeur ───────────────────────────────────────────
    function Row($label, $value, $fill = false) {
        if ($fill) $this->SetFillColor(245, 248, 255);
        else       $this->SetFillColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 9);
        $this->SetTextColor(100, 100, 100);
        $this->SetX(10);
        $this->Cell(45, 7, enc($label), 0, 0, 'L', $fill);
        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(30, 30, 30);
        $this->Cell(145, 7, enc($value), 0, 1, 'L', $fill);
    }

    // ── Badge statut coloré ────────────────────────────────────────────
    function StatusBadge($statut) {
        $colors = [
            'brouillon'  => [148, 163, 184],
            'en_attente' => [245, 158,  11],
            'actif'      => [ 37,  99, 235],
            'termine'    => [ 16, 185, 129],
            'annule'     => [239,  68,  68],
            'archive'    => [107, 114, 128],
        ];
        $c = $colors[$statut] ?? [100, 100, 100];
        $label = strtoupper(str_replace('_', ' ', $statut));

        $x = $this->GetX();
        $y = $this->GetY();
        $this->SetFillColor($c[0], $c[1], $c[2]);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 8);
        $this->SetXY($x, $y);
        $this->Cell(30, 6, enc($label), 0, 0, 'C', true);
        $this->SetTextColor(0, 0, 0);
    }
}

// ── Initialisation ────────────────────────────────────────────────────────
$pdf = new PDF('P', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->SetMargins(10, 30, 10);
$pdf->SetAutoPageBreak(true, 20);

try {

// ════════════════════════════════════════════════════════════════════════════
// EXPORT D'UN SEUL CONTRAT
// ════════════════════════════════════════════════════════════════════════════
if (isset($_GET['id'])) {

    $id      = intval($_GET['id']);
    $contrat = getContratById($id);
    if (!$contrat) die("Contrat introuvable.");
    $rules = getRulesByContratId($id);

    $pdf->AddPage();

    // ── Titre du contrat ──────────────────────────────────────────────
    $pdf->SetFont('Arial', 'B', 18);
    $pdf->SetTextColor(37, 99, 235);
    $pdf->SetX(10);
    $pdf->Cell(190, 10, enc($contrat['titre']), 0, 1, 'C');
    $pdf->Ln(1);

    // Statut badge + date sur la même ligne
    $pdf->SetX(10);
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(120, 120, 120);
    $pdf->Cell(130, 6, enc('Contrat #' . $id . '  |  Cree le ' . date('d/m/Y', strtotime($contrat['date_creation']))), 0, 0, 'L');
    $pdf->StatusBadge($contrat['statut']);
    $pdf->Ln(8);

    // Ligne séparatrice
    $pdf->SetDrawColor(220, 230, 255);
    $pdf->SetLineWidth(0.4);
    $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
    $pdf->Ln(5);

    // ── Section 1 : Informations générales ───────────────────────────
    $pdf->SectionTitle('>', 'Informations generales');

    $fill = false;
    $pdf->Row('Statut',    strtoupper(str_replace('_', ' ', $contrat['statut'])), $fill); $fill = !$fill;
    $pdf->Row('Budget',    number_format($contrat['budget'], 2, ',', ' ') . ' DT', $fill); $fill = !$fill;
    $pdf->Row('Delai',     $contrat['delai'] . ' jours', $fill); $fill = !$fill;
    $pdf->Row('Freelancer', $contrat['freelance_info'] ?? '-', $fill); $fill = !$fill;
    $pdf->Row('Cree le',   date('d/m/Y', strtotime($contrat['date_creation'])), $fill);
    $pdf->Ln(5);

    // ── Section 2 : Description ───────────────────────────────────────
    $pdf->SectionTitle('>', 'Description du contrat');
    $pdf->SetFont('Arial', '', 8.5);
    $pdf->SetTextColor(50, 50, 50);
    $pdf->SetFillColor(250, 251, 255);
    $pdf->SetX(10);
    // Limiter la description à 800 caractères pour éviter les pages vides
    $descRaw = $contrat['description'] ?: 'Aucune description.';
    $descTrunc = mb_strlen($descRaw) > 800 ? mb_substr($descRaw, 0, 800) . '...' : $descRaw;
    $desc = enc($descTrunc);
    $pdf->MultiCell(190, 5, $desc, 0, 'L', true);
    $pdf->Ln(4);

    // ── Section 3 : Règles associées ─────────────────────────────────
    $pdf->SectionTitle('>', 'Regles associees (' . count($rules) . ')');

    if (empty($rules)) {
        $pdf->SetFont('Arial', 'I', 9);
        $pdf->SetTextColor(150, 150, 150);
        $pdf->SetX(10);
        $pdf->Cell(190, 7, 'Aucune regle associee a ce contrat.', 0, 1, 'C');
        $pdf->SetTextColor(0, 0, 0);
    } else {
        // En-tête du tableau des règles
        $pdf->SetFillColor(230, 238, 255);
        $pdf->SetTextColor(37, 99, 235);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetX(10);
        $pdf->Cell(6,   7, '#',       1, 0, 'C', true);
        $pdf->Cell(65,  7, 'Titre',   1, 0, 'L', true);
        $pdf->Cell(28,  7, 'Type',    1, 0, 'C', true);
        $pdf->Cell(28,  7, 'Valeur',  1, 0, 'C', true);
        $pdf->Cell(20,  7, 'Statut',  1, 0, 'C', true);
        $pdf->Cell(43,  7, 'Description', 1, 1, 'L', true);

        $pdf->SetTextColor(30, 30, 30);
        $pdf->SetFont('Arial', '', 8);
        $rowFill = false;

        foreach ($rules as $i => $r) {
            $fillColor = $rowFill ? [248, 250, 255] : [255, 255, 255];
            $pdf->SetFillColor($fillColor[0], $fillColor[1], $fillColor[2]);
            $lineH = 5.5;
            $pdf->SetX(10);
            $pdf->Cell(6,  $lineH, ($i + 1),                                          1, 0, 'C', $rowFill);
            $pdf->Cell(65, $lineH, enc(mb_substr($r['titre'], 0, 35)),                1, 0, 'L', $rowFill);
            $pdf->Cell(28, $lineH, enc($r['type'] ?? '-'),                            1, 0, 'C', $rowFill);
            $pdf->Cell(28, $lineH, enc(mb_substr($r['valeur'] ?? '-', 0, 15)),        1, 0, 'C', $rowFill);
            $pdf->Cell(20, $lineH, enc(ucfirst($r['statut'])),                        1, 0, 'C', $rowFill);
            $pdf->Cell(43, $lineH, enc(mb_substr($r['description'] ?? '-', 0, 30)),   1, 1, 'L', $rowFill);
            $rowFill = !$rowFill;
        }
    }
    $pdf->Ln(5);

    // ── Section 4 : Signatures ────────────────────────────────────────
    // Vérifier s'il reste assez de place (au moins 55mm) pour les signatures
    $sigH = 38;
    $sigW = 88;
    $gap  = 14;
    $spaceNeeded = 55; // hauteur totale : titre section + cadres + note

    if ($pdf->GetY() > (297 - 20 - $spaceNeeded)) {
        // Pas assez de place — nouvelle page
        $pdf->AddPage();
    }

    $pdf->SectionTitle('>', 'Signatures');

    $sigClientFile    = sigToTemp($contrat['signature_client']    ?? '');
    $sigFreelanceFile = sigToTemp($contrat['signature_freelance'] ?? '');

    $sigY = $pdf->GetY();

    // Désactiver le saut de page automatique pendant les signatures
    $pdf->SetAutoPageBreak(false);

    // ── Cadre Client ──
    $pdf->SetDrawColor(200, 210, 240);
    $pdf->SetFillColor(248, 250, 255);
    $pdf->SetLineWidth(0.3);
    $pdf->Rect(10, $sigY, $sigW, $sigH, 'DF');

    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetTextColor(37, 99, 235);
    $pdf->SetXY(10, $sigY + 2);
    $pdf->Cell($sigW, 5, 'SIGNATURE CLIENT', 0, 1, 'C');

    if ($sigClientFile) {
        try { $pdf->Image($sigClientFile, 14, $sigY + 8, $sigW - 8, $sigH - 12); }
        catch (Exception $e) { /* ignore */ }
        @unlink($sigClientFile);
    } else {
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->SetTextColor(180, 180, 180);
        $pdf->SetXY(10, $sigY + ($sigH / 2) - 2);
        $pdf->Cell($sigW, 5, 'Non signe', 0, 0, 'C');
    }

    // ── Cadre Freelancer ──
    $xF = 10 + $sigW + $gap;
    $pdf->SetFillColor(248, 250, 255);
    $pdf->Rect($xF, $sigY, $sigW, $sigH, 'DF');

    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetTextColor(37, 99, 235);
    $pdf->SetXY($xF, $sigY + 2);
    $pdf->Cell($sigW, 5, 'SIGNATURE FREELANCER', 0, 1, 'C');

    if ($sigFreelanceFile) {
        try { $pdf->Image($sigFreelanceFile, $xF + 4, $sigY + 8, $sigW - 8, $sigH - 12); }
        catch (Exception $e) { /* ignore */ }
        @unlink($sigFreelanceFile);
    } else {
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->SetTextColor(180, 180, 180);
        $pdf->SetXY($xF, $sigY + ($sigH / 2) - 2);
        $pdf->Cell($sigW, 5, 'Non signe', 0, 0, 'C');
    }

    // Réactiver le saut de page automatique
    $pdf->SetAutoPageBreak(true, 20);
    $pdf->SetY($sigY + $sigH + 5);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetDrawColor(0, 0, 0);

    // ── Note de bas de contrat ────────────────────────────────────────
    $pdf->SetFont('Arial', 'I', 7.5);
    $pdf->SetTextColor(150, 150, 150);
    $pdf->SetX(10);
    $pdf->MultiCell(190, 4.5, enc(
        'Ce document est genere automatiquement par la plateforme FreelaSkill. ' .
        'Il constitue un contrat de prestation entre les parties signataires. ' .
        'Toute modification apres signature est invalide sans accord mutuel.'
    ), 0, 'C');

    $pdf->Output('I', 'Contrat_' . $id . '.pdf');

// ════════════════════════════════════════════════════════════════════════════
// EXPORT LISTE DE TOUS LES CONTRATS
// ════════════════════════════════════════════════════════════════════════════
} elseif (isset($_GET['action']) && $_GET['action'] === 'export_all') {

    $contrats = getAllContrats();
    $pdf->AddPage();

    // ── Titre ─────────────────────────────────────────────────────────
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->SetTextColor(37, 99, 235);
    $pdf->SetX(10);
    $pdf->Cell(190, 10, 'Liste complete des contrats', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(150, 150, 150);
    $pdf->SetX(10);
    $pdf->Cell(190, 5, enc(count($contrats) . ' contrat(s) au ' . date('d/m/Y')), 0, 1, 'C');
    $pdf->Ln(4);

    // ── En-tête tableau ───────────────────────────────────────────────
    $pdf->SetFillColor(37, 99, 235);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetX(10);
    $pdf->Cell(7,   8, '#',         1, 0, 'C', true);
    $pdf->Cell(68,  8, 'Titre',     1, 0, 'L', true);
    $pdf->Cell(30,  8, 'Budget',    1, 0, 'C', true);
    $pdf->Cell(20,  8, 'Delai',     1, 0, 'C', true);
    $pdf->Cell(30,  8, 'Statut',    1, 0, 'C', true);
    $pdf->Cell(35,  8, 'Cree le',   1, 1, 'C', true);

    // ── Lignes ────────────────────────────────────────────────────────
    $pdf->SetTextColor(30, 30, 30);
    $pdf->SetFont('Arial', '', 8.5);

    $statusColors = [
        'brouillon'  => [148, 163, 184],
        'en_attente' => [245, 158,  11],
        'actif'      => [ 37,  99, 235],
        'termine'    => [ 16, 185, 129],
        'annule'     => [239,  68,  68],
        'archive'    => [107, 114, 128],
    ];

    $rowFill = false;
    foreach ($contrats as $i => $c) {
        $bg = $rowFill ? [245, 248, 255] : [255, 255, 255];
        $pdf->SetFillColor($bg[0], $bg[1], $bg[2]);

        $statut = $c['statut'];
        $sc = $statusColors[$statut] ?? [100, 100, 100];

        $pdf->SetX(10);
        $pdf->Cell(7,  7, ($i + 1),                                                    1, 0, 'C', $rowFill);
        $pdf->Cell(68, 7, enc(mb_substr($c['titre'], 0, 38)),                          1, 0, 'L', $rowFill);
        $pdf->Cell(30, 7, number_format($c['budget'], 2, ',', ' ') . ' DT',           1, 0, 'R', $rowFill);
        $pdf->Cell(20, 7, $c['delai'] . ' j',                                         1, 0, 'C', $rowFill);

        // Cellule statut colorée
        $pdf->SetFillColor($sc[0], $sc[1], $sc[2]);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 7.5);
        $pdf->Cell(30, 7, enc(strtoupper(str_replace('_', ' ', $statut))),             1, 0, 'C', true);

        // Retour couleur normale
        $pdf->SetFillColor($bg[0], $bg[1], $bg[2]);
        $pdf->SetTextColor(30, 30, 30);
        $pdf->SetFont('Arial', '', 8.5);
        $pdf->Cell(35, 7, date('d/m/Y', strtotime($c['date_creation'])),               1, 1, 'C', $rowFill);

        $rowFill = !$rowFill;
    }

    // ── Totaux ────────────────────────────────────────────────────────
    $pdf->Ln(4);
    $totalBudget = array_sum(array_column($contrats, 'budget'));
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetTextColor(37, 99, 235);
    $pdf->SetX(10);
    $pdf->Cell(125, 7, enc('Budget total : ' . number_format($totalBudget, 2, ',', ' ') . ' DT'), 0, 0, 'R');
    $pdf->Cell(65,  7, enc('Total : ' . count($contrats) . ' contrat(s)'), 0, 1, 'R');

    $pdf->Output('I', 'Liste_Contrats_' . date('Ymd') . '.pdf');

} else {
    die("Action invalide.");
}

} catch (Exception $e) {
    http_response_code(500);
    echo '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8">
    <title>Erreur Export PDF</title>
    <style>
        body{font-family:Arial,sans-serif;background:#0f172a;color:#cbd5e1;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0}
        .box{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);border-radius:16px;padding:2rem 2.5rem;max-width:560px;text-align:center}
        h2{color:#f87171;margin-bottom:1rem}p{color:#94a3b8;font-size:.9rem;line-height:1.6}
        .btn{display:inline-block;margin-top:1.5rem;padding:.6rem 1.5rem;background:#2563eb;color:#fff;border-radius:999px;text-decoration:none;font-size:.88rem}
        code{background:rgba(255,255,255,.05);padding:.2rem .5rem;border-radius:4px;font-size:.82rem;color:#f87171}
    </style></head><body>
    <div class="box">
        <h2>&#9888; Erreur lors de la generation du PDF</h2>
        <p>Une erreur s\'est produite pendant la creation du PDF.</p>
        <p><code>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</code></p>
        <a href="javascript:history.back()" class="btn">&#8592; Retour</a>
    </div></body></html>';
}
?>
