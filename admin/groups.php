<?php
require_once '../config.php';
if(!isAdminLoggedIn()) redirect('login.php');

function getGroupTeamIdsOrdered(PDO $pdo, int $groupId): array {
    $stmt = $pdo->prepare("
        SELECT t.id
        FROM teams t
        JOIN group_teams gt ON gt.team_id = t.id
        WHERE gt.group_id = ?
        ORDER BY gt.id ASC
    ");
    $stmt->execute([$groupId]);
    return array_map('intval', array_column($stmt->fetchAll(), 'id'));
}

function buildAdjacentCircularPairs(array $teamIds): array {
    $teamIds = array_values(array_unique(array_map('intval', $teamIds)));
    $n = count($teamIds);
    if ($n < 2) return [];

    $pairs = [];
    for ($i = 0; $i < $n - 1; $i++) {
        $pairs[] = [$teamIds[$i], $teamIds[$i + 1]];
    }
    $pairs[] = [$teamIds[$n - 1], $teamIds[0]];
    return $pairs;
}

function ensureAutoIntraGroupMatchups(PDO $pdo, int $groupId): void {
    $teamIds = getGroupTeamIdsOrdered($pdo, $groupId);
    $pairs = buildAdjacentCircularPairs($teamIds);
    if (!$pairs) return;

    $existingStmt = $pdo->prepare("
        SELECT m.id, m.team1_id, m.team2_id
        FROM matches m
        JOIN group_teams gt1 ON gt1.team_id = m.team1_id AND gt1.group_id = ?
        JOIN group_teams gt2 ON gt2.team_id = m.team2_id AND gt2.group_id = ?
        WHERE m.ground = 'Intra-Group Match'
        ORDER BY m.id ASC
    ");
    $existingStmt->execute([$groupId, $groupId]);
    $existing = $existingStmt->fetchAll();

    $exactPool = [];
    $reversePool = [];
    foreach ($existing as $row) {
        $exactKey = ((int)$row['team1_id']) . '-' . ((int)$row['team2_id']);
        $reverseKey = ((int)$row['team2_id']) . '-' . ((int)$row['team1_id']);
        $exactPool[$exactKey][] = (int)$row['id'];
        $reversePool[$reverseKey][] = (int)$row['id'];
    }

    $updateDirection = $pdo->prepare("
        UPDATE matches
        SET team1_id = ?, team2_id = ?
        WHERE id = ?
    ");
    $insert = $pdo->prepare("
        INSERT INTO matches (team1_id, team2_id, match_date, match_time, ground)
        VALUES (?, ?, '2024-12-31', '12:00:00', 'Intra-Group Match')
    ");

    foreach ($pairs as [$a, $b]) {
        if ($a === $b) continue;
        $key = $a . '-' . $b;
        $revKey = $b . '-' . $a;

        if (!empty($exactPool[$key])) {
            array_shift($exactPool[$key]); // already correct direction
            continue;
        }

        if (!empty($exactPool[$revKey])) {
            $matchId = array_shift($exactPool[$revKey]);
            $updateDirection->execute([$a, $b, $matchId]);
            continue;
        }

        if (!empty($reversePool[$key])) {
            $matchId = array_shift($reversePool[$key]);
            $updateDirection->execute([$a, $b, $matchId]);
            continue;
        }

        $insert->execute([$a, $b]);
    }
}

// Add Group
if(isset($_POST['add_group'])) {
    $pdo->prepare("INSERT INTO `groups` (group_name) VALUES (?)")->execute([$_POST['group_name']]);
    $_SESSION['message'] = "Group added!";
    redirect('groups.php');
}

// Assign Team to Group
if(isset($_POST['assign_team'])) {
    $check = $pdo->prepare("SELECT * FROM group_teams WHERE group_id=? AND team_id=?");
    $check->execute([$_POST['group_id'], $_POST['team_id']]);
    if(!$check->fetch()) {
        $pdo->prepare("INSERT INTO group_teams (group_id, team_id) VALUES (?, ?)")->execute([$_POST['group_id'], $_POST['team_id']]);
        ensureAutoIntraGroupMatchups($pdo, (int)$_POST['group_id']);
        $_SESSION['message'] = "Team assigned to group!";
    } else {
        $_SESSION['error'] = "Team already in this group!";
    }
    redirect('groups.php');
}

// Remove Team from Group
if(isset($_GET['remove'])) {
    $pdo->prepare("DELETE FROM group_teams WHERE group_id=? AND team_id=?")->execute([$_GET['group_id'], $_GET['team_id']]);
    ensureAutoIntraGroupMatchups($pdo, (int)$_GET['group_id']);
    $_SESSION['message'] = "Team removed from group!";
    redirect('groups.php');
}

// Add Custom Group Matchup
if(isset($_POST['add_group_matchup'])) {
    $group_id = $_POST['group_id'];
    $team1_id = $_POST['team1_id'];
    $team2_id = $_POST['team2_id'];
    $stmt = $pdo->prepare(
        "INSERT INTO matches (team1_id, team2_id, match_date, match_time, ground)
         VALUES (?, ?, '2024-12-31', '12:00:00', 'Intra-Group Match')"
    );
    $stmt->execute([$team1_id, $team2_id]);
    $_SESSION['message'] = "Group matchup added!";
    redirect('groups.php');
}

$groups = $pdo->query("SELECT * FROM `groups` ORDER BY group_name")->fetchAll();
$teams = $pdo->query("SELECT id, team_name FROM teams ORDER BY team_name")->fetchAll();

// Ensure auto matchups for already-existing groups/teams.
foreach ($groups as $g) {
    ensureAutoIntraGroupMatchups($pdo, (int)$g['id']);
}

include '../includes/admin_header.php';
?>

<div class="container-fluid">
    <h2>Groups Management</h2>
    <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addGroupModal">+ Create New Group</button>
    
    <?php foreach($groups as $group): 
        $assigned = $pdo->prepare("SELECT t.id, t.team_name FROM teams t JOIN group_teams gt ON t.id=gt.team_id WHERE gt.group_id=? ORDER BY gt.id ASC");
        $assigned->execute([$group['id']]);
        $assignedTeams = $assigned->fetchAll();
    ?>
        <div class="card mb-3">
            <div class="card-header bg-secondary text-white">
                <strong><?php echo htmlspecialchars($group['group_name']); ?></strong>
            </div>
            <div class="card-body">
                <h6>Teams in this group:</h6>
                <ul>
                    <?php if(count($assignedTeams) > 0): ?>
                        <?php foreach($assignedTeams as $at): ?>
                            <li>
                                <?php echo htmlspecialchars($at['team_name']); ?>
                                <a href="?remove=1&group_id=<?php echo $group['id']; ?>&team_id=<?php echo $at['id']; ?>" class="text-danger ms-2" onclick="return confirm('Remove team from group?')">[Remove]</a>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="text-muted">No teams assigned</li>
                    <?php endif; ?>
                </ul>

                <form method="POST" class="row g-2 mt-2">
                    <input type="hidden" name="group_id" value="<?php echo $group['id']; ?>">
                    <div class="col-auto">
                        <select name="team_id" class="form-select">
                            <option value="">-- Assign Team --</option>
                            <?php foreach($teams as $t): ?>
                                <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['team_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" name="assign_team" class="btn btn-sm btn-primary">Assign</button>
                    </div>
                </form>

                <button class="btn btn-info btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#addMatchupModal<?php echo $group['id']; ?>">+ Add Custom Group Matchup</button>

                <!-- Custom Matchup Modal for this group -->
                <div class="modal fade" id="addMatchupModal<?php echo $group['id']; ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <form method="POST">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5>Add Matchup for <?php echo htmlspecialchars($group['group_name']); ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="group_id" value="<?php echo $group['id']; ?>">
                                    <select name="team1_id" class="form-control mb-2" required>
                                        <option value="">Select Team 1</option>
                                        <?php foreach($assignedTeams as $at): ?>
                                        <option value="<?php echo $at['id']; ?>"><?php echo htmlspecialchars($at['team_name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <select name="team2_id" class="form-control mb-2" required>
                                        <option value="">Select Team 2</option>
                                        <?php foreach($assignedTeams as $at): ?>
                                        <option value="<?php echo $at['id']; ?>"><?php echo htmlspecialchars($at['team_name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" name="add_group_matchup" class="btn btn-primary">Add Matchup</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <?php 
                $orderedTeamIds = array_map('intval', array_column($assignedTeams, 'id'));
                $expectedPairs = buildAdjacentCircularPairs($orderedTeamIds);

                $groupMatches = $pdo->prepare("SELECT m.*, t1.team_name as tn1, t2.team_name as tn2 FROM matches m JOIN teams t1 ON m.team1_id = t1.id JOIN teams t2 ON m.team2_id = t2.id JOIN group_teams gt1 ON gt1.team_id = m.team1_id JOIN group_teams gt2 ON gt2.team_id = m.team2_id WHERE gt1.group_id = ? AND gt2.group_id = ? AND m.ground = 'Intra-Group Match'");
                $groupMatches->execute([$group['id'], $group['id']]);
                $allGroupMatches = $groupMatches->fetchAll();

                $exactMatchesMap = [];
                $reverseMatchesMap = [];
                foreach ($allGroupMatches as $gm) {
                    $k1 = $gm['team1_id'] . '-' . $gm['team2_id'];
                    $k2 = $gm['team2_id'] . '-' . $gm['team1_id'];
                    $exactMatchesMap[$k1][] = $gm;
                    $reverseMatchesMap[$k2][] = $gm;
                }

                $groupMatchesList = [];
                foreach ($expectedPairs as $pair) {
                    $k = $pair[0] . '-' . $pair[1];
                    $rk = $pair[1] . '-' . $pair[0];

                    if (!empty($exactMatchesMap[$k])) {
                        $groupMatchesList[] = array_shift($exactMatchesMap[$k]);
                    } elseif (!empty($exactMatchesMap[$rk])) {
                        $groupMatchesList[] = array_shift($exactMatchesMap[$rk]);
                    } elseif (!empty($reverseMatchesMap[$k])) {
                        $groupMatchesList[] = array_shift($reverseMatchesMap[$k]);
                    }
                }
                ?>
                <?php if(count($groupMatchesList) > 0): ?>
<h6 class="mt-3 mb-2">Group Matchups:</h6>
                <ul class="list-group list-group-flush">
                    <?php foreach($groupMatchesList as $gm): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center small">
                        <span><?php echo htmlspecialchars($gm['tn1']) . ' vs ' . htmlspecialchars($gm['tn2']); ?></span>
                        <a href="../admin/matches.php?delete=<?php echo $gm['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">×</a>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>

            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Add Group Modal -->
<div class="modal fade" id="addGroupModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Create New Group</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="text" name="group_name" class="form-control" placeholder="e.g., Group A, Group B" required>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add_group" class="btn btn-success">Create</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/admin_footer.php'; ?>