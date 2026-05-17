<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/helpers.php';
guard('student');
if($_SERVER['REQUEST_METHOD']!=='POST'){ redirect('/public/mentors.php'); }
$sid=current_user_id();
$mid=(int)($_POST['mentor_id']??0);

if($mid<=0){ set_flash('error','Invalid mentor.'); redirect('/public/mentors.php'); }

$db=get_db();

// Must have submitted goals
$goals=get_student_goals($sid);
if(!$goals){ set_flash('error','Submit your goals first.'); redirect('/public/goals.php'); }

// Already elected?
$existing=get_elected_mentor($sid);
if($existing){ set_flash('error','You have already elected a mentor. This decision is permanent.'); redirect('/public/my_mentor.php'); }

// Check mentor exists and is not full
$m=get_mentor_profile($mid);
if(!$m){ set_flash('error','Mentor not found.'); redirect('/public/mentors.php'); }
if($m['current_students'] >= $m['max_students']){
    set_flash('error','This mentor has reached capacity. Please choose another.');
    redirect('/public/mentors.php');
}

// Check deadline
$cfg=get_semester_config();
if(!empty($cfg['election_deadline']) && strtotime($cfg['election_deadline']) < strtotime('today')){
    set_flash('error','Election deadline has passed.');
    redirect('/public/mentors.php');
}

// Lock the election
try {
    $db->prepare("INSERT INTO mentor_current (student_id,mentor_id) VALUES (?,?)")->execute([$sid,$mid]);
    set_flash('success','Mentor elected successfully! Your goals have been shared with '.$m['name'].'.');
    redirect('/public/my_mentor.php');
} catch (Exception $e){
    set_flash('error','Could not elect mentor. Please try again.');
    redirect('/public/mentors.php');
}
