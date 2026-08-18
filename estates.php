<?php
$title='Estate Management'; $page='estates.php'; $entityTitle='Estate';
require_once __DIR__.'/includes/auth.php';
$columns=[['label'=>'Name'],['label'=>'Code'],['label'=>'Location'],['label'=>'Tea Acres','right'=>true],['label'=>'Manager'],['label'=>'Status']];
$configJs=json_encode([
  'table'=>'estates','title'=>'Estate',
  'columns'=>[['key'=>'name'],['key'=>'code'],['key'=>'location'],['key'=>'tea_acres','fmt'=>'num','right'=>true],['key'=>'manager'],['key'=>'status','fmt'=>'badge']],
  'fields'=>[
    ['name'=>'coupon_code','label'=>'Coupon Code','required'=>true,'addOnly'=>true,'placeholder'=>'e.g. TEA-XXXX-XXXX'],
    ['name'=>'name','label'=>'Estate Name','required'=>true],
    ['name'=>'code','label'=>'Estate Code','required'=>true],
    ['name'=>'location','label'=>'Location'],
    ['name'=>'total_acres','label'=>'Total Acres','type'=>'number','step'=>'0.01'],
    ['name'=>'tea_acres','label'=>'Tea Acres','type'=>'number','step'=>'0.01'],
    ['name'=>'manager','label'=>'Manager'],
    ['name'=>'status','label'=>'Status','type'=>'select','options'=>['Active','Inactive']],
    ['name'=>'description','label'=>'Description','type'=>'textarea','col'=>'form-full'],
  ]
]);
require __DIR__.'/includes/crud_page.php';
