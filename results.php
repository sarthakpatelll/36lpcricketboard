<?php
$page_title = 'Recent Results';
require_once 'config.php';
include 'includes/header.php';

$limit = 20;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$countStmt = $pdo->query("
    SELECT COUNT(*)
    FROM matches
    WHERE (ground IS NULL OR ground <> 'Intra-Group Match')
      AND winner_team_id IS NOT NULL
");
$total = (int)$countStmt->fetchColumn();
$totalPages = (int)ceil($total / $limit);

$stmt = $pdo->prepare("
    SELECT
        m.*,
        t1.team_name AS team1,
        t2.team_name AS team2,
        w.team_name AS winner_name
    FROM matches m
    JOIN teams t1 ON m.team1_id = t1.id
    JOIN teams t2 ON m.team2_id = t2.id
    LEFT JOIN teams w ON m.winner_team_id = w.id
    WHERE (m.ground IS NULL OR m.ground <> 'Intra-Group Match')
      AND m.winner_team_id IS NOT NULL
    ORDER BY m.match_date DESC, m.match_time DESC
    LIMIT ? OFFSET ?
");
$stmt->bindValue(1, $limit, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$results = $stmt->fetchAll();
?>

<h2>🏆 All Match Results</h2>
<p class="text-muted mb-3">Completed matches with winner, Man of the Match, victory margin, and both team scores.</p>

<div class="table-responsive">
    <table class="table table-striped table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Match</th>
                <th>Date</th>
                <th>Venue</th>
                <th>Winner</th>
                <th>Man of the Match</th>
                <th>Result</th>
                <th>Scorecard</th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($results) > 0): ?>
                <?php $sn = $offset + 1; foreach($results as $result): ?>
                    <tr>
                        <td><?php echo $sn++; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($result['team1']); ?></strong>
                            <span class="text-muted">vs</span>
                            <strong><?php echo htmlspecialchars($result['team2']); ?></strong>
                        </td>
                        <td>
                            <?php echo date('d M Y', strtotime($result['match_date'])); ?><br>
                            <small class="text-muted"><?php echo date('h:i A', strtotime($result['match_time'])); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars((string)$result['ground']); ?></td>
                        <td><span class="badge bg-success"><?php echo htmlspecialchars((string)$result['winner_name']); ?></span></td>
                        <td><?php echo !empty($result['man_of_match']) ? htmlspecialchars($result['man_of_match']) : 'N/A'; ?></td>
                        <td><?php echo !empty($result['victory_margin']) ? htmlspecialchars($result['victory_margin']) : 'N/A'; ?></td>
                        <td>
                            <div><strong><?php echo htmlspecialchars($result['team1']); ?>:</strong> <?php echo !empty($result['team1_score']) ? htmlspecialchars($result['team1_score']) : 'N/A'; ?></div>
                            <div><strong><?php echo htmlspecialchars($result['team2']); ?>:</strong> <?php echo !empty($result['team2_score']) ? htmlspecialchars($result['team2_score']) : 'N/A'; ?></div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center">No completed match results available yet.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if($totalPages > 1): ?>
    <nav>
        <ul class="pagination">
            <?php for($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
