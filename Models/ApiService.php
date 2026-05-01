<?php
/**
 * Classe abstraite de base pour tous les services API externes.
 * Respecte le principe OOP : encapsulation, héritage, abstraction.
 */
abstract class ApiService
{
    protected string $baseUrl;
    protected int    $timeout = 10;

    /**
     * Effectue une requête HTTP via cURL (PDO-like : pas de fonctions globales).
     */
    protected function httpGet(string $url, array $params = []): array
    {
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'error' => 'cURL error: ' . $error];
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['success' => false, 'error' => 'Invalid JSON response', 'raw' => $response];
        }

        return ['success' => $httpCode >= 200 && $httpCode < 300, 'data' => $data, 'code' => $httpCode];
    }

    protected function httpPost(string $url, array $payload, array $headers = []): array
    {
        $defaultHeaders = ['Content-Type: application/json', 'Accept: application/json'];
        $allHeaders = array_merge($defaultHeaders, $headers);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER     => $allHeaders,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'error' => 'cURL error: ' . $error];
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['success' => false, 'error' => 'Invalid JSON response', 'raw' => $response];
        }

        return ['success' => $httpCode >= 200 && $httpCode < 300, 'data' => $data, 'code' => $httpCode];
    }

    /**
     * Méthode abstraite que chaque service doit implémenter.
     */
    abstract public function call(array $params): array;
}
