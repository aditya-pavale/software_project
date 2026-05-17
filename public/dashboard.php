<?php
require_once '../includes/auth.php';
require_once '../includes/helpers.php';
guard(['student','mentor','admin']);
$role=current_role();
$db=get_db();

if($role==='admin'){ redirect('/public/admin.php'); }

if($role==='student'){
    $uid=current_user_id();
    $goals=get_student_goals($uid);
    $elected=get_elected_mentor($uid);
    $sessions=get_student_sessions($uid);
    $cfg=get_semester_config();
    $deadline=$cfg['election_deadline']??null;
    $days_left=$deadline?days_until($deadline):null;
    if($elected) $state='ELECTED';
    elseif($goals) $state='READY';
    else $state='START';
    $upcoming=array_filter($sessions,fn($s)=>$s['status']==='upcoming');
    $done_s=array_filter($sessions,fn($s)=>$s['status']==='done');
    // Count pending meet requests
    $meet_pending=(int)$db->query("SELECT COUNT(*) FROM meet_requests WHERE student_id=$uid AND status='accepted'")->fetchColumn();
}
if($role==='mentor'){
    $uid=current_user_id();
    $my_students=get_mentor_students($uid);
    $my_sessions=get_mentor_sessions($uid);
    $my_feedback=get_mentor_feedback($uid);
    $pos=count(array_filter($my_feedback,fn($f)=>$f['rating']==='up'));
    $neg=count(array_filter($my_feedback,fn($f)=>$f['rating']==='down'));
    $upcoming=array_filter($my_sessions,fn($s)=>$s['status']==='upcoming');
    $done_s=array_filter($my_sessions,fn($s)=>$s['status']==='done');
    $meet_pending=(int)$db->query("SELECT COUNT(*) FROM meet_requests WHERE mentor_id=$uid AND status='pending'")->fetchColumn();
}

$page_title='Dashboard';
include '_layout.php';
?>

<?php if($role==='student'): ?>

<div class="welcome-bar">Welcome, <?=h(current_name())?><?=current_roll()?' <span style="font-size:12px;font-weight:400;color:var(--ink3);margin-left:8px;">'.h(current_roll()).'</span>':''?></div>

<!-- JOURNEY BAR -->
<div class="card" style="margin-bottom:16px;">
  <div class="card-body" style="padding:20px 24px;">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;position:relative;">
      <!-- Connector line behind circles -->
      <div style="position:absolute;top:19px;left:10%;right:10%;height:2px;background:var(--border2);z-index:0;"></div>
      <?php
      $steps=[['START','Submit Goals'],['READY','Elect Mentor'],['ELECTED','Connected']];
      $order=['START','READY','ELECTED'];
      $ci=array_search($state,$order);
      foreach($steps as $i=>[$st,$lbl]):
        $done=$i<$ci; $cur=$i===$ci;
      ?>
        <div style="display:flex;flex-direction:column;align-items:center;gap:8px;flex:1;position:relative;z-index:1;">
          <!-- Line to next step -->
          <?php if($i<2): ?>
            <div style="position:absolute;top:19px;left:50%;right:-50%;height:2px;background:<?=$done?'var(--green2)':'var(--border2)'?>;z-index:0;"></div>
          <?php endif; ?>
          <!-- Circle -->
          <div style="width:38px;height:38px;border-radius:50%;
            background:<?=$done?'var(--green)':($cur?'var(--crimson)':'white')?>;
            border:2px solid <?=$done?'var(--green)':($cur?'var(--crimson)':'var(--border)')?>;
            display:flex;align-items:center;justify-content:center;
            font-family:'Libre Baskerville',serif;font-size:14px;font-weight:700;
            color:<?=($done||$cur)?'white':'var(--ink3)'?>;
            <?=$cur?'box-shadow:0 0 0 4px rgba(155,28,60,.15);':''?>
            position:relative;z-index:1;transition:all .25s;">
            <?=$done?'&#10003;':($i+1)?>
          </div>
          <!-- Label -->
          <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;text-align:center;
            color:<?=$done?'var(--green)':($cur?'var(--crimson)':'var(--ink3)')?>;white-space:nowrap;">
            <?=$lbl?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- NAV TILES -->
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:16px;" class="dash-tiles">
  <?php
  $tiles=[
    ['goals.php',    'My Goals',         'M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10zm0-6a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm0-2a2 2 0 1 1 0-4 2 2 0 0 1 0 4z', false],
    ['mentors.php',  'Discover Mentors', 'M21 21l-4.35-4.35M11 19A8 8 0 1 1 11 3a8 8 0 0 1 0 16z', false],
    ['mentors.php',  'Elect Mentor',     'M9 11l3 3L22 4M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11', false],
    ['my_mentor.php','My Mentor',        'M22 10v6M2 10l10-5 10 5-10 5zM6 12v5c3 3 9 3 12 0v-5', false],
    ['my_mentor.php','Book Session',     'M3 4h18v16H3V4zm4 4h10M7 12h10M7 16h6', false],
    ['profile.php',  'My Profile',       'M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z', false],
  ];
  foreach($tiles as [$href,$label,$path,$ext]):
  ?>
    <a href="<?=$href?>" class="dash-tile" style="background:white;border:1px solid var(--border2);border-radius:var(--radius2);display:flex;align-items:center;text-decoration:none;overflow:hidden;min-height:54px;box-shadow:var(--shadow);transition:var(--trans);">
      <span style="flex:1;padding:0 12px;font-size:11px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:var(--ink2);line-height:1.3;"><?=$label?></span>
      <span style="width:54px;height:54px;background:linear-gradient(145deg,var(--crimson),var(--crimson3));display:flex;align-items:center;justify-content:center;flex-shrink:0;clip-path:polygon(12px 0%,100% 0%,100% 100%,0% 100%);">
        <svg viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.92)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><path d="<?=$path?>"/></svg>
      </span>
    </a>
  <?php endforeach; ?>
