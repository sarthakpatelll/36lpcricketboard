<?php
require_once '../config.php';
if(!function_exists('isAdminLoggedIn')) {
    function isAdminLoggedIn() {
        return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
    }
}
if(!isAdminLoggedIn()) redirect('../admin/login.php');

// Add
if(isset($_POST['add_standing'])) {
    try {
        $stmt = $pdo->prepare("INSERT INTO group_standings (group_id, team_id, position, `matches`, won, lost, drawn, tied, nr, points, nrr) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_POST['group_id'], $_POST['team_id'], $_POST['position'], $_POST['matches'], $_POST['won'], $_POST['lost'], $_POST['drawn'], $_POST['tied'], $_POST['nr'], $_POST['points'], $_POST['nrr']]);
        $_SESSION['message'] = "Standing added!";
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error adding: " . $e->getMessage();
    }
    redirect('group_standings.php');
}

// Edit
if(isset($_POST['edit_standing'])) {
    try {
        $stmt = $pdo->prepare("UPDATE group_standings SET group_id=?, team_id=?, position=?, `matches`=?, won=?, lost=?, drawn=?, tied=?, nr=?, points=?, nrr=? WHERE id=?");
        $stmt->execute([$_POST['group_id'], $_POST['team_id'], $_POST['position'], $_POST['matches'], $_POST['won'], $_POST['lost'], $_POST['drawn'], $_POST['tied'], $_POST['nr'], $_POST['points'], $_POST['nrr'], $_POST['standing_id']]);
        $_SESSION['message'] = "Standing updated!";
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error updating: " . $e->getMessage();
    }
    redirect('group_standings.php');
}

// Delete
if(isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM group_standings WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        $_SESSION['message'] = "Standing deleted!";
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error deleting: " . $e->getMessage();
    }
    redirect('group_standings.php');
}

$groups = $pdo->query("SELECT * FROM `groups` ORDER BY group_name")->fetchAll();
$teams = $pdo->query("SELECT * FROM teams ORDER BY team_name")->fetchAll();
$standings = [];
try {
    $standings = $pdo->query("SELECT gs.*, g.group_name, t.team_name FROM group_standings gs LEFT JOIN `groups` g ON gs.group_id = g.id LEFT JOIN teams t ON gs.team_id = t.id ORDER BY g.group_name, COALESCE(gs.position, 9999), gs.points DESC, gs.nrr DESC")->fetchAll();
} catch(PDOException $e) {
    // Table not exist yet, empty
}

include '../includes/admin_header.php';
?>

