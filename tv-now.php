<?php
require_once __DIR__.'/core.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, max-age=0');
echo json_encode(['item'=>geah_tv_takeover()]);
