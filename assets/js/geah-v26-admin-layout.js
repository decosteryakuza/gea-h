/* GEA-H V26 - Correction menu admin */
(function(){
  function ready(){
    var btn=document.getElementById('admin-burger');
    var side=document.querySelector('.admin-layout .sidebar');
    var overlay=document.getElementById('admin-overlay');
    if(!btn || !side) return;
    function close(){ side.classList.remove('open'); overlay && overlay.classList.remove('open'); }
    function toggle(){ side.classList.toggle('open'); overlay && overlay.classList.toggle('open', side.classList.contains('open')); }
    btn.addEventListener('click', toggle);
    overlay && overlay.addEventListener('click', close);
    window.addEventListener('resize', function(){ if(window.innerWidth>720) close(); });
  }
  if(document.readyState==='loading') document.addEventListener('DOMContentLoaded', ready); else ready();
})();