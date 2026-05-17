<?php
require_once '../includes/auth.php';
require_once '../includes/helpers.php';
guard(['student','mentor','admin']);
$uid = current_user_id();
$role = current_role();
$db = get_db();
$user_st = $db->prepare("SELECT * FROM users WHERE id=?");
$user_st->execute([$uid]); $u = $user_st->fetch();

$pw_error = '';
if(isset($_POST['change_password'])){
    $cur  = $_POST['current_password'] ?? '';
    $new  = $_POST['new_password'] ?? '';
    $conf = $_POST['confirm_password'] ?? '';
    if(!$cur || !$new || !$conf){
        $pw_error = 'All fields are required.';
    } elseif(!password_verify($cur, $u['password'])){
        $pw_error = 'Current password is incorrect.';
    } elseif(strlen($new) < 4){
        $pw_error = 'New password must be at least 4 characters.';
    } elseif($new !== $conf){
        $pw_error = 'New passwords do not match.';
    } else {
        $hash = password_hash($new, PASSWORD_BCRYPT);
        $db->prepare("UPDATE users SET password=? WHERE id=?")->execute([$hash, $uid]);
        set_flash('success', 'Password updated successfully.');
        redirect('/public/settings.php');
    }
}

$page_title = 'Settings';
$breadcrumb_extra = 'Settings';
include '_layout.php';
?>

<div class="welcome-bar">Settings</div>

<div class="two-col">

  <div class="card">
    <div class="card-header"><div class="card-header-title">Change Password</div></div>
    <div class="card-body">
      <?php if($pw_error): ?>
        <div class="alert-banner-v2 danger" style="margin-bottom:14px;">
          <div><div class="ab-label">Error</div><div class="ab-body"><?=h($pw_error)?></div></div>
        </div>
      <?php endif; ?>
      <form method="POST">
        <div class="form-group">
          <label class="form-label">Current Password</label>
          <input type="password" name="current_password" class="form-control" placeholder="Enter current password" required autocomplete="current-password"/>
        </div>
        <div class="form-group">
          <label class="form-label">New Password</label>
          <input type="password" name="new_password" class="form-control" placeholder="Minimum 4 characters" required autocomplete="new-password" oninput="checkStrength(this.value)"/>
          <div id="pw-strength" style="height:4px;border-radius:2px;margin-top:8px;background:var(--bg3);overflow:hidden;">
            <div id="pw-fill" style="height:100%;width:0;transition:width .3s,background .3s;border-radius:2px;"></div>
          </div>
          <div id="pw-label" class="form-hint" style="margin-top:4px;"></div>
        </div>
        <div class="form-group">
          <label class="form-label">Confirm New Password</label>
          <input type="password" name="confirm_password" class="form-control" placeholder="Re-enter new password" required autocomplete="new-password"/>
        </div>
        <button type="submit" name="change_password" class="btn btn-primary">Update Password</button>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><div class="card-header-title">Account Information</div></div>
    <div class="card-body">
      <table style="font-size:13px;width:100%;">
        <tr><td style="color:var(--ink3);padding:8px 0;font-weight:700;width:130px;">Full Name</td><td><?=h($u['name']??'')?></td></tr>
        <tr><td style="color:var(--ink3);padding:8px 0;font-weight:700;">Email</td><td style="word-break:break-all;font-variant-numeric:tabular-nums;"><?=h($u['email']??'')?></td></tr>
        <tr><td style="color:var(--ink3);padding:8px 0;font-weight:700;">Role</td><td><span class="status-badge success"><?=ucfirst(h($role))?></span></td></tr>
        <tr><td style="color:var(--ink3);padding:8px 0;font-weight:700;">Member Since</td><td><?=date('d M Y',strtotime($u['created_at']??'now'))?></td></tr>
        <?php if($role==='student'): $sp=$db->prepare("SELECT * FROM student_profiles WHERE user_id=?"); $sp->execute([$uid]); $spr=$sp->fetch();
          if($spr): ?>
        <tr><td style="color:var(--ink3);padding:8px 0;font-weight:700;">Roll No.</td><td><?=h($spr['roll_number']??'—')?></td></tr>
        <tr><td style="color:var(--ink3);padding:8px 0;font-weight:700;">Department</td><td><?=h($spr['department']??'—')?></td></tr>
        <?php endif; endif; ?>
      </table>
    </div>
  </div>

</div>

<script>
function checkStrength(v){
  var fill = document.getElementById('pw-fill'), lbl = document.getElementById('pw-label');
  if(!v){ fill.style.width='0'; lbl.textContent=''; return; }
  var score = 0;
  if(v.length >= 4) score++;
  if(v.length >= 8) score++;
  if(/[A-Z]/.test(v)) score++;
  if(/[0-9]/.test(v)) score++;
  if(/[^a-zA-Z0-9]/.test(v)) score++;
  var colors = ['','#e53e3e','#e59a3e','#e5c43e','#22a060','#0d6640'];
  var labels = ['','Weak','Fair','Good','Strong','Very Strong'];
  fill.style.width = (score*20)+'%';
  fill.style.background = colors[score];
  lbl.textContent = labels[score];
  lbl.style.color = colors[score];
}
</script>

</div></body></html>
