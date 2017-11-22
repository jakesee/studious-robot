<?php
require_once __DIR__ . '/../bootstrap/app.php';

// Run the application silently, defering output to client
$app->respond($app->run(true)->render());