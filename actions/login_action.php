<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/helpers.php';

if($_SERVER['REQUEST_METHOD']!=='POST'){ redirect('/public/login.php'); }
$email=trim($_POST['email']??'');
$pass=$_POST['password']??'';
if(!$email||!$pass){ set_flash('error','Email and password required.'); redirect('/public/login.php'); }

$db=get_db();
$st=$db->prepare("SELECT id,name,password,role FROM users WHERE email=?");
$st->execute([$email]);
$u=$st->fetch();
if(!$u||!password_verify($pass,$u['password'])){ 
    set_flash('error','Invalid email or password.'); 
    redirect('/public/login.php'); 
}

// Regenerate session ID for security (prevents session fixation)
session_regenerate_id(true);

$_SESSION['user_id']=$u['id'];
$_SESSION['role']=$u['role'];
$_SESSION['name']=$u['name'];
$_SESSION['login_time']=time();

if($u['role']==='student'){
    $rs=$db->prepare("SELECT roll_number FROM student_profiles WHERE user_id=?");
    $rs->execute([$u['id']]);
    $rrow=$rs->fetch();
    $_SESSION['roll']=$rrow['roll_number']??'';
}

// Route based on role
switch($u['role']){
    case 'admin':  redirect('/public/admin.php'); break;
    case 'mentor': redirect('/public/dashboard.php'); break;
    default:       redirect('/public/dashboard.php'); break;
}
