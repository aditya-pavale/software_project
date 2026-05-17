<?php
require_once '../includes/auth.php';
require_once '../includes/helpers.php';
guard('mentor');
$uid=current_user_id();
$sessions=get_mentor_sessions($uid);
$upcoming=array_filter($sessions,fn($s)=>$s['status']==='upcoming');
$done_sess=array_filter($sessions,fn($s)=>$s['status']==='done');
$cancelled=array_filter($sessions,fn($s)=>$s['status']==='cancelled');

$page_title='My Sessions';
$breadcrumb_extra='My Sessions';
include '_layout.php';
?>

<div class="welcome-bar">My Sessions</div>

<div class="stats-row" style="grid-template-columns:repeat(3,1fr);">
  <div class="stat-box"><div class="stat-num"><?=count($upcoming)?></div><div class="stat-lbl">Upcoming</div></div>
  <div class="stat-box"><div class="stat-num" style="color:var(--green);"><?=count($done_sess)?></div><div class="stat-lbl">Completed</div></div>
  <div class="stat-box"><div class="stat-num" style="color:var(--ink3);"><?=count($cancelled)?></div><div class="stat-lbl">Cancelled</div></div>
</div>

<div class="tab-bar">
  <button class="tab-btn active" onclick="showTab('upcoming',event)">Upcoming (<?=count($upcoming)?>)</button>
  <button class="tab-btn" onclick="showTab('done',event)">Completed (<?=count($done_sess)?>)</button>
  <button class="tab-btn" onclick="showTab('cancelled',event)">Cancelled (<?=count($cancelled)?>)</button>
</div>

<div id="tab-upcoming" class="section active">
  <?php if(empty($upcoming)): ?>
    <div class="card"><div class="card-body" style="color:var(--ink3);font-size:13px;text-align:center;padding:48px;line-height:1.7;">No upcoming sessions.</div></div>
  <?php else: ?>
    <div class="alert-banner-v2 info">
      <div>
        <div class="ab-label">Important</div>
        <div class="ab-body">Mark sessions as completed only after they actually take place. Students can give feedback only after you mark a session as done.</div>
      </div>
    </div>
    <div class="card">
      <div class="card-body" style="padding:0;">
        <?php foreach($upcoming as $s): $d=strtotime($s['session_date']); ?>
          <div style="display:flex;align-items:center;gap:14px;padding:14px 18px;border-bottom:1px solid var(--bg3);flex-wrap:wrap;">
            <div class="session-date-box"><div class="day"><?=date('d',$d)?></div><div class="month"><?=date('M',$d)?></div></div>
            <div style="flex:1;min-width:140px;">
              <div style="font-weight:700;font-size:14px;"><?=h($s['student_name'])?><?=$s['roll_number']?' <span style="font-size:11px;color:var(--ink3);font-weight:400;margin-left:4px;">'.h($s['roll_number']).'</span>':''?></div>
              <div style="font-size:12px;color:var(--ink3);"><?=h($s['title']??'Session')?> &nbsp;&middot;&nbsp; <?=date('l',$d)?> at <?=substr($s['session_time'],0,5)?></div>
            </div>
            <form action="../actions/session_action.php" method="POST" style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;">
              <input type="hidden" name="action" value="mark_done"/>
              <input type="hidden" name="session_id" value="<?=$s['id']?>"/>
              <input type="text" name="notes" placeholder="Notes (optional)" style="border:1px solid var(--border);border-radius:var(--radius);padding:6px 10px;font-size:12px;width:160px;font-family:inherit;outline:none;"/>
              <button type="submit" class="btn btn-green btn-sm" onclick="return confirm('Mark this session as completed?')">Mark Completed</button>
            </form>
            <form action="../actions/session_action.php" method="POST" onsubmit="return confirm('Cancel this session?')">
              <input type="hidden" name="action" value="cancel"/>
              <input type="hidden" name="session_id" value="<?=$s['id']?>"/>
              <button type="submit" class="btn btn-sm" style="background:#fdf2f2;color:#c0392b;border:1px solid #f5c6c6;">Cancel</button>
            </form>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>
</div>

<div id="tab-done" class="section">
  <?php if(empty($done_sess)): ?>
    <div class="card"><div class="card-body" style="color:var(--ink3);font-size:13px;text-align:center;padding:48px;">No completed sessions yet.</div></div>
  <?php else: ?>
    <div class="card"><div class="card-body" style="padding:0;">
      <?php foreach($done_sess as $s): $d=strtotime($s['session_date']); ?>
        <div style="display:flex;align-items:center;gap:14px;padding:14px 18px;border-bottom:1px solid var(--bg3);flex-wrap:wrap;">
          <div class="session-date-box" style="background:var(--green);"><div class="day"><?=date('d',$d)?></div><div class="month"><?=date('M',$d)?></div></div>
          <div style="flex:1;min-width:140px;">
            <div style="font-weight:700;font-size:14px;"><?=h($s['student_name'])?></div>
            <div style="font-size:12px;color:var(--ink3);"><?=h($s['title']??'')?> &nbsp;&middot;&nbsp; <?=date('l',$d)?> at <?=substr($s['session_time'],0,5)?></div>
            <?php if($s['notes']): ?><div style="font-size:12px;color:var(--ink2);margin-top:5px;font-style:italic;border-left:2px solid var(--gold);padding-left:8px;"><?=h($s['notes'])?></div><?php endif; ?>
          </div>
          <span class="status-badge success">Completed</span>
        </div>
      <?php endforeach; ?>
    </div></div>
  <?php endif; ?>
</div>

<div id="tab-cancelled" class="section">
  <?php if(empty($cancelled)): ?>
    <div class="card"><div class="card-body" style="color:var(--ink3);font-size:13px;text-align:center;padding:48px;">No cancelled sessions.</div></div>
  <?php else: ?>
    <div class="card"><div class="card-body" style="padding:0;">
      <?php foreach($cancelled as $s): $d=strtotime($s['session_date']); ?>
        <div style="display:flex;align-items:center;gap:14px;padding:14px 18px;border-bottom:1px solid var(--bg3);opacity:0.7;flex-wrap:wrap;">
          <div class="session-date-box" style="background:#999;"><div class="day"><?=date('d',$d)?></div><div class="month"><?=date('M',$d)?></div></div>
          <div style="flex:1;"><div style="font-weight:700;font-size:14px;"><?=h($s['student_name'])?></div><div style="font-size:12px;color:var(--ink3);"><?=h($s['title']??'')?></div></div>
          <span class="status-badge neutral">Cancelled</span>
        </div>
      <?php endforeach; ?>
    </div></div>
  <?php endif; ?>
</div>

<script>
function showTab(name,e){
  document.querySelectorAll('.section').forEach(s=>s.classList.remove('active'));
  document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
  document.getElementById('tab-'+name).classList.add('active');
  e.target.classList.add('active');
}
</script>

</div></body></html>
