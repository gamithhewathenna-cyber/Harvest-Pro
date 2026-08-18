async function changePw(){
  const cur=document.getElementById('curPw').value;
  const nw=document.getElementById('newPw').value;
  const cf=document.getElementById('confPw').value;
  if(!cur||nw.length<6){toast('Enter your current password and a new password (6+ chars)',true);return;}
  if(nw!==cf){toast('New passwords do not match',true);return;}
  try{
    await api('api/profile.php?action=change_password',{current_password:cur,new_password:nw});
    toast('Password updated');
    document.getElementById('curPw').value='';
    document.getElementById('newPw').value='';
    document.getElementById('confPw').value='';
  }catch(e){toast(e.message,true);}
}
