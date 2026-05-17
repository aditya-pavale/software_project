<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/helpers.php';

if($_SERVER['REQUEST_METHOD']!=='POST'){ redirect('/public/dashboard.php'); }
$db=get_db();
$uid=current_user_id();
$role=current_role();
$action=$_POST['action']??'';

switch($action){

    // Student requests a meet with a mentor
    case 'request':
        guard('student');
        $mid=(int)($_POST['mentor_id']??0);
        $message=trim($_POST['message']??'');
        if($mid<=0){ set_flash('error','Invalid mentor.'); redirect('/public/mentors.php'); }
        // Already elected?
        $elected=get_elected_mentor($uid);
        if($elected){ set_flash('info','You have already elected a mentor.'); redirect('/public/my_mentor.php'); }
        // Already requested?
        $existing=get_meet_request($uid,$mid);
        if($existing){ set_flash('info','You already have a meet request with this mentor.'); redirect('/public/mentors.php'); }
        // Mentor must exist
        $m=$db->prepare("SELECT id,name FROM users WHERE id=? AND role='mentor'");
        $m->execute([$mid]); $mentor=$m->fetch();
        if(!$mentor){ set_flash('error','Mentor not found.'); redirect('/public/mentors.php'); }
        $db->prepare("INSERT INTO meet_requests (student_id,mentor_id,message) VALUES (?,?,?)")
           ->execute([$uid,$mid,$message]);
        set_flash('success','Meet request sent to '.$mentor['name'].'. They will respond shortly.');
        redirect('/public/mentors.php');
        break;

    // Mentor accepts or declines a meet request
    case 'respond':
        guard('mentor');
        $req_id=(int)($_POST['request_id']??0);
        $status=$_POST['status']??'';
        $reply=trim($_POST['reply']??'');
        if(!in_array($status,['accepted','declined'])){ set_flash('error','Invalid status.'); redirect('/public/meet_requests.php'); }
        // Verify this request belongs to this mentor
        $req=$db->prepare("SELECT * FROM meet_requests WHERE id=? AND mentor_id=?");
        $req->execute([$req_id,$uid]); $row=$req->fetch();
        if(!$row){ set_flash('error','Request not found.'); redirect('/public/meet_requests.php'); }
        $db->prepare("UPDATE meet_requests SET status=?,mentor_reply=? WHERE id=?")
           ->execute([$status,$reply,$req_id]);
        set_flash('success','Response sent to student.');
        redirect('/public/meet_requests.php');
        break;

    default:
        redirect('/public/dashboard.php');
}
