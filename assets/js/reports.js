document.getElementById('rFrom').value=new Date().toISOString().slice(0,8)+'01';
document.getElementById('rTo').value=new Date().toISOString().slice(0,10);
document.getElementById('rType').onchange=e=>{document.getElementById('priceWrap').style.display=e.target.value==='profit'?'':'none';};
(async()=>{const j=await api('api/lookups.php?what=estates');
  const s=document.getElementById('rEstate');
  j.rows.forEach(r=>s.insertAdjacentHTML('beforeend',`<option value="${r.id}">${esc(r.name)}</option>`));})();

function q(){
  return new URLSearchParams({type:document.getElementById('rType').value,
    from:document.getElementById('rFrom').value,to:document.getElementById('rTo').value,
    estate_id:document.getElementById('rEstate').value,price:document.getElementById('rPrice').value||''});
}
async function runReport(){
  const j=await api('api/reports.php?'+q());
  document.getElementById('reportHeader').style.display='';
  document.getElementById('rTitle').textContent=j.title;
  document.getElementById('rMeta').textContent=`Range: ${j.from} to ${j.to}`;
  document.getElementById('rHead').innerHTML='<tr>'+j.cols.map((c,i)=>`<th class="${i>0?'right':''}">${esc(c)}</th>`).join('')+'</tr>';
  document.getElementById('rBody').innerHTML=j.rows.length?j.rows.map(row=>
    '<tr>'+row.map((c,i)=>`<td class="${i>0?'right':''}">${esc(c)}</td>`).join('')+'</tr>').join('')
    :`<tr><td colspan="${j.cols.length}" class="empty">No data for this period</td></tr>`;
}
function exportCsv(){window.location='api/reports.php?export=csv&'+q();}