</div>

<!-- STATS -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:16px;" class="stats-row">
  <div class="stat-box"><div class="stat-num" style="font-size:22px;color:<?=$goals?'var(--green)':'var(--ink3)'?>;"><?=$goals?'Done':'Pending'?></div><div class="stat-lbl">Goals</div></div>
  <div class="stat-box"><div class="stat-num" style="font-size:22px;color:<?=$elected?'var(--green)':'var(--ink3)'?>;"><?=$elected?'Done':'Pending'?></div><div class="stat-lbl">Mentor</div></div>
  <div class="stat-box"><div class="stat-num" style="color:var(--green);"><?=count($done_s)?></div><div class="stat-lbl">Sessions Done</div></div>
  <div class="stat-box"><div class="stat-num" style="color:<?=($days_left!==null&&$days_left<=7&&$days_left>=0)?'#c0392b':'var(--crimson)'?>;"><?=$days_left===null?'—':($days_left<0?'Passed':$days_left)?></div><div class="stat-lbl">Days Left</div></div>
</div>

<!-- BOTTOM: Next Step + Inspiration + Sessions — stacks on mobile -->
<div style="display:grid;grid-template-columns:1fr 1.4fr 1fr;gap:14px;" class="dash-bottom-3">

  <div class="card" style="margin-bottom:0;">
    <div class="card-header"><div class="card-header-title">Next Step</div></div>
    <div class="card-body" style="text-align:center;padding:22px 18px;">
      <?php if($state==='START'): ?>
        <div style="font-family:'Libre Baskerville',serif;font-size:36px;color:var(--crimson);line-height:1;margin-bottom:6px;">1</div>
        <div style="font-family:'Libre Baskerville',serif;font-size:15px;color:var(--ink);margin-bottom:6px;font-weight:700;">Submit Your Goals</div>
        <div style="font-size:12px;color:var(--ink3);margin-bottom:14px;line-height:1.6;">Tell us about your academic goals, challenges, and expectations.</div>
        <a href="goals.php" class="btn btn-primary" style="display:inline-flex;">Get Started</a>
      <?php elseif($state==='READY'): ?>
        <div style="font-family:'Libre Baskerville',serif;font-size:36px;color:var(--crimson);line-height:1;margin-bottom:6px;">2</div>
        <div style="font-family:'Libre Baskerville',serif;font-size:15px;color:var(--ink);margin-bottom:6px;font-weight:700;">Choose Your Mentor</div>
        <div style="font-size:12px;color:var(--ink3);margin-bottom:14px;line-height:1.6;">Browse faculty and elect the mentor who suits you best.</div>
        <a href="mentors.php" class="btn btn-primary" style="display:inline-flex;">Browse Mentors</a>
      <?php else: ?>
        <div style="font-size:11px;font-weight:800;color:var(--green);text-transform:uppercase;letter-spacing:1px;padding:10px 0 4px;">Your Mentor</div>
        <div style="font-family:'Libre Baskerville',serif;font-size:15px;color:var(--crimson);margin-bottom:4px;font-weight:700;"><?=h($elected['name'])?></div>
        <div style="font-size:12px;color:var(--ink3);margin-bottom:14px;"><?=h($elected['specialization']??'Faculty')?></div>
        <a href="my_mentor.php" class="btn btn-primary" style="display:inline-flex;">Open Workspace</a>
      <?php endif; ?>
    </div>
  </div>

  <div class="card" style="margin-bottom:0;">
    <div class="card-header"><div class="card-header-title">Inspiration</div></div>
    <div class="card-body" style="display:flex;align-items:center;gap:16px;">
      <img src="https://amma.org/wp-content/uploads/sq-amma-001-1.jpg" alt="Amma"
        style="width:66px;height:66px;border-radius:50%;object-fit:cover;border:3px solid var(--gold);flex-shrink:0;"
        onerror="this.style.display='none'"/>
      <div>
        <p style="font-size:12.5px;color:var(--ink2);line-height:1.65;font-style:italic;">&ldquo;Youngsters need to understand the real purpose of life. They need courage and wisdom to face the challenges of life.&rdquo;</p>
        <p style="font-size:11px;color:var(--crimson);font-weight:700;margin-top:8px;">— Mata Amritanandamayi Devi</p>
      </div>
    </div>
  </div>

  <div class="card" style="margin-bottom:0;">
    <div class="card-header">
      <div class="card-header-title">Upcoming Sessions</div>
      <?php if($elected): ?><a href="my_mentor.php" class="btn btn-outline btn-sm">All</a><?php endif; ?>
    </div>
    <div class="card-body" style="padding:8px 0;min-height:80px;">
      <?php if(empty($upcoming)): ?>
        <div style="text-align:center;padding:20px 14px;color:var(--ink3);font-size:12.5px;line-height:1.7;">
          <?=$elected?'No sessions yet. <a href="my_mentor.php" style="color:var(--crimson);font-weight:600;font-size:12px;text-decoration:none;">Book one</a>':'Elect a mentor first.'?>
        </div>
      <?php else: foreach(array_slice($upcoming,0,3) as $s): $d=strtotime($s['session_date']); ?>
        <div style="display:flex;align-items:center;gap:10px;padding:9px 14px;border-bottom:1px solid var(--bg3);">
          <div class="session-date-box"><div class="day"><?=date('d',$d)?></div><div class="month"><?=date('M',$d)?></div></div>
          <div style="flex:1;min-width:0;">
            <div style="font-size:12px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?=h($s['title']??'Session')?></div>
            <div style="font-size:11px;color:var(--ink3);"><?=h($s['mentor_name'])?></div>
          </div>
          <div style="font-size:12px;color:var(--crimson);font-weight:700;"><?=substr($s['session_time'],0,5)?></div>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>

