<?php
require_once __DIR__.'/config.php';
require_once __DIR__.'/db.php';

function h(string $s):string{ return htmlspecialchars($s,ENT_QUOTES,'UTF-8'); }

function redirect(string $u):void{
    if($u!==''&&$u[0]==='/'&&strpos($u,'//')!==0){ $u=BASE_URL.$u; }
    header("Location: $u"); exit;
}

function set_flash(string $type,string $msg):void{ $_SESSION['flash']=['type'=>$type,'msg'=>$msg]; }
function get_flash():?array{
    if(isset($_SESSION['flash'])){ $f=$_SESSION['flash']; unset($_SESSION['flash']); return $f; }
    return null;
}

function get_all_mentors():array{
    $db=get_db();
    return $db->query("
        SELECT u.id,u.name,mp.specialization,mp.quote,mp.max_students,mp.department AS mentor_dept,mp.photo_url,
               COUNT(mc.id) AS current_students
        FROM users u JOIN mentor_profiles mp ON mp.user_id=u.id
        LEFT JOIN mentor_current mc ON mc.mentor_id=u.id
        WHERE u.role='mentor'
        GROUP BY u.id ORDER BY u.name
    ")->fetchAll();
}

function get_mentor_profile(int $mid):?array{
    $db=get_db();
    $st=$db->prepare("
        SELECT u.id,u.name,u.email,mp.specialization,mp.quote,mp.max_students,mp.department,mp.photo_url,
               (SELECT COUNT(*) FROM mentor_current WHERE mentor_id=u.id) AS current_students
        FROM users u JOIN mentor_profiles mp ON mp.user_id=u.id
        WHERE u.id=? AND u.role='mentor'
    ");
    $st->execute([$mid]);
    $r=$st->fetch();
    return $r?:null;
}

function get_student_goals(int $sid):?array{
    $db=get_db();
    $st=$db->prepare("SELECT * FROM student_goals WHERE student_id=?");
    $st->execute([$sid]);
    $r=$st->fetch();
    return $r?:null;
}

function get_elected_mentor(int $sid):?array{
    $db=get_db();
    $st=$db->prepare("
        SELECT mc.mentor_id,u.name,u.email,mp.specialization,mp.quote,mp.photo_url,mc.locked_at
        FROM mentor_current mc
        JOIN users u ON u.id=mc.mentor_id
        JOIN mentor_profiles mp ON mp.user_id=u.id
        WHERE mc.student_id=?
    ");
    $st->execute([$sid]);
    $r=$st->fetch();
    return $r?:null;
}

function get_student_sessions(int $sid):array{
    $db=get_db();
    $st=$db->prepare("
        SELECT s.*,u.name AS mentor_name
        FROM sessions s JOIN users u ON u.id=s.mentor_id
        WHERE s.student_id=?
        ORDER BY s.session_date DESC,s.session_time DESC
    ");
    $st->execute([$sid]);
    return $st->fetchAll();
}

function get_mentor_students(int $mid):array{
    $db=get_db();
    $st=$db->prepare("
        SELECT u.id,u.name,u.email,sp.department,sp.roll_number,sg.academic_goals,sg.challenges,sg.expectations,sg.vision,sg.skills_develop,
               (SELECT COUNT(*) FROM sessions WHERE student_id=u.id AND mentor_id=mc.mentor_id AND status='upcoming') AS upcoming_count,
               (SELECT COUNT(*) FROM sessions WHERE student_id=u.id AND mentor_id=mc.mentor_id AND status='done') AS done_count
        FROM mentor_current mc
        JOIN users u ON u.id=mc.student_id
        LEFT JOIN student_profiles sp ON sp.user_id=u.id
        LEFT JOIN student_goals sg ON sg.student_id=u.id
        WHERE mc.mentor_id=?
        ORDER BY u.name
    ");
    $st->execute([$mid]);
    return $st->fetchAll();
}

function get_mentor_sessions(int $mid):array{
    $db=get_db();
    $st=$db->prepare("
        SELECT s.*,u.name AS student_name,sp.roll_number
        FROM sessions s
        JOIN users u ON u.id=s.student_id
        LEFT JOIN student_profiles sp ON sp.user_id=u.id
        WHERE s.mentor_id=?
        ORDER BY s.session_date DESC,s.session_time DESC
    ");
    $st->execute([$mid]);
    return $st->fetchAll();
}

function get_student_feedback_for_mentor(int $sid, int $mid):?array{
    $db=get_db();
    $st=$db->prepare("SELECT * FROM feedback WHERE student_id=? AND mentor_id=? ORDER BY id DESC LIMIT 1");
    $st->execute([$sid,$mid]);
    $r=$st->fetch();
    return $r?:null;
}

function get_mentor_feedback(int $mid):array{
    $db=get_db();
    $st=$db->prepare("
        SELECT f.*,u.name AS student_name,sp.roll_number
        FROM feedback f
        JOIN users u ON u.id=f.student_id
        LEFT JOIN student_profiles sp ON sp.user_id=u.id
        WHERE f.mentor_id=?
        ORDER BY f.submitted_at DESC
    ");
    $st->execute([$mid]);
    return $st->fetchAll();
}

function get_semester_config():?array{
    $db=get_db();
    $r=$db->query("SELECT * FROM semester_config WHERE id=1")->fetch();
    return $r?:null;
}

function days_until(string $date):?int{
    if(!$date) return null;
    $d1=new DateTime('today'); $d2=new DateTime($date);
    return (int)$d1->diff($d2)->format('%r%a');
}

function log_error(string $msg):void{
    $logFile = __DIR__.'/../logs/error.log';
    if(!is_writable(dirname($logFile))){ $logFile = sys_get_temp_dir().'/mentorbridge_error.log'; }
    @file_put_contents($logFile, date('[Y-m-d H:i:s]')." $msg\n", FILE_APPEND);
}

function get_meet_requests_for_mentor(int $mid):array{
    $db=get_db();
    $st=$db->prepare("
        SELECT mr.*,u.name AS student_name,sp.roll_number,sp.department
        FROM meet_requests mr
        JOIN users u ON u.id=mr.student_id
        LEFT JOIN student_profiles sp ON sp.user_id=mr.student_id
        WHERE mr.mentor_id=?
        ORDER BY mr.created_at DESC
    ");
    $st->execute([$mid]);
    return $st->fetchAll();
}

function get_meet_request(int $sid, int $mid):?array{
    $db=get_db();
    $st=$db->prepare("SELECT * FROM meet_requests WHERE student_id=? AND mentor_id=?");
    $st->execute([$sid,$mid]);
    $r=$st->fetch();
    return $r?:null;
}
