async function loadCoupons(){
  const b=document.getElementById('couponBody');
  let j;
  try{ j=await api('api/coupons.php?action=list'); }
  catch(e){ b.innerHTML=`<tr><td colspan="6" class="empty">Couldn't load data: ${esc(e.message)} — <a href="javascript:loadCoupons()">Retry</a></td></tr>`; return; }
  if(!j.rows.length){ b.innerHTML='<tr><td colspan="6" class="empty">No coupons yet. Generate some above.</td></tr>'; return; }
  b.innerHTML=j.rows.map(r=>`<tr>
    <td style="font-family:monospace">${esc(r.code)}</td>
    <td><span class="badge ${r.status==='Used'?'b-gray':'b-green'}">${esc(r.status)}</span></td>
    <td>${r.used_by_name?esc(r.used_by_name)+' ('+esc(r.used_by_email)+')':'—'}</td>
    <td>${esc(r.used_for_estate_name||'—')}</td>
    <td>${esc(r.used_at||'—')}</td>
    <td class="right">${r.status==='Unused'?`<button class="btn red sm" onclick="revokeCoupon(${r.id})">Revoke</button>`:''}</td>
  </tr>`).join('');
}

async function generateCoupons(){
  const count=parseInt(document.getElementById('genCount').value,10);
  if(!count||count<1||count>100){toast('Enter a number between 1 and 100',true);return;}
  try{
    const j=await api('api/coupons.php?action=generate',{count});
    document.getElementById('genResult').style.display='';
    document.getElementById('genCodes').value=j.codes.join('\n');
    toast(`Generated ${j.codes.length} coupon${j.codes.length===1?'':'s'}`);
    loadCoupons();
  }catch(e){toast(e.message,true);}
}

async function revokeCoupon(id){
  if(!confirmDelete('Revoke this unused coupon code?'))return;
  try{await api('api/coupons.php?action=delete',{id});toast('Revoked');loadCoupons();}
  catch(e){toast(e.message,true);}
}

loadCoupons();