</div>


<?php elseif($role==='mentor'): ?>

<div class="welcome-bar">Welcome, <?=h(current_name())?></div>

<!-- MENTOR NAV TILES -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:16px;" class="dash-tiles">
  <?php
  $mentor_tiles=[
    ['mentor_students.php', 'My Students',     'M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75'],
    ['mentor_sessions.php', 'Sessions',        'M3 4h18v16H3V4zm4 4h10M7 12h10M7 16h6'],
    ['meet_requests.php',   'Meet Requests'.($meet_pending?' ('.$meet_pending.')':''), 'M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z'],
    ['mentor_feedback.php', 'Ratings',         'M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z'],
    ['profile.php',         'My Profile',      'M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z'],
  ];
  foreach($mentor_tiles as [$href,$label,$path]):
  ?>
    <a href="<?=$href?>" class="dash-tile" style="background:white;border:1px solid var(--border2);border-radius:var(--radius2);display:flex;align-items:center;text-decoration:none;overflow:hidden;min-height:54px;box-shadow:var(--shadow);transition:var(--trans);">
      <span style="flex:1;padding:0 12px;font-size:11px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:var(--ink2);line-height:1.3;"><?=$label?></span>
      <span style="width:54px;height:54px;background:linear-gradient(145deg,var(--crimson),var(--crimson3));display:flex;align-items:center;justify-content:center;flex-shrink:0;clip-path:polygon(12px 0%,100% 0%,100% 100%,0% 100%);">
        <svg viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.92)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><path d="<?=$path?>"/></svg>
      </span>
    </a>
  <?php endforeach; ?>
