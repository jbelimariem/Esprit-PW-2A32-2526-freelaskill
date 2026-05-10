<?php
require_once __DIR__ . '/../../controllers/session.php';
destroySession();
header('Location: login.php');
exit;

