<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .sidebar { background: #343a40; min-height: 100vh; }
        .sidebar a { color: white; text-decoration: none; display: block; padding: 10px 15px; }
        .sidebar a:hover { background: #007bff; }
        @media (max-width: 768px) { .sidebar { min-height: auto; margin-bottom: 20px; } }
    </style>
</head>
<body>
<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">⚡ Admin Panel</a>
        <div>
            <a href="../index.php" class="btn btn-sm btn-outline-light me-2">View Site</a>
            <a href="logout.php" class="btn btn-sm btn-danger">Logout</a>
        </div>
    </div>
</nav>

<div class="container-fluid mt-3">
    <div class="row">
        <div class="col-md-2 mb-3">
            <div class="sidebar p-3">
                <a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="teams.php"><i class="fas fa-users"></i> Teams</a>
                <a href="players.php"><i class="fas fa-user-plus"></i> Players</a>
<a href="groups.php"><i class="fas fa-layer-group"></i> Groups</a>
                <a href="group_standings.php"><i class="fas fa-table-list"></i> Group Standings</a>
                <a href="matches.php"><i class="fas fa-calendar-alt"></i> Matches</a>
                <a href="sponsors.php"><i class="fas fa-handshake"></i> Sponsors</a>
            </div>
        </div>
        <div class="col-md-10">
            <?php if(isset($_SESSION['message'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
            <?php endif; ?>
            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>