<?php
$page_title = 'All Teams';
require_once 'config.php';
include 'includes/header.php';

$teams = $pdo->query("SELECT * FROM teams ORDER BY team_name")->fetchAll();
?>

<h2 class="mb-4">🏏 Tournament Teams</h2>

<?php if(count($teams) > 0): ?>
    <div class="row mb-3">
        <div class="col-md-6">
            <input
                type="text"
                id="teamSearchInput"
                class="form-control"
                placeholder="Search team (e.g. in for India)"
                autocomplete="off"
            >
        </div>
    </div>
<?php endif; ?>

<div class="row">
    <?php if(count($teams) > 0): ?>
        <?php foreach($teams as $team): ?>
            <div class="col-md-4 col-sm-6 mb-4 team-card" data-team-name="<?php echo htmlspecialchars(strtolower($team['team_name']), ENT_QUOTES); ?>">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <div class="team-icon mx-auto">
                            <?php echo strtoupper(substr($team['team_name'], 0, 2)); ?>
                        </div>
                        <h5><?php echo htmlspecialchars($team['team_name']); ?></h5>
                        <p class="text-muted">Captain: <?php echo htmlspecialchars($team['captain_name']); ?></p>
                        <a href="team_detail.php?id=<?php echo $team['id']; ?>" class="btn btn-outline-primary btn-sm">View Details</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12"><div class="alert alert-info">No teams added yet.</div></div>
    <?php endif; ?>
</div>

<?php if(count($teams) > 0): ?>
    <div id="noTeamFoundMessage" class="alert alert-warning d-none">No team found for your search.</div>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var searchInput = document.getElementById('teamSearchInput');
        var teamCards = document.querySelectorAll('.team-card');
        var noTeamFoundMessage = document.getElementById('noTeamFoundMessage');

        if (!searchInput || teamCards.length === 0) return;

        searchInput.addEventListener('input', function () {
            var query = searchInput.value.trim().toLowerCase();
            var visibleCount = 0;

            teamCards.forEach(function (card) {
                var teamName = (card.getAttribute('data-team-name') || '').toLowerCase();
                var isMatch = query === '' || teamName.indexOf(query) !== -1;
                card.style.display = isMatch ? '' : 'none';
                if (isMatch) visibleCount++;
            });

            if (noTeamFoundMessage) {
                noTeamFoundMessage.classList.toggle('d-none', visibleCount > 0);
            }
        });
    });
    </script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>