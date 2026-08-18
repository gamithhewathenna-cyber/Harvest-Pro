// Reusable CRUD manager. Config passed via window.CRUD
// { table, title, fields:[{name,label,type,options?,required?,col?}], columns:[{key,label,fmt?}], filters?:[] }
const C = window.CRUD;
let CACHE=[], LOOKUPS={};
if(C.saveLabel){const b=document.getElementById('saveBtn'); if(b) b.textContent=C.saveLabel;}

async function loadLookup(name){
  if(LOOKUPS[name]) return LOOKUPS[name];
  const j=await api('api/lookups.php?what='+name);
  LOOKUPS[name]=j.rows; return j.rows;
}

async function buildForm(row){
  const f=document.getElementById('crudForm'); f.innerHTML='';
  for(const fd of C.fields){
    if(fd.addOnly && row) continue; // only relevant when adding a new record
    const val=row?(row[fd.name]??''):(fd.default??'');
    let input='';
    if(fd.type==='select'){
      let opts=fd.options||[];
      if(fd.lookup) opts=(await loadLookup(fd.lookup)).map(r=>({v:r.id,t:(r.emp_code?r.emp_code+' - ':'')+(r.full_name||r.name)}));
      input=`<select name="${fd.name}" ${fd.required?'required':''}>${fd.allowEmpty?'<option value="">—</option>':''}`+
        opts.map(o=>{const v=o.v??o,t=o.t??o;return `<option value="${esc(v)}" ${String(val)===String(v)?'selected':''}>${esc(t)}</option>`}).join('')+'</select>';
    } else if(fd.type==='multiselect'){
      let opts=fd.options||[];
      if(fd.lookup) opts=(await loadLookup(fd.lookup)).map(r=>({v:r.id,t:(r.emp_code?r.emp_code+' - ':'')+(r.full_name||r.name)}));
      const selected=String(val).split(',').filter(Boolean);
      input=`<select name="${fd.name}" multiple size="${Math.min(6,Math.max(3,opts.length))}">`+
        opts.map(o=>{const v=o.v??o,t=o.t??o;return `<option value="${esc(v)}" ${selected.includes(String(v))?'selected':''}>${esc(t)}</option>`}).join('')+'</select>';
    } else if(fd.type==='textarea'){
      input=`<textarea name="${fd.name}" rows="2">${esc(val)}</textarea>`;
    } else {
      input=`<input type="${fd.type||'text'}" name="${fd.name}" value="${esc(val)}" ${fd.required?'required':''} ${fd.step?'step='+fd.step:''} ${fd.placeholder?'placeholder="'+esc(fd.placeholder)+'"':''}>`;
    }
    f.insertAdjacentHTML('beforeend',`<div class="field ${fd.col||''}"><label>${esc(fd.label)}${fd.required?' *':''}</label>${input}</div>`);
  }
  document.getElementById('crudId').value=row?row.id:'';
  document.getElementById('modalTitle').textContent=(row?'Edit ':'Add ')+C.title;
}

async function openAdd(){ await buildForm(null); openModal('crudModal'); }
async function openEdit(id){
  try{
    const j=await api(`api/crud.php?table=${C.table}&action=get&id=${id}`);
    await buildForm(j.row); openModal('crudModal');
  }catch(e){ toast(e.message,true); }
}
async function saveForm(){
  const f=document.getElementById('crudForm');
  const data={id:document.getElementById('crudId').value||''};
  let valid=true;
  f.querySelectorAll('input,select,textarea').forEach(el=>{
    const v=el.multiple?Array.from(el.selectedOptions).map(o=>o.value).join(','):el.value;
    if(el.required && !v){el.style.borderColor='#c0392b';valid=false;} else el.style.borderColor='';
    data[el.name]=v;
  });
  if(!valid){toast('Please fill required fields',true);return;}
  try{
    await api(`api/crud.php?table=${C.table}&action=save`,data);
    toast('Saved successfully'); closeModal('crudModal'); loadRows();
  }catch(e){toast(e.message,true);}
}
async function delRow(id){
  if(!confirmDelete())return;
  try{await api(`api/crud.php?table=${C.table}&action=delete`,{id});toast('Deleted');loadRows();}
  catch(e){toast(e.message,true);}
}

async function loadRows(){
  const b=document.getElementById('crudBody');
  const params=new URLSearchParams({table:C.table,action:'list'});
  if(C.getFilters) Object.entries(C.getFilters()).forEach(([k,v])=>v&&params.set(k,v));
  let j;
  try{
    j=await api('api/crud.php?'+params);
  }catch(e){
    b.innerHTML=`<tr><td colspan="${C.columns.length+1}" class="empty">Couldn't load data: ${esc(e.message)} — <a href="javascript:loadRows()">Retry</a></td></tr>`;
    return;
  }
  CACHE=j.rows;
  if(!j.rows.length){b.innerHTML=`<tr><td colspan="${C.columns.length+1}" class="empty">No records yet. Click "Add ${esc(C.title)}".</td></tr>`;return;}
  b.innerHTML=j.rows.map(r=>'<tr>'+C.columns.map(c=>{
    let v=r[c.key];
    if(c.fmt==='money')v=money(v); else if(c.fmt==='num')v=fmt(v);
    else if(c.fmt==='badge'){const m={Active:'b-green',Inactive:'b-gray',Approved:'b-green',Pending:'b-amber',Paid:'b-blue',Rejected:'b-red',Draft:'b-gray',Open:'b-amber',Completed:'b-green','On Leave':'b-amber',Terminated:'b-red',Critical:'b-red',High:'b-amber',Medium:'b-blue',Low:'b-gray'};v=`<span class="badge ${m[v]||'b-gray'}">${esc(v||'')}</span>`;}
    else v=esc(v==null?'':v);
    return `<td class="${c.right?'right':''}">${v}</td>`;
  }).join('')+`<td class="right" style="white-space:nowrap">
    <button class="btn gray sm" onclick="openEdit(${r.id})">Edit</button>
    <button class="btn red sm" onclick="delRow(${r.id})">✕</button></td></tr>`).join('');
}
window.openAdd=openAdd;window.openEdit=openEdit;window.saveForm=saveForm;window.delRow=delRow;window.loadRows=loadRows;
