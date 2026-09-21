/* GEA-H V29 - menu réduit / étendu */
(function(){
  function ready(){
    var layout=document.querySelector('.admin-layout');
    var sidebar=document.querySelector('.admin-layout .sidebar');
    if(!layout || !sidebar) return;
    if(!sidebar.querySelector('.sidebar-toggle-mini')){
      var btn=document.createElement('button');
      btn.type='button';
      btn.className='sidebar-toggle-mini';
      btn.textContent='⇤ Réduire le menu';
      sidebar.insertBefore(btn, sidebar.firstChild);
      btn.addEventListener('click', function(){
        layout.classList.toggle('sidebar-collapsed');
        var collapsed=layout.classList.contains('sidebar-collapsed');
        localStorage.setItem('geah_sidebar_collapsed', collapsed ? '1':'0');
        btn.textContent=collapsed ? '☰' : '⇤ Réduire le menu';
      });
    }
    if(localStorage.getItem('geah_sidebar_collapsed')==='1' && window.innerWidth>720){
      layout.classList.add('sidebar-collapsed');
      var b=sidebar.querySelector('.sidebar-toggle-mini');
      if(b) b.textContent='☰';
    }
  }
  if(document.readyState==='loading') document.addEventListener('DOMContentLoaded', ready); else ready();
})();