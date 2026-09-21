<?php
/* Sert les fichiers de /uploads via PHP (contourne un serveur qui ne sert pas /uploads). */
$f = $_GET['f'] ?? '';
$f = str_replace(['..', '\\', "\0"], '', $f);
$f = ltrim($f, '/');
$base = realpath(__DIR__ . '/uploads');
$path = realpath(__DIR__ . '/uploads/' . $f);
if ($base === false || $path === false || strpos($path, $base) !== 0 || !is_file($path)) { http_response_code(404); exit('Not found'); }
$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
$types = ['jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png','gif'=>'image/gif','webp'=>'image/webp','svg'=>'image/svg+xml','mp4'=>'video/mp4','webm'=>'video/webm','mov'=>'video/quicktime','ogg'=>'video/ogg','pdf'=>'application/pdf'];
if (!isset($types[$ext])) { http_response_code(403); exit('Forbidden'); }
$size = filesize($path);
$type = $types[$ext];
header('Content-Type: ' . $type);
header('Cache-Control: public, max-age=86400');
header('Accept-Ranges: bytes');
// Support des plages (lecture/seek vidéo)
if (isset($_SERVER['HTTP_RANGE']) && preg_match('/bytes=(\d*)-(\d*)/', $_SERVER['HTTP_RANGE'], $m)) {
    $start = $m[1] === '' ? 0 : (int)$m[1];
    $end = $m[2] === '' ? $size - 1 : (int)$m[2];
    if ($start > $end || $end >= $size) { header('Content-Range: bytes */' . $size); http_response_code(416); exit; }
    http_response_code(206);
    header('Content-Range: bytes ' . $start . '-' . $end . '/' . $size);
    header('Content-Length: ' . ($end - $start + 1));
    $fp = fopen($path, 'rb'); fseek($fp, $start); $left = $end - $start + 1;
    while ($left > 0 && !feof($fp)) { $chunk = fread($fp, min(8192, $left)); echo $chunk; $left -= strlen($chunk); flush(); }
    fclose($fp); exit;
}
header('Content-Length: ' . $size);
readfile($path);
