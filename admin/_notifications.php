<?php
// Widget notifications (inclus dans la barre latérale, visible sur toutes les pages admin)
if(function_exists('auth_user')):
$__nu=auth_user(); $__nem=strtolower($__nu['email']??'');
$__nt=array_values(array_filter(read_json('tasks.json',[]),function($t)use($__nem){ return $__nem!=='' && strtolower($t['assign_email']??'')===$__nem && ($t['status']??'')!=='terminé'; }));
$__nm=array_values(array_filter(read_json('internal_messages.json',[]),function($m)use($__nem){ return $__nem!=='' && strpos(strtolower(json_encode($m)),$__nem)!==false; }));
$__nm=array_slice(array_reverse($__nm),0,8);
$__ncount=count($__nt)+count($__nm);
?>
<div id="geahNotif">
  <button id="geahBell" type="button" aria-label="Notifications">🔔<?php if($__ncount>0): ?><span class="gnb"><?=$__ncount?></span><?php endif; ?></button>
  <div id="geahPanel">
    <div class="gnp-h"><b>🔔 Notifications</b><button type="button" id="geahClose">✕</button></div>
    <div class="gnp-body">
      <?php if($__ncount===0): ?><p class="gnp-empty">Aucune nouvelle notification.</p><?php endif; ?>
      <?php foreach($__nt as $t): ?>
        <div class="gnp-item" data-id="task-<?=e($t['id']??'')?>">
          <div class="gnp-txt">📋 <b>Tâche :</b> <?=e($t['title']??'')?><small>De <?=e($t['by_name']??'')?><?=!empty($t['due'])?' · échéance '.e($t['due']):''?></small></div>
          <div class="gnp-act"><a href="/admin/taches.php">Voir</a><button type="button" class="gnp-later">Plus tard</button></div>
        </div>
      <?php endforeach; ?>
      <?php foreach($__nm as $m): ?>
        <div class="gnp-item" data-id="msg-<?=e($m['id']??md5(json_encode($m)))?>">
          <div class="gnp-txt">✉️ <b>Message :</b> <?=e($m['subject']??mb_strimwidth(($m['message']??'Message interne'),0,60,'…'))?><small><?=e($m['date']??'')?></small></div>
          <div class="gnp-act"><a href="/admin/internal-contacts.php">Voir</a><button type="button" class="gnp-later">Plus tard</button></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<style>
#geahNotif{display:contents}
#geahBell{position:fixed;top:14px;right:16px;z-index:9999;width:46px;height:46px;border-radius:50%;border:none;background:#d4a23a;color:#211400;font-size:20px;cursor:pointer;box-shadow:0 4px 14px rgba(0,0,0,.4)}
#geahBell .gnb{position:absolute;top:-3px;right:-3px;background:#dc2626;color:#fff;font-size:11px;font-weight:800;min-width:19px;height:19px;border-radius:10px;display:flex;align-items:center;justify-content:center;padding:0 4px}
#geahPanel{position:fixed;top:66px;right:16px;z-index:9999;width:330px;max-width:92vw;max-height:70vh;overflow:auto;background:#0e1c26;border:1px solid #2a3f4d;border-radius:14px;box-shadow:0 10px 30px rgba(0,0,0,.5);display:none}
.gnp-h{display:flex;justify-content:space-between;align-items:center;padding:12px 14px;border-bottom:1px solid #2a3f4d}
.gnp-h button{background:none;border:none;color:#9fb;font-size:16px;cursor:pointer}
.gnp-empty{padding:18px 14px;color:#8aa0b8;margin:0}
.gnp-item{padding:11px 14px;border-bottom:1px solid #1b2c38}
.gnp-txt{font-size:13px;color:#e6eef5}.gnp-txt small{display:block;color:#8aa0b8;margin-top:3px;font-size:11px}
.gnp-act{display:flex;gap:8px;margin-top:7px}
.gnp-act a{background:#d4a23a;color:#211400;text-decoration:none;padding:4px 12px;border-radius:8px;font-size:12px;font-weight:700}
.gnp-act .gnp-later{background:#10212d;color:#9fb;border:1px solid #2a3f4d;padding:4px 12px;border-radius:8px;font-size:12px;cursor:pointer}
</style>
<script>
(function(){
  var bell=document.getElementById('geahBell'),panel=document.getElementById('geahPanel'),close=document.getElementById('geahClose');
  if(!bell)return;
  function snz(){ try{return JSON.parse(localStorage.getItem('geah_snooze')||'{}');}catch(e){return {};} }
  function isSnoozed(id){ var s=snz(); return s[id]&&s[id]>Date.now(); }
  document.querySelectorAll('#geahPanel .gnp-item').forEach(function(it){ if(isSnoozed(it.dataset.id)) it.style.display='none'; });
  function recount(){
    var vis=Array.prototype.filter.call(document.querySelectorAll('#geahPanel .gnp-item'),function(i){return i.style.display!=='none';}).length;
    var nb=bell.querySelector('.gnb');
    if(vis===0){ if(nb)nb.remove(); var body=document.querySelector('#geahPanel .gnp-body'); if(body && !body.querySelector('.gnp-item[style*="block"], .gnp-item:not([style])') && !document.querySelector('#geahPanel .gnp-empty')){ } }
    else if(nb){ nb.textContent=vis; }
  }
  recount();
  bell.onclick=function(){ panel.style.display=(panel.style.display==='block')?'none':'block'; };
  if(close)close.onclick=function(){ panel.style.display='none'; };
  document.querySelectorAll('#geahPanel .gnp-later').forEach(function(b){
    b.onclick=function(){ var it=b.closest('.gnp-item'); var s=snz(); s[it.dataset.id]=Date.now()+8*3600*1000; try{localStorage.setItem('geah_snooze',JSON.stringify(s));}catch(e){} it.style.display='none'; recount(); };
  });
  document.addEventListener('click',function(e){ if(panel.style.display==='block' && !panel.contains(e.target) && e.target!==bell) panel.style.display='none'; });
})();
</script>
<?php endif; ?>
