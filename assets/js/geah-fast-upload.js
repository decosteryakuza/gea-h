/* GEA-H — Compression des photos AVANT l'upload pour un envoi beaucoup plus rapide.
   Redimensionne chaque image à 1600px max et la ré-encode en JPEG ~0.82.
   Une photo de 5 Mo passe souvent à ~400 Ko : envoi 5 à 10x plus rapide.
   N'agit que sur les images (jamais les vidéos). */
(function(){
  'use strict';
  if(window.__GEAH_FAST_UPLOAD__) return; window.__GEAH_FAST_UPLOAD__=true;
  var MAXDIM=1600, QUALITY=0.82, MINSIZE=350*1024; // n'optimise pas les fichiers déjà légers

  function isImg(f){ return f && /^image\/(jpeg|jpg|png|webp|heic|heif)$/i.test(f.type||''); }

  function compress(file){
    return new Promise(function(res){
      try{
        if(!isImg(file) || file.size < MINSIZE){ res(file); return; }
        var url=URL.createObjectURL(file);
        var img=new Image();
        img.onload=function(){
          try{
            var w=img.naturalWidth||img.width, h=img.naturalHeight||img.height;
            var scale=Math.min(1, MAXDIM/Math.max(w,h));
            var cw=Math.max(1,Math.round(w*scale)), ch=Math.max(1,Math.round(h*scale));
            var c=document.createElement('canvas'); c.width=cw; c.height=ch;
            var ctx=c.getContext('2d'); ctx.drawImage(img,0,0,cw,ch);
            URL.revokeObjectURL(url);
            c.toBlob(function(blob){
              if(!blob || blob.size>=file.size){ res(file); return; } // on garde l'original s'il est déjà plus petit
              var name=(file.name||'photo').replace(/\.(png|webp|jpeg|jpg|heic|heif)$/i,'')+'.jpg';
              var nf;
              try{ nf=new File([blob], name, {type:'image/jpeg', lastModified:Date.now()}); }
              catch(e){ blob.name=name; nf=blob; }
              res(nf);
            }, 'image/jpeg', QUALITY);
          }catch(e){ URL.revokeObjectURL(url); res(file); }
        };
        img.onerror=function(){ URL.revokeObjectURL(url); res(file); };
        img.src=url;
      }catch(e){ res(file); }
    });
  }

  function processInput(input){
    var files=Array.prototype.slice.call(input.files||[]);
    if(!files.length) return Promise.resolve();
    return Promise.all(files.map(compress)).then(function(out){
      try{
        if(typeof DataTransfer==='undefined') return; // navigateur trop ancien : on laisse l'original
        var dt=new DataTransfer();
        out.forEach(function(f){ try{ dt.items.add(f); }catch(e){} });
        if(dt.files && dt.files.length) input.files=dt.files;
        input.setAttribute('data-geah-compressed','1');
      }catch(e){}
    });
  }

  function imageInputs(form){
    return Array.prototype.slice.call(form.querySelectorAll('input[type=file]')).filter(function(i){
      var acc=(i.getAttribute('accept')||'').toLowerCase();
      return acc.indexOf('image')>=0 || /photo|image|gallery|galerie/i.test(i.name||'');
    });
  }

  // Quand l'utilisateur (re)choisit des fichiers, on réautorise la compression
  document.addEventListener('change', function(ev){
    var i=ev.target; if(i && i.type==='file') i.removeAttribute('data-geah-compressed');
  }, true);

  // Au moment de l'envoi : on compresse puis on soumet
  document.addEventListener('submit', function(ev){
    var form=ev.target; if(!form || form.tagName!=='FORM') return;
    var inputs=imageInputs(form).filter(function(i){
      return i.files && i.files.length && i.getAttribute('data-geah-compressed')!=='1';
    });
    if(!inputs.length) return; // rien à compresser → envoi normal

    ev.preventDefault();
    var note=document.createElement('div');
    note.textContent='📦 Optimisation des photos pour un envoi rapide…';
    note.style.cssText='position:fixed;left:50%;bottom:24px;transform:translateX(-50%);background:#16a34a;color:#fff;padding:11px 20px;border-radius:12px;z-index:99999;font-weight:700;box-shadow:0 10px 30px rgba(0,0,0,.35);font-family:system-ui,sans-serif';
    document.body.appendChild(note);

    Promise.all(inputs.map(processInput)).then(function(){
      if(note.parentNode) note.parentNode.removeChild(note);
      if(typeof form.requestSubmit==='function') form.requestSubmit(); else form.submit();
    }).catch(function(){
      if(note.parentNode) note.parentNode.removeChild(note);
      if(typeof form.requestSubmit==='function') form.requestSubmit(); else form.submit();
    });
  }, true);
})();
