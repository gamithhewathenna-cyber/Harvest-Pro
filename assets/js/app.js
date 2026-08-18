// Core helpers
function toast(msg, err){
  const t=document.getElementById('toast');
  t.textContent=msg; t.className='toast show'+(err?' err':'');
  setTimeout(()=>t.className='toast',2600);
}
async function api(url, data, timeoutMs){
  const ctrl=new AbortController();
  const timer=setTimeout(()=>ctrl.abort(), timeoutMs||15000);
  const opt={headers:{'Content-Type':'application/json'}, signal:ctrl.signal};
  if(data!==undefined){opt.method='POST';opt.body=JSON.stringify({...data,csrf:window.CSRF});}
  try{
    const r=await fetch(url,opt);
    const j=await r.json();
    if(!j.ok) throw new Error(j.error||'Request failed');
    return j;
  }catch(e){
    if(e.name==='AbortError') throw new Error('Request timed out. Check your connection and try again.');
    throw e;
  }finally{
    clearTimeout(timer);
  }
}
function money(n){return 'LKR '+Number(n||0).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2});}
function fmt(n){return Number(n||0).toLocaleString();}
function esc(s){const d=document.createElement('div');d.textContent=s==null?'':s;return d.innerHTML;}

// Modal
function openModal(id){document.getElementById(id).classList.add('show');}
function closeModal(id){document.getElementById(id).classList.remove('show');}
document.addEventListener('click',e=>{
  if(e.target.classList.contains('modal-bg')) e.target.classList.remove('show');
});

function confirmDelete(msg){return confirm(msg||'Are you sure you want to delete this? This cannot be undone.');}

// Tiny bar chart (canvas)
function barChart(canvas, labels, values, color){
  const ctx=canvas.getContext('2d'), W=canvas.width=canvas.offsetWidth*2, H=canvas.height=canvas.offsetHeight*2;
  ctx.scale(1,1); ctx.clearRect(0,0,W,H);
  const max=Math.max(...values,1), pad=40*2, bw=(W-pad)/values.length*0.6, gap=(W-pad)/values.length*0.4;
  ctx.font='20px Segoe UI'; ctx.fillStyle='#6b7d72';
  values.forEach((v,i)=>{
    const x=pad+i*(bw+gap)+gap/2, h=(H-70*2)*(v/max), y=H-50*2-h;
    ctx.fillStyle=color||'#1f7a4d';
    ctx.beginPath(); ctx.roundRect(x,y,bw,h,[8]); ctx.fill();
    ctx.fillStyle='#6b7d72'; ctx.textAlign='center';
    ctx.fillText(labels[i], x+bw/2, H-20*2);
    ctx.fillStyle='#1c2b23'; ctx.fillText(fmt(v), x+bw/2, y-8);
  });
}

// Dual line chart
function lineChart(canvas, labels, series){ // series:[{name,color,data}]
  const ctx=canvas.getContext('2d'), W=canvas.width=canvas.offsetWidth*2, H=canvas.height=canvas.offsetHeight*2;
  ctx.clearRect(0,0,W,H);
  const all=series.flatMap(s=>s.data), max=Math.max(...all,1);
  const padL=70*2,padB=40*2,padT=20*2, plotW=W-padL-20, plotH=H-padB-padT;
  ctx.strokeStyle='#e3e9e4'; ctx.lineWidth=1;
  for(let g=0;g<=4;g++){const y=padT+plotH*g/4;ctx.beginPath();ctx.moveTo(padL,y);ctx.lineTo(W-20,y);ctx.stroke();
    ctx.fillStyle='#6b7d72';ctx.font='18px Segoe UI';ctx.textAlign='right';ctx.fillText(fmt(Math.round(max*(1-g/4))),padL-10,y+6);}
  ctx.textAlign='center';
  labels.forEach((l,i)=>{const x=padL+plotW*i/(labels.length-1);ctx.fillStyle='#6b7d72';ctx.fillText(l,x,H-12*2);});
  series.forEach(s=>{
    ctx.strokeStyle=s.color;ctx.lineWidth=3;ctx.beginPath();
    s.data.forEach((v,i)=>{const x=padL+plotW*i/(labels.length-1),y=padT+plotH*(1-v/max);i?ctx.lineTo(x,y):ctx.moveTo(x,y);});
    ctx.stroke();
    s.data.forEach((v,i)=>{const x=padL+plotW*i/(labels.length-1),y=padT+plotH*(1-v/max);
      ctx.fillStyle=s.color;ctx.beginPath();ctx.arc(x,y,5,0,7);ctx.fill();});
  });
}
