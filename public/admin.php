<?php
require_once '../includes/auth.php';
require_once '../includes/helpers.php';
guard('admin');
$db=get_db();

$total_students=(int)$db->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn();
$total_mentors=(int)$db->query("SELECT COUNT(*) FROM users WHERE role='mentor'")->fetchColumn();
$elected_count=(int)$db->query("SELECT COUNT(*) FROM mentor_current")->fetchColumn();
$goals_submitted=(int)$db->query("SELECT COUNT(*) FROM student_goals")->fetchColumn();
$goals_pct=$total_students?round(($goals_submitted/$total_students)*100):0;

$rows=$db->query("
    SELECT u.id,u.name,u.email,sp.department,sp.roll_number,
           CASE WHEN mc.id IS NOT NULL THEN 'ELECTED'
                WHEN sg.id IS NOT NULL THEN 'READY'
                ELSE 'START' END AS state,
           m.name AS mentor_name
    FROM users u
    LEFT JOIN student_profiles sp ON sp.user_id=u.id
    LEFT JOIN student_goals sg ON sg.student_id=u.id
    LEFT JOIN mentor_current mc ON mc.student_id=u.id
    LEFT JOIN users m ON m.id=mc.mentor_id
    WHERE u.role='student' ORDER BY u.name
")->fetchAll();

$mentors_raw=$db->query("SELECT u.id,u.name,COUNT(mc.id) AS cnt,mp.max_students FROM users u JOIN mentor_profiles mp ON mp.user_id=u.id LEFT JOIN mentor_current mc ON mc.mentor_id=u.id WHERE u.role='mentor' GROUP BY u.id ORDER BY u.name")->fetchAll();

$page_title='Admin Control';
$breadcrumb_extra='Admin';
include '_layout.php';

$state_pills=['START'=>'neutral','READY'=>'pending','ELECTED'=>'success'];
$state_labels=['START'=>'NO GOALS','READY'=>'READY','ELECTED'=>'ELECTED'];
?>

<div class="welcome-bar">Admin Control Panel</div>

<div class="stats-row">
  <div class="stat-box"><div class="stat-num"><?=$total_students?></div><div class="stat-lbl">Students</div></div>
  <div class="stat-box"><div class="stat-num"><?=$total_mentors?></div><div class="stat-lbl">Mentors</div></div>
  <div class="stat-box"><div class="stat-num" style="color:var(--green);"><?=$elected_count?></div><div class="stat-lbl">Elections Done</div></div>
  <div class="stat-box"><div class="stat-num" style="color:var(--gold);"><?=$goals_pct?>%</div><div class="stat-lbl">Goals Coverage</div></div>
</div>

<div class="tab-bar">
  <button class="tab-btn active" onclick="showTab('students',event)">Students (<?=$total_students?>)</button>
  <button class="tab-btn" onclick="showTab('mentors',event)">Mentors (<?=$total_mentors?>)</button>
  <button class="tab-btn" onclick="showTab('config',event)">Configuration</button>
  <button class="tab-btn" onclick="window.location='admin_reports.php'">Reports</button>
</div>

<div id="tab-students" class="section active">
  <div class="admin-search-bar">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--ink3)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
    <input type="text" id="search-input" placeholder="Search by name, roll, or email…" oninput="filterTable(this.value)"/>
    <select id="state-filter" class="admin-filter-select" onchange="filterByState(this.value)">
      <option value="">All States</option>
      <option value="START">No Goals</option>
      <option value="READY">Ready</option>
      <option value="ELECTED">Elected</option>
    </select>
  </div>

  <div class="card">
    <div class="card-body" style="padding:0;">
      <div class="table-wrap">
      <table id="students-table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Roll No.</th>
            <th>State</th>
            <th>Elected Mentor</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($rows as $s): ?>
          <tr data-search="<?=h(strtolower($s['name'].' '.$s['email'].' '.($s['roll_number']??'')))?>" data-state="<?=h($s['state'])?>">
            <td>
              <div style="font-weight:700;font-size:13px;"><?=h($s['name'])?></div>
              <div style="font-size:11px;color:var(--ink3);font-variant-numeric:tabular-nums;"><?=h($s['email'])?></div>
            </td>
            <td style="font-variant-numeric:tabular-nums;"><?=h($s['roll_number']??'—')?></td>
            <td><span class="status-badge <?=$state_pills[$s['state']]?>"><?=$state_labels[$s['state']]?></span></td>
            <td><?=h($s['mentor_name']??'—')?></td>
            <td>
              <?php if($s['mentor_name']): ?>
                <form action="../actions/admin_action.php" method="POST" style="display:inline" onsubmit="return confirm('Remove this student\'s mentor mapping? Student can re-elect.')">
                  <input type="hidden" name="action" value="unmap_student"/>
                  <input type="hidden" name="student_id" value="<?=$s['id']?>"/>
                  <button type="submit" class="btn btn-sm btn-danger">Unmap</button>
                </form>
              <?php endif; ?>
              <form action="../actions/admin_action.php" method="POST" style="display:inline" onsubmit="var p=prompt('New password for <?=addslashes($s['name'])?>:','amma');if(!p)return false;this.querySelector('[name=new_password]').value=p;return true;">
                <input type="hidden" name="action" value="reset_password"/>
                <input type="hidden" name="user_id" value="<?=$s['id']?>"/>
                <input type="hidden" name="new_password" value=""/>
                <button type="submit" class="btn btn-sm" style="background:#eef4ff;color:#1a4f8a;border:1px solid #b8d4f5;" title="Reset password">Reset PW</button>
              </form>
              <form action="../actions/admin_action.php" method="POST" style="display:inline" onsubmit="return confirm('Delete <?=addslashes($s['name'])?> permanently? This removes all their data.')">
                <input type="hidden" name="action" value="delete_user"/>
                <input type="hidden" name="user_id" value="<?=$s['id']?>"/>
                <button type="submit" class="btn btn-sm" style="background:none;color:#c0392b;border:1px solid #f5c6c6;" title="Delete">Delete</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      </div>
    </div>
  </div>
