/* GEA-H V24 - traduction intégrée légère */
(function(){
  const dict={
    fr:{"JOUR J -":"JOUR J -","Une surprise vous attend très bientôt":"Une surprise vous attend très bientôt","Connexion":"Connexion","Plein écran":"Plein écran"},
    en:{"JOUR J -":"D-DAY -","Une surprise vous attend très bientôt":"A surprise is coming very soon","Connexion":"Login","Plein écran":"Fullscreen"},
    es:{"JOUR J -":"DÍA D","Une surprise vous attend très bientôt":"Una sorpresa te espera muy pronto","Connexion":"Conexión","Plein écran":"Pantalla completa"},
    pt:{"JOUR J -":"DIA D","Une surprise vous attend très bientôt":"Uma surpresa espera por você muito em breve","Connexion":"Entrar","Plein écran":"Tela cheia"},
    ar:{"JOUR J -":"اليوم المنتظر","Une surprise vous attend très bientôt":"مفاجأة تنتظركم قريبًا جدًا","Connexion":"تسجيل الدخول","Plein écran":"ملء الشاشة"}
  };
  function lang(){return localStorage.getItem('geah_lang') || 'fr';}
  function apply(){
    const l=dict[lang()]?lang():'fr', d=dict[l];
    document.querySelectorAll('h1,.video-placeholder p,a,button,.v5-kicker').forEach(el=>{
      const t=(el.textContent||'').trim();
      if(d[t]) el.textContent=d[t];
    });
    if(l==='ar') document.documentElement.dir='rtl';
  }
  if(document.readyState==='loading') document.addEventListener('DOMContentLoaded',apply); else apply();
})();
