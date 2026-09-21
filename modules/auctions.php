<?php require_once __DIR__.'/../core.php'; if(public_blocked()){header('Location:/');exit;} $items=data_list('auctions'); $media=data_list('media_library'); ?><!doctype html><html><head><meta charset="utf-8"><link rel="stylesheet" href="/assets/css/style.css?v=110"></head><body><header class="header"><div class="wrap nav"><a class="brand" href="/">GEA-H.SAU</a></div></header><section class="hero"><div class="wrap"><h1>Enchères</h1><p>Module public actif.</p></div></section><section class="section"><div class="wrap grid"><?php foreach(array_reverse($items) as $item): ?><div class="card"><h3><?=e($item['title']??$item['name']??'Élément')?></h3><p><?=e($item['description']??'')?></p><?=geah_public_gallery($item)?>
<?php
$itemId = $item['id'] ?? '';
foreach($media as $mm):
    if(($mm['module'] ?? '') === "auctions" && (string)($mm['item_id'] ?? '') === (string)$itemId):
        $u = $mm['url'] ?? '';
        if(($mm['type'] ?? '') === 'photo' && $u){
            echo '<img class="media-thumb" src="'.e(media_src($u)).'" alt="">';
        } elseif($u){
            echo '<p><a class="btn btn-light" target="_blank" href="'.e(media_src($u)).'">Voir média</a></p>';
        }
    endif;
endforeach;
?>
</div><?php endforeach; ?></div></section><script>
(function(){const saved=localStorage.getItem('geah_theme')||'dark';if(saved==='light')document.body.classList.add('light-mode');function ready(){const btn=document.createElement('button');btn.className='theme-toggle';function label(){btn.textContent=document.body.classList.contains('light-mode')?'🌙 Mode sombre':'☀️ Mode clair'}btn.onclick=function(){document.body.classList.toggle('light-mode');localStorage.setItem('geah_theme',document.body.classList.contains('light-mode')?'light':'dark');label()};label();document.body.appendChild(btn)}if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',ready);else ready();})();
</script><?php echo geah_footer(); ?></body></html>