<div class="container-fluid">
    <h2>Group Standings Management</h2>
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addModal">+ Add Standing</button>
    
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Position</th>
                    <th>Group</th>
                    <th>Team</th>
                    <th>Matches</th>
                    <th>Won</th>
                    <th>Lost</th>
                    <th>Drawn</th>
                    <th>Tied</th>
                    <th>N/R</th>
                    <th>Pts</th>
                    <th>NRR</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($standings as $standing): ?>
                <tr>
                    <td><?php echo isset($standing['position']) ? (int)$standing['position'] : '-'; ?></td>
                    <td><?php echo htmlspecialchars($standing['group_name']); ?></td>
                    <td><?php echo htmlspecialchars($standing['team_name']); ?></td>
                    <td><?php echo $standing['matches']; ?></td>
                    <td><?php echo $standing['won']; ?></td>
                    <td><?php echo $standing['lost']; ?></td>
                    <td><?php echo isset($standing['drawn']) ? (int)$standing['drawn'] : 0; ?></td>
                    <td><?php echo isset($standing['tied']) ? (int)$standing['tied'] : 0; ?></td>
                    <td><?php echo $standing['nr']; ?></td>
                    <td><strong><?php echo $standing['points']; ?></strong></td>
                    <td><?php echo number_format($standing['nrr'],3); ?></td>
                    <td>
                        <button class="btn btn-sm btn-warning" onclick="editStanding(<?php echo $standing['id']; ?>, <?php echo $standing['group_id']; ?>, <?php echo $standing['team_id']; ?>, '<?php echo (int)($standing['position'] ?? 0); ?>', '<?php echo $standing['matches']; ?>', '<?php echo $standing['won']; ?>', '<?php echo $standing['lost']; ?>', '<?php echo (int)($standing['drawn'] ?? 0); ?>', '<?php echo (int)($standing['tied'] ?? 0); ?>', '<?php echo $standing['nr']; ?>', '<?php echo $standing['points']; ?>', '<?php echo $standing['nrr']; ?>')">Edit</button>
                        <a href="?delete=<?php echo $standing['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5>Add Group Standing</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label>Group</label>
                            <select name="group_id" class="form-select" required>
                                <option value="">Select Group</option>
                                <?php
                                $groups = $pdo->query("SELECT * FROM `groups` ORDER BY group_name")->fetchAll();
                                foreach($groups as $g) echo "<option value='{$g['id']}'>" . htmlspecialchars($g['group_name']) . "</option>";
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>Team</label>
                            <select name="team_id" class="form-select" required>
                                <option value="">Select Team</option>
                                <?php
                                $teams = $pdo->query("SELECT * FROM teams ORDER BY team_name")->fetchAll();
                                foreach($teams as $t) echo "<option value='{$t['id']}'>" . htmlspecialchars($t['team_name']) . "</option>";
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-3"><input type="number" name="position" class="form-control" min="1" placeholder="Position" value="1"></div>
                        <div class="col-md-3"><input type="number" name="matches" class="form-control" min="0" placeholder="Matches" value="0"></div>
                        <div class="col-md-3"><input type="number" name="won" class="form-control" min="0" placeholder="Won" value="0"></div>
                        <div class="col-md-3"><input type="number" name="lost" class="form-control" min="0" placeholder="Lost" value="0"></div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-3"><input type="number" name="drawn" class="form-control" min="0" placeholder="Drawn" value="0"></div>
                        <div class="col-md-3"><input type="number" name="tied" class="form-control" min="0" placeholder="Tied" value="0"></div>
                        <div class="col-md-3"><input type="number" name="nr" class="form-control" min="0" placeholder="N/R" value="0"></div>
                        <div class="col-md-3"><input type="number" name="points" class="form-control" min="0" placeholder="Pts" value="0"></div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12"><input type="number" name="nrr" class="form-control" step="0.001" placeholder="NRR e.g. 1.234" value="0.000"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add_standing" class="btn btn-primary">Add</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5>Edit Standing</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit_standing_id" name="standing_id">
                    <div class="row">
                        <div class="col-md-6">
                            <label>Group</label>
                            <select id="edit_group_id" name="group_id" class="form-select" required>
                                <option value="">Select</option>
                                <?php foreach($groups as $g): ?>
                                    <option value="<?php echo $g['id']; ?>"><?php echo htmlspecialchars($g['group_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>Team</label>
                            <select id="edit_team_id" name="team_id" class="form-select" required>
                                <option value="">Select</option>
                                <?php foreach($teams as $t): ?>
                                    <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['team_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-3"><input type="number" name="position" id="edit_position" class="form-control" min="1" placeholder="Position"></div>
                        <div class="col-md-3"><input type="number" name="matches" id="edit_matches" class="form-control" min="0" placeholder="Matches"></div>
                        <div class="col-md-3"><input type="number" name="won" id="edit_won" class="form-control" min="0" placeholder="Won"></div>
                        <div class="col-md-3"><input type="number" name="lost" id="edit_lost" class="form-control" min="0" placeholder="Lost"></div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-3"><input type="number" name="drawn" id="edit_drawn" class="form-control" min="0" placeholder="Drawn"></div>
                        <div class="col-md-3"><input type="number" name="tied" id="edit_tied" class="form-control" min="0" placeholder="Tied"></div>
                        <div class="col-md-3"><input type="number" name="nr" id="edit_nr" class="form-control" min="0" placeholder="N/R"></div>
                        <div class="col-md-3"><input type="number" name="points" id="edit_points" class="form-control" min="0" placeholder="Pts"></div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12"><input type="number" name="nrr" id="edit_nrr" class="form-control" step="0.001" placeholder="NRR"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="edit_standing" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editStanding(id, gid, tid, pos, m, w, l, d, t, nr, pt, nrr) {
    document.getElementById('edit_standing_id').value = id;
    document.getElementById('edit_group_id').value = gid;
    document.getElementById('edit_team_id').value = tid;
    document.getElementById('edit_position').value = pos;
    document.getElementById('edit_matches').value = m;
    document.getElementById('edit_won').value = w;
    document.getElementById('edit_lost').value = l;
    document.getElementById('edit_drawn').value = d;
    document.getElementById('edit_tied').value = t;
    document.getElementById('edit_nr').value = nr;
    document.getElementById('edit_points').value = pt;
    document.getElementById('edit_nrr').value = nrr;
    new bootstrap.Modal(document.getElementById('editModal')).show();
}
</script>

<?php include '../includes/admin_footer.php'; ?>
