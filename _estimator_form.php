<?php
if(!function_exists("geah_estimate")){ http_response_code(403); exit; }
if(($_SERVER['REQUEST_METHOD'] ?? 'GET')==='POST'){ require_user_login($_SERVER['REQUEST_URI'] ?? '/'); }
$est=null;
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='estimate'){
    $est=geah_estimate($_POST['standing']??'standard',$_POST['surface']??0,$_POST['niveaux']??1,$_POST['terrain']??0);
}
?>
<form class="card" method="post">
  <input type="hidden" name="action" value="estimate">
  <h2>Estimer le coût d'un projet</h2>
  <div class="form-grid">
    <label>Type de bien<input name="type_bien" placeholder="Villa, immeuble R+2..."></label>
    <label>Standing<select name="standing"><option value="economique">Économique</option><option value="standard" selected>Standard</option><option value="luxe">Haut de gamme</option></select></label>
    <label>Surface au sol (m²)<input type="number" name="surface" min="1" step="1" required></label>
    <label>Nombre de niveaux<input type="number" name="niveaux" min="1" value="1"></label>
    <label>Surface terrain (m², option)<input type="number" name="terrain" min="0" value="0"></label>
  </div>
  <button class="btn btn-primary">Calculer l'estimation</button>
</form>
<?php if($est): ?>
<div class="card">
  <h2>Estimation indicative</h2>
  <p>Surface totale construite : <b><?=number_format($est['surface_totale'],0,',',' ')?> m²</b> · Prix retenu : <b><?=money($est['prix_m2'])?>/m²</b></p>
  <table class="table"><tr><th>Poste</th><th>Montant estimé</th></tr>
    <?php foreach($est['rows'] as $k=>$v): ?><tr><td><?=e($k)?></td><td><?=money($v)?></td></tr><?php endforeach; ?>
    <tr><td><b>Coût construction</b></td><td><b><?=money($est['construction'])?></b></td></tr>
    <?php if($est['terrain']>0): ?><tr><td>Terrain</td><td><?=money($est['terrain'])?></td></tr><?php endif; ?>
    <tr><td><b>TOTAL ESTIMÉ</b></td><td style="font-size:20px;color:#013328"><b><?=money($est['total'])?></b></td></tr>
  </table>
  <p style="color:#6b7280;font-size:13px">⚠️ Estimation indicative basée sur des prix moyens paramétrables. Le coût réel dépend du terrain, des fondations, des matériaux et des prestations — à valider par un bureau d'études / ingénieur BTP.</p>
</div>
<?php endif; ?>
