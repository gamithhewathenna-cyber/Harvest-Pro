<?php
$title='Expenses'; $page='expenses.php'; $entityTitle='Expense';
require_once __DIR__.'/includes/auth.php';
$columns=[['label'=>'Date'],['label'=>'Category'],['label'=>'Supplier'],['label'=>'Description'],['label'=>'Amount','right'=>true],['label'=>'Status']];
$configJs=json_encode([
  'table'=>'expenses','title'=>'Expense',
  'columns'=>[['key'=>'expense_date'],['key'=>'category'],['key'=>'supplier'],['key'=>'description'],
    ['key'=>'amount','fmt'=>'money','right'=>true],['key'=>'status','fmt'=>'badge']],
  'fields'=>[
    ['name'=>'expense_date','label'=>'Date','type'=>'date','required'=>true,'default'=>date('Y-m-d')],
    ['name'=>'estate_id','label'=>'Estate','type'=>'select','lookup'=>'estates','required'=>true],
    ['name'=>'category','label'=>'Category','type'=>'select','options'=>['Labour','Fertilizer','Clearing','Transport','Fuel','Machinery','Maintenance','Chemicals','Equipment','Other']],
    ['name'=>'supplier','label'=>'Supplier'],
    ['name'=>'quantity','label'=>'Quantity','type'=>'number','step'=>'0.01'],
    ['name'=>'amount','label'=>'Amount','type'=>'number','step'=>'0.01','required'=>true],
    ['name'=>'payment_method','label'=>'Payment Method'],
    ['name'=>'reference','label'=>'Reference No.'],
    ['name'=>'status','label'=>'Status','type'=>'select','options'=>['Draft','Pending','Approved','Rejected','Paid']],
    ['name'=>'description','label'=>'Description','type'=>'textarea','col'=>'form-full'],
    ['name'=>'notes','label'=>'Notes','type'=>'textarea','col'=>'form-full'],
  ]
]);
require __DIR__.'/includes/crud_page.php';
