<?php
class SmtpClient {
    private $server = 'smtp.gmail.com';
    private $port = 587;
    private $user;
    private $pass;
    private $socket = null;
    private $logs = [];
    private $lastError = '';

    public function __construct($user, $pass) {
        $this->user = trim((string)$user);
        $this->pass = str_replace(' ', '', trim((string)$pass));
    }

    private function readResponse() {
        $response = '';
        while ($line = fgets($this->socket, 515)) {
            $response .= $line;
            if (strlen($line) >= 4 && substr($line, 3, 1) === ' ') {
                break;
            }
        }
        return $response;
    }

    private function execute($command, $expectedResponse) {
        if ($command !== null) {
            fputs($this->socket, $command . "\r\n");
        }

        $response = $this->readResponse();
        $safeCommand = preg_match('/^[A-Za-z0-9+\/=]+$/', (string)$command) ? '[base64 hidden]' : (string)$command;
        $this->logs[] = "OUT: $safeCommand | IN: $response";

        if (substr($response, 0, 3) !== (string)$expectedResponse) {
            throw new Exception("SMTP Error: Expected $expectedResponse but got $response");
        }

        return $response;
    }

    private function serverName() {
        return !empty($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost';
    }

    private function encodeHeader($value) {
        if (function_exists('mb_encode_mimeheader')) {
            return mb_encode_mimeheader($value, 'UTF-8', 'B', "\r\n");
        }

        return '=?UTF-8?B?' . base64_encode($value) . '?=';
    }

    private function dotStuff($message) {
        $message = str_replace(["\r\n", "\r"], "\n", (string)$message);
        return preg_replace('/^\./m', '..', $message);
    }

    private function validateConfig($to) {
        if ($this->user === '' || $this->user === 'votre_email@gmail.com') {
            throw new Exception("GMAIL_USER n'est pas configure avec une vraie adresse Gmail.");
        }

        if (!filter_var($this->user, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("GMAIL_USER n'est pas une adresse email valide.");
        }

        if ($this->pass === '' || strlen($this->pass) < 16) {
            throw new Exception("GMAIL_PASS doit etre un mot de passe d'application Gmail de 16 caracteres.");
        }

        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Adresse destinataire invalide: $to");
        }
    }

    public function send($to, $fromName, $subject, $htmlMessage) {
        try {
            $this->validateConfig($to);

            $context = stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ],
            ]);

            $this->socket = stream_socket_client(
                "tcp://{$this->server}:{$this->port}",
                $errno,
                $errstr,
                30,
                STREAM_CLIENT_CONNECT,
                $context
            );

            if (!$this->socket) {
                throw new Exception("Could not connect to {$this->server}: $errstr ($errno)");
            }

            stream_set_timeout($this->socket, 30);
            $serverName = $this->serverName();

            $this->execute(null, '220');
            $this->execute('EHLO ' . $serverName, '250');
            $this->execute('STARTTLS', '220');

            // Gmail refuse l'authentification tant que STARTTLS n'est pas actif.
            if (!stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new Exception('Failed to start TLS encryption.');
            }

            $this->execute('EHLO ' . $serverName, '250');
            $this->execute('AUTH LOGIN', '334');
            $this->execute(base64_encode($this->user), '334');
            $this->execute(base64_encode($this->pass), '235');

            $this->execute("MAIL FROM: <{$this->user}>", '250');
            $this->execute("RCPT TO: <$to>", '250');
            $this->execute('DATA', '354');

            $headers = [
                "From: {$fromName} <{$this->user}>",
                "To: <$to>",
                'Subject: ' . $this->encodeHeader($subject),
                'MIME-Version: 1.0',
                'Content-Type: text/html; charset=UTF-8',
                'Content-Transfer-Encoding: 8bit',
                'Date: ' . date('r'),
                'Message-ID: <' . time() . '.' . bin2hex(random_bytes(4)) . '@' . $serverName . '>',
            ];

            $data = implode("\r\n", $headers) . "\r\n\r\n" . $this->dotStuff($htmlMessage) . "\r\n.";
            $this->execute($data, '250');
            $this->execute('QUIT', '221');

            fclose($this->socket);
            $this->socket = null;
            return true;
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            $this->logs[] = 'CRITICAL ERROR: ' . $this->lastError;
            error_log('[TalentBridge SMTP] ' . $this->lastError);

            if ($this->socket) {
                fclose($this->socket);
                $this->socket = null;
            }

            return false;
        }
    }

    public function getLogs() {
        return $this->logs;
    }

    public function getLastError() {
        return $this->lastError;
    }
}
