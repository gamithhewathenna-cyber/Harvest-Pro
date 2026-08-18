<?php
$title='User Management'; $page='admin_users.php'; $pageScript='assets/js/admin_users.js';
require __DIR__.'/includes/admin_header.php';
?>
<div class="page-head"><h2>User Management</h2></div>

<div class="card"><div class="tbl-wrap"><table class="tbl">
  <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Belongs To</th><th>Status</th><th>Last Login</th><th class="right">Actions</th></tr></thead>
  <tbody id="userBody"><tr><td colspan="7" class="empty">Loading…</td></tr></tbody>
</table></div></div>

<div class="modal-bg" id="pwModal">
  <div class="modal">
    <div class="modal-h"><h3>Reset Password</h3><span class="x" onclick="closeModal('pwModal')">&times;</span></div>
    <div class="modal-b"><div class="form-row">
      <div class="field" style="grid-column:1/-1"><label>New Password</label><input id="pwVal" type="text" placeholder="min 6 chars"></div>
    </div></div>
    <div class="modal-f"><button class="btn gray" onclick="closeModal('pwModal')">Cancel</button>
      <button class="btn" onclick="submitPw()">Update Password</button></div>
  </div>
</div>

<div class="modal-bg" id="delModal">
  <div class="modal">
    <div class="modal-h"><h3>Delete Customer Account</h3><span class="x" onclick="closeModal('delModal')">&times;</span></div>
    <div class="modal-b">
      <p style="font-size:13px">This permanently deletes <b id="delEmailLabel"></b> and <b>all</b> of their estates, employees, assignments, expenses, payroll, and other data. This cannot be undone.</p>
      <div class="form-row"><div class="field" style="grid-column:1/-1"><label>Type the email to confirm</label><input id="delConfirm" type="text"></div></div>
    </div>
    <div class="modal-f"><button class="btn gray" onclick="closeModal('delModal')">Cancel</button>
      <button class="btn red" onclick="submitDelete()">Delete Permanently</button></div>
  </div>
</div>

<?php require __DIR__.'/includes/footer.php'; ?>
