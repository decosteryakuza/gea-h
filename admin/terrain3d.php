<?php
require_once __DIR__ . '/../core.php';
require_once __DIR__ . '/auth.php';
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Terrain Vision 3D — GEA-H</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head>
<body><div class="admin-layout"><?php include __DIR__.'/sidebar.php'; ?><main class="admin-main">
<h1>Terrain Vision 3D</h1>

<div class="lot-warn">
  <b>🏗️ Visualisation 3D conceptuelle du morcellement.</b> Fais tourner / zoomer la scène pour présenter le projet.<br>
  <b>⚠️</b> Le relief réel nécessite un <b>levé topographique</b>, et le <b>bornage légal</b> reste celui du <b>géomètre-expert agréé</b> (AutoCAD + Covadis/KAJO, GPS RTK).
</div>

<div class="card">
  <h3>Paramètres du morcellement</h3>
  <div class="form-grid">
    <label>Longueur terrain (m) <input id="L" type="number" value="120"></label>
    <label>Largeur terrain (m) <input id="W" type="number" value="90"></label>
    <label>Largeur des voies (m) <input id="rw" type="number" value="8"></label>
    <label>Mode de découpe
      <select id="mode" onchange="toggleMode()">
        <option value="dim">Par dimensions de lot</option>
        <option value="nb">Par nombre de parcelles</option>
      </select>
    </label>
    <label id="wrapLW">Largeur d'un lot (m) <input id="lw" type="number" value="18"></label>
    <label id="wrapLD">Profondeur d'un lot (m) <input id="ld" type="number" value="22"></label>
    <label id="wrapN" style="display:none">Nombre de parcelles <input id="nb" type="number" value="12"></label>
  </div>
  <div style="display:flex;gap:14px;flex-wrap:wrap;align-items:center;margin-top:6px">
    <button class="btn btn-gold" type="button" onclick="build3D()">🧱 Générer la vue 3D</button>
    <label style="display:flex;align-items:center;gap:6px"><input type="checkbox" id="optLabels" checked onchange="build3D()"> Numéros</label>
    <label style="display:flex;align-items:center;gap:6px"><input type="checkbox" id="optBornes" checked onchange="build3D()"> Bornes</label>
  </div>
  <p id="sum3d" class="lot-sum" style="margin-top:10px"></p>
  <div id="viewport" style="width:100%;height:480px;border-radius:14px;overflow:hidden;background:#0b1f1a;margin-top:8px;touch-action:none"></div>
  <p style="color:#6b7280;font-size:13px;margin-top:8px">🖱️ Glisser = tourner · molette / pincer = zoomer. Schéma conceptuel non géoréférencé — à valider par un géomètre-expert.</p>
</div>

<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/build/three.min.js"></script>
<script>
function v(id){ return document.getElementById(id).value; }
function toggleMode(){
  var m=v('mode');
  document.getElementById('wrapLW').style.display = m==='dim'?'':'none';
  document.getElementById('wrapLD').style.display = m==='dim'?'':'none';
  document.getElementById('wrapN').style.display  = m==='nb'?'':'none';
}

var renderer, scene, camera, model, animating=false;
var radius=160, theta=0.7, phi=0.95, center={x:0,y:0,z:0};

function initScene(){
  var el=document.getElementById('viewport');
  renderer=new THREE.WebGLRenderer({antialias:true, alpha:true});
  renderer.setPixelRatio(window.devicePixelRatio||1);
  renderer.setSize(el.clientWidth, el.clientHeight);
  el.innerHTML=''; el.appendChild(renderer.domElement);
  scene=new THREE.Scene();
  camera=new THREE.PerspectiveCamera(50, el.clientWidth/el.clientHeight, 0.5, 8000);
  scene.add(new THREE.AmbientLight(0xffffff, 0.65));
  var sun=new THREE.DirectionalLight(0xffffff, 0.8); sun.position.set(120,200,80); scene.add(sun);
  var sky=new THREE.HemisphereLight(0xbfe3ff, 0x335544, 0.4); scene.add(sky);
  attachControls(el);
  window.addEventListener('resize', function(){ if(!renderer)return; var e=document.getElementById('viewport'); renderer.setSize(e.clientWidth,e.clientHeight); camera.aspect=e.clientWidth/e.clientHeight; camera.updateProjectionMatrix(); });
  if(!animating){ animating=true; loop(); }
}
function updateCam(){
  phi=Math.max(0.15, Math.min(1.45, phi));
  camera.position.x = center.x + radius*Math.sin(phi)*Math.cos(theta);
  camera.position.y = center.y + radius*Math.cos(phi);
  camera.position.z = center.z + radius*Math.sin(phi)*Math.sin(theta);
  camera.lookAt(center.x, center.y, center.z);
}
function loop(){ requestAnimationFrame(loop); if(renderer&&scene&&camera){ updateCam(); renderer.render(scene,camera); } }

