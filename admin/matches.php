<?php
require_once '../config.php';
if(!isAdminLoggedIn()) redirect('login.php');

// Add Match
if(isset($_POST['add_match'])) {
    $stmt = $pdo->prepare("INSERT INTO matches (team1_id, team2_id, match_date, match_time, ground) VALUES (?,?,?,?,?)");
    $stmt->execute([$_POST['team1'], $_POST['team2'], $_POST['match_date'], $_POST['match_time'], $_POST['ground']]);
    $_SESSION['message'] = "Match added!";
    redirect('matches.php');
}

// Update Match Result
if(isset($_POST['update_result'])) {
    $matchId = (int)($_POST['match_id'] ?? 0);
    $winnerId = !empty($_POST['winner_team_id']) ? (int)$_POST['winner_team_id'] : null;
    $manOfMatch = trim($_POST['man_of_match'] ?? '');
    $victoryMargin = trim($_POST['victory_margin'] ?? '');
    $team1Score = trim($_POST['team1_score'] ?? '');
    $team2Score = trim($_POST['team2_score'] ?? '');

    if ($matchId > 0) {
        $stmt = $pdo->prepare("
            UPDATE matches 
            SET winner_team_id = ?, man_of_match = ?, victory_margin = ?, team1_score = ?, team2_score = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $winnerId,
            $manOfMatch !== '' ? $manOfMatch : null,
            $victoryMargin !== '' ? $victoryMargin : null,
            $team1Score !== '' ? $team1Score : null,
            $team2Score !== '' ? $team2Score : null,
            $matchId
        ]);

        $_SESSION['message'] = "Match result updated!";
    }
    redirect('matches.php');
}

// Delete Match
if(isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM matches WHERE id=?")->execute([$_GET['delete']]);
    $_SESSION['message'] = "Match deleted!";
    redirect('matches.php');
}

$matches = $pdo->query("
    SELECT 
        m.*, 
        t1.team_name AS t1name, 
        t2.team_name AS t2name,
        w.team_name AS winner_name
    FROM matches m 
    JOIN teams t1 ON m.team1_id=t1.id 
    JOIN teams t2 ON m.team2_id=t2.id
    LEFT JOIN teams w ON m.winner_team_id = w.id
    ORDER BY m.match_date DESC
")->fetchAll();
$teams = $pdo->query("SELECT id, team_name FROM teams ORDER BY team_name")->fetchAll();
include '../includes/admin_header.php';
?>

<div class="container-fluid">
    <h2>Match Management</h2>
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addMatchModal">+ Add New Match</button>
    
    <table class="table table-bordered table-striped align-middle">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Match</th>
                <th>Date</th>
                <th>Time</th>
                <th>Ground</th>
                <th>Result Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($matches as $m): ?>
            <tr>
                <td><?php echo $m['id']; ?></td>
                <td><?php echo htmlspecialchars($m['t1name'] . " vs " . $m['t2name']); ?></td>
                <td><?php echo $m['match_date']; ?></td>
                <td><?php echo $m['match_time']; ?></td>
                <td><?php echo htmlspecialchars($m['ground']); ?></td>
                <td>
                    <?php if(!empty($m['winner_team_id'])): ?>
                        <span class="badge bg-success">Completed</span><br>
                        <small class="text-muted">Winner: <?php echo htmlspecialchars($m['winner_name']); ?></small>
                    <?php else: ?>
                        <span class="badge bg-secondary">Pending</span>
                    <?php endif; ?>
                </td>
                <td>
                    <button 
                        type="button"
                        class="btn btn-sm btn-outline-primary mb-1"
                        data-bs-toggle="modal"
                        data-bs-target="#resultModal<?php echo $m['id']; ?>"
                    >
                        Update Result
                    </button>
                    <a href="?delete=<?php echo $m['id']; ?>" class="btn btn-sm btn-danger mb-1" onclick="return confirm('Delete match?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Add Match Modal -->
<div class="modal fade" id="addMatchModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Add New Match</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <select name="team1" class="form-control mb-2" required>
                        <option value="">Select Team 1</option>
                        <?php foreach($teams as $t): ?>
                            <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['team_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="team2" class="form-control mb-2" required>
                        <option value="">Select Team 2</option>
                        <?php foreach($teams as $t): ?>
                            <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['team_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="date" name="match_date" class="form-control mb-2" required>
                    <input type="time" name="match_time" class="form-control mb-2" required>
                    <input type="text" name="ground" placeholder="Ground Name" class="form-control" required>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add_match" class="btn btn-primary">Save Match</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php foreach($matches as $m): ?>
<div class="modal fade" id="resultModal<?php echo $m['id']; ?>" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST">
            <input type="hidden" name="match_id" value="<?php echo $m['id']; ?>">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Update Match Result</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-light border">
                        <strong><?php echo htmlspecialchars($m['t1name']); ?></strong> vs <strong><?php echo htmlspecialchars($m['t2name']); ?></strong><br>
                        <small class="text-muted">
                            <?php echo htmlspecialchars($m['match_date']); ?> | <?php echo htmlspecialchars($m['match_time']); ?> | <?php echo htmlspecialchars($m['ground']); ?>
                        </small>
                    </div>

                    <label class="form-label mb-1">Winner Team</label>
                    <select name="winner_team_id" class="form-control mb-2">
                        <option value="">Select Winner Team</option>
                        <option value="<?php echo $m['team1_id']; ?>" <?php echo ((int)$m['winner_team_id'] === (int)$m['team1_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($m['t1name']); ?>
                        </option>
                        <option value="<?php echo $m['team2_id']; ?>" <?php echo ((int)$m['winner_team_id'] === (int)$m['team2_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($m['t2name']); ?>
                        </option>
                    </select>

                    <label class="form-label mb-1">Man of the Match</label>
                    <input type="text" name="man_of_match" class="form-control mb-2" value="<?php echo htmlspecialchars((string)($m['man_of_match'] ?? '')); ?>" placeholder="e.g. Rahul Sharma">

                    <label class="form-label mb-1">Victory Margin</label>
                    <input type="text" name="victory_margin" class="form-control mb-2" value="<?php echo htmlspecialchars((string)($m['victory_margin'] ?? '')); ?>" placeholder="e.g. Won by 18 runs">

                    <label class="form-label mb-1"><?php echo htmlspecialchars($m['t1name']); ?> Score</label>
                    <input type="text" name="team1_score" class="form-control mb-2" value="<?php echo htmlspecialchars((string)($m['team1_score'] ?? '')); ?>" placeholder="e.g. 168/6 (20 overs)">

                    <label class="form-label mb-1"><?php echo htmlspecialchars($m['t2name']); ?> Score</label>
                    <input type="text" name="team2_score" class="form-control" value="<?php echo htmlspecialchars((string)($m['team2_score'] ?? '')); ?>" placeholder="e.g. 150/9 (20 overs)">
                </div>
                <div class="modal-footer">
                    <button type="submit" name="update_result" class="btn btn-primary">Save Result</button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endforeach; ?>

<?php include '../includes/admin_footer.php'; ?>