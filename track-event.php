<?php
$raw = file_get_contents("php://input");
$event = json_decode($raw, true);
if(!$event) exit;
$file = __DIR__ . "/storage/audit_logs/events.log";
if(!is_dir(dirname($file))) mkdir(dirname($file),0775,true);
file_put_contents($file, date('c') . " " . json_encode($event, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND);

$metricsFile = __DIR__ . "/data/performance_metrics.json";
$metrics = file_exists($metricsFile) ? (json_decode(file_get_contents($metricsFile), true) ?: []) : [];
$map = ["page_view"=>"visites","click"=>"clics","property_view"=>"vues_biens","contact"=>"contacts","reservation"=>"reservations","purchase"=>"achats","payment"=>"paiements","video_view"=>"videos_vues"];
$key = $map[$event["event"] ?? ""] ?? null;
if($key){ $metrics[$key] = intval($metrics[$key] ?? 0) + 1; }
if(!is_dir(dirname($metricsFile))) mkdir(dirname($metricsFile),0775,true);
file_put_contents($metricsFile, json_encode($metrics, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
echo "OK";
