<?php require_once __DIR__.'/../core.php';
$room=preg_replace('~[^A-Za-z0-9_-]~','',$_GET['room']??'');
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Réunion GEA-H</title><link rel="stylesheet" href="/assets/css/style.css?v=110"><style>#jitsi{height:78vh;min-height:420px;border-radius:14px;overflow:hidden;background:#000}</style></head>
<body><header class="header"><div class="wrap nav"><a class="brand" href="/">GEA-HOLDING.SAU</a><nav class="menu"><a href="/">Accueil</a><?php echo account_link(); ?></nav></div></header>
<section class="section"><div class="wrap">
<?php if($room===''): ?>
  <h1>Rejoindre une réunion GEA-H</h1>
  <form class="card" method="get" style="max-width:520px">
    <label>Code de la réunion<input name="room" placeholder="Ex: GEAH-A1B2C3" required style="text-transform:uppercase"></label>
    <button class="btn btn-primary" style="margin-top:8px">Entrer dans la réunion</button>
    <p style="color:#6b7280;font-size:13px;margin-top:6px">Le code vous est communiqué par l'administrateur GEA-H.</p>
  </form>
<?php else: ?>
  <h1 style="margin-bottom:8px">Réunion GEA-H</h1>
  <p style="color:#6b7280;margin:0 0 10px">Autorisez la caméra et le micro quand votre navigateur le demande. Réunion : <b><?=e($room)?></b></p>
  <div id="jitsi"></div>
  <script src="https://meet.jit.si/external_api.js"></script>
  <script>
  (function(){
    try{
      var api=new JitsiMeetExternalAPI("meet.jit.si",{roomName:<?php echo json_encode('GEAH_'.$room); ?>,parentNode:document.getElementById('jitsi'),width:'100%',height:'100%',configOverwrite:{startWithAudioMuted:false,prejoinPageEnabled:true},interfaceConfigOverwrite:{MOBILE_APP_PROMO:false}});
      api.addEventListener('readyToClose',function(){ window.location.href='/'; });
    }catch(e){ document.getElementById('jitsi').innerHTML='<p style="color:#fff;padding:20px">Impossible de charger la visio. Vérifiez votre connexion.</p>'; }
  })();
  </script>
<?php endif; ?>
</div></section></body></html>
