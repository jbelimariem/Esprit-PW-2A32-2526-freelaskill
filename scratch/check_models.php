<?php
require 'secrets.php';
$url = 'https://generativelanguage.googleapis.com/v1beta/models?key=' . GEMINI_API_KEY;

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response: " . substr($response, 0, 1000) . "...\n";

$data = json_decode($response, true);
if (isset($data['models'])) {
    echo "\nAvailable models:\n";
    foreach ($data['models'] as $m) {
        echo "- " . $m['name'] . " (Methods: " . implode(', ', $m['supportedGenerationMethods']) . ")\n";
    }
} else {
    echo "\nNo models found or error: " . ($data['error']['message'] ?? 'Unknown error') . "\n";
}
