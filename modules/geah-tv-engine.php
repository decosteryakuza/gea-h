<?php
if(!function_exists('geah_tv_collect_playlist')){
function geah_tv_collect_playlist(){
    $items = [];
    $sources = ['geah_tv.json','tv_programme.json','tv_programmes.json','videos.json','media.json'];
    foreach($sources as $file){
      $data = function_exists('read_json') ? read_json($file, []) : [];
      if(!is_array($data)) continue;
      foreach($data as $row){
        if(!is_array($row)) continue;
        $url = $row['url'] ?? $row['video'] ?? $row['src'] ?? $row['file'] ?? $row['path'] ?? $row['video_url'] ?? $row['media_url'] ?? '';
        if(!$url && !empty($row['filename'])) $url = '/uploads/'.ltrim($row['filename'],'/');
        if(!$url) continue;
        $title = $row['title'] ?? $row['name'] ?? $row['titre'] ?? 'GEA-H TV';
        $status = strtolower($row['status'] ?? $row['etat'] ?? 'published');
        if(in_array($status, ['draft','brouillon','disabled','desactive','désactivé'])) continue;
        if(strpos($url, 'http') !== 0 && strpos($url, '/') !== 0) $url = '/'.ltrim($url,'/');
        $items[] = ['title'=>$title,'url'=>$url,'type'=>$row['type'] ?? 'video','priority'=>intval($row['priority'] ?? 0)];
      }
    }
    if(!$items){
      $dirs = ['uploads','uploads/tv','uploads/videos','assets/videos','storage/videos','storage/tv'];
      foreach($dirs as $dir){
        $full = __DIR__.'/../'.$dir;
        if(!is_dir($full)) continue;
        foreach(scandir($full) as $f){
          if(preg_match('/\.(mp4|webm|ogg|mov)$/i', $f)){
            $items[] = ['title'=>pathinfo($f, PATHINFO_FILENAME),'url'=>'/'.$dir.'/'.$f,'type'=>'video','priority'=>0];
          }
        }
      }
    }
    usort($items, function($a,$b){ return ($b['priority'] ?? 0) <=> ($a['priority'] ?? 0); });
    return array_values($items);
}}
if(!function_exists('geah_tv_playlist_json')){
function geah_tv_playlist_json(){ return json_encode(geah_tv_collect_playlist(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); }
}
if(!function_exists('geah_dashboard_showroom_slides')){
function geah_dashboard_showroom_slides(){
    $slides = [];
    $props = function_exists('read_json') ? read_json('properties.json', []) : [];
    if(is_array($props)){
        $props = array_reverse(array_values(array_filter($props, function($p){
            $st = function_exists('mb_strtolower') ? mb_strtolower(trim($p['status'] ?? 'Disponible')) : strtolower(trim($p['status'] ?? 'Disponible'));
            $val = function_exists('mb_strtolower') ? mb_strtolower(trim($p['validation'] ?? 'ok')) : strtolower(trim($p['validation'] ?? 'ok'));
            return !in_array($st, ['retiré','retire','en attente validation','en attente','refusé'], true) && !in_array($val, ['pending','pending_edit','retire','refuse'], true);
        })));
        foreach($props as $p){
            $imgs = function_exists('geah_property_images') ? geah_property_images($p) : ((!empty($p['images'])&&is_array($p['images']))?$p['images']:[]);
            if(!$imgs) continue;
            $tx = strtolower($p['transaction'] ?? '');
            if($tx!=='location' && $tx!=='vente'){ $stx=strtolower($p['status']??''); $tx = ($stx==='à louer'||$stx==='a louer'||$stx==='location') ? 'location' : 'vente'; }
            $slides[] = [
                'type'  => 'prop',
                'img'   => function_exists('media_src') ? media_src($imgs[0]) : $imgs[0],
                'title' => $p['title'] ?? 'Bien immobilier',
                'price' => $p['price'] ?? '',
                'city'  => $p['city'] ?? '',
                'tx'    => $tx,
                'id'    => $p['id'] ?? ''
            ];
        }
    }
    $vids = function_exists('geah_tv_collect_playlist') ? geah_tv_collect_playlist() : [];
    if(is_array($vids)){
        foreach($vids as $v){
            $u = $v['url'] ?? $v['video_url'] ?? '';
            if($u==='') continue;
            $slides[] = ['type'=>'video','url'=>$u,'title'=>$v['title'] ?? 'GEA-H TV'];
        }
    }
    return $slides;
}}
if(!function_exists('geah_tv_dashboard_html')){
function geah_tv_dashboard_html($height='360px'){
    $slides = geah_dashboard_showroom_slides();
    if(empty($slides)){
        return '<section class="geah-tv-dashboard-screen geah-tv-empty-mini" style="--tv-height:130px">'
            .'<div class="tv-screen-head"><div><span class="tv-live-dot"></span><b>GEA-H Vitrine</b><small>Diffusion automatique</small></div></div>'
            .'<div class="tv-mini-empty">Ajoutez des biens (avec photos) ou des vidéos — ils seront diffusés ici automatiquement, comme une vitrine publicitaire.</div>'
            .'</section>';
    }
    $json = htmlspecialchars(json_encode($slides, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8');
    return '<section class="geah-tv-dashboard-screen" data-slides="'.$json.'" style="--tv-height:'.$height.'">'
        .'<div class="tv-bg-glow"></div>'
        .'<div class="tv-screen-head"><div><span class="tv-live-dot"></span><b>GEA-H Vitrine</b><small>Biens &amp; pub en continu</small></div><span class="tv-badge">LIVE AUTO</span></div>'
        .'<div class="tv-player-wrap"><div class="tv-slide-stage"></div><video class="geah-tv-player" muted playsinline preload="metadata" style="display:none"></video></div>'
        .'<div class="tv-nowbar"><span class="tv-now-title">Diffusion...</span><span class="tv-now-count">'.count($slides).' éléments</span></div>'
        .'</section>';
}}
