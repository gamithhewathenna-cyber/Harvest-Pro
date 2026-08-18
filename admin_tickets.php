<?php
$title='Support Tickets'; $page='admin_tickets.php'; $pageScript='assets/js/admin_tickets.js';
require __DIR__.'/includes/admin_header.php';
?>
<div class="page-head" id="listHead">
  <h2>Support Tickets</h2>
  <div class="filters" style="margin:0">
    <span class="chip active" data-status="">All</span>
    <span class="chip" data-status="Open">Open</span>
    <span class="chip" data-status="Answered">Answered</span>
    <span class="chip" data-status="Closed">Closed</span>
  </div>
</div>

<div class="card" id="listView"><div class="tbl-wrap"><table class="tbl">
  <thead><tr><th>Subject</th><th>Customer</th><th>Status</th><th>Last Update</th><th class="right">Actions</th></tr></thead>
  <tbody id="ticketBody"><tr><td colspan="5" class="empty">Loading…</td></tr></tbody>
</table></div></div>

<div id="threadView" style="display:none">
  <div class="page-head">
    <h2 id="threadSubject"></h2>
    <div style="display:flex;gap:8px">
      <button class="btn gray" onclick="setStatus('Answered')">Mark Answered</button>
      <button class="btn red" onclick="setStatus('Closed')">Close Ticket</button>
      <button class="btn gray" onclick="backToList()">← Back</button>
    </div>
  </div>
  <p style="font-size:13px;color:var(--muted)" id="threadCustomer"></p>
  <div class="card mb"><div class="card-pad" id="threadMsgs" style="display:flex;flex-direction:column;gap:12px"></div></div>
  <div class="card"><div class="card-pad">
    <div class="field"><label>Reply</label><textarea id="replyMsg" rows="3"></textarea></div>
    <button class="btn" style="margin-top:10px" onclick="sendReply()">Send Reply</button>
  </div></div>
</div>

<?php require __DIR__.'/includes/footer.php'; ?>
