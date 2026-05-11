<?php
require 'config.php';
$pdo = config::getConnexion();

echo "--- OFFRES_EMPLOI ---\n";
$q = $pdo->query('DESCRIBE offres_emploi');
while($r = $q->fetch(PDO::FETCH_ASSOC)) {
    echo $r['Field'] . " - " . $r['Type'] . "\n";
}

echo "\n--- JOB_APPLICATIONS ---\n";
$q = $pdo->query('DESCRIBE job_applications');
while($r = $q->fetch(PDO::FETCH_ASSOC)) {
    echo $r['Field'] . " - " . $r['Type'] . "\n";
}
