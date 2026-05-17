<?php
require_once '../includes/auth.php';
require_once '../includes/helpers.php';
guard(['student','mentor','admin']);
$uid=current_user_id();
$role=current_role();
$db=get_db();
$u=$db->prepare("SELECT * FROM users WHERE id=?");
$u->execute([$uid]); $user=$u->fetch();

if($role==='student'){
    $sp=$db->prepare("SELECT * FROM student_profiles WHERE user_id=?");
    $sp->execute([$uid]); $profile=$sp->fetch();
    $goals=get_student_goals($uid);
    $elected=get_elected_mentor($uid);
} elseif($role==='mentor'){
    $mp=$db->prepare("SELECT * FROM mentor_profiles WHERE user_id=?");
    $mp->execute([$uid]); $profile=$mp->fetch();
    $stu_count=$db->prepare("SELECT COUNT(*) FROM mentor_current WHERE mentor_id=?");
    $stu_count->execute([$uid]); $student_count=$stu_count->fetchColumn();
} else {
    $profile=null;
}

$page_title='My Profile';
$breadcrumb_extra='Profile';
include '_layout.php';
$parts=explode(' ',trim($user['name']));
$init2=strtoupper(substr($parts[0],0,1).(count($parts)>1?substr($parts[count($parts)-1],0,1):''));
?>

<div class="welcome-bar">My Profile</div>

<div class="two-col">

  <div class="card" style="margin-bottom:0;">
    <div style="background:linear-gradient(135deg,var(--crimson),var(--crimson3));padding:32px 24px;text-align:center;color:white;">
      <div style="width:96px;height:96px;margin:0 auto;border-radius:50%;background:rgba(255,255,255,0.2);border:3px solid var(--gold);display:flex;align-items:center;justify-content:center;font-family:'Libre Baskerville',serif;font-size:30px;color:white;"><?=$init2?></div>
      <div style="font-family:'Libre Baskerville',serif;font-size:20px;font-weight:700;margin-top:14px;"><?=h($user['name'])?></div>
      <div style="font-size:12px;opacity:0.8;margin-top:4px;letter-spacing:1.5px;text-transform:uppercase;"><?=h($role)?></div>
    </div>
    <div class="card-body">
      <table style="font-size:13px;width:100%;">
        <tr><td style="color:var(--ink3);padding:8px 0;font-weight:700;width:120px;">Email</td><td style="word-break:break-all;font-variant-numeric:tabular-nums;"><?=h($user['email'])?></td></tr>
        <?php if($role==='student' && $profile): ?>
          <tr><td style="color:var(--ink3);padding:8px 0;font-weight:700;">Roll No.</td><td><?=h($profile['roll_number']??'—')?></td></tr>
          <tr><td style="color:var(--ink3);padding:8px 0;font-weight:700;">Department</td><td><?=h($profile['department']??'—')?></td></tr>
        <?php elseif($role==='mentor' && $profile): ?>
          <tr><td style="color:var(--ink3);padding:8px 0;font-weight:700;">Department</td><td><?=h($profile['department']??'—')?></td></tr>
          <tr><td style="color:var(--ink3);padding:8px 0;font-weight:700;">Designation</td><td><?=h($profile['specialization']??'—')?></td></tr>
          <tr><td style="color:var(--ink3);padding:8px 0;font-weight:700;">Capacity</td><td style="font-variant-numeric:tabular-nums;"><?=$student_count?> / <?=$profile['max_students']?> students</td></tr>
        <?php endif; ?>
        <tr><td style="color:var(--ink3);padding:8px 0;font-weight:700;">Joined</td><td><?=date('d M Y',strtotime($user['created_at']))?></td></tr>
      </table>
    </div>
  </div>

  <div>
    <?php if($role==='student'): ?>
      <div class="card">
        <div class="card-header"><div class="card-header-title">My Status</div></div>
        <div class="card-body" style="padding:8px 0;">
          <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 18px;border-bottom:1px solid var(--bg3);">
            <span style="font-size:13px;color:var(--ink2);font-weight:600;">Goals Submitted</span>
            <span class="status-badge <?=$goals?'success':'neutral'?>"><?=$goals?'Yes':'Pending'?></span>
          </div>
          <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 18px;">
            <span style="font-size:13px;color:var(--ink2);font-weight:600;">Mentor Elected</span>
            <span class="status-badge <?=$elected?'success':'neutral'?>"><?=$elected?h($elected['name']):'Pending'?></span>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>

</div>

</div></body></html>
