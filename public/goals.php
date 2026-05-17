<?php
require_once '../includes/auth.php';
require_once '../includes/helpers.php';
guard('student');
$uid=current_user_id();
$existing=get_student_goals($uid);
$elected=get_elected_mentor($uid);
$page_title='My Goals';
$breadcrumb_extra='My Goals';
include '_layout.php';
?>

<div class="welcome-bar">My Goals &amp; Expectations</div>

<?php if($elected): ?>
  <div class="alert-banner-v2 success">
    <div>
      <div class="ab-label">Locked</div>
      <div class="ab-body">Your goals have been shared with your elected mentor, <strong><?=h($elected['name'])?></strong>. They are now read-only.</div>
    </div>
  </div>
<?php elseif($existing): ?>
  <div class="alert-banner-v2 info">
    <div>
      <div class="ab-label">Saved</div>
      <div class="ab-body">Your goals are saved. You can update them anytime before electing a mentor.</div>
    </div>
  </div>
<?php else: ?>
  <div class="alert-banner-v2 info">
    <div>
      <div class="ab-label">Before You Begin</div>
      <div class="ab-body">Fill out these five short questions once. When you elect a mentor, your goals will be shared with them.</div>
    </div>
  </div>
<?php endif; ?>

<div class="card">
  <div class="card-header"><div class="card-header-title">Goal Form</div></div>
  <div class="card-body">
    <form action="../actions/goals_action.php" method="POST">
      <div class="form-group">
        <label class="form-label">1 &nbsp;·&nbsp; Academic goals this semester</label>
        <textarea name="academic_goals" class="form-control" rows="3" placeholder="What grades, projects, or milestones are you aiming for?" <?=$elected?'readonly':''?> required><?=h($existing['academic_goals']??'')?></textarea>
      </div>
      <div class="form-group">
        <label class="form-label">2 &nbsp;·&nbsp; Current challenges you are facing</label>
        <textarea name="challenges" class="form-control" rows="3" placeholder="Time management, specific subjects, balance with extracurriculars, etc." <?=$elected?'readonly':''?> required><?=h($existing['challenges']??'')?></textarea>
      </div>
      <div class="form-group">
        <label class="form-label">3 &nbsp;·&nbsp; What you expect from your mentor</label>
        <textarea name="expectations" class="form-control" rows="3" placeholder="Career advice, regular check-ins, technical guidance, etc." <?=$elected?'readonly':''?> required><?=h($existing['expectations']??'')?></textarea>
      </div>
      <div class="form-group">
        <label class="form-label">4 &nbsp;·&nbsp; Your vision for the end of this semester</label>
        <textarea name="vision" class="form-control" rows="3" placeholder="Where do you see yourself at the end of this term?" <?=$elected?'readonly':''?> required><?=h($existing['vision']??'')?></textarea>
      </div>
      <div class="form-group">
        <label class="form-label">5 &nbsp;·&nbsp; Skills you want to develop</label>
        <textarea name="skills_develop" class="form-control" rows="3" placeholder="Leadership, public speaking, technical skills, etc." <?=$elected?'readonly':''?> required><?=h($existing['skills_develop']??'')?></textarea>
      </div>
      <?php if(!$elected): ?>
      <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:8px;">
        <a href="dashboard.php" class="btn btn-outline">Cancel</a>
        <button type="submit" class="btn btn-primary"><?=$existing?'Update Goals':'Save Goals'?></button>
      </div>
      <?php else: ?>
      <div style="margin-top:8px;">
        <a href="my_mentor.php" class="btn btn-outline">Back to My Mentor</a>
      </div>
      <?php endif; ?>
    </form>
  </div>
</div>

</div></body></html>
