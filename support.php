<?php
$title='Support'; $page='support.php'; $pageScript='assets/js/support.js';
require __DIR__.'/includes/header.php';
?>
<div class="page-head" id="listHead">
  <h2>Support</h2>
  <button class="btn" onclick="openNew()">➕ New Ticket</button>
</div>

<div class="card" id="listView"><div class="tbl-wrap"><table class="tbl">
  <thead><tr><th>Subject</th><th>Status</th><th>Last Update</th><th class="right">Actions</th></tr></thead>
  <tbody id="ticketBody"><tr><td colspan="4" class="empty">Loading…</td></tr></tbody>
</table></div></div>

<div id="threadView" style="display:none">
  <div class="page-head"><h2 id="threadSubject"></h2><button class="btn gray" onclick="backToList()">← Back to Tickets</button></div>
  <div class="card mb"><div class="card-pad" id="threadMsgs" style="display:flex;flex-direction:column;gap:12px"></div></div>
  <div class="card"><div class="card-pad">
    <div class="field"><label>Reply</label><textarea id="replyMsg" rows="3"></textarea></div>
    <button class="btn" style="margin-top:10px" onclick="sendReply()">Send Reply</button>
  </div></div>
</div>

<div class="modal-bg" id="newTicketModal">
  <div class="modal">
    <div class="modal-h"><h3>New Support Ticket</h3><span class="x" onclick="closeModal('newTicketModal')">&times;</span></div>
    <div class="modal-b">
      <div class="field"><label>Subject</label><input id="newSubject" type="text"></div>
      <div class="field" style="margin-top:10px"><label>Message</label><textarea id="newMessage" rows="4"></textarea></div>
    </div>
    <div class="modal-f"><button class="btn gray" onclick="closeModal('newTicketModal')">Cancel</button>
      <button class="btn" onclick="submitNew()">Submit</button></div>
  </div>
</div>

<?php require __DIR__.'/includes/footer.php'; ?>
