<?php
$title='Service Management'; $page='service.php'; $entityTitle='Service';
require_once __DIR__.'/includes/auth.php';
$columns=[['label'=>'Service Name'],['label'=>'Description'],['label'=>'Status'],['label'=>'Unit Type'],['label'=>'Rate per Unit (LKR)','right'=>true]];
$configJs=json_encode([
  'table'=>'service_cycles','title'=>'Service','saveLabel'=>'Add Service',
  'columns'=>[['key'=>'service_name'],['key'=>'description'],['key'=>'status','fmt'=>'badge'],
    ['key'=>'unit_type'],['key'=>'rate_per_unit','fmt'=>'money','right'=>true]],
  'fields'=>[
    ['name'=>'service_name','label'=>'Service Name','required'=>true],
    ['name'=>'description','label'=>'Description','type'=>'textarea','col'=>'form-full'],
    ['name'=>'status','label'=>'Status','type'=>'select','options'=>['Active','Inactive']],
    ['name'=>'unit_type','label'=>'Unit Type'],
    ['name'=>'rate_per_unit','label'=>'Rate per Unit (LKR)','type'=>'number','step'=>'0.01'],
  ]
]);
require __DIR__.'/includes/crud_page.php';
