<?php
require_once '../config.php';
if(!isAdminLoggedIn()) redirect('login.php');

// Add Team
if(isset($_POST['add_team'])) {
    $stmt = $pdo->prepare("INSERT INTO teams (team_name, captain_name) VALUES (?, ?)");
    $stmt->execute([$_POST['team_name'], $_POST['captain_name']]);
    $_SESSION['message'] = "Team added successfully!";
    redirect('teams.php');
}

// Edit Team
if(isset($_POST['edit_team'])) {
    $stmt = $pdo->prepare("UPDATE teams SET team_name=?, captain_name=? WHERE id=?");
    $stmt->execute([$_POST['team_name'], $_POST['captain_name'], $_POST['team_id']]);
    $_SESSION['message'] = "Team updated!";
    redirect('teams.php');
}

// Delete Team
if(isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM teams WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    $_SESSION['message'] = "Team deleted!";
    redirect('teams.php');
}

$teams = $pdo->query("SELECT * FROM teams ORDER BY team_name")->fetchAll();
include '../includes/admin_header.php';
?>

<div class="container-fluid">
    <h2>Team Management</h2>
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addModal">+ Add New Team</button>
    
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr><th>ID</th><th>Team Name</th><th>Captain</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach($teams as $team): ?>
                <tr>
                    <td><?php echo $team['id']; ?></td>
                    <td><?php echo htmlspecialchars($team['team_name']); ?></td>
                    <td><?php echo htmlspecialchars($team['captain_name']); ?></td>
                    <td>
                        <button class="btn btn-sm btn-warning" onclick="editTeam(<?php echo $team['id']; ?>, '<?php echo addslashes($team['team_name']); ?>', '<?php echo addslashes($team['captain_name']); ?>')">Edit</button>
                        <a href="?delete=<?php echo $team['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this team?')">Delete</a>
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
                    <h5>Add New Team</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="text" name="team_name" class="form-control mb-2" placeholder="Team Name" required>
                    <input type="text" name="captain_name" class="form-control" placeholder="Captain Name" required>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add_team" class="btn btn-primary">Save</button>
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
                    <h5>Edit Team</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="team_id" id="edit_id">
                    <input type="text" name="team_name" id="edit_name" class="form-control mb-2" required>
                    <input type="text" name="captain_name" id="edit_captain" class="form-control" required>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="edit_team" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editTeam(id, name, captain) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_captain').value = captain;
    new bootstrap.Modal(document.getElementById('editModal')).show();
}
</script>

<?php include '../includes/admin_footer.php'; ?>