function attachControls(el){
  var dragging=false, lx=0, ly=0, pinch=0;
  el.addEventListener('mousedown', function(e){ dragging=true; lx=e.clientX; ly=e.clientY; });
  window.addEventListener('mouseup', function(){ dragging=false; });
  window.addEventListener('mousemove', function(e){ if(!dragging)return; theta+=(e.clientX-lx)*0.006; phi-=(e.clientY-ly)*0.006; lx=e.clientX; ly=e.clientY; });
  el.addEventListener('wheel', function(e){ e.preventDefault(); radius*=(1+e.deltaY*0.0012); radius=Math.max(20,Math.min(2000,radius)); }, {passive:false});
  el.addEventListener('touchstart', function(e){ if(e.touches.length===1){ dragging=true; lx=e.touches[0].clientX; ly=e.touches[0].clientY; } else if(e.touches.length===2){ pinch=Math.hypot(e.touches[0].clientX-e.touches[1].clientX, e.touches[0].clientY-e.touches[1].clientY); } }, {passive:false});
  el.addEventListener('touchmove', function(e){ e.preventDefault(); if(e.touches.length===1&&dragging){ theta+=(e.touches[0].clientX-lx)*0.006; phi-=(e.touches[0].clientY-ly)*0.006; lx=e.touches[0].clientX; ly=e.touches[0].clientY; } else if(e.touches.length===2){ var d=Math.hypot(e.touches[0].clientX-e.touches[1].clientX, e.touches[0].clientY-e.touches[1].clientY); if(pinch){ radius*=(pinch/d); radius=Math.max(20,Math.min(2000,radius)); } pinch=d; } }, {passive:false});
  el.addEventListener('touchend', function(e){ if(e.touches.length===0) dragging=false; });
}

function makeLabel(text){
  var c=document.createElement('canvas'); c.width=128; c.height=72;
  var x=c.getContext('2d');
  x.fillStyle='rgba(1,51,40,0.88)'; roundRect(x,4,4,120,64,12); x.fill();
  x.fillStyle='#ffd98a'; x.font='bold 40px sans-serif'; x.textAlign='center'; x.textBaseline='middle'; x.fillText(text,64,38);
  var tex=new THREE.CanvasTexture(c);
  var sp=new THREE.Sprite(new THREE.SpriteMaterial({map:tex, depthTest:false}));
  sp.scale.set(9,5,1); return sp;
}
function roundRect(x,a,b,w,h,r){ x.beginPath(); x.moveTo(a+r,b); x.arcTo(a+w,b,a+w,b+h,r); x.arcTo(a+w,b+h,a,b+h,r); x.arcTo(a,b+h,a,b,r); x.arcTo(a,b,a+w,b,r); x.closePath(); }

