<?php
$title='Employee Management'; $page='employees.php'; $entityTitle='Worker';
require_once __DIR__.'/includes/auth.php';
$columns=[['label'=>'Emp ID'],['label'=>'Name'],['label'=>'Phone'],['label'=>'Role'],['label'=>'KG Rate','right'=>true],['label'=>'Daily Rate','right'=>true],['label'=>'Status']];
$configJs=json_encode([
  'table'=>'employees','title'=>'Worker',
  'columns'=>[['key'=>'emp_code'],['key'=>'full_name'],['key'=>'phone'],['key'=>'job_role'],
    ['key'=>'kg_rate','fmt'=>'num','right'=>true],['key'=>'daily_rate','fmt'=>'num','right'=>true],['key'=>'status','fmt'=>'badge']],
  'fields'=>[
    ['name'=>'emp_code','label'=>'Employee ID','required'=>true],
    ['name'=>'full_name','label'=>'Full Name','required'=>true],
    ['name'=>'nic','label'=>'NIC'],
    ['name'=>'phone','label'=>'Phone'],
    ['name'=>'gender','label'=>'Gender','type'=>'select','options'=>['Male','Female','Other']],
    ['name'=>'dob','label'=>'Date of Birth','type'=>'date'],
    ['name'=>'joining_date','label'=>'Joining Date','type'=>'date'],
    ['name'=>'employment_type','label'=>'Employment Type'],
    ['name'=>'job_role','label'=>'Job Role'],
    ['name'=>'estate_id','label'=>'Estate','type'=>'select','lookup'=>'estates','allowEmpty'=>true],
    ['name'=>'daily_rate','label'=>'Daily Rate','type'=>'number','step'=>'0.01'],
    ['name'=>'kg_rate','label'=>'KG Rate','type'=>'number','step'=>'0.01'],
    ['name'=>'overtime_rate','label'=>'Overtime Rate','type'=>'number','step'=>'0.01'],
    ['name'=>'bank_details','label'=>'Bank Details'],
    ['name'=>'emergency_contact','label'=>'Emergency Contact'],
    ['name'=>'status','label'=>'Status','type'=>'select','options'=>['Active','Inactive','On Leave','Terminated']],
    ['name'=>'address','label'=>'Address','type'=>'textarea','col'=>'form-full'],
    ['name'=>'notes','label'=>'Notes','type'=>'textarea','col'=>'form-full'],
  ]
]);
require __DIR__.'/includes/crud_page.php';
