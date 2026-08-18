async function changeEmail(){
  const new_email=document.getElementById('newEmail').value.trim();
  const current_password=document.getElementById('emailCurPw').value;
  if(!new_email||!current_password){toast('Enter a new email and your current password',true);return;}
  try{
    const j=await api('api/profile.php?action=change_email',{new_email,current_password});
    toast('Email updated');
    document.getElementById('curEmail').textContent=j.email;
    document.getElementById('newEmail').value='';
    document.getElementById('emailCurPw').value='';
  }catch(e){toast(e.message,true);}
}

async function changeAdminPw(){
  const current_password=document.getElementById('pwCurPw').value;
  const new_password=document.getElementById('pwNewPw').value;
  const confirm=document.getElementById('pwConfPw').value;
  if(!current_password||new_password.length<6){toast('Enter your current password and a new password (6+ chars)',true);return;}
  if(new_password!==confirm){toast('New passwords do not match',true);return;}
  try{
    await api('api/profile.php?action=change_password',{current_password,new_password});
    toast('Password updated');
    document.getElementById('pwCurPw').value='';
    document.getElementById('pwNewPw').value='';
    document.getElementById('pwConfPw').value='';
  }catch(e){toast(e.message,true);}
}

function showLogo(url){
  const wrap=document.getElementById('logoPreviewWrap');
  if(url){
    document.getElementById('logoPreview').src=url;
    wrap.style.display='flex';
    document.getElementById('removeLogoBtn').style.display='';
  }else{
    wrap.style.display='none';
    document.getElementById('removeLogoBtn').style.display='none';
  }
}

(async()=>{
  try{
    const j=await api('api/admin_settings.php?action=get');
    document.getElementById('siteName').value=j.settings.site_name||'';
    document.getElementById('supportEmail').value=j.settings.support_email||'';
    showLogo(j.settings.logo_url);
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

async function uploadLogo(){
  const input=document.getElementById('logoFile');
  if(!input.files.length){toast('Choose a logo file first',true);return;}
  const fd=new FormData();
  fd.append('logo',input.files[0]);
  fd.append('csrf',window.CSRF);
  try{
    const r=await fetch('api/admin_settings.php?action=upload_logo',{method:'POST',body:fd});
    const j=await r.json();
    if(!j.ok) throw new Error(j.error||'Upload failed');
    toast('Logo updated');
    showLogo(j.logo_url);
    input.value='';
  }catch(e){toast(e.message,true);}
}

async function removeLogo(){
  if(!confirmDelete('Remove the current logo and go back to the default icon?'))return;
  try{
    await api('api/admin_settings.php?action=remove_logo',{});
    toast('Logo removed');
    showLogo('');
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
