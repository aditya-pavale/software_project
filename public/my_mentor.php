<?php
require_once '../includes/auth.php';
require_once '../includes/helpers.php';
guard('student');
$uid=current_user_id();
$elected=get_elected_mentor($uid);
if(!$elected){ set_flash('info','You have not elected a mentor yet.'); redirect('/public/mentors.php'); }
$db=get_db();
$mid=$elected['mentor_id'];
$goals=get_student_goals($uid);
$sessions=get_student_sessions($uid);
$upcoming=array_filter($sessions,fn($s)=>$s['status']==='upcoming');
$done_sessions=array_filter($sessions,fn($s)=>$s['status']==='done');
$existing_feedback=get_student_feedback_for_mentor($uid,$mid);
$can_feedback=count($done_sessions)>0 && !$existing_feedback;
$init=strtoupper(substr(implode('',array_map(fn($w)=>$w[0],explode(' ',$elected['name']))),0,2));

$page_title='My Mentor';
$breadcrumb_extra='My Mentor';
include '_layout.php';
?>

<div class="welcome-bar">My Mentor</div>

<!-- Mentor hero -->
<div class="card">
  <div class="card-body" style="display:flex;align-items:center;gap:18px;flex-wrap:wrap;">
    <div style="width:80px;height:80px;border-radius:50%;flex-shrink:0;border:3px solid var(--gold);overflow:hidden;background:var(--crimson);position:relative;">
      <?php if(!empty($elected['photo_url'])): ?>
        <img src="<?=h($elected['photo_url'])?>" alt="<?=h($elected['name'])?>" style="width:80px;height:80px;min-width:80px;object-fit:cover;object-position:center 15%;display:block;" onerror="this.style.display='none';document.getElementById('mfb').style.display='flex';"/>
        <div id="mfb" style="display:none;position:absolute;inset:0;align-items:center;justify-content:center;font-family:'Libre Baskerville',serif;font-size:26px;color:white;font-weight:700;"><?=$init?></div>
      <?php else: ?>
        <div style="display:flex;width:100%;height:100%;align-items:center;justify-content:center;font-family:'Libre Baskerville',serif;font-size:26px;color:white;font-weight:700;"><?=$init?></div>
      <?php endif; ?>
    </div>
    <div style="flex:1;min-width:200px;">
      <div style="font-family:'Libre Baskerville',serif;font-size:20px;color:var(--crimson);font-weight:700;"><?=h($elected['name'])?></div>
      <div style="font-size:13px;color:var(--ink3);margin-top:3px;"><?=h($elected['specialization']??'Faculty')?></div>
      <div style="font-size:12px;color:var(--ink3);margin-top:3px;font-variant-numeric:tabular-nums;"><?=h($elected['email'])?></div>
      <?php if(!empty($elected['quote'])): ?>
        <div style="font-size:12px;color:var(--ink2);margin-top:8px;font-style:italic;border-left:3px solid var(--gold);padding-left:10px;">&ldquo;<?=h($elected['quote'])?>&rdquo;</div>
      <?php endif; ?>
    </div>
    <div style="display:flex;flex-direction:column;gap:6px;flex-shrink:0;align-items:flex-end;">
      <span class="status-badge success">Elected</span>
      <span style="font-size:11px;color:var(--ink3);">Since <?=date('d M Y',strtotime($elected['locked_at']))?></span>
    </div>
  </div>
</div>

<div class="tab-bar">
  <button class="tab-btn active" onclick="showTab('sessions',event)">Sessions (<?=count($sessions)?>)</button>
  <button class="tab-btn" onclick="showTab('goals',event)">My Goals</button>
  <button class="tab-btn" onclick="showTab('feedback',event)">Feedback</button>
</div>

<!-- Sessions -->
<div id="tab-sessions" class="section active">
  <div class="card">
    <div class="card-header"><div class="card-header-title">Book a New Session</div></div>
    <div class="card-body">
      <form action="../actions/session_action.php" method="POST">
        <input type="hidden" name="action" value="book"/>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" placeholder="e.g. First check-in" required maxlength="200"/>
          </div>
          <div class="form-group">
            <label class="form-label">Date</label>
            <input type="date" name="session_date" class="form-control" required min="<?=date('Y-m-d')?>"/>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Time</label>
            <input type="time" name="session_time" class="form-control" required/>
          </div>
          <div class="form-group" style="display:flex;align-items:flex-end;">
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">Book Session</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <?php if(!empty($upcoming)): ?>
  <div class="card">
    <div class="card-header"><div class="card-header-title">Upcoming (<?=count($upcoming)?>)</div></div>
    <div class="card-body" style="padding:0;">
      <?php foreach($upcoming as $s): $d=strtotime($s['session_date']); ?>
        <div style="display:flex;align-items:center;gap:14px;padding:14px 18px;border-bottom:1px solid var(--bg3);flex-wrap:wrap;">
          <div class="session-date-box"><div class="day"><?=date('d',$d)?></div><div class="month"><?=date('M',$d)?></div></div>
          <div style="flex:1;min-width:120px;">
            <div style="font-weight:600;font-size:14px;"><?=h($s['title']??'Session')?></div>
            <div style="font-size:11px;color:var(--ink3);"><?=date('l',$d)?> at <?=substr($s['session_time'],0,5)?></div>
          </div>
          <span class="status-badge pending">Upcoming</span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <?php if(!empty($done_sessions)): ?>
  <div class="card">
    <div class="card-header"><div class="card-header-title">Completed (<?=count($done_sessions)?>)</div></div>
    <div class="card-body" style="padding:0;">
      <?php foreach($done_sessions as $s): $d=strtotime($s['session_date']); ?>
        <div style="display:flex;align-items:center;gap:14px;padding:14px 18px;border-bottom:1px solid var(--bg3);flex-wrap:wrap;">
          <div class="session-date-box" style="background:var(--green);"><div class="day"><?=date('d',$d)?></div><div class="month"><?=date('M',$d)?></div></div>
          <div style="flex:1;min-width:120px;">
            <div style="font-weight:600;font-size:14px;"><?=h($s['title']??'Session')?></div>
            <div style="font-size:11px;color:var(--ink3);"><?=date('l',$d)?> at <?=substr($s['session_time'],0,5)?></div>
            <?php if($s['notes']): ?><div style="font-size:12px;color:var(--ink2);margin-top:5px;font-style:italic;border-left:2px solid var(--gold);padding-left:8px;"><?=h($s['notes'])?></div><?php endif; ?>
          </div>
          <span class="status-badge success">Completed</span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<!-- Goals -->