function computeLots(){
  var L=+v('L'), W=+v('W'), rw=+v('rw'); rw=rw>0?rw:0;
  var lw, ld, cols, rows, target=0;
  if(v('mode')==='dim'){
    lw=+v('lw'); ld=+v('ld');
    if(!(lw>0&&ld>0)) return null;
    cols=Math.floor((L+rw)/(lw+rw)); rows=Math.floor((W+rw)/(ld+rw));
  } else {
    target=Math.max(1, parseInt(v('nb'))||1);
    cols=Math.max(1, Math.round(Math.sqrt(target*(L/W))));
    rows=Math.ceil(target/cols);
    lw=(L-(cols+1)*rw)/cols; ld=(W-(rows+1)*rw)/rows;
  }
  if(cols<1||rows<1||lw<=0||ld<=0) return null;
  var lots=[], idx=1;
  for(var r=0;r<rows;r++){ for(var c=0;c<cols;c++){
    if(target && idx>target) break;
    lots.push({n:idx++, x:rw+c*(lw+rw), y:rw+r*(ld+rw), w:lw, d:ld});
  }}
  return {L:L,W:W,rw:rw,lw:lw,ld:ld,lots:lots};
}

function build3D(){
  if(!window.THREE){ document.getElementById('sum3d').innerHTML='<div class="error">La librairie 3D n\'a pas pu se charger (vérifie la connexion internet).</div>'; return; }
  if(!renderer) initScene();
  var P=computeLots();
  if(!P){ document.getElementById('sum3d').innerHTML='<div class="error">Paramètres invalides (lots trop grands pour le terrain).</div>'; return; }
  if(model){ scene.remove(model); }
  model=new THREE.Group();

  var L=P.L, W=P.W, lots=P.lots;
  var showLabels=document.getElementById('optLabels').checked && lots.length<=80;
  var showBornes=document.getElementById('optBornes').checked && lots.length<=40;

  // Sol / voiries
  var ground=new THREE.Mesh(new THREE.BoxGeometry(L+8, 1, W+8), new THREE.MeshLambertMaterial({color:0x55606b}));
  ground.position.set(0,-0.5,0); model.add(ground);

  var lotMat=new THREE.MeshLambertMaterial({color:0x2f9e63});
  var edgeMat=new THREE.LineBasicMaterial({color:0x05321f});
  var posMat=new THREE.MeshLambertMaterial({color:0xd4a23a});

  lots.forEach(function(lt){
    // centrer le terrain autour de (0,0)
    var cx=(lt.x+lt.w/2)-L/2;
    var cz=(lt.y+lt.d/2)-W/2;
    var h=0.6;
    var geo=new THREE.BoxGeometry(lt.w, h, lt.d);
    var m=new THREE.Mesh(geo, lotMat); m.position.set(cx, h/2, cz); model.add(m);
    var eg=new THREE.LineSegments(new THREE.EdgesGeometry(geo), edgeMat); eg.position.copy(m.position); model.add(eg);
    if(showLabels){ var lab=makeLabel(String(lt.n)); lab.position.set(cx, h+4, cz); model.add(lab); }
    if(showBornes){
      var cs=[[lt.x,lt.y],[lt.x+lt.w,lt.y],[lt.x+lt.w,lt.y+lt.d],[lt.x,lt.y+lt.d]];
      cs.forEach(function(p){
        var post=new THREE.Mesh(new THREE.CylinderGeometry(0.35,0.35,2.4,8), posMat);
        post.position.set(p[0]-L/2, 1.2, p[1]-W/2); model.add(post);
      });
    }
  });

  // Flèche Nord (vers -Z)
  var nLab=makeLabel('N'); nLab.position.set(-L/2-4, 4, -W/2-4); model.add(nLab);

  scene.add(model);
  center={x:0, y:0, z:0};
  radius=Math.max(L,W)*1.4; theta=0.7; phi=0.9;

  var areaLots=lots.length*P.lw*P.ld;
  var roadArea=Math.max(0, L*W-areaLots);
  document.getElementById('sum3d').innerHTML=
    '<div><span>Parcelles</span><b>'+lots.length+'</b></div>'
    +'<div><span>Taille / parcelle</span><b>'+P.lw.toFixed(1)+'×'+P.ld.toFixed(1)+' m</b></div>'
    +'<div><span>Surface parcelles</span><b>'+areaLots.toFixed(0)+' m²</b></div>'
    +'<div><span>Surface voiries</span><b>'+roadArea.toFixed(0)+' m²</b></div>';
}

window.addEventListener('load', build3D);
</script>
</main></div></body></html>
