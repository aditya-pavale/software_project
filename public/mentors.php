<?php
require_once '../includes/auth.php';
require_once '../includes/helpers.php';
guard('student');
$uid=current_user_id();
$db=get_db();
$goals=get_student_goals($uid);
$elected=get_elected_mentor($uid);
$mentors=get_all_mentors();
$cfg=get_semester_config();
$deadline=$cfg['election_deadline']??null;
$deadline_passed=$deadline && strtotime($deadline) < strtotime('today');
$search=trim($_GET['q']??'');
if($search){
    $q=strtolower($search);
    $mentors=array_filter($mentors,fn($m)=>str_contains(strtolower($m['name']),$q)||str_contains(strtolower($m['specialization']??''),$q));
}
$page_title='Discover Mentors';
$breadcrumb_extra='Discover Mentors';
include '_layout.php';
$avatar_colors=['#9b1c3c','#1a4f8a','#2d6a4f','#7b2d8b','#c8930a','#1a5f7a','#7a3015','#2a4f2a'];
?>

<div class="welcome-bar">Discover Mentors</div>

<?php if($elected): ?>
  <div class="ab2 ab2-success"><div class="ab2-label">Mentor Elected</div><div class="ab2-body">You have elected <strong><?=h($elected['name'])?></strong>. <a href="my_mentor.php">Go to your workspace</a></div></div>
<?php elseif(!$goals): ?>
  <div class="ab2 ab2-warning"><div class="ab2-label">Action Required</div><div class="ab2-body">Please <a href="goals.php">submit your goals</a> before electing a mentor.</div></div>
<?php elseif($deadline_passed): ?>
  <div class="ab2 ab2-danger"><div class="ab2-label">Deadline Passed</div><div class="ab2-body">The election deadline has passed. Please contact your administrator.</div></div>
<?php else: ?>
  <div class="ab2 ab2-info"><div class="ab2-label">Ready to Elect</div><div class="ab2-body">Browse the <strong><?=count($mentors)?> faculty members</strong> below and click <strong>Elect Mentor</strong> on your choice. This decision is permanent.</div></div>
<?php endif; ?>

<form method="GET" style="display:flex;align-items:center;gap:10px;background:white;border:1px solid var(--border);border-radius:var(--radius2);padding:10px 16px;margin-bottom:18px;box-shadow:var(--shadow);">
  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--ink3)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
  <input type="text" name="q" value="<?=h($search)?>" placeholder="Search by name or specialization…" autocomplete="off" style="flex:1;border:none;outline:none;font-size:13px;font-family:inherit;background:transparent;"/>
  <?php if($search): ?><a href="mentors.php" style="color:var(--crimson);font-size:12px;font-weight:700;text-decoration:none;white-space:nowrap;">Clear</a><?php endif; ?>
  <button type="submit" class="btn btn-primary btn-sm">Search</button>
</form>

<?php if(empty($mentors)): ?>
  <div class="card"><div class="card-body" style="text-align:center;padding:48px;color:var(--ink3);">No mentors match your search.</div></div>
<?php else: ?>
<div class="mentor-grid-v3">
<?php foreach($mentors as $idx=>$m):
  $is_elected=$elected && $elected['mentor_id']==$m['id'];
  $is_full=$m['current_students']>=$m['max_students'];
  $cap_pct=min(100,($m['current_students']/$m['max_students'])*100);
  $words=explode(' ',trim($m['name']));
  $init=strtoupper(substr($words[0],0,1).(count($words)>1?substr($words[count($words)-1],0,1):''));
  $bg=$avatar_colors[$idx % count($avatar_colors)];
