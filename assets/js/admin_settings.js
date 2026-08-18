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
    toast('Site settings saved');
  }catch(e){toast(e.message,true);}
}

(async()=>{
  try{
    const j=await api('api/admin_email.php?action=get');
    const s=j.settings;
    document.getElementById('smtpHost').value=s.smtp_host;
    document.getElementById('smtpPort').value=s.smtp_port;
    document.getElementById('smtpUser').value=s.smtp_user;
    document.getElementById('fromEmail').value=s.from_email;
    document.getElementById('fromName').value=s.from_name;
    document.getElementById('encryption').value=s.encryption;
    if(s.has_password) document.getElementById('pwNote').style.display='';
  }catch(e){toast(e.message,true);}
})();

async function saveEmail(){
  try{
    await api('api/admin_email.php?action=save',{
      smtp_host:document.getElementById('smtpHost').value,
      smtp_port:document.getElementById('smtpPort').value,
      smtp_user:document.getElementById('smtpUser').value,
      smtp_pass:document.getElementById('smtpPass').value,
      from_email:document.getElementById('fromEmail').value,
      from_name:document.getElementById('fromName').value,
      encryption:document.getElementById('encryption').value,
    });
    toast('Email settings saved');
    document.getElementById('smtpPass').value='';
    document.getElementById('pwNote').style.display='';
  }catch(e){toast(e.message,true);}
}

async function testEmail(){
  try{
    const j=await api('api/admin_email.php?action=test',{});
    toast('Test email sent to '+j.sent_to);
  }catch(e){toast(e.message,true);}
}
