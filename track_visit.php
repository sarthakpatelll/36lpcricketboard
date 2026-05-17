<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

ensureAnalyticsTables($pdo);

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    $data = $_POST;
}

$action = $data['action'] ?? '';

try {
    if ($action === 'start') {
        $visitorId = trim((string)($data['visitor_id'] ?? ''));
        if ($visitorId === '') {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => 'visitor_id is required']);
            exit;
        }

        $pageUrl = trim((string)($data['page_url'] ?? ''));
        $pageUrl = substr($pageUrl, 0, 255);

        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM website_visits WHERE visitor_id = ?");
        $checkStmt->execute([$visitorId]);
        $isNewVisitor = ((int)$checkStmt->fetchColumn() === 0) ? 1 : 0;

        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

        $insertStmt = $pdo->prepare("
            INSERT INTO website_visits (visitor_id, page_url, user_agent, ip_address, is_new_visitor)
            VALUES (?, ?, ?, ?, ?)
        ");
        $insertStmt->execute([$visitorId, $pageUrl, $userAgent, $ipAddress, $isNewVisitor]);

        echo json_encode([
            'success' => true,
            'visit_id' => (int)$pdo->lastInsertId(),
            'is_new_visitor' => (bool)$isNewVisitor
        ]);
        exit;
    }

    if ($action === 'ping' || $action === 'end') {
        $visitId = (int)($data['visit_id'] ?? 0);
        if ($visitId <= 0) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => 'visit_id is required']);
            exit;
        }

        if ($action === 'ping') {
            $updateStmt = $pdo->prepare("
                UPDATE website_visits
                SET duration_seconds = GREATEST(TIMESTAMPDIFF(SECOND, started_at, NOW()), 0)
                WHERE id = ? AND ended_at IS NULL
            ");
            $updateStmt->execute([$visitId]);
            echo json_encode(['success' => true]);
            exit;
        }

        $endStmt = $pdo->prepare("
            UPDATE website_visits
            SET ended_at = NOW(),
                duration_seconds = GREATEST(TIMESTAMPDIFF(SECOND, started_at, NOW()), 0)
            WHERE id = ? AND ended_at IS NULL
        ");
        $endStmt->execute([$visitId]);

        echo json_encode(['success' => true]);
        exit;
    }

    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Invalid action']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Tracking error']);
}
?>