?>
<div class="mc3 <?=$is_elected?'mc3-elected':''?>">
  <div class="mc3-top" style="background:<?=$bg?>;">
    <!-- Photo with hard circular clip wrapper -->
    <div class="mc3-photo-wrap">
      <?php if(!empty($m['photo_url'])): ?>
        <img src="<?=h($m['photo_url'])?>" alt="<?=h($m['name'])?>" class="mc3-photo" onerror="this.parentElement.innerHTML='<span class=mc3-init-fb><?=$init?></span>'"/>
      <?php else: ?>
        <span class="mc3-init-fb"><?=$init?></span>
      <?php endif; ?>
    </div>
    <div class="mc3-info">
      <div class="mc3-name"><?=h($m['name'])?></div>
      <div class="mc3-desig"><?=h($m['specialization']??'Faculty')?></div>
    </div>
    <?php if($is_elected): ?><div class="mc3-elected-badge">Selected</div><?php endif; ?>
  </div>
  <div class="mc3-body">
    <?php if(!empty($m['quote'])): ?>
      <div class="mc3-quote">&ldquo;<?=h($m['quote'])?>&rdquo;</div>
    <?php endif; ?>
    <div class="mc3-cap-row">
      <span class="mc3-cap-lbl">Students</span>
      <span class="mc3-cap-val"><?=$m['current_students']?> / <?=$m['max_students']?></span>
    </div>
    <div style="height:4px;background:var(--bg3);border-radius:2px;overflow:hidden;">
      <div style="height:100%;width:<?=$cap_pct?>%;background:<?=$cap_pct>=100?'#c0392b':($cap_pct>70?'var(--gold)':'var(--green2)')?>;border-radius:2px;"></div>
    </div>
  </div>
  <div class="mc3-foot">
    <?php
    $meet=get_meet_request($uid,$m['id']);
    ?>
    <?php if($is_elected): ?>
      <a href="my_mentor.php" class="btn btn-green" style="width:100%;justify-content:center;margin-bottom:6px;">Your Elected Mentor</a>
    <?php elseif($elected): ?>
      <button class="btn btn-outline" disabled style="width:100%;justify-content:center;opacity:0.4;cursor:not-allowed;">Another Mentor Elected</button>
    <?php elseif(!$goals): ?>
      <a href="goals.php" class="btn btn-gold" style="width:100%;justify-content:center;">Submit Goals First</a>
    <?php elseif($deadline_passed): ?>
      <button class="btn btn-outline" disabled style="width:100%;justify-content:center;opacity:0.4;cursor:not-allowed;">Deadline Passed</button>
    <?php elseif($is_full): ?>
      <button class="btn btn-outline" disabled style="width:100%;justify-content:center;opacity:0.5;cursor:not-allowed;">At Capacity</button>
    <?php else: ?>
      <form action="../actions/elect_action.php" method="POST" style="width:100%;margin-bottom:6px;" onsubmit="return confirm('Elect <?=addslashes(h($m['name']))?> as your mentor?\n\nThis decision is permanent.')">
        <input type="hidden" name="mentor_id" value="<?=$m['id']?>"/>
        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">Elect Mentor</button>
      </form>
      <!-- Meet Request -->
      <?php if($meet): ?>
        <div style="text-align:center;padding:6px 0;font-size:11px;font-weight:700;
          color:<?=$meet['status']==='accepted'?'var(--green)':($meet['status']==='declined'?'#c0392b':'var(--ink3)')?>;
          text-transform:uppercase;letter-spacing:.5px;">
          Meet Request: <?=ucfirst($meet['status'])?>
          <?php if($meet['mentor_reply']): ?>
            <div style="font-size:11px;font-weight:400;color:var(--ink2);text-transform:none;letter-spacing:0;margin-top:4px;font-style:italic;border-top:1px solid var(--border2);padding-top:6px;">"<?=h(substr($meet['mentor_reply'],0,80)).(strlen($meet['mentor_reply'])>80?'…':'')?>"</div>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <button onclick="openMeetModal(<?=$m['id']?>, '<?=addslashes(h($m['name']))?>')" class="btn btn-outline btn-sm" style="width:100%;justify-content:center;">Request a Meet</button>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<style>
