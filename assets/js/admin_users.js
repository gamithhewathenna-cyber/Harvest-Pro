let CUR_PW_ID=null, CUR_DEL=null;

async function loadUsers(){
  const b=document.getElementById('userBody');
  let j;
  try{ j=await api('api/admin_users.php?action=list'); }
  catch(e){ b.innerHTML=`<tr><td colspan="7" class="empty">Couldn't load data: ${esc(e.message)} — <a href="javascript:loadUsers()">Retry</a></td></tr>`; return; }
  if(!j.rows.length){ b.innerHTML='<tr><td colspan="7" class="empty">No users yet.</td></tr>'; return; }
  b.innerHTML=j.rows.map(r=>`<tr>
    <td>${esc(r.name)}</td>
    <td>${esc(r.email)}</td>
    <td><span class="badge b-blue">${esc(r.role)}</span></td>
    <td>${r.owner_user_id?esc(r.owner_name||'')+' ('+esc(r.owner_email||'')+')':'<em>Tenant owner</em>'}</td>
    <td><span class="badge ${r.status==='Active'?'b-green':'b-gray'}">${esc(r.status)}</span></td>
    <td>${esc(r.last_login||'Never')}</td>
    <td class="right" style="white-space:nowrap">
      <button class="btn gray sm" onclick="openPw(${r.id})">Reset Password</button>
      <button class="btn red sm" onclick="openDelete(${r.id},'${esc(r.email).replace(/'/g,"\\'")}')">Delete</button>
    </td>
  </tr>`).join('');
}

function openPw(id){ CUR_PW_ID=id; document.getElementById('pwVal').value=''; openModal('pwModal'); }
async function submitPw(){
  const password=document.getElementById('pwVal').value;
  if(password.length<6){toast('Enter a password with 6+ characters',true);return;}
  try{
    await api('api/admin_users.php?action=set_password',{id:CUR_PW_ID,password});
    toast('Password updated'); closeModal('pwModal');
  }catch(e){toast(e.message,true);}
}

function openDelete(id,email){
  CUR_DEL={id,email};
  document.getElementById('delEmailLabel').textContent=email;
  document.getElementById('delConfirm').value='';
  openModal('delModal');
}
async function submitDelete(){
  const confirm_email=document.getElementById('delConfirm').value;
  try{
    await api('api/admin_users.php?action=delete',{id:CUR_DEL.id,confirm_email});
    toast('Deleted'); closeModal('delModal'); loadUsers();
  }catch(e){toast(e.message,true);}
}

loadUsers();
