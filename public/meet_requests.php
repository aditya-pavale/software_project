<?php
require_once '../includes/auth.php';
require_once '../includes/helpers.php';
guard('mentor');
$uid=current_user_id();
$requests=get_meet_requests_for_mentor($uid);
$pending=array_filter($requests,fn($r)=>$r['status']==='pending');
$responded=array_filter($requests,fn($r)=>$r['status']!=='pending');

$page_title='Meet Requests';
$breadcrumb_extra='Meet Requests';
include '_layout.php';
?>

<div class="welcome-bar">Meet Requests <span style="font-size:13px;font-weight:400;color:var(--ink3);font-family:'Source Sans 3',sans-serif;">(<?=count($pending)?> pending)</span></div>

<?php if(empty($requests)): ?>
  <div class="card">
    <div class="card-body" style="text-align:center;padding:56px 24px;">
      <div style="font-family:'Libre Baskerville',serif;font-size:18px;color:var(--ink2);margin-bottom:8px;">No meet requests yet</div>
      <div style="font-size:13px;color:var(--ink3);line-height:1.7;">Students will send meet requests here before electing you as their mentor.</div>
    </div>
  </div>
<?php else: ?>

<div class="tab-bar">
  <button class="tab-btn active" onclick="showTab('pending',event)">Pending (<?=count($pending)?>)</button>
  <button class="tab-btn" onclick="showTab('responded',event)">Responded (<?=count($responded)?>)</button>
</div>

<!-- PENDING -->
<div id="tab-pending" class="section active">
  <?php if(empty($pending)): ?>
    <div class="card"><div class="card-body" style="text-align:center;padding:32px;color:var(--ink3);">No pending requests.</div></div>
  <?php else: foreach($pending as $r):
    $init=strtoupper(substr($r['student_name'],0,2));
  ?>
    <div class="card" style="margin-bottom:14px;">
      <div class="card-header">
        <div class="card-header-title">
          <div style="width:32px;height:32px;border-radius:50%;background:var(--crimson);color:white;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;margin-right:4px;"><?=$init?></div>
          <?=h($r['student_name'])?>
        </div>
        <span class="status-badge pending">Pending</span>
      </div>
      <div class="card-body">
        <div style="font-size:11px;color:var(--ink3);margin-bottom:10px;font-weight:700;"><?=h($r['roll_number']??'')?> &nbsp;&middot;&nbsp; <?=h($r['department']??'')?> &nbsp;&middot;&nbsp; Requested <?=date('d M Y',strtotime($r['created_at']))?></div>

        <?php if($r['message']): ?>
          <div style="background:var(--bg3);border-left:3px solid var(--crimson);padding:12px 14px;border-radius:0 var(--radius) var(--radius) 0;font-size:13px;color:var(--ink2);line-height:1.65;margin-bottom:16px;">
            <div style="font-size:10px;font-weight:800;color:var(--crimson);text-transform:uppercase;letter-spacing:.6px;margin-bottom:5px;">Student's Message</div>
            <?=nl2br(h($r['message']))?>
          </div>
        <?php else: ?>
          <div style="color:var(--ink3);font-size:13px;font-style:italic;margin-bottom:16px;">No message provided.</div>
        <?php endif; ?>

        <form action="../actions/meet_action.php" method="POST">
          <input type="hidden" name="action" value="respond"/>
          <input type="hidden" name="request_id" value="<?=$r['id']?>"/>
          <div class="form-group">
            <label class="form-label">Your Reply to Student <span style="font-weight:400;color:var(--ink3);">(Optional — e.g. a meeting time)</span></label>
            <textarea name="reply" class="form-control" rows="3" placeholder="e.g. I'm available Tuesday 10am–12pm in my office. Please visit."></textarea>
          </div>
          <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <button type="submit" name="status" value="accepted" class="btn btn-green">Accept Meet Request</button>
            <button type="submit" name="status" value="declined" class="btn btn-danger btn-sm" onclick="return confirm('Decline this meet request?')">Decline</button>
          </div>
        </form>
      </div>
    </div>
  <?php endforeach; endif; ?>
</div>

<!-- RESPONDED -->
<div id="tab-responded" class="section">
  <?php if(empty($responded)): ?>
    <div class="card"><div class="card-body" style="text-align:center;padding:32px;color:var(--ink3);">No responded requests.</div></div>
  <?php else: foreach($responded as $r): ?>
    <div class="card" style="margin-bottom:14px;">
      <div class="card-header">
        <div class="card-header-title"><?=h($r['student_name'])?></div>
        <span class="status-badge <?=$r['status']==='accepted'?'success':'danger'?>"><?=ucfirst($r['status'])?></span>
      </div>
      <div class="card-body">
        <div style="font-size:11px;color:var(--ink3);margin-bottom:8px;"><?=h($r['roll_number']??'')?> &nbsp;&middot;&nbsp; <?=date('d M Y',strtotime($r['created_at']))?></div>
        <?php if($r['message']): ?>
          <div style="background:var(--bg3);border-left:3px solid var(--border);padding:10px 12px;border-radius:0 var(--radius) var(--radius) 0;font-size:13px;color:var(--ink2);margin-bottom:10px;"><?=nl2br(h($r['message']))?></div>
        <?php endif; ?>
        <?php if($r['mentor_reply']): ?>
          <div style="background:#f0faf5;border-left:3px solid var(--green2);padding:10px 12px;border-radius:0 var(--radius) var(--radius) 0;font-size:13px;color:var(--ink2);">
            <div style="font-size:10px;font-weight:800;color:var(--green);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">Your Reply</div>
            <?=nl2br(h($r['mentor_reply']))?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; endif; ?>
</div>

<script>
function showTab(name,e){
  document.querySelectorAll('.section').forEach(s=>s.classList.remove('active'));
  document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
  document.getElementById('tab-'+name).classList.add('active');
  e.target.classList.add('active');
}
</script>

<?php endif; ?>
</div></body></html>
