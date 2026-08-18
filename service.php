<?php
$title='Service Management'; $page='service.php'; $entityTitle='Service';
require_once __DIR__.'/includes/auth.php';
$columns=[['label'=>'Service'],['label'=>'Asset'],['label'=>'Last Service'],['label'=>'Next Service'],['label'=>'Cost','right'=>true],['label'=>'Status']];
$configJs=json_encode([
  'table'=>'service_cycles','title'=>'Service',
  'columns'=>[['key'=>'service_name'],['key'=>'asset'],['key'=>'last_service_date'],['key'=>'next_service_date'],
    ['key'=>'cost','fmt'=>'money','right'=>true],['key'=>'status','fmt'=>'badge']],
  'fields'=>[
    ['name'=>'service_name','label'=>'Service Name','required'=>true],
    ['name'=>'asset','label'=>'Asset'],
    ['name'=>'estate_id','label'=>'Estate','type'=>'select','lookup'=>'estates','allowEmpty'=>true],
    ['name'=>'last_service_date','label'=>'Last Service Date','type'=>'date'],
    ['name'=>'next_service_date','label'=>'Next Service Date','type'=>'date'],
    ['name'=>'frequency','label'=>'Frequency'],
    ['name'=>'cost','label'=>'Cost','type'=>'number','step'=>'0.01'],
    ['name'=>'supplier','label'=>'Supplier / Technician'],
    ['name'=>'status','label'=>'Status','type'=>'select','options'=>['Active','Inactive']],
    ['name'=>'notes','label'=>'Notes','type'=>'textarea','col'=>'form-full'],
  ]
]);
require __DIR__.'/includes/crud_page.php';
