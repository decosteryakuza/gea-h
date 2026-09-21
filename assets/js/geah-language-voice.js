/* GEA-H V13 - Langues africaines + détection + commande vocale */
(function(){
  if(window.__GEAH_V13_LANG_VOICE__) return; window.__GEAH_V13_LANG_VOICE__=true;
  var LANGS=[
    ['fr','🇫🇷 Français','Français'],['en','🇬🇧 English','English'],['es','🇪🇸 Español','Español'],['pt','🇵🇹 Português','Português'],['ar','🇸🇦 العربية','العربية'],
    ['sw','🇹🇿 Kiswahili','Kiswahili'],['ha','🇳🇬 Hausa','Hausa'],['yo','🇳🇬 Yoruba','Yoruba'],['ig','🇳🇬 Igbo','Igbo'],['ak','🇬🇭 Akan / Twi','Akan/Twi'],
    ['bm','🇲🇱 Bambara','Bambara'],['dyu','🇨🇮 Dioula / Jula','Dioula'],['wo','🇸🇳 Wolof','Wolof'],['ln','🇨🇩 Lingala','Lingala'],['rw','🇷🇼 Kinyarwanda','Kinyarwanda'],
    ['lg','🇺🇬 Luganda','Luganda'],['sn','🇿🇼 Shona','Shona'],['zu','🇿🇦 Zulu','Zulu'],['xh','🇿🇦 Xhosa','Xhosa'],['st','🇱🇸 Sesotho','Sesotho'],
    ['ee','🇹🇬 Ewe','Ewe'],['fon','🇧🇯 Fon','Fon'],['am','🇪🇹 Amharic','Amharic'],['so','🇸🇴 Somali','Somali'],['om','🇪🇹 Oromo','Oromo'],
    ['baoule','🇨🇮 Baoulé','Baoulé'],['bete','🇨🇮 Bété','Bété'],['agni','🇨🇮 Agni','Agni'],['attie','🇨🇮 Attié','Attié'],['senoufo','🇨🇮 Sénoufo','Sénoufo'],['malinké','🇨🇮 Malinké','Malinké']
  ];
  function getLangName(code){ var f=LANGS.find(function(l){return l[0]===code}); return f?f[2]:code; }
  function detectLanguage(){
    var saved=localStorage.getItem('geah_lang'); if(saved) return saved;
    return 'fr'; /* Francais par defaut ; l'utilisateur choisit sa langue dans le selecteur de traduction */
  }
  function style(){
    if(document.getElementById('geahV13LangVoiceStyle')) return;
    var s=document.createElement('style'); s.id='geahV13LangVoiceStyle'; s.textContent='\
      .geah-lang-box{right:18px!important;bottom:92px!important}.geah-lang-box select{max-width:190px}.geah-voice-btn{border:0;border-radius:999px;background:#d4a23a;color:#102016;font-weight:900;padding:9px 11px;cursor:pointer;box-shadow:0 8px 20px rgba(0,0,0,.18)}\
      .geah-voice-btn.listening{animation:geahPulse 1s infinite;background:#16a34a;color:white}@keyframes geahPulse{50%{transform:scale(1.08)}}\
      .geah-lang-note{font-size:11px;color:#d4a23a;margin-top:5px}.geah-voice-tools{display:flex;gap:6px;align-items:center}\
    '; document.head.appendChild(s);
  }
  function populateSelect(){
    var sel=document.getElementById('geah-lang-select'); if(!sel) return;
    sel.innerHTML=''; LANGS.forEach(function(l){ var o=document.createElement('option'); o.value=l[0]; o.textContent=l[1]; sel.appendChild(o); });
    var lang=detectLanguage(); sel.value=lang; applyLang(lang,false);
    sel.addEventListener('change',function(){ applyLang(this.value,true); });
  }
  var LOCAL_DICT={
    en:{'Biens':'Properties','Meubles':'Furniture','Matériaux':'Materials','Conception 3D':'3D Design','Résidences':'Residences','Hôtels':'Hotels','Enchères':'Auctions','Contact':'Contact','Déposer une annonce':'Post listing','Connexion':'Login','Inscription':'Sign up','Déconnexion':'Logout','Tableau de bord':'Dashboard','Mon compte':'My account','Voir les biens':'View properties','Plateforme immobilière GEA-H':'GEA-H Real Estate Platform','Votre question…':'Your question…','Envoyer':'Send','Accueil':'Home'},
    es:{'Biens':'Inmuebles','Meubles':'Muebles','Matériaux':'Materiales','Conception 3D':'Diseño 3D','Résidences':'Residencias','Hôtels':'Hoteles','Enchères':'Subastas','Contact':'Contacto','Déposer une annonce':'Publicar anuncio','Connexion':'Iniciar sesión','Inscription':'Registrarse','Déconnexion':'Cerrar sesión','Tableau de bord':'Panel','Mon compte':'Mi cuenta','Voir les biens':'Ver inmuebles','Plateforme immobilière GEA-H':'Plataforma inmobiliaria GEA-H','Votre question…':'Su pregunta…','Envoyer':'Enviar','Accueil':'Inicio'},
    pt:{'Biens':'Imóveis','Meubles':'Móveis','Matériaux':'Materiais','Conception 3D':'Design 3D','Résidences':'Residências','Hôtels':'Hotéis','Enchères':'Leilões','Contact':'Contato','Déposer une annonce':'Publicar anúncio','Connexion':'Entrar','Inscription':'Inscrever-se','Déconnexion':'Sair','Tableau de bord':'Painel','Mon compte':'Minha conta','Voir les biens':'Ver imóveis','Plateforme immobilière GEA-H':'Plataforma imobiliária GEA-H','Votre question…':'Sua pergunta…','Envoyer':'Enviar','Accueil':'Início'},
    ar:{'Biens':'العقارات','Meubles':'الأثاث','Matériaux':'المواد','Conception 3D':'تصميم ثلاثي الأبعاد','Résidences':'الإقامات','Hôtels':'الفنادق','Enchères':'المزادات','Contact':'اتصال','Déposer une annonce':'إضافة إعلان','Connexion':'تسجيل الدخول','Inscription':'إنشاء حساب','Déconnexion':'تسجيل الخروج','Tableau de bord':'لوحة التحكم','Mon compte':'حسابي','Voir les biens':'عرض العقارات','Plateforme immobilière GEA-H':'منصة GEA-H العقارية','Votre question…':'اكتب سؤالك…','Envoyer':'إرسال','Accueil':'الرئيسية'}
  };
  function geahClearGoogtrans(){ try{ var h=location.hostname, ex='expires=Thu, 01 Jan 1970 00:00:00 GMT'; document.cookie='googtrans=;path=/;'+ex; document.cookie='googtrans=;path=/;domain='+h+';'+ex; document.cookie='googtrans=;path=/;domain=.'+h+';'+ex; }catch(e){} }
  function localTranslate(code){
    document.documentElement.lang=code; document.documentElement.dir=(code==='ar'?'rtl':'ltr');
    if(code==='fr') return;
    var d=LOCAL_DICT[code]; if(!d) return;
    document.querySelectorAll('a,button,h1,h2,h3,h4,span,p,label,option').forEach(function(el){
      if(el.children.length) return; var t=(el.textContent||'').trim(); if(d[t]) el.textContent=d[t];
    });
    document.querySelectorAll('input[placeholder]').forEach(function(el){ var t=el.getAttribute('placeholder'); if(d[t]) el.setAttribute('placeholder',d[t]); });
  }
  function applyLang(code,manual){
    geahClearGoogtrans();
    localStorage.setItem('geah_lang',code); document.cookie='geah_lang='+code+';path=/;max-age=31536000';
    var sel=document.getElementById('geah-lang-select'); if(sel && sel.value!==code) sel.value=code;
    localTranslate(code);
    var note=document.getElementById('geahLangNote');
    if(!note){ var box=document.querySelector('.geah-lang-box'); if(box){ note=document.createElement('div'); note.id='geahLangNote'; note.className='geah-lang-note'; box.appendChild(note); } }
    if(note){ note.textContent=LOCAL_DICT[code]?'Traduction activée':(code==='fr'?'':'Dialecte IA : l’assistant traduira et répondra en '+getLangName(code)); }
    window.dispatchEvent(new CustomEvent('geah:language',{detail:{code:code,name:getLangName(code)}}));
  }
  function addVoice(){
    var input=document.getElementById('gc-q'), send=document.getElementById('gc-send'); if(!input||!send||document.getElementById('geahVoiceBtn')) return;
    var wrap=document.createElement('span'); wrap.className='geah-voice-tools';
    var mic=document.createElement('button'); mic.type='button'; mic.id='geahVoiceBtn'; mic.className='geah-voice-btn'; mic.title='Commande vocale'; mic.textContent='🎙️';
    var speak=document.createElement('button'); speak.type='button'; speak.id='geahSpeakBtn'; speak.className='geah-voice-btn'; speak.title='Lecture vocale'; speak.textContent='🔊';
    send.parentNode.insertBefore(wrap,send); wrap.appendChild(mic); wrap.appendChild(speak);
    var SR=window.SpeechRecognition||window.webkitSpeechRecognition;
    if(SR){ mic.addEventListener('click',function(){
      var r=new SR(); r.lang=(localStorage.getItem('geah_lang')||'fr'); r.interimResults=false; r.maxAlternatives=1;
      mic.classList.add('listening'); mic.textContent='🎤';
      r.onresult=function(ev){ input.value=ev.results[0][0].transcript; setTimeout(function(){send.click();},250); };
      r.onerror=function(){ alert('Commande vocale indisponible ou micro refusé.'); };
      r.onend=function(){ mic.classList.remove('listening'); mic.textContent='🎙️'; };
      r.start();
    }); } else { mic.onclick=function(){ alert('Commande vocale non supportée par ce navigateur.'); }; }
    speak.addEventListener('click',function(){
      var msgs=document.querySelectorAll('.gc-msg.gc-bot'); if(!msgs.length) return; var txt=msgs[msgs.length-1].textContent.trim();
      if(!('speechSynthesis' in window)){ alert('Lecture vocale non supportée.'); return; }
      speechSynthesis.cancel(); var u=new SpeechSynthesisUtterance(txt); u.lang=(localStorage.getItem('geah_lang')||'fr'); speechSynthesis.speak(u);
    });
    var body=document.getElementById('gc-body'); if(body && window.MutationObserver){
      new MutationObserver(function(muts){ muts.forEach(function(m){ m.addedNodes&&m.addedNodes.forEach(function(n){ if(n.nodeType===1&&n.classList.contains('gc-bot')&&localStorage.getItem('geah_auto_speak')==='1'){ var txt=n.textContent.trim(); if(txt && txt!=='…' && 'speechSynthesis' in window){ var u=new SpeechSynthesisUtterance(txt); u.lang=(localStorage.getItem('geah_lang')||'fr'); speechSynthesis.speak(u); } } }); }); }).observe(body,{childList:true});
    }
  }
  function voiceShortcuts(){
    document.addEventListener('geah:voice-command',function(e){ var t=(e.detail||'').toLowerCase(); });
  }
  function init(){ style(); populateSelect(); addVoice(); voiceShortcuts(); }
  if(document.readyState==='loading') document.addEventListener('DOMContentLoaded',init); else init();
})();
