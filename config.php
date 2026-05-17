<?php
session_start();
require_once __DIR__ . '/config/database.php';

define('SITE_URL', 'http://localhost/cricket_tournament/');

function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}

function getActiveSponsors($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM sponsors WHERE status = 1 AND show_in_footer = 1 ORDER BY id DESC");
    $stmt->execute();
    return $stmt->fetchAll();
}

function getActivePopupAds($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM sponsors WHERE status = 1 AND show_in_popup = 1 AND image_path IS NOT NULL AND image_path <> '' ORDER BY id ASC");
    $stmt->execute();
    return $stmt->fetchAll();
}

function tableExists($pdo, $tableName) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM information_schema.TABLES 
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?
    ");
    $stmt->execute([$tableName]);
    return (int)$stmt->fetchColumn() > 0;
}

function ensureSponsorsTable($pdo) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS sponsors (
            id INT AUTO_INCREMENT PRIMARY KEY,
            sponsor_text VARCHAR(255) DEFAULT NULL,
            image_path VARCHAR(255) DEFAULT NULL,
            status TINYINT(1) NOT NULL DEFAULT 1,
            show_in_footer TINYINT(1) NOT NULL DEFAULT 1,
            show_in_popup TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
}

function ensureGroupStandingsTable($pdo) {
    if (tableExists($pdo, 'group_standings')) {
        return;
    }

    $hasGroupsTable = tableExists($pdo, 'groups');
    $hasTeamsTable = tableExists($pdo, 'teams');

    $createSql = "
        CREATE TABLE IF NOT EXISTS group_standings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            group_id INT NOT NULL,
            team_id INT NOT NULL,
            position INT DEFAULT NULL,
            `matches` INT DEFAULT 0,
            won INT DEFAULT 0,
            lost INT DEFAULT 0,
            drawn INT DEFAULT 0,
            tied INT DEFAULT 0,
            nr INT DEFAULT 0,
            points INT DEFAULT 0,
            nrr DECIMAL(5,3) DEFAULT 0.000,
            UNIQUE KEY unique_group_team (group_id, team_id),
            KEY idx_group (group_id),
            KEY idx_team (team_id)
    ";

    if ($hasGroupsTable) {
        $createSql .= ",
            FOREIGN KEY (group_id) REFERENCES `groups`(id) ON DELETE CASCADE";
    }
    if ($hasTeamsTable) {
        $createSql .= ",
            FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE";
    }

    $createSql .= "
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";

    $pdo->exec($createSql);
}

function ensureGroupStandingsColumns($pdo) {
    static $checked = false;
    if ($checked) return;
    $checked = true;

    if (!tableExists($pdo, 'group_standings')) {
        return;
    }

    $columns = $pdo->query("SHOW COLUMNS FROM group_standings")->fetchAll();
    $existingColumns = [];
    foreach ($columns as $column) {
        $existingColumns[$column['Field']] = true;
    }

    if (!isset($existingColumns['position'])) {
        $pdo->exec("ALTER TABLE group_standings ADD COLUMN position INT DEFAULT NULL AFTER team_id");
    }
    if (!isset($existingColumns['drawn'])) {
        $pdo->exec("ALTER TABLE group_standings ADD COLUMN drawn INT DEFAULT 0 AFTER lost");
    }
    if (!isset($existingColumns['tied'])) {
        if (isset($existingColumns['tie'])) {
            $pdo->exec("ALTER TABLE group_standings CHANGE COLUMN tie tied INT DEFAULT 0");
        } else {
            $pdo->exec("ALTER TABLE group_standings ADD COLUMN tied INT DEFAULT 0 AFTER drawn");
        }
    }
}

function ensureSponsorPlacementColumns($pdo) {
    static $checked = false;
    if ($checked) return;
    $checked = true;

    if (!tableExists($pdo, 'sponsors')) {
        return;
    }

    $hasFooter = false;
    $hasPopup = false;
    $cols = $pdo->query("SHOW COLUMNS FROM sponsors")->fetchAll();
    foreach ($cols as $col) {
        if (($col['Field'] ?? '') === 'show_in_footer') $hasFooter = true;
        if (($col['Field'] ?? '') === 'show_in_popup') $hasPopup = true;
    }

    if (!$hasFooter) {
        $pdo->exec("ALTER TABLE sponsors ADD COLUMN show_in_footer TINYINT(1) NOT NULL DEFAULT 1");
    }
    if (!$hasPopup) {
        $pdo->exec("ALTER TABLE sponsors ADD COLUMN show_in_popup TINYINT(1) NOT NULL DEFAULT 1");
    }
}

function ensureAnalyticsTables($pdo) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS website_visits (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            visitor_id VARCHAR(64) NOT NULL,
            page_url VARCHAR(255) DEFAULT NULL,
            user_agent TEXT DEFAULT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            started_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            ended_at DATETIME DEFAULT NULL,
            duration_seconds INT UNSIGNED NOT NULL DEFAULT 0,
            is_new_visitor TINYINT(1) NOT NULL DEFAULT 0,
            INDEX idx_visitor_id (visitor_id),
            INDEX idx_started_at (started_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
}

function ensureMatchResultColumns($pdo) {
    static $checked = false;
    if ($checked) return;
    $checked = true;

    $columns = $pdo->query("SHOW COLUMNS FROM matches")->fetchAll();
    $existingColumns = [];
    foreach ($columns as $column) {
        $existingColumns[$column['Field']] = true;
    }

    if (!isset($existingColumns['winner_team_id'])) {
        $pdo->exec("ALTER TABLE matches ADD COLUMN winner_team_id INT NULL AFTER ground");
    }
    if (!isset($existingColumns['man_of_match'])) {
        $pdo->exec("ALTER TABLE matches ADD COLUMN man_of_match VARCHAR(120) NULL AFTER winner_team_id");
    }
    if (!isset($existingColumns['victory_margin'])) {
        $pdo->exec("ALTER TABLE matches ADD COLUMN victory_margin VARCHAR(120) NULL AFTER man_of_match");
    }
    if (!isset($existingColumns['team1_score'])) {
        $pdo->exec("ALTER TABLE matches ADD COLUMN team1_score VARCHAR(40) NULL AFTER victory_margin");
    }
    if (!isset($existingColumns['team2_score'])) {
        $pdo->exec("ALTER TABLE matches ADD COLUMN team2_score VARCHAR(40) NULL AFTER team1_score");
    }
}

function formatDuration($seconds) {
    $seconds = (int)$seconds;
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $remainingSeconds = $seconds % 60;
    return sprintf('%02d:%02d:%02d', $hours, $minutes, $remainingSeconds);
}

ensureSponsorsTable($pdo);
ensureGroupStandingsTable($pdo);
ensureGroupStandingsColumns($pdo);
ensureSponsorPlacementColumns($pdo);
ensureMatchResultColumns($pdo);
?>