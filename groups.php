<?php
$page_title = 'Groups';
require_once 'config.php';
include 'includes/header.php';

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

$groups = $pdo->query("SELECT * FROM `groups` ORDER BY group_name")->fetchAll();
?>

<h2>Tournament Groups</h2>
<style>
    .group-team-card {
        border: 1px solid #dbe2ea;
        border-radius: 8px;
        background: #ffffff;
        padding: 7px 9px;
        text-align: center;
        font-size: 0.82rem;
        font-weight: 500;
        color: #334155;
        line-height: 1.25;
        min-height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.18s ease;
        word-break: break-word;
    }
    .group-team-card:hover {
        border-color: #c3d4ea;
        background: #f8fbff;
    }
    .group-team-col {
        margin-bottom: 8px;
    }

    .matchups-card {
        border: 1px solid #d9e2ef;
        border-radius: 10px;
        background: #ffffff;
        overflow: hidden;
    }
    .matchups-header {
        background: #1f3c88;
        color: #fff;
        font-weight: 600;
        font-size: 0.95rem;
        padding: 10px 14px;
        letter-spacing: 0.2px;
    }
    .matchups-list {
        list-style: none;
        margin: 0;
        padding: 0;
    }
    .matchups-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 14px;
        border-bottom: 1px solid #edf1f7;
        font-size: 0.94rem;
    }
    .matchups-item:last-child {
        border-bottom: none;
    }
    .match-no {
        min-width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #eef3ff;
        color: #1f3c88;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.78rem;
    }
    .match-text {
        color: #1f2937;
        line-height: 1.35;
    }
    .match-vs {
        color: #6b7280;
        font-weight: 600;
    }
    @media (max-width: 768px) {
        .group-team-card {
            font-size: 0.76rem;
            min-height: 38px;
            padding: 6px 7px;
        }
    }
</style>
<?php if(count($groups) > 0): ?>
    <div class="accordion" id="groupsAccordion">
        <?php foreach($groups as $index => $group): 
            $stmt = $pdo->prepare("SELECT t.* FROM teams t JOIN group_teams gt ON t.id = gt.team_id WHERE gt.group_id = ? ORDER BY gt.id ASC");
            $stmt->execute([$group['id']]);
            $teamsInGroup = $stmt->fetchAll();
        ?>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $group['id']; ?>">
                        <?php echo htmlspecialchars($group['group_name']); ?> (<?php echo count($teamsInGroup); ?> teams)
                    </button>
                </h2>
                <div id="collapse<?php echo $group['id']; ?>" class="accordion-collapse collapse" data-bs-parent="#groupsAccordion">
                    <div class="accordion-body">
                        <?php if(count($teamsInGroup) > 0): ?>
                            <div class="row">
                                <?php foreach($teamsInGroup as $team): ?>
                                    <div class="col-md-3 col-6 group-team-col">
                                        <a href="team_detail.php?id=<?php echo $team['id']; ?>" class="text-decoration-none">
                                            <div class="group-team-card">
                                                <?php echo htmlspecialchars($team['team_name']); ?>
                                            </div>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                        <?php 
                        $groupMatches = $pdo->prepare("SELECT m.*, t1.team_name as tn1, t2.team_name as tn2 FROM matches m JOIN teams t1 ON m.team1_id = t1.id JOIN teams t2 ON m.team2_id = t2.id JOIN group_teams gt1 ON gt1.team_id = m.team1_id JOIN group_teams gt2 ON gt2.team_id = m.team2_id WHERE gt1.group_id = ? AND gt2.group_id = ? AND m.ground = 'Intra-Group Match'");
                        $groupMatches->execute([$group['id'], $group['id']]);
                        $allGroupMatches = $groupMatches->fetchAll();

                        $orderedTeamIds = array_map('intval', array_column($teamsInGroup, 'id'));
                        $expectedPairs = buildAdjacentCircularPairs($orderedTeamIds);

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
                        <div class="matchups-card">
                            <div class="matchups-header">Official Fixture Order</div>
                            <ul class="matchups-list">
                                <?php foreach($groupMatchesList as $idx => $gm): ?>
                                <li class="matchups-item">
                                    <span class="match-no"><?php echo $idx + 1; ?></span>
                                    <span class="match-text">
                                        <strong><?php echo htmlspecialchars($gm['tn1']); ?></strong>
                                        <span class="match-vs">vs</span>
                                        <strong><?php echo htmlspecialchars($gm['tn2']); ?></strong>
                                    </span>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>

                        <?php 
                        $standingsStmt = $pdo->prepare("SELECT gs.*, t.team_name FROM group_standings gs JOIN teams t ON gs.team_id = t.id WHERE gs.group_id = ? ORDER BY COALESCE(gs.position, 9999), gs.points DESC, gs.nrr DESC");
                        $standingsStmt->execute([$group['id']]);
                        $standings = $standingsStmt->fetchAll();
                        ?>
                        <?php if(count($standings) > 0): ?>
                        <h6 class="mt-4 mb-3">🏆 Points Table</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Pos</th>
                                        <th>Team</th>
                                        <th>Matches</th>
                                        <th>Won</th>
                                        <th>Lost</th>
                                        <th>Drawn</th>
                                        <th>Tied</th>
                                        <th>N/R</th>
                                        <th>Points</th>
                                        <th>NRR</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($standings as $pos => $st): ?>
                                    <tr>
                                        <td><?php echo isset($st['position']) && (int)$st['position'] > 0 ? (int)$st['position'] : ($pos + 1); ?></td>
                                        <td><strong><?php echo htmlspecialchars($st['team_name']); ?></strong></td>
                                        <td><?php echo $st['matches']; ?></td>
                                        <td><?php echo $st['won']; ?></td>
                                        <td><?php echo $st['lost']; ?></td>
                                        <td><?php echo isset($st['drawn']) ? (int)$st['drawn'] : 0; ?></td>
                                        <td><?php echo isset($st['tied']) ? (int)$st['tied'] : (isset($st['tie']) ? (int)$st['tie'] : 0); ?></td>
                                        <td><?php echo $st['nr']; ?></td>
                                        <td><strong><?php echo $st['points']; ?></strong></td>
                                        <td><?php echo number_format($st['nrr'], 3); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <p class="text-muted mt-4">No points data. <a href="admin/group_standings.php">Admin: Add Points</a></p>
                        <?php endif; ?>

                        <?php else: ?>
                            <p class="text-muted">No teams assigned to this group.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>

    <div class="alert alert-info">No groups created yet.</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>