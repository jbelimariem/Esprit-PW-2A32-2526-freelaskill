<?php

class EmailApiService
{
    private $config;

    public function __construct(array $config = [])
    {
        $this->config = $config ?: require __DIR__ . '/../../controllers/email_config.php';
    }

    public function sendPasswordReset($email, $prenom, $code)
    {
        $subject = 'FreelaSkill - Code de reinitialisation';

        return $this->sendEmail([
            'to_email' => $email,
            'to_name'  => $prenom,
            'subject'  => $subject,
            'html'     => $this->buildPasswordResetHtml($prenom, $code),
            'text'     => "Bonjour {$prenom},\n\nVotre code de verification FreelaSkill : {$code}\n\nValable 15 minutes.",
        ]);
    }

    public function sendEmail(array $message)
    {
        if (!function_exists('curl_init')) {
            return ['ok' => false, 'error' => 'Extension PHP cURL manquante pour appeler API email.'];
        }

        $provider = strtolower(trim((string) ($this->config['provider'] ?? 'brevo')));

        if ($provider === 'resend') {
            return $this->sendViaResend($message);
        }

        if ($provider === 'generic') {
            return $this->sendViaGenericApi($message);
        }

        return $this->sendViaBrevo($message);
    }

    private function sendViaBrevo(array $message)
    {
        $apiKey = trim((string) ($this->config['api_key'] ?? ''));

        if ($apiKey === '') {
            return ['ok' => false, 'error' => 'Cle API email manquante. Configurez MAIL_API_KEY ou controllers/email_config.local.php.'];
        }

        $payload = [
            'sender'      => [
                'email' => $this->getFromEmail(),
                'name'  => $this->getFromName(),
            ],
            'to'          => [[
                'email' => $message['to_email'],
                'name'  => $message['to_name'] ?? '',
            ]],
            'subject'     => $message['subject'],
            'htmlContent' => $message['html'],
            'textContent' => $message['text'],
        ];

        return $this->postJson(
            $this->getApiUrl('https://api.brevo.com/v3/smtp/email'),
            [
                'Accept: application/json',
                'Content-Type: application/json',
                'api-key: ' . $apiKey,
            ],
            $payload
        );
    }

    private function sendViaResend(array $message)
    {
        $apiKey = trim((string) ($this->config['api_key'] ?? ''));

        if ($apiKey === '') {
            return ['ok' => false, 'error' => 'Cle API email manquante. Configurez MAIL_API_KEY ou controllers/email_config.local.php.'];
        }

        $payload = [
            'from'    => $this->getFromName() . ' <' . $this->getFromEmail() . '>',
            'to'      => [$message['to_email']],
            'subject' => $message['subject'],
            'html'    => $message['html'],
            'text'    => $message['text'],
        ];

        return $this->postJson(
            $this->getApiUrl('https://api.resend.com/emails'),
            [
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
            $payload
        );
    }

    private function sendViaGenericApi(array $message)
    {
        $url = $this->getApiUrl('');

        if ($url === '') {
            return ['ok' => false, 'error' => 'URL API email manquante. Configurez MAIL_API_URL.'];
        }

        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
        ];

        $apiKey = trim((string) ($this->config['api_key'] ?? ''));

        if ($apiKey !== '') {
            $headers[] = 'Authorization: Bearer ' . $apiKey;
        }

        $payload = [
            'from_email' => $this->getFromEmail(),
            'from_name'  => $this->getFromName(),
            'to_email'   => $message['to_email'],
            'to_name'    => $message['to_name'] ?? '',
            'subject'    => $message['subject'],
            'html'       => $message['html'],
            'text'       => $message['text'],
        ];

        return $this->postJson($url, $headers, $payload);
    }

