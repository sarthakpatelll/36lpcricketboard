<?php
require_once '../config.php';
if(!isAdminLoggedIn()) redirect('login.php');
ensureAnalyticsTables($pdo);

$totalTeams = $pdo->query("SELECT COUNT(*) FROM teams")->fetchColumn();
$totalMatches = $pdo->query("SELECT COUNT(*) FROM matches")->fetchColumn();
$totalGroups = $pdo->query("SELECT COUNT(*) FROM `groups`")->fetchColumn();
$totalPlayers = $pdo->query("SELECT COUNT(*) FROM players")->fetchColumn();

$totalWebsiteVisits = (int)$pdo->query("SELECT COUNT(*) FROM website_visits")->fetchColumn();
$totalUniqueUsers = (int)$pdo->query("SELECT COUNT(DISTINCT visitor_id) FROM website_visits")->fetchColumn();
$newUsers = (int)$pdo->query("SELECT COUNT(DISTINCT visitor_id) FROM website_visits WHERE is_new_visitor = 1")->fetchColumn();
$repeatUsers = (int)$pdo->query("
    SELECT COUNT(*) FROM (
        SELECT visitor_id
        FROM website_visits
        GROUP BY visitor_id
        HAVING COUNT(*) > 1
    ) x
")->fetchColumn();
$repeatVisits = (int)$pdo->query("
    SELECT COUNT(*) FROM website_visits
    WHERE visitor_id IN (
        SELECT visitor_id
        FROM website_visits
        GROUP BY visitor_id
        HAVING COUNT(*) > 1
    )
")->fetchColumn();

$totalTimeSeconds = (int)$pdo->query("SELECT COALESCE(SUM(duration_seconds), 0) FROM website_visits")->fetchColumn();
$avgTimePerUserSeconds = $totalUniqueUsers > 0 ? (int)round($totalTimeSeconds / $totalUniqueUsers) : 0;

$dailyStats = $pdo->query("
    SELECT
        DATE(started_at) AS visit_date,
        COUNT(*) AS total_visits,
        COUNT(DISTINCT visitor_id) AS unique_users,
        ROUND(COUNT(*) / NULLIF(COUNT(DISTINCT visitor_id), 0), 2) AS visits_per_user
    FROM website_visits
    GROUP BY DATE(started_at)
    ORDER BY visit_date DESC
    LIMIT 14
")->fetchAll();

$userVisitStats = $pdo->query("
    SELECT
        visitor_id,
        COUNT(*) AS total_visits,
        COALESCE(SUM(duration_seconds), 0) AS total_duration,
        MAX(started_at) AS last_seen
    FROM website_visits
    GROUP BY visitor_id
    ORDER BY total_visits DESC, last_seen DESC
    LIMIT 20
")->fetchAll();

include '../includes/admin_header.php';
?>
<div class="container-fluid">
    <h2>Dashboard</h2>
    <div class="row mt-4">
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h3><?php echo $totalTeams; ?></h3>
                    <p>Teams</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h3><?php echo $totalMatches; ?></h3>
                    <p>Matches</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h3><?php echo $totalGroups; ?></h3>
                    <p>Groups</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h3><?php echo $totalPlayers; ?></h3>
                    <p>Players</p>
                </div>
            </div>
        </div>
    </div>

    <h4 class="mt-3">Website Analytics</h4>
    <div class="row">
        <div class="col-md-3 mb-3">
            <div class="card text-white" style="background:#11315b;">
                <div class="card-body">
                    <h3><?php echo $totalWebsiteVisits; ?></h3>
                    <p class="mb-0">Total Website Visits</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h3><?php echo $totalUniqueUsers; ?></h3>
                    <p class="mb-0">Total Unique Users</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h3><?php echo $newUsers; ?></h3>
                    <p class="mb-0">New Users</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h3><?php echo $repeatUsers; ?></h3>
                    <p class="mb-0">Repeat Users</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Repeat User Visits</h6>
                    <h4 class="mb-0"><?php echo $repeatVisits; ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Average Time Spend Per User</h6>
                    <h4 class="mb-0"><?php echo formatDuration($avgTimePerUserSeconds); ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Total Time Spend</h6>
                    <h4 class="mb-0"><?php echo formatDuration($totalTimeSeconds); ?></h4>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-lg-6 mb-3">
            <div class="card">
                <div class="card-header bg-dark text-white">Daily Visits Per User (Last 14 Days)</div>
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Total Visits</th>
                                <th>Unique Users</th>
                                <th>Visits / User</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($dailyStats)): ?>
                                <?php foreach($dailyStats as $day): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($day['visit_date']); ?></td>
                                        <td><?php echo (int)$day['total_visits']; ?></td>
                                        <td><?php echo (int)$day['unique_users']; ?></td>
                                        <td><?php echo (float)$day['visits_per_user']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center">No analytics data yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-3">
            <div class="card">
                <div class="card-header bg-dark text-white">User Visit Frequency</div>
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>Total Visits</th>
                                <th>Total Time</th>
                                <th>Last Seen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($userVisitStats)): ?>
                                <?php foreach($userVisitStats as $userStat): ?>
                                    <tr>
                                        <td><small><?php echo htmlspecialchars($userStat['visitor_id']); ?></small></td>
                                        <td><?php echo (int)$userStat['total_visits']; ?></td>
                                        <td><?php echo formatDuration((int)$userStat['total_duration']); ?></td>
                                        <td><?php echo htmlspecialchars($userStat['last_seen']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center">No user visit data yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/admin_footer.php'; ?>