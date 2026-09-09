document.addEventListener('DOMContentLoaded',()=>{
  const nav=document.querySelector('.side-nav');
  if(!nav)return;
  nav.addEventListener('click',e=>{
    const item=e.target.closest('[data-view]');
    if(!item)return;
    document.querySelectorAll('.nav-item[data-view],.submenu button[data-view]').forEach(x=>x.classList.remove('active'));
    item.classList.add('active');
    const menu=item.closest('details');
    if(menu)menu.open=true;
  });
});