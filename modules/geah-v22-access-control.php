<?php
function geah_v22_read_json($file){ return file_exists($file) ? (json_decode(file_get_contents($file), true) ?: []) : []; }
function geah_user_has_paid_access($userIdentifier, $feature = 'premium'){
  $licenses = geah_v22_read_json(__DIR__ . '/../data/licenses.json');
  $now = time();
  foreach($licenses as $l){
    if(($l['status'] ?? '') !== 'active') continue;
    if(strtolower($l['user'] ?? '') !== strtolower($userIdentifier)) continue;
    if(strtotime($l['expires_at'] ?? '1970-01-01') < $now) continue;
    return true;
  }
  return false;
}
function geah_require_paid_access($userIdentifier, $feature = 'premium'){
  if(!geah_user_has_paid_access($userIdentifier, $feature)){
    header("Location: /pricing.php?feature=".urlencode($feature)); exit;
  }
}
