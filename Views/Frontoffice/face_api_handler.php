<?php
// views/frontoffice/face_api_handler.php
require_once __DIR__ . '/../../controllers/FaceApiController.php';

(new FaceApiController())->execute();
