<?php
require_once __DIR__ . '/../bootstrap/app.php';

$app = new \Application();

// Run the application silently, defering output to client
$app->run();
