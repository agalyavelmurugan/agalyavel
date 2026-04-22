<?php
/**
 * api/subjects.php — CRUD for Subjects (faculty_id nullable)
 */
require_once __DIR__ . '/../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];
$db     = getDB();

switch ($method) {

    case 'GET':
        if (isset($_GET['id'])) {
            $id   = (int)$_GET['id'];
            $stmt = $db->prepare(
                "SELECT s.*, f.name AS faculty_name, f.designation AS faculty_desig
                 FROM subjects s LEFT JOIN faculty f ON f.id = s.faculty_id WHERE s.id = ?"
            );
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            if (!$row) jsonResponse(['success'=>false,'message'=>'Subject not found'], 404);
            jsonResponse(['success'=>true,'data'=>sanitizeSubject($row)]);
        }
        $where=[]; $params=[]; $types='';
        if (!empty($_GET['prog']))       { $where[]='s.programme=?';   $params[]=$_GET['prog'];         $types.='s'; }
        if (!empty($_GET['sem']))        { $where[]='s.semester=?';    $params[]=$_GET['sem'];          $types.='s'; }
        if (!empty($_GET['sem_type']))   { $where[]='s.sem_type=?';    $params[]=$_GET['sem_type'];     $types.='s'; }
        if (!empty($_GET['faculty_id'])) { $where[]='s.faculty_id=?'; $params[]=(int)$_GET['faculty_id']; $types.='i'; }
        if (!empty($_GET['type']))       { $where[]='s.subject_type=?';$params[]=$_GET['type'];         $types.='s'; }

        $sql = "SELECT s.*, f.name AS faculty_name, f.designation AS faculty_desig
                FROM subjects s LEFT JOIN faculty f ON f.id = s.faculty_id"
             . ($where ? ' WHERE '.implode(' AND ',$where) : '')
             . ' ORDER BY s.programme, s.semester, s.sem_type, s.id';
        $stmt = $db->prepare($sql);
        if ($params) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res=$stmt->get_result(); $rows=[];
        while ($r=$res->fetch_assoc()) $rows[]=sanitizeSubject($r);
        jsonResponse(['success'=>true,'data'=>$rows,'count'=>count($rows)]);

    case 'POST':
        $body = json_decode(file_get_contents('php://input'), true);
        validateSubjectInput($body, $db);
        $facId = !empty($body['faculty_id']) ? (int)$body['faculty_id'] : null;
        $stmt  = $db->prepare(
            "INSERT INTO subjects (name,code,programme,semester,sem_type,subject_type,hours_per_week,faculty_id,notes)
             VALUES (?,?,?,?,?,?,?,?,?)"
        );
        $n=$body['name']; $c=$body['code']??''; $pr=$body['programme'];
        $sm=$body['semester']; $st=$body['sem_type']; $sbt=$body['subject_type'];
        $h=(int)$body['hours_per_week']; $nt=$body['notes']??'';
        $stmt->bind_param('ssssssiis',$n,$c,$pr,$sm,$st,$sbt,$h,$facId,$nt);
        if (!$stmt->execute()) jsonResponse(['success'=>false,'message'=>'Insert failed: '.$db->error],500);
        $newId=$db->insert_id;
        $newRow=$db->query("SELECT s.*,f.name AS faculty_name FROM subjects s LEFT JOIN faculty f ON f.id=s.faculty_id WHERE s.id=$newId")->fetch_assoc();
        jsonResponse(['success'=>true,'message'=>'Subject added successfully','data'=>sanitizeSubject($newRow)],201);

    case 'PUT':
        if (empty($_GET['id'])) jsonResponse(['success'=>false,'message'=>'ID required'],400);
        $id=$_GET['id']; $body=json_decode(file_get_contents('php://input'),true);
        validateSubjectInput($body,$db);
        $facId=!empty($body['faculty_id'])?(int)$body['faculty_id']:null;
        $n=$body['name']; $c=$body['code']??''; $pr=$body['programme'];
        $sm=$body['semester']; $st=$body['sem_type']; $sbt=$body['subject_type'];
        $h=(int)$body['hours_per_week']; $nt=$body['notes']??'';
        $stmt=$db->prepare("UPDATE subjects SET name=?,code=?,programme=?,semester=?,sem_type=?,subject_type=?,hours_per_week=?,faculty_id=?,notes=? WHERE id=?");
        $stmt->bind_param('ssssssiisi',$n,$c,$pr,$sm,$st,$sbt,$h,$facId,$nt,$id);
        if (!$stmt->execute()) jsonResponse(['success'=>false,'message'=>'Update failed: '.$db->error],500);
        jsonResponse(['success'=>true,'message'=>'Subject updated successfully']);

    case 'DELETE':
        if (empty($_GET['id'])) jsonResponse(['success'=>false,'message'=>'ID required'],400);
        $id=(int)$_GET['id'];
        $stmt=$db->prepare("DELETE FROM subjects WHERE id=?");
        $stmt->bind_param('i',$id); $stmt->execute();
        if ($stmt->affected_rows===0) jsonResponse(['success'=>false,'message'=>'Subject not found'],404);
        jsonResponse(['success'=>true,'message'=>'Subject deleted successfully']);

    default:
        jsonResponse(['success'=>false,'message'=>'Method not allowed'],405);
}

