<?php
// views/frontoffice/chatbot_api.php

require_once __DIR__ . '/../../controllers/ChatbotController.php';

(new ChatbotController())->handle();
