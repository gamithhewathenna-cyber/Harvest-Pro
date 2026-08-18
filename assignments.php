<?php $title='Daily Assignment'; $page='assignments.php'; $pageScript='assets/js/assignments.js';
require __DIR__.'/includes/header.php'; ?>

<div class="page-head">
  <h2>Daily Assignments</h2>
  <div style="display:flex;gap:8px">
    <button class="btn gray" onclick="switchTab('list')" id="tabList">History</button>
    <button class="btn" onclick="switchTab('bulk')" id="tabBulk">➕ Bulk Entry</button>
  </div>
</div>

<!-- BULK ENTRY -->
<div id="bulkView" class="card mb">
  <div class="card-h"><h3>Bulk Daily Entry</h3><small>Fast field data entry — optimized for mobile</small></div>
  <div class="card-pad">
    <div class="form-row3 mb">
      <div class="field"><label>Date</label><input type="date" id="bDate"></div>
      <div class="field"><label>Estate</label><select id="bEstate" onchange="loadBulkContext()"></select></div>
      <div class="field"><label>Section</label><select id="bSection"></select></div>
    </div>
    <div class="form-row mb">
      <div class="field"><label>Assignment Type</label><select id="bType" onchange="onTypeChange()"></select></div>
      <div class="field"><label>Supervisor</label><input id="bSup" placeholder="Supervisor name"></div>
    </div>
    <div class="tbl-wrap">
      <table class="tbl" id="bulkTbl">
        <thead><tr><th>Worker</th><th id="thKg">KG</th><th>Rate</th><th>Allowance</th><th>Deduction</th><th class="right">Total</th></tr></thead>
        <tbody id="bulkBody"><tr><td colspan="6" class="empty">Select an estate to load workers</td></tr></tbody>
      </table>
    </div>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:16px;flex-wrap:wrap;gap:12px">
      <div class="muted" id="bulkSummary">Workers: 0 · Total KG: 0 · Total: LKR 0.00</div>
      <button class="btn" onclick="saveBulk()">💾 Save All Assignments</button>
    </div>
  </div>
</div>

<!-- HISTORY LIST -->
<div id="listView" style="display:none">
  <div class="card mb"><div class="card-pad">
    <div class="form-row3">
      <div class="field"><label>From</label><input type="date" id="fFrom"></div>
      <div class="field"><label>To</label><input type="date" id="fTo"></div>
      <div class="field"><label>Estate</label><select id="fEstate"><option value="">All</option></select></div>
    </div>
    <button class="btn sm" onclick="loadList()">Filter</button>
  </div></div>
  <div class="card"><div class="tbl-wrap"><table class="tbl">
    <thead><tr><th>Date</th><th>Worker</th><th>Estate / Section</th><th>Type</th><th class="right">KG</th><th class="right">Cost</th><th></th></tr></thead>
    <tbody id="listBody"><tr><td colspan="7" class="empty">Loading…</td></tr></tbody>
  </table></div></div>
</div>

<?php require __DIR__.'/includes/footer.php'; ?>