function sanitizeSubject(array $row): array {
    return [
        'id'             => (int)$row['id'],
        'name'           => $row['name'],
        'code'           => $row['code'] ?? '',
        'programme'      => $row['programme'],
        'semester'       => $row['semester'],
        'sem_type'       => $row['sem_type'],
        'subject_type'   => $row['subject_type'],
        'hours_per_week' => (int)$row['hours_per_week'],
        'faculty_id'     => isset($row['faculty_id']) ? (int)$row['faculty_id'] : null,
        'faculty_name'   => $row['faculty_name'] ?? '',
        'faculty_desig'  => $row['faculty_desig'] ?? '',
        'notes'          => $row['notes'] ?? '',
        'created_at'     => $row['created_at'] ?? null,
    ];
}

function validateSubjectInput(?array $body, $db): void {
    if (!$body || empty($body['name']))
        jsonResponse(['success'=>false,'message'=>'Subject name is required'],422);
    if (empty($body['programme']) || !in_array($body['programme'],['UG','PG']))
        jsonResponse(['success'=>false,'message'=>'Programme must be UG or PG'],422);
    if (empty($body['semester']) || !in_array($body['semester'],['I','II','III','IV','V','VI']))
        jsonResponse(['success'=>false,'message'=>'Invalid semester'],422);
    if (empty($body['sem_type']) || !in_array($body['sem_type'],['Odd','Even']))
        jsonResponse(['success'=>false,'message'=>'sem_type must be Odd or Even'],422);
    if (empty($body['subject_type']) || !in_array($body['subject_type'],['Theory','Lab']))
        jsonResponse(['success'=>false,'message'=>'subject_type must be Theory or Lab'],422);
    $body['hours_per_week']=isset($body['hours_per_week'])?(int)$body['hours_per_week']:5;
    if ($body['hours_per_week']<1||$body['hours_per_week']>12)
        jsonResponse(['success'=>false,'message'=>'hours_per_week must be 1-12'],422);
    // faculty is optional
    if (!empty($body['faculty_id'])) {
        $fid=(int)$body['faculty_id'];
        $chk=$db->prepare("SELECT id FROM faculty WHERE id=?");
        $chk->bind_param('i',$fid); $chk->execute();
        if (!$chk->get_result()->fetch_assoc())
            jsonResponse(['success'=>false,'message'=>'Selected faculty does not exist'],422);
    }
    $body['code']=$body['code']??'';
    $body['notes']=$body['notes']??'';
}
