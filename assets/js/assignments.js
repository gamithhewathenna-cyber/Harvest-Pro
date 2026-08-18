let TYPES=[], WORKERS=[];
document.getElementById('bDate').value=new Date().toISOString().slice(0,10);
document.getElementById('fTo').value=new Date().toISOString().slice(0,10);
document.getElementById('fFrom').value=new Date(Date.now()-7*864e5).toISOString().slice(0,10);

function switchTab(t){
  document.getElementById('bulkView').style.display=t==='bulk'?'':'none';
  document.getElementById('listView').style.display=t==='list'?'':'none';
  document.getElementById('tabBulk').className=t==='bulk'?'btn':'btn gray';
  document.getElementById('tabList').className=t==='list'?'btn':'btn gray';
  if(t==='list') loadList();
}

async function init(){
  try{
    const est=await api('api/lookups.php?what=estates');
    ['bEstate','fEstate'].forEach(id=>{const s=document.getElementById(id);
      est.rows.forEach(r=>s.insertAdjacentHTML('beforeend',`<option value="${r.id}">${esc(r.name)}</option>`));});
    const t=await api('api/lookups.php?what=assignment_types');
    TYPES=t.rows;
    const bt=document.getElementById('bType');
    TYPES.forEach(r=>bt.insertAdjacentHTML('beforeend',`<option value="${r.id}" data-p="${r.is_plucking}">${esc(r.name)}</option>`));
    if(est.rows.length) loadBulkContext();
  }catch(e){ toast(e.message,true); }
}
function isPluck(){const o=document.getElementById('bType').selectedOptions[0];return o&&o.dataset.p==='1';}
function onTypeChange(){document.getElementById('thKg').textContent=isPluck()?'KG':'Units';buildRows();}

async function loadBulkContext(){
  const eid=document.getElementById('bEstate').value;
  try{
    const sec=await api('api/lookups.php?what=sections&estate_id='+eid);
    const ss=document.getElementById('bSection'); ss.innerHTML='<option value="">—</option>';
    sec.rows.forEach(r=>ss.insertAdjacentHTML('beforeend',`<option value="${r.id}">${esc(r.name)}</option>`));
    const w=await api('api/lookups.php?what=employees&estate_id='+eid);
    WORKERS=w.rows; buildRows();
  }catch(e){ toast(e.message,true); }
}
function buildRows(){
  const b=document.getElementById('bulkBody');
  if(!WORKERS.length){b.innerHTML='<tr><td colspan="6" class="empty">No active workers in this estate</td></tr>';summ();return;}
  const pl=isPluck();
  b.innerHTML=WORKERS.map(w=>`<tr data-id="${w.id}">
    <td><strong>${esc(w.emp_code)}</strong> ${esc(w.full_name)}</td>
    <td><input type="number" inputmode="decimal" class="cKg" style="width:80px;padding:6px" value="0" oninput="rowCalc(this)"></td>
    <td><input type="number" inputmode="decimal" class="cRate" style="width:80px;padding:6px" value="${pl?(w.kg_rate||0):(w.daily_rate||0)}" oninput="rowCalc(this)"></td>
    <td><input type="number" inputmode="decimal" class="cAllow" style="width:70px;padding:6px" value="0" oninput="rowCalc(this)"></td>
    <td><input type="number" inputmode="decimal" class="cDed" style="width:70px;padding:6px" value="0" oninput="rowCalc(this)"></td>
    <td class="right cTotal" style="font-weight:700">0.00</td></tr>`).join('');
  summ();
}
function rowCalc(inp){
  const tr=inp.closest('tr'), pl=isPluck();
  const kg=+tr.querySelector('.cKg').value||0, rate=+tr.querySelector('.cRate').value||0;
  const al=+tr.querySelector('.cAllow').value||0, de=+tr.querySelector('.cDed').value||0;
  const total=(pl?kg*rate:rate)+al-de;
  tr.querySelector('.cTotal').textContent=total.toFixed(2);
  summ();
}
function summ(){
  let n=0,kg=0,tot=0;
  document.querySelectorAll('#bulkBody tr[data-id]').forEach(tr=>{
    const k=+tr.querySelector('.cKg').value||0, t=+tr.querySelector('.cTotal').textContent||0;
    if(k>0||t!==0)n++; kg+=k; tot+=t;
  });
  document.getElementById('bulkSummary').textContent=`Workers: ${n} · Total KG: ${fmt(kg)} · Total: ${money(tot)}`;
}
async function saveBulk(){
  const o=document.getElementById('bType').selectedOptions[0];
  const rows=[];
  document.querySelectorAll('#bulkBody tr[data-id]').forEach(tr=>{
    rows.push({employee_id:tr.dataset.id,kg:tr.querySelector('.cKg').value,rate:tr.querySelector('.cRate').value,
      allowance:tr.querySelector('.cAllow').value,deduction:tr.querySelector('.cDed').value});
  });
  try{
    const j=await api('api/assignments.php?action=bulk',{
      work_date:document.getElementById('bDate').value,
      estate_id:document.getElementById('bEstate').value,
      section_id:document.getElementById('bSection').value,
      assignment_type_id:o.value, assignment_type:o.textContent, is_plucking:isPluck()?1:0,
      supervisor:document.getElementById('bSup').value, rows
    });
    toast(`Saved ${j.saved} assignments`);
    buildRows();
  }catch(e){toast(e.message,true);}
}

async function loadList(){
  const q=new URLSearchParams({from:document.getElementById('fFrom').value,to:document.getElementById('fTo').value,estate_id:document.getElementById('fEstate').value});
  const b=document.getElementById('listBody');
  let j;
  try{ j=await api('api/assignments.php?action=list&'+q); }
  catch(e){ b.innerHTML=`<tr><td colspan="7" class="empty">Couldn't load data: ${esc(e.message)} — <a href="javascript:loadList()">Retry</a></td></tr>`; return; }
  b.innerHTML=j.rows.length?j.rows.map(r=>`<tr>
    <td>${r.work_date}</td><td><strong>${esc(r.emp_code)}</strong> ${esc(r.full_name)}</td>
    <td>${esc(r.estate_name||'')}${r.section_name?' / '+esc(r.section_name):''}</td>
    <td>${esc(r.assignment_type||'')}</td><td class="right">${fmt(r.kg)}</td><td class="right">${money(r.cost)}</td>
    <td class="right"><button class="btn red sm" onclick="delAsg(${r.id})">✕</button></td></tr>`).join('')
    :'<tr><td colspan="7" class="empty">No assignments found</td></tr>';
}
async function delAsg(id){
  if(!confirmDelete())return;
  try{await api('api/assignments.php?action=delete',{id});toast('Deleted');loadList();}catch(e){toast(e.message,true);}
}
init();