.ab2{padding:12px 18px;border-radius:var(--radius);margin-bottom:18px;font-size:13px;border-left:4px solid;background:white;box-shadow:var(--shadow);}
.ab2-label{font-weight:800;font-size:10.5px;text-transform:uppercase;letter-spacing:.8px;margin-bottom:3px;}
.ab2-body{color:var(--ink2);line-height:1.55;}
.ab2-body a{color:var(--crimson);font-weight:700;text-decoration:none;border-bottom:1px solid currentColor;}
.ab2-info{border-color:#3b82f6;} .ab2-info .ab2-label{color:#1a4f8a;}
.ab2-warning{border-color:var(--gold);} .ab2-warning .ab2-label{color:#7a5a00;}
.ab2-success{border-color:var(--green2);} .ab2-success .ab2-label{color:#0d6640;}
.ab2-danger{border-color:#c0392b;} .ab2-danger .ab2-label{color:#9b1c1c;}

/* Grid */
.mentor-grid-v3{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:14px;}
.mc3{background:white;border:1px solid var(--border2);border-radius:var(--radius2);overflow:hidden;box-shadow:var(--shadow);transition:transform .18s,box-shadow .18s;display:flex;flex-direction:column;}
.mc3:hover{transform:translateY(-3px);box-shadow:var(--shadow2);}
.mc3-elected{border:2px solid var(--gold);}

/* Top header — overflow:hidden is KEY to clip the photo */
.mc3-top{display:flex;align-items:center;gap:13px;padding:16px;color:white;position:relative;overflow:hidden;flex-shrink:0;}

/* Photo wrapper: hard 52×52 circle, clips EVERYTHING inside */
.mc3-photo-wrap{
  width:52px; height:52px;
  min-width:52px; min-height:52px;
  max-width:52px; max-height:52px;
  border-radius:50%;
  overflow:hidden;           /* ← THIS clips the tall portrait */
  border:2px solid rgba(255,255,255,.45);
  background:rgba(255,255,255,.18);
  display:flex; align-items:center; justify-content:center;
  flex-shrink:0;
  position:relative;
  z-index:1;
}
/* The actual img is sized to fill the circle from the top (face area) */
.mc3-photo{
  width:52px;
  height:52px;
  object-fit:cover;
  object-position:center 15%; /* show face, not chest */
  display:block;
  flex-shrink:0;
}
/* Fallback initials inside wrap */
.mc3-init-fb{
  font-family:'Libre Baskerville',serif;font-size:17px;
  font-weight:700;color:white;
  display:flex;align-items:center;justify-content:center;
  width:100%;height:100%;
}

.mc3-info{flex:1;min-width:0;overflow:hidden;}
.mc3-name{font-weight:700;font-size:13.5px;line-height:1.3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.mc3-desig{font-size:11px;opacity:.72;margin-top:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.mc3-elected-badge{position:absolute;top:8px;right:8px;background:var(--gold);color:white;font-size:9px;font-weight:800;letter-spacing:.6px;text-transform:uppercase;padding:3px 7px;border-radius:12px;}

.mc3-body{padding:14px 16px;flex:1;display:flex;flex-direction:column;gap:10px;}
.mc3-quote{font-size:11.5px;color:var(--ink3);font-style:italic;line-height:1.55;border-left:3px solid var(--gold);padding-left:10px;}
.mc3-cap-row{display:flex;justify-content:space-between;margin-bottom:4px;}
.mc3-cap-lbl{font-size:10px;color:var(--ink3);font-weight:700;text-transform:uppercase;letter-spacing:.5px;}
.mc3-cap-val{font-size:11px;color:var(--ink2);font-weight:700;}
.mc3-foot{padding:0 16px 16px;}

@media(max-width:600px){.mentor-grid-v3{grid-template-columns:1fr 1fr;}}
@media(max-width:420px){.mentor-grid-v3{grid-template-columns:1fr;}}
</style>

<!-- Meet Request Modal -->
<div id="meet-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;padding:20px;">
  <div style="background:white;border-radius:12px;max-width:480px;width:100%;padding:28px;box-shadow:0 20px 60px rgba(0,0,0,.25);">
    <div style="font-family:'Libre Baskerville',serif;font-size:18px;color:var(--crimson);margin-bottom:6px;">Request a Meet</div>
    <div id="meet-mentor-name" style="font-size:13px;color:var(--ink3);margin-bottom:18px;"></div>
    <form action="../actions/meet_action.php" method="POST">
      <input type="hidden" name="action" value="request"/>
      <input type="hidden" name="mentor_id" id="meet-mentor-id"/>
      <div class="form-group">
        <label class="form-label">Your Message <span style="font-weight:400;color:var(--ink3);">(Optional)</span></label>
        <textarea name="message" class="form-control" rows="4" placeholder="Introduce yourself and explain what you'd like to discuss before making your decision…"></textarea>
      </div>
      <div style="background:#eef4ff;border-left:3px solid #3b82f6;padding:10px 14px;border-radius:0 6px 6px 0;font-size:12px;color:#1a4f8a;margin-bottom:18px;line-height:1.6;">
        The mentor will receive your request and can accept or decline. You can elect them regardless of the meet outcome.
      </div>
      <div style="display:flex;gap:10px;">
        <button type="button" onclick="closeMeetModal()" class="btn btn-outline">Cancel</button>
        <button type="submit" class="btn btn-primary">Send Request</button>
      </div>
    </form>
  </div>
</div>

<script>
function openMeetModal(id, name){
  document.getElementById('meet-mentor-id').value = id;
  document.getElementById('meet-mentor-name').textContent = 'To: ' + name;
  var m = document.getElementById('meet-modal');
  m.style.display = 'flex';
  document.body.style.overflow = 'hidden';
}
function closeMeetModal(){
  document.getElementById('meet-modal').style.display = 'none';
  document.body.style.overflow = '';
}
document.getElementById('meet-modal').addEventListener('click', function(e){
  if(e.target===this) closeMeetModal();
});
</script>

</div></body></html>
