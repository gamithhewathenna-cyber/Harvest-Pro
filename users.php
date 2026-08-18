<?php
$title='User Management'; $page='users.php'; $entityTitle='User';
require_once __DIR__.'/includes/auth.php';
require_login();
if(!can_admin()){ echo '<link rel="stylesheet" href="assets/css/app.css"><div style="padding:40px;text-align:center">Access denied. Admins only.</div>'; exit; }
$columns=[['label'=>'Name'],['label'=>'Email'],['label'=>'Role'],['label'=>'Status'],['label'=>'Last Login']];
$configJs=json_encode([
  'table'=>'users','title'=>'User',
  'columns'=>[['key'=>'name'],['key'=>'email'],['key'=>'role','fmt'=>'badge'],['key'=>'status','fmt'=>'badge'],['key'=>'last_login']],
  'fields'=>[
    ['name'=>'name','label'=>'Name','required'=>true],
    ['name'=>'email','label'=>'Email','type'=>'email','required'=>true],
    ['name'=>'phone','label'=>'Phone'],
    ['name'=>'address','label'=>'Address','col'=>'form-full'],
    ['name'=>'role','label'=>'Role','type'=>'select','options'=>['Owner','Administrator','Estate Manager','Supervisor','Accountant','Viewer']],
    ['name'=>'assigned_estate_ids','label'=>'Assigned Estate(s)','type'=>'multiselect','lookup'=>'estates'],
    ['name'=>'status','label'=>'Status','type'=>'select','options'=>['Active','Inactive']],
  ]
]);
$initJs = "loadRows();";
require __DIR__.'/includes/crud_page.php';
?>
<div class="card mb" style="margin-top:16px"><div class="card-pad">
  <h3 style="margin-top:0">Set / Reset Password</h3>
  <div class="form-row3">
    <div class="field"><label>User ID</label><input id="pwId" type="number" placeholder="From table"></div>
    <div class="field"><label>New Password</label><input id="pwVal" type="text" placeholder="min 6 chars"></div>
    <div class="field" style="display:flex;align-items:flex-end"><button class="btn" onclick="setPw()">Update Password</button></div>
  </div>
</div></div>
<script>
async function setPw(){
  const id=document.getElementById('pwId').value, password=document.getElementById('pwVal').value;
  if(!id||password.length<6){toast('Enter user ID and 6+ char password',true);return;}
  try{await api('api/crud.php?table=users&action=set_password',{id,password});toast('Password updated');
    document.getElementById('pwId').value='';document.getElementById('pwVal').value='';}
  catch(e){toast(e.message,true);}
}
</script>
