<?php
$page_title = 'Team Details';
require_once 'config.php';
include 'includes/header.php';

$team_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if(!$team_id) redirect('teams.php');

$stmt = $pdo->prepare("SELECT * FROM teams WHERE id = ?");
$stmt->execute([$team_id]);
$team = $stmt->fetch();
if(!$team) redirect('teams.php');

// Get players
$players = $pdo->prepare("SELECT * FROM players WHERE team_id = ? ORDER BY player_name");
$players->execute([$team_id]);
$playerList = $players->fetchAll();

// Get matches
$matches = $pdo->prepare("SELECT m.*, t1.team_name as t1name, t2.team_name as t2name 
                         FROM matches m 
                         JOIN teams t1 ON m.team1_id = t1.id 
                         JOIN teams t2 ON m.team2_id = t2.id 
                         WHERE (m.ground IS NULL OR m.ground <> 'Intra-Group Match')
                           AND (m.team1_id = ? OR m.team2_id = ?)
                         ORDER BY m.match_date ASC");
$matches->execute([$team_id, $team_id]);
$matchList = $matches->fetchAll();
?>

<div class="card mb-4">
    <div class="card-header bg-dark text-white">
        <h3><?php echo htmlspecialchars($team['team_name']); ?></h3>
    </div>
    <div class="card-body">
        <p><strong>👨 Captain:</strong> <?php echo htmlspecialchars($team['captain_name']); ?></p>
    </div>
</div>

<h4>👥 Players (<?php echo count($playerList); ?>)</h4>
<div class="row mb-4">
    <?php if(count($playerList) > 0): ?>
        <?php foreach($playerList as $player): ?>
            <div class="col-md-3 col-6 mb-2">
                <div class="border rounded p-2 text-center bg-light">
                    <i class="fas fa-user-circle fa-2x"></i>
                    <div><?php echo htmlspecialchars($player['player_name']); ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12"><p class="text-muted">No players added yet.</p></div>
    <?php endif; ?>
</div>

<h4>📅 Match Schedule</h4>
<div class="table-responsive">
    <table class="table table-bordered">
        <thead class="table-primary">
            <tr><th>Opponent</th><th>Date</th><th>Time</th><th>Ground</th></tr>
        </thead>
        <tbody>
            <?php if(count($matchList) > 0): ?>
                <?php foreach($matchList as $match): ?>
                    <tr>
                        <td>
                            <?php 
                            $opponent = ($match['team1_id'] == $team_id) ? $match['t2name'] : $match['t1name'];
                            echo htmlspecialchars($opponent);
                            ?>
                        </td>
                        <td><?php echo date('d M Y', strtotime($match['match_date'])); ?></td>
                        <td><?php echo date('h:i A', strtotime($match['match_time'])); ?></td>
                        <td><?php echo htmlspecialchars($match['ground']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4" class="text-center">No matches scheduled.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<a href="teams.php" class="btn btn-secondary">← Back to Teams</a>

<?php include 'includes/footer.php'; ?>