(async()=>{
  try{
    const j=await api('api/admin_dashboard.php');
    document.getElementById('k_active_users').textContent=fmt(j.active_users);
    document.getElementById('k_open_tickets').textContent=j.open_tickets+' open';
  }catch(e){toast(e.message,true);}
})();
