<?php
$title='Reminders'; $page='reminders.php'; $entityTitle='Reminder';
require_once __DIR__.'/includes/auth.php';
$columns=[['label'=>'Title'],['label'=>'Type'],['label'=>'Due Date'],['label'=>'Priority'],['label'=>'Assigned'],['label'=>'Status']];
$configJs=json_encode([
  'table'=>'reminders','title'=>'Reminder',
  'columns'=>[['key'=>'title'],['key'=>'type'],['key'=>'due_date'],['key'=>'priority','fmt'=>'badge'],['key'=>'assigned_user'],['key'=>'status','fmt'=>'badge']],
  'fields'=>[
    ['name'=>'title','label'=>'Reminder Title','required'=>true,'col'=>'form-full'],
    ['name'=>'type','label'=>'Type','type'=>'select','options'=>['Fertilizer','Clearing','Machinery Service','Employee','Payment','Harvest','General']],
    ['name'=>'priority','label'=>'Priority','type'=>'select','options'=>['Low','Medium','High','Critical']],
    ['name'=>'estate_id','label'=>'Estate','type'=>'select','lookup'=>'estates','allowEmpty'=>true],
    ['name'=>'due_date','label'=>'Due Date','type'=>'date','required'=>true],
    ['name'=>'assigned_user','label'=>'Assigned User'],
    ['name'=>'status','label'=>'Status','type'=>'select','options'=>['Open','Completed']],
    ['name'=>'description','label'=>'Description','type'=>'textarea','col'=>'form-full'],
  ]
]);
require __DIR__.'/includes/crud_page.php';
