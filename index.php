<?php
$page_title = 'Home';
require_once 'config.php';
include 'includes/header.php';

// Upcoming matches
$stmt = $pdo->prepare("SELECT m.*, t1.team_name as team1, t2.team_name as team2 
                        FROM matches m 
                        JOIN teams t1 ON m.team1_id = t1.id 
                        JOIN teams t2 ON m.team2_id = t2.id 
                        WHERE (m.ground IS NULL OR m.ground <> 'Intra-Group Match')
                          AND m.winner_team_id IS NULL
                          AND m.match_date >= CURDATE() 
                        ORDER BY m.match_date ASC LIMIT 3");
$stmt->execute();
$upcoming = $stmt->fetchAll();

// Recent results
$stmt2 = $pdo->prepare("SELECT m.*, t1.team_name as team1, t2.team_name as team2, w.team_name as winner_name
                        FROM matches m 
                        JOIN teams t1 ON m.team1_id = t1.id 
                        JOIN teams t2 ON m.team2_id = t2.id 
                        LEFT JOIN teams w ON m.winner_team_id = w.id
                        WHERE (m.ground IS NULL OR m.ground <> 'Intra-Group Match')
                          AND m.winner_team_id IS NOT NULL
                        ORDER BY m.match_date DESC LIMIT 3");
$stmt2->execute();
$recent = $stmt2->fetchAll();

$latest_count = $pdo->query("SELECT COUNT(*) FROM matches WHERE (ground IS NULL OR ground <> 'Intra-Group Match') AND winner_team_id IS NOT NULL")->fetchColumn();

$totalTeams = $pdo->query("SELECT COUNT(*) FROM teams")->fetchColumn();
$totalMatches = $pdo->query("SELECT COUNT(*) FROM matches WHERE ground IS NULL OR ground <> 'Intra-Group Match'")->fetchColumn();
$totalGroups = $pdo->query("SELECT COUNT(*) FROM `groups`")->fetchColumn();

$logoWebPath = null;
if (file_exists(__DIR__ . '/assets/logo.png')) {
    $logoWebPath = 'assets/logo.png';
}
?>

<div class="row">
    <div class="col-12 text-center mb-3">
        <?php if($logoWebPath): ?>
            <img src="<?php echo htmlspecialchars($logoWebPath); ?>" alt="36 LP Cricket Board Logo" style="max-height: 110px; width: auto;" class="mb-2">
        <?php endif; ?>
        <p class="lead"><b><h1>36 LPL 2026 - Season 9</h1></b></p>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-header text-white" style="background-color: #11315b;">📅 Upcoming Matches</div>
            <div class="card-body">
                <?php if(count($upcoming) > 0): ?>
                    <ul class="list-group">
                        <?php foreach($upcoming as $match): ?>
                            <li class="list-group-item">
                                <strong><?php echo htmlspecialchars($match['team1']); ?></strong> vs <strong><?php echo htmlspecialchars($match['team2']); ?></strong><br>
                                <small>📅 <?php echo date('d M Y', strtotime($match['match_date'])); ?> | ⏰ <?php echo date('h:i A', strtotime($match['match_time'])); ?> | 🏟️ <?php echo htmlspecialchars($match['ground']); ?></small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted">No upcoming matches scheduled.</p>
                <?php endif; ?>
                <a href="schedule.php" class="btn btn-sm btn-outline-primary mt-3">View All Upcoming Matches →</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-header text-white" style="background-color: #11315b;">🏆 Recent Results</div>
            <div class="card-body">
                <?php if(count($recent) > 0): ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach($recent as $match): ?>
                            <li class="list-group-item px-0 py-3 border-bottom">
                                <div class="d-flex justify-content-between flex-wrap">
                                    <h6 class="mb-1 fw-bold"><?php echo htmlspecialchars($match['team1']); ?> vs <?php echo htmlspecialchars($match['team2']); ?></h6>
                                    <small class="text-muted"><?php echo date('d M Y', strtotime($match['match_date'])); ?></small>
                                </div>
                                <div class="small mb-1">
                                    <span class="badge bg-success me-2">Winner: <?php echo htmlspecialchars((string)$match['winner_name']); ?></span>
                                    <?php if(!empty($match['victory_margin'])): ?>
                                        <span class="badge bg-dark"><?php echo htmlspecialchars($match['victory_margin']); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="small text-muted mb-1">
                                    <?php if(!empty($match['man_of_match'])): ?>
                                        ⭐ Man of the Match: <strong><?php echo htmlspecialchars($match['man_of_match']); ?></strong>
                                    <?php else: ?>
                                        ⭐ Man of the Match: <span class="text-muted">Not updated</span>
                                    <?php endif; ?>
                                </div>
                                <div class="small">
                                    <strong><?php echo htmlspecialchars($match['team1']); ?>:</strong> <?php echo !empty($match['team1_score']) ? htmlspecialchars($match['team1_score']) : 'N/A'; ?><br>
                                    <strong><?php echo htmlspecialchars($match['team2']); ?>:</strong> <?php echo !empty($match['team2_score']) ? htmlspecialchars($match['team2_score']) : 'N/A'; ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted">No match results published yet.</p>
                <?php endif; ?>
                <a href="results.php" class="btn btn-sm btn-outline-primary mt-3">View All Results →</a>
            </div>
        </div>
    </div>
</div>

<div class="row mt-2">
    <div class="col-md-4 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-users fa-3x text-primary"></i>
                <h3 class="mt-2"><?php echo $totalTeams; ?></h3>
                <p>Teams</p>
                <a href="teams.php" class="btn btn-primary btn-sm">View Teams</a>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-calendar-alt fa-3x text-success"></i>
                <h3 class="mt-2"><?php echo $totalMatches; ?></h3>
                <p>Matches Schedule</p>
                <a href="schedule.php" class="btn btn-success btn-sm">View Schedule</a>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-trophy fa-3x text-info"></i>
                <h3 class="mt-2"><?php echo $latest_count; ?></h3>
                <p>All Results</p>
                <a href="results.php" class="btn btn-info btn-sm">View Results</a>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-layer-group fa-3x text-warning"></i>
                <h3 class="mt-2"><?php echo $totalGroups; ?></h3>
                <p>Groups</p>
                <a href="groups.php" class="btn btn-warning btn-sm">View Groups</a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>