</div>

<!-- MENTOR STATS -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:16px;" class="stats-row">
  <div class="stat-box"><div class="stat-num"><?=count($my_students)?></div><div class="stat-lbl">Students</div></div>
  <div class="stat-box"><div class="stat-num"><?=count($upcoming)?></div><div class="stat-lbl">Upcoming Sessions</div></div>
  <div class="stat-box"><div class="stat-num" style="color:var(--green);"><?=count($done_s)?></div><div class="stat-lbl">Completed Sessions</div></div>
  <div class="stat-box"><div class="stat-num" style="color:var(--gold);"><?=($pos+$neg)?round($pos/($pos+$neg)*100).'%':'—'?></div><div class="stat-lbl">Approval Rate</div></div>
</div>

<!-- MENTOR TWO-COL -->
<div class="two-col">
  <div class="card" style="margin-bottom:0;">
    <div class="card-header"><div class="card-header-title">My Students</div><a href="mentor_students.php" class="btn btn-outline btn-sm">View All</a></div>
    <div class="card-body" style="padding:0;">
      <?php if(empty($my_students)): ?>
        <div style="padding:28px;color:var(--ink3);font-size:13px;text-align:center;">No students assigned yet.</div>
      <?php else: foreach(array_slice($my_students,0,6) as $s):
        $init=strtoupper(substr(implode('',array_map(fn($w)=>$w[0],explode(' ',$s['name']))),0,2));
      ?>
        <div class="student-row">
          <div class="s-init"><?=$init?></div>
          <div class="s-info"><div class="s-name"><?=h($s['name'])?></div><div class="s-dept"><?=h($s['roll_number']??'')?></div></div>
          <span class="status-badge <?=$s['done_count']>0?'success':'neutral'?>"><?=$s['done_count']?> done</span>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>
  <div class="card" style="margin-bottom:0;">
    <div class="card-header"><div class="card-header-title">Upcoming Sessions</div><a href="mentor_sessions.php" class="btn btn-outline btn-sm">All</a></div>
    <div class="card-body" style="padding:8px 0;">
      <?php if(empty($upcoming)): ?>
        <div style="text-align:center;padding:28px;color:var(--ink3);font-size:13px;">No upcoming sessions.</div>
      <?php else: foreach(array_slice($upcoming,0,5) as $s): $d=strtotime($s['session_date']); ?>
        <div style="display:flex;align-items:center;gap:10px;padding:9px 14px;border-bottom:1px solid var(--bg3);">
          <div class="session-date-box"><div class="day"><?=date('d',$d)?></div><div class="month"><?=date('M',$d)?></div></div>
          <div style="flex:1;min-width:0;">
            <div style="font-size:12px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?=h($s['student_name'])?></div>
            <div style="font-size:11px;color:var(--ink3);"><?=h($s['title']??'Session')?></div>
          </div>
          <div style="font-size:12px;color:var(--crimson);font-weight:700;"><?=substr($s['session_time'],0,5)?></div>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>
</div>

<?php endif; ?>

</div></body></html>
