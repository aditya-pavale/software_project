<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/helpers.php';
if(is_logged_in()) redirect('/public/dashboard.php');
$flash = get_flash();
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=5.0"/>
<meta name="theme-color" content="#9b1c3c"/>
<title>MentorBridge · myAmrita</title>
<link rel="icon" type="image/png" href="https://upload.wikimedia.org/wikipedia/en/thumb/3/30/Amrita_Vishwa_Vidyapeetham_-_Logo_Icon.svg/32px-Amrita_Vishwa_Vidyapeetham_-_Logo_Icon.svg.png"/>
<link rel="apple-touch-icon" href="https://upload.wikimedia.org/wikipedia/en/thumb/3/30/Amrita_Vishwa_Vidyapeetham_-_Logo_Icon.svg/180px-Amrita_Vishwa_Vidyapeetham_-_Logo_Icon.svg.png"/>
<link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&family=Source+Sans+3:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html{height:100%;}
body{
  font-family:'Source Sans 3',system-ui,sans-serif;
  min-height:100vh; display:flex;
  background:#1a0a10;
  -webkit-font-smoothing:antialiased;
}

/* SPLIT LAYOUT */
.login-wrap{
  display:grid;
  grid-template-columns:1fr 440px;
  min-height:100vh; width:100%;
}

