<?php
// views/frontoffice/google_auth.php
session_start();
require_once __DIR__ . '/../../controllers/UserController.php';

$userController = new UserController();
$users = $userController->getAll();
$mockAccounts = [];

// Get up to 3 real users to mock
foreach ($users as $u) {
    if (count($mockAccounts) >= 3) break;
    $mockAccounts[] = [
        'name' => $u->getPrenom() . ' ' . $u->getNom(),
        'email' => $u->getEmail(),
        'initial' => strtoupper(substr($u->getPrenom(), 0, 1) ?: 'U'),
        'color' => '#' . substr(md5($u->getEmail()), 0, 6)
    ];
}

// Add a fake unregistered account for testing signup
$mockAccounts[] = [
    'name' => 'Nouveau Utilisateur',
    'email' => 'nouveau_' . rand(100, 999) . '@gmail.com',
    'initial' => 'N',
    'color' => '#8b5cf6'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    
    $existingUser = $userController->getByEmail($email);
    
    if ($existingUser) {
        // Redirect to login processing
        echo "<form id='auto-login' method='POST' action='login.php'>
                <input type='hidden' name='google_login' value='1'>
                <input type='hidden' name='email' value='" . htmlspecialchars($email, ENT_QUOTES) . "'>
              </form>
              <script>document.getElementById('auto-login').submit();</script>";
        exit;
    } else {
        // Redirect to complete profile
        header('Location: google_complete.php?email=' . urlencode($email));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in - Google Accounts</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0; padding: 0;
            background-color: #202124; color: #e8eaed;
            font-family: 'Roboto', sans-serif;
            display: flex; justify-content: center; align-items: center;
            min-height: 100vh;
        }
        .top-bar {
            position: absolute; top: 0; left: 0; right: 0;
            padding: 15px 24px; display: flex; align-items: center; gap: 12px;
            border-bottom: 1px solid #3c4043;
        }
        .top-bar span { font-size: 18px; font-weight: 500; }
        
        .container {
            display: flex; width: 100%; max-width: 1040px;
            padding: 0 40px; gap: 60px; align-items: center;
        }
        
        .left-panel { flex: 1; }
        .logo-placeholder {
            width: 48px; height: 48px; background: #f43f5e;
            border-radius: 12px; margin-bottom: 24px;
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 24px; font-weight: bold;
        }
        .left-panel h1 { font-size: 44px; font-weight: 400; margin: 0 0 16px 0; color: #e8eaed; }
        .left-panel p { font-size: 16px; color: #e8eaed; margin: 0; }
        .left-panel p span { color: #8ab4f8; font-weight: 500; }

        .right-panel {
            flex: 1; display: flex; flex-direction: column; gap: 8px;
            border-left: 1px solid #3c4043; padding-left: 60px;
            min-height: 400px; justify-content: center;
        }
        
        .account-item {
            display: flex; align-items: center; padding: 12px 16px;
            border-radius: 24px; cursor: pointer; transition: background 0.2s;
            border: 1px solid transparent;
        }
        .account-item:hover { background-color: rgba(255,255,255,0.04); border-color: #5f6368; }
        
        .avatar {
            width: 32px; height: 32px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 15px; font-weight: 500; margin-right: 16px;
        }
        .account-info { flex: 1; display: flex; flex-direction: column; }
        .account-name { font-size: 14px; font-weight: 500; color: #e8eaed; }
        .account-email { font-size: 12px; color: #9aa0a6; margin-top: 2px; }
        
        .add-account { border-top: 1px solid #3c4043; margin-top: 16px; padding-top: 16px; }
        .icon-wrapper {
            width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;
            margin-right: 16px; color: #e8eaed;
        }

        @media (max-width: 768px) {
            .container { flex-direction: column; gap: 40px; padding: 40px 20px; }
            .right-panel { border-left: none; padding-left: 0; min-height: auto; width: 100%; border-top: 1px solid #3c4043; padding-top: 40px; }
            .left-panel h1 { font-size: 32px; }
        }
    </style>
</head>
<body>

    <div class="top-bar">
        <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google" width="24" height="24">
        <span>Sign in with Google</span>
    </div>

    <div class="container">
        <div class="left-panel">
            <div class="logo-placeholder">F</div>
            <h1>Choose an account</h1>
            <p>to continue to <span>FreelaSkill</span></p>
        </div>
        
        <div class="right-panel">
            <form id="google-auth-form" method="POST" style="display:none;">
                <input type="hidden" name="email" id="selected-email">
            </form>
            
            <?php foreach ($mockAccounts as $acc): ?>
            <div class="account-item" onclick="selectAccount('<?php echo htmlspecialchars($acc['email'], ENT_QUOTES); ?>')">
                <div class="avatar" style="background-color: <?php echo $acc['color']; ?>;"><?php echo $acc['initial']; ?></div>
                <div class="account-info">
                    <div class="account-name"><?php echo htmlspecialchars($acc['name']); ?></div>
                    <div class="account-email"><?php echo htmlspecialchars($acc['email']); ?></div>
                </div>
            </div>
            <?php endforeach; ?>
            
            <div class="add-account">
                <div class="account-item" onclick="customAccount()">
                    <div class="icon-wrapper">
                        <svg focusable="false" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5 11h-4v4h-2v-4H7v-2h4V7h2v4h4v2z"></path></svg>
                    </div>
                    <div class="account-name">Use another account</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function selectAccount(email) {
            document.getElementById('selected-email').value = email;
            document.getElementById('google-auth-form').submit();
        }
        
        function customAccount() {
            let email = prompt("Enter your Google email address:");
            if (email && email.trim() !== '') {
                selectAccount(email.trim());
            }
        }
    </script>
</body>
</html>
