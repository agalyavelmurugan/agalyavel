<?php
/**
 * api/dashboard.php
 * Returns summary statistics for the dashboard
 * GET /api/dashboard.php
 */

require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['success'=>false,'message'=>'Method not allowed'], 405);
}

$db = getDB();

// Faculty count + hours summary
$facResult = $db->query(
    "SELECT f.id, f.name, f.designation, f.specialization, f.max_hours,
            COALESCE(SUM(s.hours_per_week),0) AS assigned_hours,
            COUNT(s.id) AS subject_count
     FROM faculty f
     LEFT JOIN subjects s ON s.faculty_id = f.id
     GROUP BY f.id
     ORDER BY f.id"
);
$faculty = [];
while ($r = $facResult->fetch_assoc()) {
    $faculty[] = [
        'id'             => (int)$r['id'],
        'name'           => $r['name'],
        'designation'    => $r['designation'],
        'specialization' => $r['specialization'],
        'max_hours'      => (int)$r['max_hours'],
        'assigned_hours' => (int)$r['assigned_hours'],
        'subject_count'  => (int)$r['subject_count'],
    ];
}

// Subject counts
$subStats = $db->query(
    "SELECT
        COUNT(*) AS total,
        SUM(programme='UG') AS ug_count,
        SUM(programme='PG') AS pg_count,
        SUM(subject_type='Lab') AS lab_count,
        SUM(subject_type='Theory') AS theory_count,
        SUM(sem_type='Odd') AS odd_count,
        SUM(sem_type='Even') AS even_count
     FROM subjects"
)->fetch_assoc();

// Timetable count
$ttCount = (int)$db->query("SELECT COUNT(*) AS cnt FROM timetables")->fetch_assoc()['cnt'];

// Class-wise subject summary
$classes = [
    ['cls'=>'I B.Sc',  'prog'=>'UG', 'oddSem'=>'I',   'evenSem'=>'II'],
    ['cls'=>'II B.Sc', 'prog'=>'UG', 'oddSem'=>'III',  'evenSem'=>'IV'],
    ['cls'=>'III B.Sc','prog'=>'UG', 'oddSem'=>'V',    'evenSem'=>'VI'],
    ['cls'=>'I M.Sc',  'prog'=>'PG', 'oddSem'=>'I',   'evenSem'=>'II'],
    ['cls'=>'II M.Sc', 'prog'=>'PG', 'oddSem'=>'III',  'evenSem'=>'IV'],
];

$classSummary = [];
foreach ($classes as $c) {
    $stmt = $db->prepare(
        "SELECT sem_type, COUNT(*) AS cnt FROM subjects
         WHERE programme=? AND semester=? GROUP BY sem_type"
    );
    // Odd
    $stmt->bind_param('ss', $c['prog'], $c['oddSem']);
    $stmt->execute();
    $odd = $stmt->get_result()->fetch_assoc();

    $stmt->bind_param('ss', $c['prog'], $c['evenSem']);
    $stmt->execute();
    $even = $stmt->get_result()->fetch_assoc();

    // check tt
    $ttStmt = $db->prepare(
        "SELECT sem_type FROM timetables WHERE programme=? AND class_name=?"
    );
    $ttStmt->bind_param('ss', $c['prog'], $c['cls']);
    $ttStmt->execute();
    $ttRes = $ttStmt->get_result();
    $hasTT = ['Odd'=>false,'Even'=>false];
    while ($tr = $ttRes->fetch_assoc()) { $hasTT[$tr['sem_type']] = true; }

    $classSummary[] = [
        'class'            => $c['cls'],
        'programme'        => $c['prog'],
        'odd_sem'          => $c['oddSem'],
        'even_sem'         => $c['evenSem'],
        'odd_subject_count'  => $odd  ? (int)$odd['cnt']  : 0,
        'even_subject_count' => $even ? (int)$even['cnt'] : 0,
        'has_tt_odd'         => $hasTT['Odd'],
        'has_tt_even'        => $hasTT['Even'],
    ];
}

jsonResponse([
    'success' => true,
    'data'    => [
        'faculty_count'    => count($faculty),
        'subject_count'    => (int)$subStats['total'],
        'ug_subject_count' => (int)$subStats['ug_count'],
        'pg_subject_count' => (int)$subStats['pg_count'],
        'lab_count'        => (int)$subStats['lab_count'],
        'theory_count'     => (int)$subStats['theory_count'],
        'timetable_count'  => $ttCount,
        'faculty'          => $faculty,
        'class_summary'    => $classSummary,
    ]
]);
