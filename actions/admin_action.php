<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/helpers.php';
guard('admin');
if($_SERVER['REQUEST_METHOD']!=='POST'){ redirect('/public/admin.php'); }
$db=get_db();
$action=$_POST['action']??'';

switch($action){
    case 'unmap_student':
        $sid=(int)($_POST['student_id']??0);
        $db->prepare("DELETE FROM mentor_current WHERE student_id=?")->execute([$sid]);
        set_flash('success','Mentor mapping removed.');
        break;
    case 'delete_user':
        $uid=(int)($_POST['user_id']??0);
        $db->prepare("DELETE FROM users WHERE id=? AND role!='admin'")->execute([$uid]);
        set_flash('success','User deleted.');
        break;
    case 'reset_password':
        $uid=(int)($_POST['user_id']??0);
        $new_pw=trim($_POST['new_password']??'amma');
        if(strlen($new_pw)<3){ set_flash('error','Password too short.'); break; }
        $hash=password_hash($new_pw,PASSWORD_BCRYPT);
        $db->prepare("UPDATE users SET password=? WHERE id=?")->execute([$hash,$uid]);
        set_flash('success','Password reset successfully.');
        break;
    case 'update_config':
        $db->prepare("UPDATE semester_config SET semester_name=?,goals_deadline=?,election_deadline=? WHERE id=1")
           ->execute([$_POST['semester_name'],$_POST['goals_deadline'],$_POST['election_deadline']]);
        set_flash('success','Configuration saved.');
        break;
    default:
        set_flash('error','Unknown action.');
}
redirect('/public/admin.php');
