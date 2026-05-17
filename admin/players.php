<?php
require_once '../config.php';
if(!isAdminLoggedIn()) redirect('login.php');

// Add Player(s)
if(isset($_POST['add_player'])) {
    $team_id = $_POST['team_id'];
    if(!empty($team_id) && isset($_POST['player_name']) && is_array($_POST['player_name'])) {
        $count = 0;
        foreach($_POST['player_name'] as $name) {
            $name = trim($name);
            if(!empty($name)) {
                $stmt = $pdo->prepare("INSERT INTO players (team_id, player_name) VALUES (?, ?)");
                $stmt->execute([$team_id, $name]);
                $count++;
            }
        }
        if($count > 0) {
            $_SESSION['message'] = "$count player(s) added successfully!";
        } else {
            $_SESSION['error'] = "No valid player names provided!";
        }
    } else {
        $_SESSION['error'] = "Please select a team!";
    }
    redirect('players.php');
}

// Delete Player
if(isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM players WHERE id=?")->execute([$_GET['delete']]);
    $_SESSION['message'] = "Player deleted!";
    redirect('players.php');
}

$players = $pdo->query("SELECT p.*, t.team_name FROM players p JOIN teams t ON p.team_id = t.id ORDER BY t.team_name, p.player_name")->fetchAll();
$teams = $pdo->query("SELECT id, team_name FROM teams ORDER BY team_name")->fetchAll();
include '../includes/admin_header.php';
?>

<div class="container-fluid">
    <h2>Player Management</h2>
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addPlayerModal">+ Add Player</button>
    
    <table class="table table-bordered">
        <thead class="table-dark">
            <tr><th>ID</th><th>Player Name</th><th>Team</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php foreach($players as $p): ?>
            <tr>
                <td><?php echo $p['id']; ?></td>
                <td><?php echo htmlspecialchars($p['player_name']); ?></td>
                <td><?php echo htmlspecialchars($p['team_name']); ?></td>
                <td><a href="?delete=<?php echo $p['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete player?')">Delete</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addPlayerModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Add Player</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <select name="team_id" class="form-control mb-2" required>
                        <option value="">Select Team</option>
                        <?php foreach($teams as $t): ?>
                            <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['team_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label class="form-label mb-2 fw-bold">Add up to 14 Players for the Team:</label>
                    <?php for($i = 1; $i <= 14; $i++): ?>
                    <div class="mb-1">
                        <input type="text" name="player_name[]" placeholder="Player <?php echo $i; ?>" class="form-control form-control-sm" />
                    </div>
                    <?php endfor; ?>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add_player" class="btn btn-primary">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/admin_footer.php'; ?>