<div id="tab-goals" class="section">
  <div class="alert-banner-v2 info">
    <div>
      <div class="ab-label">Read-Only</div>
      <div class="ab-body">These goals were shared with <strong><?=h($elected['name'])?></strong> when you elected them.</div>
    </div>
  </div>
  <?php if($goals): $qs=[
      'academic_goals'=>['1','Academic goals this semester'],
      'challenges'=>['2','Current challenges you are facing'],
      'expectations'=>['3','What you expect from your mentor'],
      'vision'=>['4','Your vision for end of this semester'],
      'skills_develop'=>['5','Skills you want to develop'],
  ]; foreach($qs as $k=>$q): ?>
    <div class="card">
      <div class="card-body">
        <div style="font-size:10.5px;font-weight:800;color:var(--ink3);text-transform:uppercase;letter-spacing:0.8px;margin-bottom:6px;">Question <?=$q[0]?></div>
        <div style="font-family:'Libre Baskerville',serif;font-size:14px;color:var(--ink);margin-bottom:10px;"><?=h($q[1])?></div>
        <div style="background:var(--bg3);border-left:3px solid var(--crimson);padding:12px 14px;font-size:13px;color:var(--ink2);line-height:1.7;border-radius:0 var(--radius) var(--radius) 0;"><?=nl2br(h($goals[$k]??'—'))?></div>
      </div>
    </div>
  <?php endforeach; endif; ?>
</div>

<!-- Feedback -->
<div id="tab-feedback" class="section">
  <?php if($existing_feedback): ?>
    <div class="card">
      <div class="card-header"><div class="card-header-title">Your Feedback</div></div>
      <div class="card-body">
        <div style="margin-bottom:14px;">
          <span class="status-badge <?=$existing_feedback['rating']==='up'?'success':'danger'?>" style="font-size:12px;padding:5px 14px;">
            <?=$existing_feedback['rating']==='up'?'Positive Experience':'Needs Improvement'?>
          </span>
          <span style="font-size:11px;color:var(--ink3);margin-left:10px;">Submitted on <?=date('d M Y',strtotime($existing_feedback['submitted_at']))?></span>
        </div>
        <?php if($existing_feedback['comment']): ?>
          <div style="background:var(--bg3);border-left:3px solid var(--gold);padding:12px 14px;font-size:13px;color:var(--ink2);font-style:italic;line-height:1.7;border-radius:0 var(--radius) var(--radius) 0;">
            &ldquo;<?=h($existing_feedback['comment'])?>&rdquo;
          </div>
        <?php endif; ?>
      </div>
    </div>
  <?php elseif($can_feedback): ?>
    <div class="card">
      <div class="card-header"><div class="card-header-title">Rate Your Experience</div></div>
      <div class="card-body">
        <p style="font-size:13px;color:var(--ink3);margin-bottom:14px;line-height:1.6;">You have completed a session. Share your experience &mdash; feedback is permanent and helps improve mentorship.</p>
        <form action="../actions/feedback_action.php" method="POST">
          <input type="hidden" name="mentor_id" value="<?=$mid?>"/>
          <div class="form-group">
            <div class="form-label">Overall Experience</div>
            <div class="rating-btns">
              <button type="button" id="btn-up" class="rating-btn" onclick="setRating('up')">Positive</button>
              <button type="button" id="btn-down" class="rating-btn" onclick="setRating('down')">Needs Improvement</button>
            </div>
            <input type="hidden" name="rating" id="rating-val" required/>
          </div>
          <div class="form-group">
            <label class="form-label">Comments <span style="font-weight:400;color:var(--ink3);">(Optional)</span></label>
            <textarea name="comment" class="form-control" placeholder="What worked well? What could be improved?" rows="4"></textarea>
          </div>
          <button type="submit" id="submit-fb" class="btn btn-primary" disabled>Submit Feedback</button>
        </form>
      </div>
    </div>
  <?php else: ?>
    <div class="card">
      <div class="card-body" style="text-align:center;padding:48px 24px;color:var(--ink3);">
        <div style="font-family:'Libre Baskerville',serif;font-size:18px;color:var(--ink2);margin-bottom:8px;">Feedback Pending</div>
        <div style="font-size:13px;max-width:380px;margin:0 auto;line-height:1.7;">You will be able to submit feedback once you have at least one session that your mentor has marked as completed.</div>
      </div>
    </div>
  <?php endif; ?>
</div>

<script>
function showTab(name,e){
  document.querySelectorAll('.section').forEach(s=>s.classList.remove('active'));
  document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
  document.getElementById('tab-'+name).classList.add('active');
  e.target.classList.add('active');
}
function setRating(v){
  document.getElementById('rating-val').value=v;
  document.getElementById('btn-up').className='rating-btn'+(v==='up'?' up':'');
  document.getElementById('btn-down').className='rating-btn'+(v==='down'?' down':'');
  document.getElementById('submit-fb').disabled=false;
}
</script>

</div></body></html>
