let PV=[];
document.getElementById('pFrom').value=new Date().toISOString().slice(0,8)+'01';
document.getElementById('pTo').value=new Date().toISOString().slice(0,10);
const CAN_APPROVE = window.CAN_APPROVE === true;

function pTab(t){
  document.getElementById('genView').style.display=t==='gen'?'':'none';
  document.getElementById('histView').style.display=t==='hist'?'':'none';
  document.getElementById('tabGen').className=t==='gen'?'btn':'btn gray';
  document.getElementById('tabHist').className=t==='hist'?'btn':'btn gray';
  if(t==='hist') loadHist();
}
(async()=>{try{
  const j=await api('api/lookups.php?what=estates');
  const s=document.getElementById('pEstate');
  j.rows.forEach(r=>s.insertAdjacentHTML('beforeend',`<option value="${r.id}">${esc(r.name)}</option>`));
}catch(e){toast(e.message,true);}})();

async function preview(){
  const q=new URLSearchParams({from:document.getElementById('pFrom').value,to:document.getElementById('pTo').value,estate_id:document.getElementById('pEstate').value});
  let j;
  try{ j=await api('api/payroll.php?action=preview&'+q); }catch(e){ toast(e.message,true); return; }
  PV=j.rows;
  const b=document.getElementById('pvBody');
  if(!j.rows.length){b.innerHTML='<tr><td colspan="9" class="empty">No assignments in this period</td></tr>';document.getElementById('genBtn').style.display='none';return;}
  b.innerHTML=j.rows.map(r=>`<tr>
    <td><strong>${esc(r.emp_code)}</strong> ${esc(r.name)}</td>
    <td class="right">${r.days}</td><td class="right">${fmt(r.kg)}</td>
    <td class="right">${money(r.plucking)}</td><td class="right">${money(r.assignment_pay)}</td>
    <td class="right">${money(r.allowances)}</td><td class="right">${money(r.deductions)}</td>
    <td class="right"><strong>${money(r.gross)}</strong></td><td class="right"><strong>${money(r.net)}</strong></td></tr>`).join('');
  document.getElementById('genBtn').style.display='';
}
async function generate(){
  try{
    const j=await api('api/payroll.php?action=generate',{
      from:document.getElementById('pFrom').value,to:document.getElementById('pTo').value,
      estate_id:document.getElementById('pEstate').value, rows:PV});
    toast(`Payroll saved for ${j.saved} workers`); pTab('hist');
  }catch(e){toast(e.message,true);}
}
async function loadHist(){
  const b=document.getElementById('histBody');
  let j;
  try{ j=await api('api/payroll.php?action=list'); }
  catch(e){ b.innerHTML=`<tr><td colspan="7" class="empty">Couldn't load data: ${esc(e.message)} — <a href="javascript:loadHist()">Retry</a></td></tr>`; return; }
  const m={Draft:'b-gray',Calculated:'b-amber',Approved:'b-green',Paid:'b-blue'};
  b.innerHTML=j.rows.length?j.rows.map(r=>`<tr>
    <td><strong>${esc(r.emp_code)}</strong> ${esc(r.full_name)}</td>
    <td>${r.period_from} → ${r.period_to}</td>
    <td class="right">${money(r.gross)}</td><td class="right">${money(r.deductions)}</td>
    <td class="right"><strong>${money(r.net)}</strong></td>
    <td><span class="badge ${m[r.status]||'b-gray'}">${r.status}</span></td>
    <td class="right" style="white-space:nowrap">
      ${CAN_APPROVE&&r.status==='Calculated'?`<button class="btn sm" onclick="appr(${r.id})">Approve</button>`:''}
      ${CAN_APPROVE&&r.status==='Approved'?`<button class="btn blue sm" onclick="payIt(${r.id})">Mark Paid</button>`:''}
      <button class="btn red sm" onclick="delP(${r.id})">✕</button></td></tr>`).join('')
    :'<tr><td colspan="7" class="empty">No payroll runs yet</td></tr>';
}
async function appr(id){try{await api('api/payroll.php?action=approve',{id});toast('Approved');loadHist();}catch(e){toast(e.message,true);}}
async function payIt(id){const method=prompt('Payment method?','Bank Transfer');if(method===null)return;try{await api('api/payroll.php?action=pay',{id,method});toast('Marked paid');loadHist();}catch(e){toast(e.message,true);}}
async function delP(id){if(!confirmDelete())return;try{await api('api/payroll.php?action=delete',{id});toast('Deleted');loadHist();}catch(e){toast(e.message,true);}}
