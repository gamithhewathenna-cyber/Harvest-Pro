(async()=>{
  try{
    const j=await api('api/admin_settings.php?action=get');
    document.getElementById('siteName').value=j.settings.site_name||'';
    document.getElementById('supportEmail').value=j.settings.support_email||'';
  }catch(e){toast(e.message,true);}
})();

async function saveSettings(){
  try{
    await api('api/admin_settings.php?action=save',{
      site_name:document.getElementById('siteName').value,
      support_email:document.getElementById('supportEmail').value,
    });
    toast('Settings saved');
  }catch(e){toast(e.message,true);}
}
