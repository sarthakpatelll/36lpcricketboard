<?php
$page_title = 'Match Schedule';
require_once 'config.php';
include 'includes/header.php';

$limit = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$total = $pdo->query("SELECT COUNT(*) FROM matches WHERE ground IS NULL OR ground <> 'Intra-Group Match'")->fetchColumn();
$totalPages = ceil($total / $limit);

$stmt = $pdo->prepare("SELECT m.*, t1.team_name as team1, t2.team_name as team2 
                        FROM matches m 
                        JOIN teams t1 ON m.team1_id = t1.id 
                        JOIN teams t2 ON m.team2_id = t2.id 
                        WHERE m.ground IS NULL OR m.ground <> 'Intra-Group Match'
                        ORDER BY m.match_date ASC, m.match_time ASC 
                        LIMIT ? OFFSET ?");
$stmt->bindValue(1, $limit, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$matches = $stmt->fetchAll();
?>

<h2>📅 Full Match Schedule</h2>
<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr><th>#</th><th>Match</th><th>Date</th><th>Time</th><th>Ground</th></tr>
        </thead>
        <tbody>
            <?php if(count($matches) > 0): ?>
                <?php $sn = $offset + 1; foreach($matches as $match): ?>
                    <tr>
                        <td><?php echo $sn++; ?></td>
                        <td><strong><?php echo htmlspecialchars($match['team1']); ?></strong> vs <strong><?php echo htmlspecialchars($match['team2']); ?></strong></td>
                        <td><?php echo date('d M Y', strtotime($match['match_date'])); ?></td>
                        <td><?php echo date('h:i A', strtotime($match['match_time'])); ?></td>
                        <td><?php echo htmlspecialchars($match['ground']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" class="text-center">No matches scheduled.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if($totalPages > 1): ?>
    <nav><ul class="pagination">
        <?php for($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
            </li>
        <?php endfor; ?>
    </ul></nav>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>