/* LEFT PANEL */
.login-left{
  background:linear-gradient(160deg,#5a0d20 0%,#9b1c3c 50%,#7a1530 100%);
  display:flex; flex-direction:column;
  align-items:flex-start; justify-content:space-between;
  padding:56px 60px;
  position:relative; overflow:hidden;
}
.login-left::before{
  content:''; position:absolute; inset:0;
  background:
    radial-gradient(circle at 15% 85%, rgba(229,168,18,.12) 0%,transparent 55%),
    radial-gradient(circle at 85% 15%, rgba(255,255,255,.04) 0%,transparent 45%);
  pointer-events:none;
}
.login-left::after{
  content:''; position:absolute; bottom:-40px; right:-40px;
  width:340px; height:340px;
  background:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='340' height='340'%3E%3Cg opacity='0.06' stroke='%23ffffff' stroke-width='1' fill='none'%3E%3Ccircle cx='170' cy='170' r='150'/%3E%3Ccircle cx='170' cy='170' r='110'/%3E%3Ccircle cx='170' cy='170' r='70'/%3E%3Ccircle cx='170' cy='170' r='30'/%3E%3C/g%3E%3C/svg%3E")
    no-repeat bottom right;
  pointer-events:none;
}

.login-brand{position:relative;z-index:1;}
.login-brand-logo{
  width:60px; height:60px;
  background:rgba(255,255,255,.08);
  border:1.5px solid rgba(255,255,255,.18);
  border-radius:12px;
  display:flex; align-items:center; justify-content:center;
  margin-bottom:32px; padding:10px;
}
.login-brand-logo img{width:100%;height:100%;object-fit:contain;filter:brightness(0) invert(1);opacity:.9;}

.login-brand-row{display:flex;align-items:baseline;gap:14px;margin-bottom:8px;}
.login-brand-my{
  font-family:'Libre Baskerville',serif;
  font-style:italic; font-size:38px; color:#f5c842; line-height:1;
}
.login-brand-amrita{
  font-family:'Libre Baskerville',serif;
  font-size:48px; font-weight:700; color:white;
  letter-spacing:3px; line-height:1;
}
.login-brand-divider{
  width:60px; height:2px;
  background:linear-gradient(90deg,#e5a812,transparent);
  border-radius:1px; margin:20px 0 22px;
}
.login-brand-tagline{
  font-size:12px; color:rgba(255,255,255,.5);
  letter-spacing:3px; text-transform:uppercase;
  font-weight:600;
}

.login-features{
  display:flex; flex-direction:column; gap:14px;
  position:relative; z-index:1; margin-top:48px;
}
.login-feature{
  padding:14px 18px;
  background:rgba(255,255,255,.05);
  border:1px solid rgba(255,255,255,.1);
  border-radius:8px;
  border-left:3px solid #e5a812;
}
.login-feature-title{font-size:12px;font-weight:700;color:rgba(255,255,255,.92);margin-bottom:4px;letter-spacing:0.4px;text-transform:uppercase;}
.login-feature-sub{font-size:12.5px;color:rgba(255,255,255,.55);line-height:1.55;}

.login-left-quote{position:relative;z-index:1;}
.login-left-quote blockquote{
  font-family:'Libre Baskerville',serif;
  font-style:italic; font-size:13.5px;
  color:rgba(255,255,255,.5);
  line-height:1.8;
  border-left:2px solid rgba(229,168,18,.4);
  padding-left:18px; margin-bottom:10px;
}
.login-left-quote cite{font-size:10.5px;color:rgba(255,255,255,.3);letter-spacing:1.2px;text-transform:uppercase;font-weight:600;font-style:normal;}

/* RIGHT PANEL */
.login-right{
  background:white;
  display:flex; flex-direction:column; justify-content:center;
  padding:56px 48px;
  position:relative;
}
.login-right::before{
  content:''; position:absolute; top:0; left:0; right:0; height:4px;
  background:linear-gradient(90deg,#9b1c3c 0%,#e5a812 50%,#9b1c3c 100%);
}

.login-form-header{margin-bottom:36px;}
.login-form-title{
  font-family:'Libre Baskerville',serif;
  font-size:28px; font-weight:700; color:#1a1a1a; margin-bottom:10px;
}
.login-form-sub{font-size:13.5px;color:#7a7a7a;line-height:1.65;}

.lf-group{margin-bottom:22px;position:relative;}
.lf-label{
  display:block; font-size:11px; font-weight:800;
  color:#3d3d3d; text-transform:uppercase; letter-spacing:0.9px;
  margin-bottom:8px;
}
.lf-input-wrap{position:relative;}
.lf-input{
  width:100%; padding:13px 16px;
  border:1.5px solid #e0dbd4; border-radius:8px;
  font-size:14px; font-family:'Source Sans 3',sans-serif;
  color:#1a1a1a; background:#fafaf9;
  transition:border-color .15s,box-shadow .15s,background .15s;
  outline:none; -webkit-appearance:none;
}
.lf-input:focus{
  border-color:#9b1c3c; background:white;
  box-shadow:0 0 0 3px rgba(155,28,60,.08);
}
.lf-input::placeholder{color:#c0bab3;}
.lf-toggle-pw{
  position:absolute; right:14px; top:50%; transform:translateY(-50%);
  background:none; border:none; cursor:pointer; color:#9a9a9a; padding:4px;
  font-size:11px; font-weight:700; letter-spacing:0.5px; text-transform:uppercase;
  font-family:inherit;
}
.lf-toggle-pw:hover { color: #9b1c3c; }

.lf-submit{
  width:100%; padding:15px;
  background:linear-gradient(135deg,#b52248,#9b1c3c);
  color:white; border:none; border-radius:8px;
  font-size:13px; font-weight:700; font-family:'Source Sans 3',sans-serif;
  letter-spacing:1.3px; text-transform:uppercase;
  cursor:pointer;
  box-shadow:0 4px 16px rgba(155,28,60,.3);
  transition:all .2s; margin-top:8px;
  -webkit-tap-highlight-color:transparent;
}
.lf-submit:hover{
  background:linear-gradient(135deg,#9b1c3c,#7a1530);
  box-shadow:0 6px 22px rgba(155,28,60,.4);
  transform:translateY(-1px);
}
.lf-submit:active{transform:translateY(0);}

.lf-flash{
  padding:14px 18px; border-radius:8px; margin-bottom:22px;
  font-size:13px; font-weight:500; border-left:4px solid;
}
.lf-flash.error{background:#fdf2f2;color:#9b1c1c;border-color:#e53e3e;}
.lf-flash.success{background:#f0faf5;color:#0d6640;border-color:#22a060;}
.lf-flash .lf-label{font-size:10.5px;font-weight:800;text-transform:uppercase;letter-spacing:0.8px;margin-bottom:2px;display:block;opacity:0.75;}

.login-footer{
  margin-top:44px; padding-top:24px;
  border-top:1px solid #f0ece8; text-align:center;
}
.login-footer p{font-size:11px;color:#c0bab3;letter-spacing:.4px;line-height:1.8;}

/* RESPONSIVE */
@media(max-width:780px){
  .login-wrap{grid-template-columns:1fr;}
  .login-left{display:none;}
  .login-right{padding:60px 28px 40px;min-height:100vh;justify-content:flex-start;}
  .login-right::before{height:3px;}
  .login-right::after{
    content:''; display:block;
    width:52px; height:52px;
    background:url('https://upload.wikimedia.org/wikipedia/en/thumb/3/30/Amrita_Vishwa_Vidyapeetham_-_Logo_Icon.svg/96px-Amrita_Vishwa_Vidyapeetham_-_Logo_Icon.svg.png') center/contain no-repeat;
    margin:0 auto 24px;
  }
  .login-form-title{font-size:24px;}
}
@media(max-width:480px){
  .login-right{padding:50px 20px 30px;}
  .lf-input{ font-size:16px !important; }
}
</style>
</head>
<body>
<div class="login-wrap">

  <div class="login-left">
    <div class="login-brand">
      <div class="login-brand-logo">
        <img src="https://upload.wikimedia.org/wikipedia/en/thumb/3/30/Amrita_Vishwa_Vidyapeetham_-_Logo_Icon.svg/960px-Amrita_Vishwa_Vidyapeetham_-_Logo_Icon.svg.png"
          alt="Amrita" onerror="this.style.display='none'"/>
      </div>
      <div class="login-brand-row">
        <span class="login-brand-my">my</span>
        <span class="login-brand-amrita">AMRITA</span>
      </div>
      <div class="login-brand-divider"></div>
      <div class="login-brand-tagline">MentorBridge &nbsp;&middot;&nbsp; Counselling Portal</div>

      <div class="login-features">
        <div class="login-feature">
          <div class="login-feature-title">Discover Mentors</div>
          <div class="login-feature-sub">Explore and connect with faculty mentors across specializations</div>
        </div>
        <div class="login-feature">
          <div class="login-feature-title">Share Your Goals</div>
          <div class="login-feature-sub">Submit your academic goals once before choosing your mentor</div>
        </div>
        <div class="login-feature">
          <div class="login-feature-title">Manage Sessions</div>
          <div class="login-feature-sub">Book, track, and review your counselling sessions</div>
        </div>
      </div>
    </div>

    <div class="login-left-quote">
      <blockquote>
        Youngsters need to understand the real purpose of life. They need courage and wisdom to face the challenges of life.
      </blockquote>
      <cite>Mata Amritanandamayi Devi</cite>
    </div>
  </div>

  <div class="login-right">
    <div class="login-form-header">
      <div class="login-form-title">Welcome back</div>
      <div class="login-form-sub">Sign in with your Amrita student or faculty credentials to access MentorBridge.</div>
    </div>

    <?php if($flash): ?>
      <div class="lf-flash <?=h($flash['type'])?>">
        <span class="lf-label"><?=$flash['type']==='error'?'Error':'Notice'?></span>
        <?=h($flash['msg'])?>
      </div>
    <?php endif; ?>

    <form action="../actions/login_action.php" method="POST" autocomplete="on">
      <div class="lf-group">
        <label class="lf-label" for="lf-email">Email Address</label>
        <div class="lf-input-wrap">
          <input type="email" id="lf-email" name="email" class="lf-input"
            placeholder="bl.en.u4cse23101@bl.students.amrita.edu"
            required autofocus autocomplete="email" inputmode="email"/>
        </div>
      </div>
      <div class="lf-group">
        <label class="lf-label" for="lf-pass">Password</label>
        <div class="lf-input-wrap">
          <input type="password" id="lf-pass" name="password" class="lf-input"
            placeholder="Enter your password" required autocomplete="current-password"/>
          <button type="button" class="lf-toggle-pw" onclick="togglePw()" aria-label="Toggle password visibility">Show</button>
        </div>
      </div>
      <button type="submit" class="lf-submit">Sign In</button>
    </form>

    <div class="login-footer">
      <p>Amrita Vishwa Vidyapeetham &nbsp;&middot;&nbsp; Mentorship &amp; Counselling Division</p>
      <p style="margin-top:6px;font-size:10.5px;color:#d0cac4;">Contact your administrator if you cannot sign in</p>
    </div>
  </div>

</div>
<script>
function togglePw(){
  var inp = document.getElementById('lf-pass');
  var btn = document.querySelector('.lf-toggle-pw');
  if(inp.type==='password'){ inp.type='text'; btn.textContent='Hide'; }
  else { inp.type='password'; btn.textContent='Show'; }
}
</script>
</body>
</html>
