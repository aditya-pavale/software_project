<?php
require_once '../includes/auth.php';
require_once '../includes/helpers.php';
$role  = current_role();
$name  = current_name();
$roll  = current_roll();
$flash = get_flash();
$parts = explode(' ', trim($name));
$initials2 = strtoupper(substr($parts[0],0,1).(count($parts)>1?substr($parts[count($parts)-1],0,1):''));
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=5.0"/>
<meta name="theme-color" content="#9b1c3c"/>
<meta name="apple-mobile-web-app-capable" content="yes"/>
<title><?=h($page_title??'MentorBridge')?> · myAmrita MentorBridge</title>
<link rel="icon" type="image/png" href="https://upload.wikimedia.org/wikipedia/en/thumb/3/30/Amrita_Vishwa_Vidyapeetham_-_Logo_Icon.svg/32px-Amrita_Vishwa_Vidyapeetham_-_Logo_Icon.svg.png"/>
<link rel="apple-touch-icon" href="https://upload.wikimedia.org/wikipedia/en/thumb/3/30/Amrita_Vishwa_Vidyapeetham_-_Logo_Icon.svg/180px-Amrita_Vishwa_Vidyapeetham_-_Logo_Icon.svg.png"/>
<link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&family=Source+Sans+3:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="assets/css/style.css"/>
</head>
<body>

<div class="top-header">
  <div class="header-logo-area">
    <img src="https://upload.wikimedia.org/wikipedia/en/thumb/3/30/Amrita_Vishwa_Vidyapeetham_-_Logo_Icon.svg/960px-Amrita_Vishwa_Vidyapeetham_-_Logo_Icon.svg.png"
      alt="Amrita" class="header-logo-img" onerror="this.style.display='none'"/>
    <div class="header-logo-divider"></div>
    <div class="header-title">
      <span class="header-title-my">my</span>
      <span class="header-title-amrita">AMRITA</span>
      <span class="header-title-sub">MentorBridge</span>
    </div>
  </div>

  <div class="header-right">
    <div class="header-user-pill">
      <div class="header-avatar"><?=$initials2?></div>
      <div>
        <div class="header-user-name"><?=h($name)?><?=$roll?' <span style="font-size:10px;opacity:.6;margin-left:4px;">'.h($roll).'</span>':''?></div>
        <div class="header-user-role"><?=h($role)?></div>
      </div>
    </div>
    <a href="settings.php" title="Settings" aria-label="Settings" style="padding:6px 10px;">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.8)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
    </a>
    <a href="../actions/logout_action.php">Logout</a>
  </div>
</div>

<div class="breadcrumb">
  <a href="dashboard.php">Home</a>
  <?php if(isset($breadcrumb_extra)): ?>
    <span style="color:var(--border);margin:0 4px;">›</span>
    <span><?=h($breadcrumb_extra)?></span>
  <?php endif; ?>
</div>

<div class="page-wrap">

<?php if($flash): ?>
  <?php $label = ['success'=>'Success','error'=>'Error','info'=>'Notice'][$flash['type']] ?? 'Notice'; ?>
  <div class="flash <?=h($flash['type'])?>" role="alert" id="flash-msg">
    <div class="flash-content">
      <span class="flash-label"><?=$label?></span>
      <span class="flash-msg"><?=h($flash['msg'])?></span>
    </div>
    <button class="flash-close" onclick="this.parentElement.remove()" aria-label="Dismiss">×</button>
  </div>
  <script>setTimeout(function(){var el=document.getElementById('flash-msg');if(el){el.style.transition='opacity .4s';el.style.opacity='0';setTimeout(function(){el.remove();},400);}},6000);</script>
<?php endif; ?>