    private function postJson($url, array $headers, array $payload)
    {
        $body = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($body === false) {
            return ['ok' => false, 'error' => 'Impossible de preparer le JSON email.'];
        }

        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_CONNECTTIMEOUT => (int) ($this->config['connect_timeout'] ?? 8),
            CURLOPT_TIMEOUT        => (int) ($this->config['timeout'] ?? 25),
        ]);

        $response = curl_exec($ch);
        $status   = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            return ['ok' => false, 'error' => 'Impossible de contacter API email: ' . $error];
        }

        if ($status < 200 || $status >= 300) {
            return ['ok' => false, 'error' => $this->extractApiError($response, $status)];
        }

        return ['ok' => true, 'error' => ''];
    }

    private function extractApiError($response, $status)
    {
        $data = json_decode((string) $response, true);

        if (is_array($data)) {
            if (isset($data['message']) && is_string($data['message'])) {
                return 'API email HTTP ' . $status . ': ' . $data['message'];
            }

            if (isset($data['error']) && is_string($data['error'])) {
                return 'API email HTTP ' . $status . ': ' . $data['error'];
            }

            if (isset($data['error']['message']) && is_string($data['error']['message'])) {
                return 'API email HTTP ' . $status . ': ' . $data['error']['message'];
            }
        }

        $summary = trim((string) $response);

        if ($summary === '') {
            $summary = 'Erreur API email.';
        }

        return 'API email HTTP ' . $status . ': ' . substr($summary, 0, 300);
    }

    private function getApiUrl($default)
    {
        $url = trim((string) ($this->config['api_url'] ?? ''));
        return $url !== '' ? $url : $default;
    }

    private function getFromEmail()
    {
        return trim((string) ($this->config['from_email'] ?? ''));
    }

    private function getFromName()
    {
        $name = trim((string) ($this->config['from_name'] ?? ''));
        return $name !== '' ? $name : 'FreelaSkill';
    }

    private function buildPasswordResetHtml($prenom, $code)
    {
        $safeName = htmlspecialchars((string) $prenom, ENT_QUOTES, 'UTF-8');
        $safeCode = preg_replace('/\D/', '', (string) $code);

        $digits = implode('', array_map(function ($digit) {
            return '
            <td style="width:48px;height:56px;text-align:center;vertical-align:middle;
                        background:#1e293b;border:2px solid #334155;border-radius:10px;
                        font-size:28px;font-weight:700;font-family:monospace;color:#ffffff;">
                ' . htmlspecialchars($digit, ENT_QUOTES, 'UTF-8') . '
            </td>
            <td style="width:8px;"></td>';
        }, str_split($safeCode)));

        return '
        <!DOCTYPE html>
        <html>
        <body style="margin:0;padding:0;background:#0f172a;font-family:\'Segoe UI\',Arial,sans-serif;">
        <table width="100%" cellpadding="0" cellspacing="0">
          <tr>
            <td align="center" style="padding:40px 20px;">
              <table width="520" cellpadding="0" cellspacing="0" style="background:#1e293b;border-radius:20px;border:1px solid #334155;">
                <tr>
                  <td align="center" style="padding:36px 40px 24px;">
                    <div style="font-size:22px;font-weight:700;color:#ffffff;">
                      <span style="color:#ef4444;">&#9632;</span> Freela<span style="color:#3b82f6;">Skill</span>
                    </div>
                  </td>
                </tr>
                <tr>
                  <td style="padding:0 40px 32px;">
                    <p style="font-size:16px;color:#94a3b8;margin:0 0 8px;">Bonjour <strong style="color:#ffffff;">' . $safeName . '</strong>,</p>
                    <p style="font-size:15px;color:#94a3b8;margin:0 0 28px;line-height:1.6;">
                      Vous avez demande la reinitialisation de votre mot de passe.<br>
                      Utilisez ce code pour confirmer votre identite :
                    </p>
                    <table cellpadding="0" cellspacing="0" style="margin:0 auto 28px;">
                      <tr>' . $digits . '</tr>
                    </table>
                    <div style="background:#0f172a;border:1px solid #334155;border-radius:12px;padding:14px 20px;text-align:center;">
                      <span style="font-size:13px;color:#64748b;">
                        Ce code expire dans <strong style="color:#f59e0b;">15 minutes</strong>
                      </span>
                    </div>
                    <p style="font-size:13px;color:#475569;margin:24px 0 0;line-height:1.6;">
                      Si vous n avez pas demande cette reinitialisation, ignorez cet email.<br>
                      Votre mot de passe ne sera pas modifie.
                    </p>
                  </td>
                </tr>
                <tr>
                  <td style="border-top:1px solid #1e3a5f;padding:20px 40px;text-align:center;">
                    <p style="font-size:12px;color:#334155;margin:0;">&copy; 2026 FreelaSkill - Tous droits reserves</p>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
        </body>
        </html>';
    }
}
