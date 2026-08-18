<?php
// Expects: $title, $page, $entityTitle, $columns (array of ['label']), $configJs (JS config string)
require __DIR__.'/header.php';
?>
<div class="page-head">
  <h2><?=e($title)?></h2>
  <?php if(can_edit()): ?><button class="btn" onclick="openAdd()">➕ Add <?=e($entityTitle)?></button><?php endif; ?>
</div>

<?php if(!empty($filterBar)) echo $filterBar; ?>

<div class="card"><div class="tbl-wrap"><table class="tbl">
  <thead><tr>
    <?php foreach($columns as $c) echo '<th class="'.($c['right']??false?'right':'').'">'.e($c['label']).'</th>'; ?>
    <th class="right">Actions</th>
  </tr></thead>
  <tbody id="crudBody"><tr><td colspan="<?=count($columns)+1?>" class="empty">Loading…</td></tr></tbody>
</table></div></div>

<div class="modal-bg" id="crudModal">
  <div class="modal">
    <div class="modal-h"><h3 id="modalTitle">Add</h3><span class="x" onclick="closeModal('crudModal')">&times;</span></div>
    <div class="modal-b"><input type="hidden" id="crudId"><div class="form-row" id="crudForm"></div></div>
    <div class="modal-f"><button class="btn gray" onclick="closeModal('crudModal')">Cancel</button>
      <button class="btn" id="saveBtn" onclick="saveForm()">Save</button></div>
  </div>
</div>

<script>window.CRUD = <?=$configJs?>;</script>
<script src="assets/js/crud.js"></script>
<script><?=$initJs ?? 'loadRows();'?></script>
<?php require __DIR__.'/footer.php'; ?>
