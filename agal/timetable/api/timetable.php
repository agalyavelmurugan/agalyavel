<?php
/**
 * api/timetable.php
 * Save / Load / Delete timetable allocations
 * Uses: annamalai_timetable DB → timetables table
 *
 * GET    ?class=X&prog=Y&sem_type=Z  → load one timetable
 * GET    (no params)                 → list all saved timetables
 * POST                               → save / upsert timetable
 * DELETE ?class=X&prog=Y&sem_type=Z  → delete one timetable
 */

require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    jsonResponse(['ok' => true]);
}

$method = $_SERVER['REQUEST_METHOD'];
$db     = getDB();

// Ensure all columns exist (safe to run every time)
$db->query("ALTER TABLE `timetables`
    ADD COLUMN IF NOT EXISTS `faculty_list`   LONGTEXT NULL AFTER `tt_data`,
    ADD COLUMN IF NOT EXISTS `completed_subs` TEXT     NULL AFTER `faculty_list`
");

// ── GET ──────────────────────────────────────────────────────
if ($method === 'GET') {

    if (!empty($_GET['class'])) {
        // Load single class timetable
        $prog    = $_GET['prog']     ?? '';
        $cls     = $_GET['class']    ?? '';
        $semType = $_GET['sem_type'] ?? '';

        $stmt = $db->prepare(
            "SELECT * FROM timetables
             WHERE programme=? AND class_name=? AND sem_type=?
             LIMIT 1"
        );
        $stmt->bind_param('sss', $prog, $cls, $semType);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if ($row) {
            jsonResponse([
                'success' => true,
                'data'    => [
                    'tt_data'        => json_decode($row['tt_data'],        true) ?? [],
                    'faculty_list'   => json_decode($row['faculty_list']   ?? '[]', true) ?? [],
                    'completed_subs' => json_decode($row['completed_subs'] ?? '[]', true) ?? [],
                    'saved_at'       => $row['updated_at'],
                ]
            ]);
        } else {
            jsonResponse(['success' => false, 'message' => 'No timetable found']);
        }
    }

    // List all saved timetables (summary for dashboard)
    $res  = $db->query(
        "SELECT programme, class_name, sem_type, updated_at AS saved_at
         FROM timetables
         ORDER BY programme, class_name"
    );
    $rows = [];
    while ($r = $res->fetch_assoc()) $rows[] = $r;
    jsonResponse(['success' => true, 'data' => $rows]);
}

// ── POST — Save / Upsert ─────────────────────────────────────
if ($method === 'POST') {
    $raw = json_decode(file_get_contents('php://input'), true);
    if (!$raw) {
        jsonResponse(['success' => false, 'message' => 'Invalid JSON body'], 400);
    }

    $prog     = trim($raw['programme']  ?? '');
    $cls      = trim($raw['class_name'] ?? '');
    $semType  = trim($raw['sem_type']   ?? '');

    if (!$prog || !$cls || !$semType) {
        jsonResponse(['success' => false, 'message' => 'programme, class_name and sem_type are required'], 400);
    }

    $ttData   = json_encode($raw['tt_data']        ?? []);
    $facList  = json_encode($raw['faculty_list']   ?? []);
    $compSubs = json_encode($raw['completed_subs'] ?? []);

    $stmt = $db->prepare(
        "INSERT INTO timetables
             (programme, class_name, sem_type, tt_data, faculty_list, completed_subs)
         VALUES (?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
             tt_data        = VALUES(tt_data),
             faculty_list   = VALUES(faculty_list),
             completed_subs = VALUES(completed_subs),
             updated_at     = CURRENT_TIMESTAMP"
    );
    $stmt->bind_param('ssssss', $prog, $cls, $semType, $ttData, $facList, $compSubs);

    if ($stmt->execute()) {
        jsonResponse(['success' => true, 'message' => "Saved: $cls ($semType)"]);
    } else {
        jsonResponse(['success' => false, 'message' => $db->error], 500);
    }
}

// ── DELETE ───────────────────────────────────────────────────
if ($method === 'DELETE') {
    $prog    = $_GET['prog']     ?? '';
    $cls     = $_GET['class']    ?? '';
    $semType = $_GET['sem_type'] ?? '';

    $stmt = $db->prepare(
        "DELETE FROM timetables
         WHERE programme=? AND class_name=? AND sem_type=?"
    );
    $stmt->bind_param('sss', $prog, $cls, $semType);
    $stmt->execute();

    jsonResponse(['success' => true, 'message' => 'Timetable deleted']);
}

jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);