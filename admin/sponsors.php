<?php
require_once '../config.php';
if(!isAdminLoggedIn()) redirect('login.php');

// Add Sponsor
if(isset($_POST['add_sponsor'])) {
    $showInFooter = isset($_POST['show_in_footer']) ? 1 : 0;
    $showInPopup = isset($_POST['show_in_popup']) ? 1 : 0;
    if ($showInFooter === 0 && $showInPopup === 0) {
        $_SESSION['error'] = "Please select at least one ad placement (Footer or Popup).";
        redirect('sponsors.php');
    }

    $imagePath = null;
    if(isset($_FILES['sponsor_image']) && $_FILES['sponsor_image']['error'] === 0) {
        $targetDir = "../assets/uploads/";
        if(!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $ext = pathinfo($_FILES['sponsor_image']['name'], PATHINFO_EXTENSION);
        $filename = time() . "_sponsor." . $ext;
        move_uploaded_file($_FILES['sponsor_image']['tmp_name'], $targetDir . $filename);
        $imagePath = "assets/uploads/" . $filename;
    }
    $stmt = $pdo->prepare("INSERT INTO sponsors (sponsor_text, image_path, status, show_in_footer, show_in_popup) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$_POST['sponsor_text'], $imagePath, isset($_POST['status']) ? 1 : 0, $showInFooter, $showInPopup]);
    $_SESSION['message'] = "Sponsor added!";
    redirect('sponsors.php');
}

// Toggle Status
if(isset($_POST['toggle_status'])) {
    $stmt = $pdo->prepare("UPDATE sponsors SET status = NOT status WHERE id = ?");
    $stmt->execute([$_POST['sponsor_id']]);
    $_SESSION['message'] = "Sponsor status updated!";
    redirect('sponsors.php');
}

// Delete Sponsor
if(isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM sponsors WHERE id=?")->execute([$_GET['delete']]);
    $_SESSION['message'] = "Sponsor deleted!";
    redirect('sponsors.php');
}

$sponsors = $pdo->query("SELECT * FROM sponsors ORDER BY id DESC")->fetchAll();
include '../includes/admin_header.php';
?>

<div class="container-fluid">
    <h2>Sponsor Management</h2>
    <div class="alert alert-info py-2">
        Popup ad ke liye sponsor image upload karein aur status Active rakhein. Website open hone par ads one-by-one popup me dikhengi.
    </div>
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addSponsorModal">+ Add Sponsor</button>
    
    <table class="table table-bordered">
        <thead class="table-dark">
            <tr><th>ID</th><th>Sponsor</th><th>Placement</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php foreach($sponsors as $s): ?>
            <tr>
                <td><?php echo $s['id']; ?></td>
                <td>
                    <?php if($s['image_path'] && file_exists($s['image_path'])): ?>
                        <img src="../<?php echo $s['image_path']; ?>" height="50" alt="Sponsor">
                    <?php else: ?>
                        <?php echo htmlspecialchars($s['sponsor_text']); ?>
                    <?php endif; ?>
                </td>
                <td>
                    <?php
                    $placements = [];
                    if (!empty($s['show_in_footer'])) $placements[] = 'Footer';
                    if (!empty($s['show_in_popup'])) $placements[] = 'Popup';
                    echo !empty($placements) ? htmlspecialchars(implode(' + ', $placements)) : 'Not selected';
                    ?>
                </td>
                <td><?php echo $s['status'] ? "✅ Active" : "❌ Inactive"; ?></td>
                <td>
                    <form method="POST" style="display:inline">
                        <input type="hidden" name="sponsor_id" value="<?php echo $s['id']; ?>">
                        <button type="submit" name="toggle_status" class="btn btn-sm btn-warning">Toggle</button>
                    </form>
                    <a href="?delete=<?php echo $s['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete sponsor?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Add Sponsor Modal -->
<div class="modal fade" id="addSponsorModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Add Sponsor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="text" name="sponsor_text" class="form-control mb-2" placeholder="Sponsor Name/Text">
                    <input type="file" name="sponsor_image" class="form-control mb-2" accept="image/*">
                    <div class="mb-2">
                        <label class="form-label mb-1">Show this ad in:</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="show_in_footer" id="showInFooter" checked>
                            <label class="form-check-label" for="showInFooter">Footer Sponsored Section</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="show_in_popup" id="showInPopup" checked>
                            <label class="form-check-label" for="showInPopup">Popup Ad</label>
                        </div>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="status" id="sponsorStatus" checked>
                        <label class="form-check-label" for="sponsorStatus">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add_sponsor" class="btn btn-primary">Add Sponsor</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/admin_footer.php'; ?>