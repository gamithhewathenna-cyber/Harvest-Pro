let CUR_TICKET=null;
const badgeMap={Open:'b-amber',Answered:'b-green',Closed:'b-gray'};

async function loadTickets(){
  const b=document.getElementById('ticketBody');
  let j;
  try{ j=await api('api/tickets.php?action=list'); }
  catch(e){ b.innerHTML=`<tr><td colspan="4" class="empty">Couldn't load data: ${esc(e.message)} — <a href="javascript:loadTickets()">Retry</a></td></tr>`; return; }
  if(!j.rows.length){ b.innerHTML='<tr><td colspan="4" class="empty">No tickets yet. Click "New Ticket" if you need help.</td></tr>'; return; }
  b.innerHTML=j.rows.map(r=>`<tr>
    <td>${esc(r.subject)}</td>
    <td><span class="badge ${badgeMap[r.status]||'b-gray'}">${esc(r.status)}</span></td>
    <td>${esc(r.updated_at)}</td>
    <td class="right"><button class="btn gray sm" onclick="openThread(${r.id})">View</button></td>
  </tr>`).join('');
}

function openNew(){ document.getElementById('newSubject').value=''; document.getElementById('newMessage').value=''; openModal('newTicketModal'); }
async function submitNew(){
  const subject=document.getElementById('newSubject').value.trim();
  const message=document.getElementById('newMessage').value.trim();
  if(!subject||!message){toast('Enter a subject and message',true);return;}
  try{
    const j=await api('api/tickets.php?action=create',{subject,message});
    closeModal('newTicketModal'); toast('Ticket submitted');
    loadTickets(); openThread(j.id);
  }catch(e){toast(e.message,true);}
}

function renderMsgs(replies){
  const wrap=document.getElementById('threadMsgs');
  wrap.innerHTML=replies.map(r=>`
    <div style="align-self:${r.is_admin_reply?'flex-start':'flex-end'};max-width:70%">
      <div style="background:${r.is_admin_reply?'#eef1ef':'var(--green-l)'};border-radius:12px;padding:10px 14px">
        <div style="font-size:11px;color:var(--muted);margin-bottom:4px">${esc(r.user_name)}${r.is_admin_reply?' (Support)':''} · ${esc(r.created_at)}</div>
        <div style="font-size:13px;white-space:pre-wrap">${esc(r.message)}</div>
      </div>
    </div>`).join('');
}

async function openThread(id){
  try{
    const j=await api('api/tickets.php?action=thread&id='+id);
    CUR_TICKET=id;
    document.getElementById('threadSubject').textContent=j.ticket.subject;
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
    await api('api/tickets.php?action=reply',{ticket_id:CUR_TICKET,message});
    document.getElementById('replyMsg').value='';
    openThread(CUR_TICKET);
  }catch(e){toast(e.message,true);}
}

loadTickets();
