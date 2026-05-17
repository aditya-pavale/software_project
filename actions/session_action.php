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
    case 'book':
        if($role!=='student'){ http_response_code(403); die('Forbidden'); }
        $elected=get_elected_mentor($uid);
        if(!$elected){ set_flash('error','Elect a mentor first.'); redirect('/public/mentors.php'); }
        $title=trim($_POST['title']??'');
        $date=$_POST['session_date']??'';
        $time=$_POST['session_time']??'';
        if(!$title||!$date||!$time){ set_flash('error','All fields required.'); redirect('/public/my_mentor.php#sessions'); }
        if(strtotime($date) < strtotime('today')){ set_flash('error','Cannot book sessions in the past.'); redirect('/public/my_mentor.php#sessions'); }
        $db->prepare("INSERT INTO sessions (student_id,mentor_id,title,session_date,session_time,status) VALUES (?,?,?,?,?,'upcoming')")
           ->execute([$uid,$elected['mentor_id'],$title,$date,$time]);
        set_flash('success','Session booked successfully.');
        redirect('/public/my_mentor.php#sessions');
        break;

    case 'mark_done':
        if($role!=='mentor'){ http_response_code(403); die('Forbidden'); }
        $sess_id=(int)($_POST['session_id']??0);
        $notes=trim($_POST['notes']??'');
        // Verify session belongs to this mentor
        $st=$db->prepare("SELECT id FROM sessions WHERE id=? AND mentor_id=?");
        $st->execute([$sess_id,$uid]);
        if(!$st->fetch()){ set_flash('error','Session not found.'); redirect('/public/mentor_sessions.php'); }
        $db->prepare("UPDATE sessions SET status='done',notes=? WHERE id=? AND mentor_id=?")->execute([$notes,$sess_id,$uid]);
        set_flash('success','Session marked as completed.');
        redirect('/public/mentor_sessions.php');
        break;

    case 'cancel':
        $sess_id=(int)($_POST['session_id']??0);
        if($role==='student'){
            $st=$db->prepare("SELECT id FROM sessions WHERE id=? AND student_id=?");
            $st->execute([$sess_id,$uid]);
            if(!$st->fetch()){ http_response_code(403); die('Forbidden'); }
            $db->prepare("UPDATE sessions SET status='cancelled' WHERE id=? AND student_id=?")->execute([$sess_id,$uid]);
            set_flash('info','Session cancelled.');
            redirect('/public/my_mentor.php#sessions');
        } elseif($role==='mentor'){
            $st=$db->prepare("SELECT id FROM sessions WHERE id=? AND mentor_id=?");
            $st->execute([$sess_id,$uid]);
            if(!$st->fetch()){ http_response_code(403); die('Forbidden'); }
            $db->prepare("UPDATE sessions SET status='cancelled' WHERE id=? AND mentor_id=?")->execute([$sess_id,$uid]);
            set_flash('info','Session cancelled.');
            redirect('/public/mentor_sessions.php');
        } else {
            http_response_code(403); die('Forbidden');
        }
        break;

    default:
        set_flash('error','Unknown action.');
        redirect('/public/dashboard.php');
}
