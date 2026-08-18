let CUR_TICKET=null, CUR_STATUS='';
const badgeMap={Open:'b-amber',Answered:'b-green',Closed:'b-gray'};

document.querySelectorAll('[data-status]').forEach(el=>el.onclick=()=>{
  document.querySelectorAll('[data-status]').forEach(x=>x.classList.remove('active'));
  el.classList.add('active');
  CUR_STATUS=el.dataset.status;
  loadTickets();
});

async function loadTickets(){
  const b=document.getElementById('ticketBody');
  let j;
  try{ j=await api('api/admin_tickets.php?action=list'+(CUR_STATUS?'&status='+encodeURIComponent(CUR_STATUS):'')); }
  catch(e){ b.innerHTML=`<tr><td colspan="5" class="empty">Couldn't load data: ${esc(e.message)} — <a href="javascript:loadTickets()">Retry</a></td></tr>`; return; }
  if(!j.rows.length){ b.innerHTML='<tr><td colspan="5" class="empty">No tickets.</td></tr>'; return; }
  b.innerHTML=j.rows.map(r=>`<tr>
    <td>${esc(r.subject)}</td>
    <td>${esc(r.customer_name)} (${esc(r.customer_email)})</td>
    <td><span class="badge ${badgeMap[r.status]||'b-gray'}">${esc(r.status)}</span></td>
    <td>${esc(r.updated_at)}</td>
    <td class="right"><button class="btn gray sm" onclick="openThread(${r.id})">View</button></td>
  </tr>`).join('');
}

function renderMsgs(replies){
  const wrap=document.getElementById('threadMsgs');
  wrap.innerHTML=replies.map(r=>`
    <div style="align-self:${r.is_admin_reply?'flex-end':'flex-start'};max-width:70%">
      <div style="background:${r.is_admin_reply?'var(--green-l)':'#eef1ef'};border-radius:12px;padding:10px 14px">
        <div style="font-size:11px;color:var(--muted);margin-bottom:4px">${esc(r.user_name)}${r.is_admin_reply?' (Support)':''} · ${esc(r.created_at)}</div>
        <div style="font-size:13px;white-space:pre-wrap">${esc(r.message)}</div>
      </div>
    </div>`).join('');
}

async function openThread(id){
  try{
    const j=await api('api/admin_tickets.php?action=thread&id='+id);
    CUR_TICKET=id;
    document.getElementById('threadSubject').textContent=j.ticket.subject;
    document.getElementById('threadCustomer').textContent=j.ticket.customer_name+' ('+j.ticket.customer_email+')';
    renderMsgs(j.replies);
    document.getElementById('listHead').style.display='none';
    document.getElementById('listView').style.display='none';
    document.getElementById('threadView').style.display='';
    document.getElementById('replyMsg').value='';
  }catch(e){toast(e.message,true);}
}
function backToList(){
  document.getElementById('listHead').style.display='';
  document.getElementById('listView').style.display='';
  document.getElementById('threadView').style.display='none';
  loadTickets();
}
async function sendReply(){
  const message=document.getElementById('replyMsg').value.trim();
  if(!message){toast('Enter a message',true);return;}
  try{
    await api('api/admin_tickets.php?action=reply',{ticket_id:CUR_TICKET,message});
    document.getElementById('replyMsg').value='';
    openThread(CUR_TICKET);
  }catch(e){toast(e.message,true);}
}
async function setStatus(status){
  try{
    await api('api/admin_tickets.php?action=set_status',{ticket_id:CUR_TICKET,status});
    toast('Status updated to '+status);
    openThread(CUR_TICKET);
  }catch(e){toast(e.message,true);}
}

loadTickets();
