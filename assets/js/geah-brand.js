(function(){
  const LOGO='/assets/img/logo-geah.jpeg';
  function ready(fn){document.readyState==='loading'?document.addEventListener('DOMContentLoaded',fn):fn();}
  ready(function(){
    if(/\/modules\/(city3d|studio3d|maison3d|conception3d|geah-studio)\.php/.test(location.pathname)){
      document.querySelectorAll('.geah-loader,.geah-floating-logo').forEach(function(n){n.remove();});
      return;
    }
    document.querySelectorAll('.brand').forEach(function(b){
      if(!b.querySelector('img')){
        const img=document.createElement('img'); img.src=LOGO; img.alt='Logo GEA-H'; img.className='geah-brand-logo';
        b.prepend(img);
      }
    });
    if(!document.querySelector('.admin-layout') && !document.querySelector('.login-card') && !document.querySelector('.geah-floating-logo')){
      const a=document.createElement('a'); a.href='/'; a.className='geah-floating-logo'; a.setAttribute('aria-label','Accueil GEA-H');
      const img=document.createElement('img'); img.src=LOGO; img.alt='Logo GEA-H'; a.appendChild(img);
      document.body.appendChild(a);
    }
    const loader=document.querySelector('.geah-loader');
    if(loader){setTimeout(function(){loader.classList.add('geah-loader-hide'); setTimeout(function(){loader.remove();},650);},850);}
  });
  window.addEventListener('load',function(){const loader=document.querySelector('.geah-loader'); if(loader){loader.classList.add('geah-loader-hide'); setTimeout(function(){loader.remove();},650);}});
})();