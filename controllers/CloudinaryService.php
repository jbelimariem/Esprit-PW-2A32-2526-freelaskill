<?php
// controllers/CloudinaryService.php
// Upload files to Cloudinary — no SDK needed, uses pure cURL

class CloudinaryService {

    private string $cloudName  = 'dr5rqqmhm';
    private string $apiKey     = '413485291534265';
    private string $apiSecret  = 'nEWOHvnRgVmspaoKKpCjWkdaFMw';

    // ──────────────────────────────────────────────────────
    // Upload any file from a tmp path to Cloudinary
    // $resourceType: 'image' | 'raw' (for PDF, ZIP, DOCX)
    // $folder:       subfolder in your Cloudinary account
    // Returns: ['ok' => true, 'url' => 'https://...'] or ['ok' => false, 'error' => '...']
    // ──────────────────────────────────────────────────────
    public function upload(string $tmpPath, string $resourceType = 'image', string $folder = 'freelaskill'): array
    {
        $timestamp = time();
        $params    = [
            'folder'    => $folder,
            'timestamp' => $timestamp,
        ];

        // Build the signature (alphabetical order, key=value&...)
        ksort($params);
        $paramStr  = '';
        foreach ($params as $k => $v) {
            $paramStr .= $k . '=' . $v . '&';
        }
        $paramStr = rtrim($paramStr, '&');
        $signature = sha1($paramStr . $this->apiSecret);

        $uploadUrl = "https://api.cloudinary.com/v1_1/{$this->cloudName}/{$resourceType}/upload";

        $postFields = [
            'file'      => new CURLFile($tmpPath),
            'api_key'   => $this->apiKey,
            'timestamp' => $timestamp,
            'folder'    => $folder,
            'signature' => $signature,
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $uploadUrl,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $postFields,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            return ['ok' => false, 'error' => 'Cloudinary cURL error: ' . $curlErr];
        }

        $data = json_decode($response, true);

        if ($httpCode === 200 && isset($data['secure_url'])) {
            return ['ok' => true, 'url' => $data['secure_url']];
        }

        $errMsg = $data['error']['message'] ?? 'Cloudinary upload failed (HTTP ' . $httpCode . ')';
        return ['ok' => false, 'error' => $errMsg];
    }

    // Shortcut for images (avatar)
    public function uploadImage(string $tmpPath, string $folder = 'freelaskill/avatars'): array
    {
        return $this->upload($tmpPath, 'image', $folder);
    }

    // Shortcut for raw files (CV, Portfolio — PDF, ZIP, DOCX)
    public function uploadRaw(string $tmpPath, string $folder = 'freelaskill/documents'): array
    {
        return $this->upload($tmpPath, 'raw', $folder);
    }
}
