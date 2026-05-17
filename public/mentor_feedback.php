<?php
require_once '../includes/auth.php';
require_once '../includes/helpers.php';
guard('mentor');
$uid=current_user_id();
$feedback=get_mentor_feedback($uid);
$pos=array_filter($feedback,fn($f)=>$f['rating']==='up');
$neg=array_filter($feedback,fn($f)=>$f['rating']==='down');
$total=count($feedback);
$approval = $total ? round(count($pos)/$total*100) : null;
$page_title='Student Ratings';
$breadcrumb_extra='Ratings';
include '_layout.php';
?>

<div class="welcome-bar">Student Ratings &amp; Feedback</div>

<div class="stats-row" style="grid-template-columns:repeat(4,1fr);">
  <div class="stat-box"><div class="stat-num"><?=$total?></div><div class="stat-lbl">Total Ratings</div></div>
  <div class="stat-box"><div class="stat-num" style="color:var(--green);"><?=count($pos)?></div><div class="stat-lbl">Positive</div></div>
  <div class="stat-box"><div class="stat-num" style="color:#c0392b;"><?=count($neg)?></div><div class="stat-lbl">Needs Improvement</div></div>
  <div class="stat-box"><div class="stat-num" style="color:var(--gold);"><?=$approval!==null?$approval.'%':'—'?></div><div class="stat-lbl">Approval Rate</div></div>
</div>

<?php if(empty($feedback)): ?>
  <div class="card">
    <div class="card-body" style="text-align:center;padding:64px 24px;color:var(--ink3);">
      <div style="font-family:'Libre Baskerville',serif;font-size:18px;color:var(--ink2);margin-bottom:8px;">No ratings yet</div>
      <div style="font-size:13px;max-width:420px;margin:0 auto;line-height:1.7;">Students will be able to submit feedback once you have completed sessions with them.</div>
    </div>
  </div>
<?php else: ?>
<div class="card">
  <div class="card-header"><div class="card-header-title">All Feedback</div></div>
  <div class="card-body" style="padding:0;">
    <?php foreach($feedback as $f): ?>
      <div style="padding:16px 18px;border-bottom:1px solid var(--bg3);">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px;flex-wrap:wrap;">
          <div style="flex:1;min-width:140px;">
            <div style="font-weight:700;font-size:13px;"><?=h($f['student_name'])?></div>
            <div style="font-size:11px;color:var(--ink3);"><?=h($f['roll_number']??'')?> &nbsp;&middot;&nbsp; <?=date('d M Y',strtotime($f['submitted_at']))?></div>
          </div>
          <span class="status-badge <?=$f['rating']==='up'?'success':'danger'?>"><?=$f['rating']==='up'?'Positive':'Needs Improvement'?></span>
        </div>
        <?php if($f['comment']): ?>
          <div style="background:var(--bg3);border-left:3px solid var(--gold);padding:10px 14px;font-size:12.5px;color:var(--ink2);font-style:italic;line-height:1.65;border-radius:0 var(--radius) var(--radius) 0;margin-top:8px;">&ldquo;<?=h($f['comment'])?>&rdquo;</div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

</div></body></html>
