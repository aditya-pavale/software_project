<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/helpers.php';
guard('student');
if($_SERVER['REQUEST_METHOD']!=='POST'){ redirect('/public/goals.php'); }
$sid=current_user_id();
$db=get_db();

// Block if already elected
$elected=get_elected_mentor($sid);
if($elected){ set_flash('error','Goals cannot be edited after mentor election.'); redirect('/public/goals.php'); }

$fields=['academic_goals','challenges','expectations','vision','skills_develop'];
$vals=[];
foreach($fields as $f){
    $v=trim($_POST[$f]??'');
    if($v===''){ set_flash('error','All fields are required.'); redirect('/public/goals.php'); }
    $vals[$f]=$v;
}

$existing=get_student_goals($sid);
if($existing){
    $db->prepare("UPDATE student_goals SET academic_goals=?,challenges=?,expectations=?,vision=?,skills_develop=? WHERE student_id=?")
       ->execute([$vals['academic_goals'],$vals['challenges'],$vals['expectations'],$vals['vision'],$vals['skills_develop'],$sid]);
    set_flash('success','Goals updated successfully.');
} else {
    $db->prepare("INSERT INTO student_goals (student_id,academic_goals,challenges,expectations,vision,skills_develop) VALUES (?,?,?,?,?,?)")
       ->execute([$sid,$vals['academic_goals'],$vals['challenges'],$vals['expectations'],$vals['vision'],$vals['skills_develop']]);
    set_flash('success','Goals saved! Now browse mentors and elect one.');
}
redirect('/public/mentors.php');
