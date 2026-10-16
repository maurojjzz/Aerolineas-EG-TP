<?php

require_once __DIR__ . '/../config/app.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=86400');

$aeropuertos = require __DIR__ . '/../data/aeropuertos.php';

echo json_encode($aeropuertos, JSON_UNESCAPED_UNICODE);