<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/helpers.php';
guard('student');
if($_SERVER['REQUEST_METHOD']!=='POST'){ redirect('/public/my_mentor.php'); }
$sid=current_user_id();
$mid=(int)($_POST['mentor_id']??0);
$rating=$_POST['rating']??'';
$comment=trim($_POST['comment']??'');
if(!in_array($rating,['up','down'])){ set_flash('error','Please select a rating.'); redirect('/public/my_mentor.php'); }

$db=get_db();
// Verify this is their elected mentor
$elected=get_elected_mentor($sid);
if(!$elected || $elected['mentor_id']!=$mid){ set_flash('error','Invalid mentor.'); redirect('/public/my_mentor.php'); }

// Verify there's a done session
$check=$db->prepare("SELECT COUNT(*) FROM sessions WHERE student_id=? AND mentor_id=? AND status='done'");
$check->execute([$sid,$mid]);
if(!(int)$check->fetchColumn()){ set_flash('error','Feedback only available after a completed session.'); redirect('/public/my_mentor.php'); }

// Already submitted?
$ex=get_student_feedback_for_mentor($sid,$mid);
if($ex){ set_flash('info','Feedback already submitted.'); redirect('/public/my_mentor.php'); }

$db->prepare("INSERT INTO feedback (student_id,mentor_id,rating,comment) VALUES (?,?,?,?)")
   ->execute([$sid,$mid,$rating,$comment]);
set_flash('success','Thank you for your feedback!');
redirect('/public/my_mentor.php');