</div>

<div id="tab-mentors" class="section">
  <div class="card">
    <div class="card-header"><div class="card-header-title">All Mentors</div></div>
    <div class="card-body" style="padding:0;">
      <div class="table-wrap">
        <table>
          <thead><tr><th>Name</th><th>Capacity</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody>
            <?php foreach($mentors_raw as $m): $pct=($m['cnt']/$m['max_students'])*100; ?>
              <tr>
                <td style="font-weight:600;"><?=h($m['name'])?></td>
                <td>
                  <div style="display:flex;align-items:center;gap:8px;">
                    <div style="flex:1;height:6px;background:var(--bg3);border-radius:3px;overflow:hidden;min-width:80px;max-width:140px;">
                      <div style="height:100%;width:<?=min(100,$pct)?>%;background:<?=$pct>=100?'#c0392b':($pct>70?'var(--gold)':'var(--green)')?>;"></div>
                    </div>
                    <span style="font-size:11px;font-weight:700;font-variant-numeric:tabular-nums;"><?=$m['cnt']?>/<?=$m['max_students']?></span>
                  </div>
                </td>
                <td><span class="status-badge <?=$pct>=100?'danger':'success'?>"><?=$pct>=100?'Full':$m['cnt'].' assigned'?></span></td>
                <td>
                  <form action="../actions/admin_action.php" method="POST" style="display:inline" onsubmit="var p=prompt('New password for <?=addslashes($m['name'])?>:','amma');if(!p)return false;this.querySelector('[name=new_password]').value=p;return true;">
                    <input type="hidden" name="action" value="reset_password"/>
                    <input type="hidden" name="user_id" value="<?=$m['id']?>"/>
                    <input type="hidden" name="new_password" value=""/>
                    <button type="submit" class="btn btn-sm" style="background:#eef4ff;color:#1a4f8a;border:1px solid #b8d4f5;">Reset PW</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div id="tab-config" class="section">
  <?php $cfg=get_semester_config(); ?>
  <div class="card">
    <div class="card-header"><div class="card-header-title">Semester Configuration</div></div>
    <div class="card-body">
      <form action="../actions/admin_action.php" method="POST">
        <input type="hidden" name="action" value="update_config"/>
        <div class="form-group">
          <label class="form-label">Semester Name</label>
          <input type="text" name="semester_name" class="form-control" value="<?=h($cfg['semester_name']??'')?>"/>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Goals Deadline</label>
            <input type="date" name="goals_deadline" class="form-control" value="<?=h($cfg['goals_deadline']??'')?>"/>
          </div>
          <div class="form-group">
            <label class="form-label">Election Deadline</label>
            <input type="date" name="election_deadline" class="form-control" value="<?=h($cfg['election_deadline']??'')?>"/>
          </div>
        </div>
        <button type="submit" class="btn btn-primary">Save Configuration</button>
      </form>
    </div>
  </div>
</div>

<script>
function showTab(name,e){
  document.querySelectorAll('.section').forEach(s=>s.classList.remove('active'));
  document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
  document.getElementById('tab-'+name).classList.add('active');
  e.target.classList.add('active');
  window.location.hash = name;
}
if(window.location.hash){
  var h=window.location.hash.substring(1);
  if(['students','mentors','config'].includes(h)){
    document.querySelectorAll('.section').forEach(s=>s.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
    document.getElementById('tab-'+h).classList.add('active');
    document.querySelectorAll('.tab-btn').forEach(b=>{ if(b.textContent.toLowerCase().includes(h)){ b.classList.add('active'); }});
  }
}
function filterTable(q){
  q=q.toLowerCase();
  document.querySelectorAll('#students-table tbody tr').forEach(tr=>{
    var s=tr.dataset.search||'', st=tr.dataset.state||'';
    var sf=document.getElementById('state-filter').value;
    var matchQ = !q || s.includes(q);
    var matchS = !sf || st===sf;
    tr.style.display = (matchQ && matchS) ? '' : 'none';
  });
}
function filterByState(v){ filterTable(document.getElementById('search-input').value); }
</script>

</div></body></html>
