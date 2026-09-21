/* GEA-H V21 - Langue automatique, traduction IA, voix et commande vocale */
(function(){
  const supported = ['fr','en','es','pt','ar','baoule','dioula','bete','senoufo','agni','attie','wolof','bambara','lingala','swahili','yoruba','hausa','ewe','fon'];
  window.GEAH_LANG = localStorage.getItem('geah_lang') || (navigator.language || 'fr').split('-')[0];
  if(!supported.includes(window.GEAH_LANG)) window.GEAH_LANG = 'fr';

  window.geahSpeak = function(text, lang){
    if(!('speechSynthesis' in window)) return;
    const u = new SpeechSynthesisUtterance(text);
    u.lang = lang || window.GEAH_LANG || 'fr';
    speechSynthesis.speak(u);
  };

  window.geahStartVoiceCommand = function(callback){
    const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
    if(!SR){ alert("Commande vocale non supportée sur ce navigateur."); return; }
    const rec = new SR();
    rec.lang = window.GEAH_LANG === 'en' ? 'en-US' : 'fr-FR';
    rec.interimResults = false;
    rec.onresult = (e)=> callback && callback(e.results[0][0].transcript);
    rec.start();
  };

  window.geahHandleVoiceCommand = function(text){
    const q = (text||'').toLowerCase();
    if(q.includes('ajouter') && q.includes('annonce')) location.href='/add-property.php';
    else if(q.includes('tv')) location.href='/geah-tv.php';
    else if(q.includes('studio') || q.includes('3d')) location.href='/studio-3d.php';
    else if(q.includes('gps') || q.includes('départ')) location.href='/gps-depart.php';
    else if(q.includes('bien') || q.includes('terrain')) location.href='/biens.php?q='+encodeURIComponent(text);
    else alert("Commande reçue : "+text);
  };
})();
