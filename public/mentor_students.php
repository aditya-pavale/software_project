<?php
require_once '../includes/auth.php';
require_once '../includes/helpers.php';
guard('mentor');
$uid=current_user_id();
$students=get_mentor_students($uid);

$page_title='My Students';
$breadcrumb_extra='My Students';
include '_layout.php';

$qs=[
    'academic_goals'=>'Academic Goals',
    'challenges'=>'Current Challenges',
    'expectations'=>'Expected Support',
    'vision'=>'End-of-Semester Vision',
    'skills_develop'=>'Skills to Develop',
];
?>

<div class="welcome-bar">My Students <span style="font-size:14px;font-weight:400;color:var(--ink3);font-family:'Source Sans 3',sans-serif;">(<?=count($students)?>)</span></div>

<?php if(empty($students)): ?>
  <div class="card">
    <div class="card-body" style="text-align:center;padding:64px 24px;color:var(--ink3);">
      <div style="font-family:'Libre Baskerville',serif;font-size:18px;color:var(--ink2);margin-bottom:8px;">No students assigned yet</div>
      <div style="font-size:13px;max-width:420px;margin:0 auto;line-height:1.7;">Students will appear here once they submit their goals and elect you as their mentor.</div>
    </div>
  </div>
<?php else: ?>

<div class="mentor-student-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:14px;">
<?php foreach($students as $s):
  $init=strtoupper(substr(implode('',array_map(fn($w)=>$w[0],explode(' ',$s['name']))),0,2));
  $has_goals=!empty($s['academic_goals']);
?>
  <div class="card" style="margin-bottom:0;">
    <div style="background:linear-gradient(135deg,var(--crimson),var(--crimson3));padding:14px 16px;color:white;display:flex;align-items:center;gap:12px;">
      <div style="width:42px;height:42px;border-radius:50%;background:rgba(255,255,255,.2);border:2px solid rgba(255,255,255,.4);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;flex-shrink:0;"><?=$init?></div>
      <div style="flex:1;min-width:0;">
        <div style="font-weight:700;font-size:14px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?=h($s['name'])?></div>
        <div style="font-size:11px;opacity:0.75;"><?=h($s['roll_number']??'')?></div>
      </div>
    </div>
    <div class="card-body">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:14px;">
        <div style="background:var(--bg3);border-radius:6px;padding:10px;text-align:center;">
          <div style="font-size:18px;font-weight:700;color:var(--gold);font-family:'Libre Baskerville',serif;line-height:1;"><?=$s['upcoming_count']?></div>
          <div style="font-size:9.5px;color:var(--ink3);text-transform:uppercase;letter-spacing:.5px;font-weight:700;margin-top:4px;">Upcoming</div>
        </div>
        <div style="background:var(--bg3);border-radius:6px;padding:10px;text-align:center;">
          <div style="font-size:18px;font-weight:700;color:var(--green);font-family:'Libre Baskerville',serif;line-height:1;"><?=$s['done_count']?></div>
          <div style="font-size:9.5px;color:var(--ink3);text-transform:uppercase;letter-spacing:.5px;font-weight:700;margin-top:4px;">Completed</div>
        </div>
      </div>

      <?php if($has_goals): ?>
        <button onclick="toggleGoals(<?=$s['id']?>)" id="gb-<?=$s['id']?>" style="width:100%;background:var(--bg3);border:1px solid var(--border);border-radius:var(--radius);padding:9px 12px;font-size:12px;font-weight:700;color:var(--crimson);cursor:pointer;display:flex;align-items:center;justify-content:space-between;font-family:inherit;text-transform:uppercase;letter-spacing:0.5px;">
          <span>View Student Goals</span>
          <span id="ga-<?=$s['id']?>" style="font-size:10px;">&#9662;</span>
        </button>
        <div id="g-<?=$s['id']?>" style="display:none;margin-top:10px;">
          <?php foreach($qs as $k=>$lbl): if(!empty($s[$k])): ?>
            <div style="border-left:3px solid var(--crimson);background:var(--bg3);padding:10px 12px;margin-bottom:6px;border-radius:0 var(--radius) var(--radius) 0;">
              <div style="font-size:10px;font-weight:800;color:var(--crimson);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;"><?=h($lbl)?></div>
              <div style="font-size:12.5px;color:var(--ink2);line-height:1.6;"><?=nl2br(h($s[$k]))?></div>
            </div>
          <?php endif; endforeach; ?>
        </div>
      <?php else: ?>
        <div style="background:var(--bg3);border-radius:var(--radius);padding:11px 12px;font-size:12px;color:var(--ink3);text-align:center;">
          Goals not yet submitted
        </div>
      <?php endif; ?>

      <div style="font-size:11px;color:var(--ink3);margin-top:12px;font-variant-numeric:tabular-nums;"><?=h($s['email'])?></div>
    </div>
  </div>
<?php endforeach; ?>
</div>

<script>
function toggleGoals(id){
  var g=document.getElementById('g-'+id), a=document.getElementById('ga-'+id);
  if(g.style.display==='none'){ g.style.display='block'; a.innerHTML='&#9652;'; }
  else { g.style.display='none'; a.innerHTML='&#9662;'; }
}
</script>

<?php endif; ?>
</div></body></html>
