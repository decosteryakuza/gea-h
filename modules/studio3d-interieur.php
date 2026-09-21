<?php
require_once __DIR__.'/../core.php';
$title='Studio 3D — Aménagement intérieur';
if(function_exists('geah_header')) echo geah_header($title); else echo '<!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'.$title.'</title><link rel="stylesheet" href="/assets/css/style.css?v=110"></head><body>';
?>
<style>
  .st3d-wrap{max-width:1200px;margin:0 auto;padding:12px}
  .st3d-bar{display:flex;flex-wrap:wrap;gap:8px;margin:10px 0;align-items:center}
  .st3d-bar .grp{display:flex;flex-wrap:wrap;gap:6px;background:rgba(8,19,31,.6);border:1px solid rgba(212,162,58,.3);border-radius:12px;padding:8px}
  .st3d-bar .grp b{align-self:center;color:#d4a23a;font-size:12px;margin-right:4px}
  .st3d-btn{background:#12283a;color:#fff;border:1px solid rgba(212,162,58,.35);border-radius:9px;padding:8px 11px;cursor:pointer;font-size:13px;font-weight:700}
  .st3d-btn:hover{background:rgba(212,162,58,.25)}
  .st3d-btn.danger{background:#3a1520;border-color:#a33}
  .st3d-btn.gold{background:#d4a23a;color:#08131f}
  #st3d-canvas{width:100%;height:66vh;min-height:440px;border-radius:16px;border:1px solid rgba(212,162,58,.35);background:#0a1520;display:block;touch-action:none}
  .st3d-hint{color:#9fb2c2;font-size:13px;margin:8px 0;line-height:1.5}
  .st3d-sel{color:#0abf74;font-weight:800}
</style>

<div class="st3d-wrap">
  <h1>🏠 Studio 3D — Aménagement intérieur</h1>
  <p class="st3d-hint">Ajoute des meubles, une terrasse, puis <b>clique un meuble</b> pour le sélectionner et le <b>déplacer</b> (glisse-le), le <b>pivoter</b> ou le <b>supprimer</b>. Fais tourner la vue en glissant sur le fond, zoom avec la molette.</p>

  <div class="st3d-bar">
    <div class="grp"><b>Meubles</b>
      <button class="st3d-btn" onclick="ST3D.add('canape')">🛋️ Canapé</button>
      <button class="st3d-btn" onclick="ST3D.add('fauteuil')">🪑 Fauteuil</button>
      <button class="st3d-btn" onclick="ST3D.add('table')">🍽️ Table</button>
      <button class="st3d-btn" onclick="ST3D.add('chaise')">💺 Chaise</button>
      <button class="st3d-btn" onclick="ST3D.add('lit')">🛏️ Lit</button>
      <button class="st3d-btn" onclick="ST3D.add('tv')">📺 TV</button>
      <button class="st3d-btn" onclick="ST3D.add('armoire')">🗄️ Armoire</button>
      <button class="st3d-btn" onclick="ST3D.add('plante')">🪴 Plante</button>
      <button class="st3d-btn" onclick="ST3D.add('tapis')">🟫 Tapis</button>
      <button class="st3d-btn" onclick="ST3D.add('cuisine')">🍳 Cuisine</button>
    </div>
    <div class="grp"><b>Structure</b>
      <button class="st3d-btn" onclick="ST3D.addTerrasse()">🌿 Terrasse</button>
      <button class="st3d-btn" onclick="ST3D.addRoom()">➕ Pièce</button>
      <button class="st3d-btn" onclick="ST3D.toggleRoof()">🔺 Toit</button>
    </div>
    <div class="grp"><b>Sélection</b>
      <button class="st3d-btn" onclick="ST3D.rotate()">🔄 Pivoter</button>
      <button class="st3d-btn danger" onclick="ST3D.remove()">🗑️ Supprimer</button>
    </div>
    <div class="grp"><b>Vue</b>
      <button class="st3d-btn" onclick="ST3D.view('inside')">👁️ Intérieur</button>
      <button class="st3d-btn" onclick="ST3D.view('top')">⬇️ Dessus</button>
      <button class="st3d-btn" onclick="ST3D.view('outside')">🏠 Extérieur</button>
    </div>
    <div class="grp"><b>Sol</b>
      <button class="st3d-btn" onclick="ST3D.floor('#caa472')">Parquet</button>
      <button class="st3d-btn" onclick="ST3D.floor('#e8e8ea')">Carrelage</button>
      <button class="st3d-btn" onclick="ST3D.floor('#8a9a5b')">Gazon</button>
    </div>
  </div>

  <canvas id="st3d-canvas"></canvas>
  <p class="st3d-hint">Sélection : <span id="st3d-selname" class="st3d-sel">aucune</span> — Astuce : sur mobile, touche un meuble puis glisse-le.</p>
</div>

<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/build/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>
<script>
const ST3D=(function(){
  let scene,camera,renderer,controls,raycaster,mouse,floorMesh,roof=null;
  let items=[], selected=null, dragging=false, roomW=8, roomD=6, wallH=3;
  const cv=document.getElementById('st3d-canvas');

  function init(){
    scene=new THREE.Scene();
    scene.background=new THREE.Color(0x0a1520);
    scene.fog=new THREE.Fog(0x0a1520,30,60);
    const w=cv.clientWidth,h=cv.clientHeight;
    camera=new THREE.PerspectiveCamera(55,w/h,0.1,200);
    camera.position.set(9,9,12);
    renderer=new THREE.WebGLRenderer({canvas:cv,antialias:true});
    renderer.setSize(w,h,false); renderer.setPixelRatio(Math.min(devicePixelRatio,2));
    renderer.shadowMap.enabled=true; renderer.shadowMap.type=THREE.PCFSoftShadowMap;
    controls=new THREE.OrbitControls(camera,renderer.domElement);
    controls.enableDamping=true; controls.dampingFactor=.08; controls.maxPolarAngle=Math.PI/2.05; controls.target.set(0,1,0);
    // Lumières
    scene.add(new THREE.HemisphereLight(0xffffff,0x33404d,.75));
    const sun=new THREE.DirectionalLight(0xfff2d9,1.05); sun.position.set(10,16,8); sun.castShadow=true;
    sun.shadow.mapSize.set(2048,2048); sun.shadow.camera.left=-20; sun.shadow.camera.right=20; sun.shadow.camera.top=20; sun.shadow.camera.bottom=-20;
    scene.add(sun);
    buildRoom();
    raycaster=new THREE.Raycaster(); mouse=new THREE.Vector2();
    cv.addEventListener('pointerdown',onDown); cv.addEventListener('pointermove',onMove); window.addEventListener('pointerup',onUp);
    window.addEventListener('resize',onResize);
    animate();
  }

  function buildRoom(){
    // Sol
    const floorGeo=new THREE.BoxGeometry(roomW,0.2,roomD);
    const floorMat=new THREE.MeshStandardMaterial({color:0xcaa472,roughness:.9});
    floorMesh=new THREE.Mesh(floorGeo,floorMat); floorMesh.position.y=-0.1; floorMesh.receiveShadow=true; floorMesh.name='__floor';
    scene.add(floorMesh);
    // Murs (3 pleins + 1 bas pour voir dedans)
    const wm=new THREE.MeshStandardMaterial({color:0xf3ece1,roughness:.95,side:THREE.DoubleSide});
    const mkWall=(w,h,d,x,y,z)=>{const m=new THREE.Mesh(new THREE.BoxGeometry(w,h,d),wm);m.position.set(x,y,z);m.castShadow=true;m.receiveShadow=true;m.userData.wall=true;scene.add(m);return m;};
    mkWall(roomW,wallH,0.15,0,wallH/2,-roomD/2);            // fond
    mkWall(0.15,wallH,roomD,-roomW/2,wallH/2,0);            // gauche
    mkWall(0.15,wallH,roomD, roomW/2,wallH/2,0);            // droite
    mkWall(roomW,0.6,0.15,0,0.3,roomD/2);                   // avant (bas, pour voir)
    // Grille sol subtile
    const grid=new THREE.GridHelper(roomW>roomD?roomW:roomD,12,0xd4a23a,0x24405a); grid.position.y=0.01; grid.material.opacity=.25; grid.material.transparent=true; scene.add(grid);
  }

  // ---- Fabrique de meubles (primitives + matériaux) ----
  function mat(c,r){return new THREE.MeshStandardMaterial({color:c,roughness:r==null?.7:r});}
  function box(w,h,d,c,r){const m=new THREE.Mesh(new THREE.BoxGeometry(w,h,d),mat(c,r));m.castShadow=true;m.receiveShadow=true;return m;}
  function cyl(rt,rb,h,c){const m=new THREE.Mesh(new THREE.CylinderGeometry(rt,rb,h,20),mat(c,.6));m.castShadow=true;return m;}

  const FACTORY={
    canape(){const g=new THREE.Group();const c=0x3f6fb0;const base=box(2.4,.35,1,c);base.position.y=.35;g.add(base);const back=box(2.4,.7,.25,c);back.position.set(0,.75,-.38);g.add(back);const la=box(.25,.55,1,c);la.position.set(-1.08,.6,0);g.add(la);const ra=la.clone();ra.position.x=1.08;g.add(ra);const s1=box(1.05,.18,.85,0x5b86c8);s1.position.set(-.55,.6,.03);g.add(s1);const s2=s1.clone();s2.position.x=.55;g.add(s2);g.userData.label='Canapé';return g;},
    fauteuil(){const g=new THREE.Group();const c=0x9b5de5;const base=box(1,.35,1,c);base.position.y=.35;g.add(base);const back=box(1,.6,.22,c);back.position.set(0,.7,-.38);g.add(back);const la=box(.2,.5,1,c);la.position.set(-.5,.55,0);g.add(la);const ra=la.clone();ra.position.x=.5;g.add(ra);g.userData.label='Fauteuil';return g;},
    table(){const g=new THREE.Group();const top=box(1.6,.12,.95,0x8a5a2b,.5);top.position.y=.78;g.add(top);const legc=0x5c3d1e;[[-.7,-.4],[.7,-.4],[-.7,.4],[.7,.4]].forEach(p=>{const l=box(.12,.78,.12,legc);l.position.set(p[0],.39,p[1]);g.add(l);});g.userData.label='Table';return g;},
    chaise(){const g=new THREE.Group();const c=0x555b66;const st=box(.5,.1,.5,c);st.position.y=.48;g.add(st);const bk=box(.5,.55,.1,c);bk.position.set(0,.75,-.2);g.add(bk);[[-.2,-.2],[.2,-.2],[-.2,.2],[.2,.2]].forEach(p=>{const l=box(.08,.48,.08,0x333);l.position.set(p[0],.24,p[1]);g.add(l);});g.userData.label='Chaise';return g;},
    lit(){const g=new THREE.Group();const fr=box(2.1,.4,2.6,0x6b4a2f);fr.position.y=.3;g.add(fr);const mat_=box(1.95,.35,2.45,0xf5f0e6);mat_.position.y=.6;g.add(mat_);const hb=box(2.1,.8,.2,0x6b4a2f);hb.position.set(0,.7,-1.3);g.add(hb);const p1=box(.8,.2,.5,0xdfe6ef);p1.position.set(-.5,.85,-1);g.add(p1);const p2=p1.clone();p2.position.x=.5;g.add(p2);g.userData.label='Lit';return g;},
    tv(){const g=new THREE.Group();const st=box(1.8,.5,.4,0x2a2f38);st.position.y=.25;g.add(st);const sc=box(1.7,1,.08,0x0a0a0a,.3);sc.position.set(0,1.1,0);g.add(sc);const scr=box(1.55,.85,.02,0x1b3a5c,.2);scr.position.set(0,1.1,.06);g.add(scr);g.userData.label='TV';return g;},
    armoire(){const g=new THREE.Group();const b=box(1.4,2.2,.6,0x7a5230);b.position.y=1.1;g.add(b);const d=box(.04,2.1,.02,0xd4a23a);d.position.set(0,1.1,.31);g.add(d);g.userData.label='Armoire';return g;},
    plante(){const g=new THREE.Group();const pot=cyl(.22,.28,.4,0xb5651d);pot.position.y=.2;g.add(pot);const f=new THREE.Mesh(new THREE.SphereGeometry(.45,12,12),mat(0x2e7d32,.9));f.position.y=.8;f.castShadow=true;g.add(f);g.userData.label='Plante';return g;},
    tapis(){const g=new THREE.Group();const r=box(2.6,.04,1.8,0xb04a4a,.95);r.position.y=.03;g.add(r);const r2=box(2.2,.05,1.4,0xd98c8c,.95);r2.position.y=.045;g.add(r2);g.userData.label='Tapis';return g;},
    cuisine(){const g=new THREE.Group();const c=0x3a4550;const cnt=box(2.6,.9,.7,c);cnt.position.y=.45;g.add(cnt);const top=box(2.65,.08,.75,0x2b333c,.4);top.position.y=.92;g.add(top);const sink=box(.5,.06,.4,0xbfc7cf,.3);sink.position.set(-.6,.93,0);g.add(sink);const hood=box(.9,.4,.5,0x9aa3ad);hood.position.set(.7,1.9,-.05);g.add(hood);g.userData.label='Cuisine';return g;}
  };

  function add(type){
    const g=FACTORY[type]?FACTORY[type]():null; if(!g)return;
    g.position.set((Math.random()-.5)*(roomW-2),0,(Math.random()-.5)*(roomD-2));
    g.userData.item=true; scene.add(g); items.push(g); select(g);
  }
  function addTerrasse(){
    const g=new THREE.Group();
    const deck=box(roomW,0.18,3.2,0xb98a52,.85); deck.position.set(0,0.09,roomD/2+1.7); g.add(deck);
    // lames
    for(let i=0;i<8;i++){const l=box(roomW-.3,.02,.32,0xa97a45,.85);l.position.set(0,.19,roomD/2+.35+i*.38);g.add(l);}
    // garde-corps
    const rail=box(roomW,.05,.05,0xd4a23a); rail.position.set(0,1,roomD/2+3.2); g.add(rail);
    for(let i=-1;i<=1;i++){const p=box(.06,1,.06,0xd4a23a);p.position.set(i*(roomW/2-.2),.5,roomD/2+3.2);g.add(p);}
    g.userData.item=true; g.userData.label='Terrasse'; scene.add(g); items.push(g); select(g);
  }
  function addRoom(){
    // agrandit la pièce (ajoute une extension à droite)
    roomW=Math.min(16,roomW+4);
    // reconstruire : retirer sol/murs/grille et refaire
    const rm=[]; scene.children.forEach(o=>{if(o.userData&&o.userData.wall||o.name==='__floor'||o.type==='GridHelper')rm.push(o);});
    rm.forEach(o=>scene.remove(o)); buildRoom();
  }
  function toggleRoof(){
    if(roof){scene.remove(roof);roof=null;return;}
    roof=box(roomW+.3,.2,roomD+.3,0x8a3b2e,.8); roof.position.y=wallH+.1; roof.castShadow=true; scene.add(roof);
  }

  function setName(){document.getElementById('st3d-selname').textContent=selected?(selected.userData.label||'meuble'):'aucune';}
  function select(g){ if(selected&&selected.userData.__hl){restoreColor(selected);} selected=g; if(g){highlight(g);} setName(); }
  function highlight(g){g.traverse(o=>{if(o.isMesh){o.userData.__c=o.material.color.getHex();o.material.color.offsetHSL(0,0,.12);}});g.userData.__hl=true;}
  function restoreColor(g){g.traverse(o=>{if(o.isMesh&&o.userData.__c!=null){o.material.color.setHex(o.userData.__c);}});g.userData.__hl=false;}
  function rotate(){if(selected)selected.rotation.y+=Math.PI/8;}
  function remove(){if(!selected)return;scene.remove(selected);items=items.filter(i=>i!==selected);selected=null;setName();}
  function floor(hex){if(floorMesh)floorMesh.material.color.set(hex);}

  function view(mode){
    if(mode==='top'){camera.position.set(0,16,0.01);controls.target.set(0,0,0);}
    else if(mode==='inside'){camera.position.set(0,1.7,roomD/2-0.5);controls.target.set(0,1.5,-roomD/2);}
    else{camera.position.set(9,9,12);controls.target.set(0,1,0);}
  }

  // ---- Interaction déplacement ----
  function ptr(e){const r=cv.getBoundingClientRect();mouse.x=((e.clientX-r.left)/r.width)*2-1;mouse.y=-((e.clientY-r.top)/r.height)*2+1;}
  function topItem(obj){let o=obj;while(o&&!o.userData.item)o=o.parent;return o;}
  function onDown(e){ptr(e);raycaster.setFromCamera(mouse,camera);const hits=raycaster.intersectObjects(items,true);if(hits.length){const it=topItem(hits[0].object);if(it){select(it);dragging=true;controls.enabled=false;}}else{select(null);}}
  function onMove(e){if(!dragging||!selected)return;ptr(e);raycaster.setFromCamera(mouse,camera);const hit=raycaster.intersectObject(floorMesh);if(hit.length){selected.position.x=Math.max(-roomW/2+.3,Math.min(roomW/2-.3,hit[0].point.x));selected.position.z=Math.max(-roomD/2+.3,Math.min(roomD/2+4,hit[0].point.z));}}
  function onUp(){dragging=false;controls.enabled=true;}
  function onResize(){const w=cv.clientWidth,h=cv.clientHeight;camera.aspect=w/h;camera.updateProjectionMatrix();renderer.setSize(w,h,false);}
  function animate(){requestAnimationFrame(animate);controls.update();renderer.render(scene,camera);}

  if(window.THREE&&THREE.OrbitControls){init();}else{window.addEventListener('load',function(){if(window.THREE)init();});}
  return {add,addTerrasse,addRoom,toggleRoof,rotate,remove,floor,view};
})();
</script>
<?php if(function_exists('geah_footer')) echo geah_footer(); else echo '</body></html>'; ?>
