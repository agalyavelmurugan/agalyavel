<?php
/**
 * api/faculty.php
 * CRUD for Faculty
 * GET    /api/faculty.php         → list all
 * GET    /api/faculty.php?id=1    → single
 * POST   /api/faculty.php         → create
 * PUT    /api/faculty.php?id=1    → update
 * DELETE /api/faculty.php?id=1    → delete
 */

require_once __DIR__ . '/../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];
$db     = getDB();

switch ($method) {

    // ── LIST / GET ONE ──────────────────────────────────────
    case 'GET':
        if (isset($_GET['id'])) {
            $id   = (int)$_GET['id'];
            $stmt = $db->prepare("SELECT * FROM faculty WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $row  = $stmt->get_result()->fetch_assoc();
            if (!$row) jsonResponse(['success'=>false,'message'=>'Faculty not found'], 404);
            jsonResponse(['success'=>true,'data'=>sanitizeFaculty($row)]);
        }

        // Also return assigned hours per faculty
        $sql = "SELECT f.*,
                    COALESCE(SUM(s.hours_per_week),0) AS assigned_hours
                FROM faculty f
                LEFT JOIN subjects s ON s.faculty_id = f.id
                GROUP BY f.id
                ORDER BY f.id";
        $result = $db->query($sql);
        $rows   = [];
        while ($r = $result->fetch_assoc()) {
            $rows[] = sanitizeFaculty($r);
        }
        jsonResponse(['success'=>true,'data'=>$rows,'count'=>count($rows)]);

    // ── CREATE ──────────────────────────────────────────────
    case 'POST':
        $body = json_decode(file_get_contents('php://input'), true);
        validateFacultyInput($body);

        $stmt = $db->prepare(
            "INSERT INTO faculty (name, designation, specialization, max_hours)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param('sssi',
            $body['name'], $body['designation'],
            $body['specialization'], $body['max_hours']
        );
        if (!$stmt->execute()) {
            jsonResponse(['success'=>false,'message'=>'Insert failed: '.$db->error], 500);
        }
        $newId = $db->insert_id;
        $newRow = $db->query("SELECT * FROM faculty WHERE id = $newId")->fetch_assoc();
        jsonResponse(['success'=>true,'message'=>'Faculty added successfully','data'=>sanitizeFaculty($newRow)], 201);

    // ── UPDATE ──────────────────────────────────────────────
    case 'PUT':
        if (empty($_GET['id'])) jsonResponse(['success'=>false,'message'=>'ID required'], 400);
        $id   = (int)$_GET['id'];
        $body = json_decode(file_get_contents('php://input'), true);
        validateFacultyInput($body);

        $stmt = $db->prepare(
            "UPDATE faculty SET name=?, designation=?, specialization=?, max_hours=?
             WHERE id=?"
        );
        $stmt->bind_param('sssii',
            $body['name'], $body['designation'],
            $body['specialization'], $body['max_hours'], $id
        );
        if (!$stmt->execute()) jsonResponse(['success'=>false,'message'=>'Update failed'], 500);
        if ($stmt->affected_rows === 0) jsonResponse(['success'=>false,'message'=>'Faculty not found'], 404);
        jsonResponse(['success'=>true,'message'=>'Faculty updated successfully']);

    // ── DELETE ──────────────────────────────────────────────
    case 'DELETE':
        if (empty($_GET['id'])) jsonResponse(['success'=>false,'message'=>'ID required'], 400);
        $id = (int)$_GET['id'];

        // Check if faculty has subjects
        $check = $db->prepare("SELECT COUNT(*) AS cnt FROM subjects WHERE faculty_id = ?");
        $check->bind_param('i', $id);
        $check->execute();
        $cnt = $check->get_result()->fetch_assoc()['cnt'];
        if ($cnt > 0) {
            jsonResponse(['success'=>false,'message'=>"Cannot delete: faculty has $cnt subject(s) assigned. Reassign subjects first."], 409);
        }

        $stmt = $db->prepare("DELETE FROM faculty WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        if ($stmt->affected_rows === 0) jsonResponse(['success'=>false,'message'=>'Faculty not found'], 404);
        jsonResponse(['success'=>true,'message'=>'Faculty deleted successfully']);

    default:
        jsonResponse(['success'=>false,'message'=>'Method not allowed'], 405);
}

// ── Helpers ─────────────────────────────────────────────────
function sanitizeFaculty(array $row): array {
    return [
        'id'             => (int)$row['id'],
        'name'           => $row['name'],
        'designation'    => $row['designation'],
        'specialization' => $row['specialization'] ?? '',
        'max_hours'      => (int)$row['max_hours'],
        'assigned_hours' => isset($row['assigned_hours']) ? (int)$row['assigned_hours'] : null,
        'created_at'     => $row['created_at'] ?? null,
    ];
}

function validateFacultyInput(?array $body): void {
    if (!$body || empty($body['name'])) {
        jsonResponse(['success'=>false,'message'=>'Faculty name is required'], 422);
    }
    $allowed = ['Professor & Head','Professor','Associate Professor','Assistant Professor','Lab Instructor','Guest Faculty'];
    if (!in_array($body['designation'] ?? '', $allowed)) {
        jsonResponse(['success'=>false,'message'=>'Invalid designation'], 422);
    }
    $body['max_hours'] = isset($body['max_hours']) ? (int)$body['max_hours'] : 16;
    if ($body['max_hours'] < 4 || $body['max_hours'] > 30) {
        jsonResponse(['success'=>false,'message'=>'max_hours must be between 4 and 30'], 422);
    }
}
