<?php $title='Payroll'; $page='payroll.php'; $pageScript='assets/js/payroll.js';
require __DIR__.'/includes/header.php'; ?>

<div class="page-head"><h2>Payroll</h2>
  <div style="display:flex;gap:8px">
    <button class="btn gray" id="tabGen" onclick="pTab('gen')">Generate</button>
    <button class="btn" id="tabHist" onclick="pTab('hist')">Payroll History</button>
  </div>
</div>

<div id="genView">
  <div class="card mb"><div class="card-pad">
    <div class="form-row3">
      <div class="field"><label>Period From</label><input type="date" id="pFrom"></div>
      <div class="field"><label>Period To</label><input type="date" id="pTo"></div>
      <div class="field"><label>Estate</label><select id="pEstate"><option value="">All</option></select></div>
    </div>
    <button class="btn" onclick="preview()">Calculate Payroll</button>
  </div></div>
  <div class="card"><div class="card-h"><h3>Payroll Preview</h3>
    <button class="btn" onclick="generate()" id="genBtn" style="display:none">💾 Save Payroll Run</button></div>
    <div class="tbl-wrap"><table class="tbl">
      <thead><tr><th>Worker</th><th class="right">Days</th><th class="right">KG</th><th class="right">Plucking</th>
        <th class="right">Assignment</th><th class="right">Allow.</th><th class="right">Deduct.</th>
        <th class="right">Gross</th><th class="right">Net</th></tr></thead>
      <tbody id="pvBody"><tr><td colspan="9" class="empty">Select a period and calculate</td></tr></tbody>
    </table></div>
  </div>
</div>

<div id="histView" style="display:none">
  <div class="card"><div class="tbl-wrap"><table class="tbl">
    <thead><tr><th>Worker</th><th>Period</th><th class="right">Gross</th><th class="right">Deduct.</th>
      <th class="right">Net</th><th>Status</th><th class="right">Actions</th></tr></thead>
    <tbody id="histBody"><tr><td colspan="7" class="empty">Loading…</td></tr></tbody>
  </table></div></div>
</div>

<script>window.CAN_APPROVE = <?=can_approve()?'true':'false'?>;</script>
<?php require __DIR__.'/includes/footer.php'; ?>
