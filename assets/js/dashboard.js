let RANGE='month';
const MONTHS=['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

async function initEstates(){
  const j=await api('api/lookups.php?what=estates');
  const sel=document.getElementById('estateSel');
  j.rows.forEach(r=>sel.insertAdjacentHTML('beforeend',`<option value="${r.id}">${esc(r.name)}</option>`));
}
function initYears(){
  const y=new Date().getFullYear(), sel=document.getElementById('yearSel');
  for(let i=y;i>=y-4;i--) sel.insertAdjacentHTML('beforeend',`<option>${i}</option>`);
}

document.querySelectorAll('.chip[data-range]').forEach(c=>{
  c.onclick=()=>{
    document.querySelectorAll('.chip[data-range]').forEach(x=>x.classList.remove('active'));
    c.classList.add('active'); RANGE=c.dataset.range;
    document.getElementById('customBox').style.display = RANGE==='custom'?'inline-flex':'none';
    if(RANGE!=='custom') load();
  };
});
document.getElementById('estateSel').onchange=load;
document.getElementById('yearSel').onchange=load;

async function load(){
  const est=document.getElementById('estateSel').value;
  const yr=document.getElementById('yearSel').value||new Date().getFullYear();
  let url=`api/dashboard.php?range=${RANGE}&estate=${est}&year=${yr}`;
  if(RANGE==='custom') url+=`&from=${document.getElementById('fromD').value}&to=${document.getElementById('toD').value}`;
  let j; try{ j=await api(url);}catch(e){toast(e.message,true);return;}
  const k=j.kpi;
  document.getElementById('k_workers').textContent=k.active_workers;
  document.getElementById('k_workers_s').textContent='of '+k.total_workers+' total';
  document.getElementById('k_kg').textContent=fmt(k.kg)+' KG';
  document.getElementById('k_pay').textContent=money(k.payroll);
  document.getElementById('k_pay_s').textContent=k.assignments+' assignments';
  document.getElementById('k_exp').textContent=money(k.expenses);
  document.getElementById('k_exp_s').textContent=k.expense_count+' expenses';
  document.getElementById('k_cpk').textContent=money(k.cost_per_kg);
  document.getElementById('k_avg').textContent=fmt(Math.round(k.avg_per_worker))+' KG';
  document.getElementById('k_acres').textContent=fmt(k.tea_acres);

  // chart
  lineChart(document.getElementById('peChart'),MONTHS,[
    {name:'Payroll',color:'#c98a1a',data:j.chart.payroll},
    {name:'Expenses',color:'#c0392b',data:j.chart.expenses}
  ]);

  // sections
  if(j.sections.length) barChart(document.getElementById('secChart'),j.sections.map(s=>s.name.slice(0,6)),j.sections.map(s=>+s.kg));
  else document.getElementById('secChart').parentElement.innerHTML='<div class="empty">No harvest data yet</div>';

  // events
  const ev=document.getElementById('events');
  ev.innerHTML = j.events.length ? '<table class="tbl"><tbody>'+j.events.map(e=>{
    const b=e.status==='Overdue'?'b-red':e.status==='Due Today'?'b-amber':e.status==='Due Soon'?'b-blue':'b-green';
    return `<tr><td><strong>${esc(e.title)}</strong><br><small class="muted">${esc(e.type)} · ${e.due}</small></td>
      <td class="right"><span class="badge ${b}">${e.status}</span><br><small class="muted">${e.days<0?Math.abs(e.days)+'d ago':e.days+'d'}</small></td></tr>`;
  }).join('')+'</tbody></table>' : '<div class="empty">No upcoming events</div>';

  // expense breakdown
  const total=j.expense_cat.reduce((a,c)=>a+ +c.amt,0);
  const eb=document.getElementById('expBreak');
  eb.innerHTML = j.expense_cat.length ? j.expense_cat.map(c=>{
    const pct=total?Math.round(c.amt/total*100):0;
    return `<div style="margin-bottom:12px"><div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px">
      <span>${esc(c.cat)}</span><strong>${money(c.amt)}</strong></div>
      <div style="height:8px;background:#eef1ef;border-radius:6px;overflow:hidden"><div style="width:${pct}%;height:100%;background:#1f7a4d"></div></div></div>`;
  }).join('')+`<div class="right" style="margin-top:14px;font-weight:700">Total ${money(total)}</div>` : '<div class="empty">No expenses in this period</div>';

  // top workers
  const tw=document.getElementById('topWorkers');
  tw.innerHTML = j.top_workers.length ? '<table class="tbl"><tbody>'+j.top_workers.map((w,i)=>
    `<tr><td style="width:40px"><span class="badge b-green">#${i+1}</span></td><td><strong>${esc(w.full_name)}</strong></td><td class="right">${fmt(w.kg)} KG</td></tr>`
  ).join('')+'</tbody></table>' : '<div class="empty">No data</div>';
}

(async()=>{ await initEstates(); initYears(); load(); })();
