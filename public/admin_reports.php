<?php
require_once '../includes/auth.php';
require_once '../includes/helpers.php';
guard('admin');
$db=get_db();

$total_students   = (int)$db->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn();
$total_mentors    = (int)$db->query("SELECT COUNT(*) FROM users WHERE role='mentor'")->fetchColumn();
$goals_submitted  = (int)$db->query("SELECT COUNT(*) FROM student_goals")->fetchColumn();
$elections        = (int)$db->query("SELECT COUNT(*) FROM mentor_current")->fetchColumn();
$sessions_total   = (int)$db->query("SELECT COUNT(*) FROM sessions")->fetchColumn();
$sessions_done    = (int)$db->query("SELECT COUNT(*) FROM sessions WHERE status='done'")->fetchColumn();
$feedback_pos     = (int)$db->query("SELECT COUNT(*) FROM feedback WHERE rating='up'")->fetchColumn();
$feedback_neg     = (int)$db->query("SELECT COUNT(*) FROM feedback WHERE rating='down'")->fetchColumn();

$mentor_loads=$db->query("
    SELECT u.name,
           (SELECT COUNT(*) FROM mentor_current WHERE mentor_id=u.id) AS students,
           (SELECT COUNT(*) FROM sessions WHERE mentor_id=u.id AND status='done') AS sessions_done,
           (SELECT COUNT(*) FROM feedback WHERE mentor_id=u.id AND rating='up') AS thumbs_up,
           (SELECT COUNT(*) FROM feedback WHERE mentor_id=u.id AND rating='down') AS thumbs_down,
           mp.max_students
    FROM users u JOIN mentor_profiles mp ON mp.user_id=u.id
    WHERE u.role='mentor' ORDER BY students DESC,u.name
")->fetchAll();

$page_title='Reports';
$breadcrumb_extra='Reports';
include '_layout.php';
?>

<div class="welcome-bar">System Reports &amp; Analytics</div>

<div class="stats-row">
  <div class="stat-box"><div class="stat-num"><?=$total_students?></div><div class="stat-lbl">Total Students</div></div>
  <div class="stat-box"><div class="stat-num"><?=$total_mentors?></div><div class="stat-lbl">Total Mentors</div></div>
  <div class="stat-box"><div class="stat-num" style="color:var(--green);"><?=$elections?></div><div class="stat-lbl">Elections Done</div></div>
  <div class="stat-box"><div class="stat-num" style="color:var(--gold);"><?=$total_students?round($goals_submitted/$total_students*100):0?>%</div><div class="stat-lbl">Goals Coverage</div></div>
</div>

<div class="stats-row">
  <div class="stat-box"><div class="stat-num"><?=$sessions_total?></div><div class="stat-lbl">Total Sessions</div></div>
  <div class="stat-box"><div class="stat-num" style="color:var(--green);"><?=$sessions_done?></div><div class="stat-lbl">Sessions Done</div></div>
  <div class="stat-box"><div class="stat-num" style="color:var(--green);"><?=$feedback_pos?></div><div class="stat-lbl">Positive Feedback</div></div>
  <div class="stat-box"><div class="stat-num" style="color:#c0392b;"><?=$feedback_neg?></div><div class="stat-lbl">Negative Feedback</div></div>
</div>

<div class="card">
  <div class="card-header"><div class="card-header-title">Mentor Load &amp; Performance</div></div>
  <div class="card-body" style="padding:0;">
    <div class="table-wrap">
      <table>
        <thead><tr><th>Mentor</th><th>Students</th><th>Sessions Done</th><th>Positive</th><th>Negative</th><th>Approval</th></tr></thead>
        <tbody>
        <?php foreach($mentor_loads as $m):
          $total_fb = $m['thumbs_up'] + $m['thumbs_down'];
          $approval = $total_fb ? round($m['thumbs_up']/$total_fb*100) : null;
        ?>
          <tr>
            <td style="font-weight:600;"><?=h($m['name'])?></td>
            <td style="font-variant-numeric:tabular-nums;"><?=$m['students']?>/<?=$m['max_students']?></td>
            <td style="font-variant-numeric:tabular-nums;"><?=$m['sessions_done']?></td>
            <td style="color:var(--green);font-weight:700;font-variant-numeric:tabular-nums;"><?=$m['thumbs_up']?></td>
            <td style="color:#c0392b;font-weight:700;font-variant-numeric:tabular-nums;"><?=$m['thumbs_down']?></td>
            <td style="font-variant-numeric:tabular-nums;"><?=$approval!==null?$approval.'%':'—'?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div style="display:flex;gap:12px;margin-top:14px;flex-wrap:wrap;">
  <a href="admin.php" class="btn btn-outline">Back to Admin</a>
  <a href="#" onclick="window.print();return false;" class="btn btn-primary">Print Report</a>
</div>

</div></body></html>
