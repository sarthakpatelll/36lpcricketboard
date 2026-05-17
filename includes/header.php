<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>36LP Premier League - <?php echo $page_title ?? 'Home'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; }
        body { background: #f0f2f5; font-family: 'Segoe UI', sans-serif; overflow-x: hidden; }
        .navbar-brand { font-weight: bold; }
        .navbar-brand, .navbar-brand span { white-space: normal; word-break: break-word; }
        .footer { background: #2c3e50; color: white; padding: 8px 0; margin-top: 20px; }
        .footer p { margin: 0; font-size: 0.92rem; }
        .sponsor-scroll { background: white; padding: 6px 0; overflow: hidden; white-space: nowrap; border-top: 1px solid #ddd; border-bottom: 1px solid #ddd; margin-bottom: 8px; }
        .sponsor-item { display: inline-block; margin: 0 30px; vertical-align: middle; }
        .sponsor-item img {
            width: 320px;
            aspect-ratio: 16 / 5;
            height: auto;
            object-fit: contain;
        }
        .card { border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: 0.3s; }
        .card:hover { transform: translateY(-3px); }
        .team-icon { width: 70px; height: 70px; background: #007bff; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 28px; margin: 0 auto 15px; }
        .table { background: #fff; }
        .table td, .table th { vertical-align: middle; }

        @media (max-width: 768px) {
            h1 { font-size: 1.6rem; }
            h2 { font-size: 1.35rem; }
            h3 { font-size: 1.2rem; }
            .lead { font-size: 0.98rem; }

            .container, .container-fluid { padding-left: 12px; padding-right: 12px; }
            .navbar-brand { font-size: 0.98rem; line-height: 1.25; max-width: 75vw; }
            .navbar-nav .nav-link { padding-top: 10px; padding-bottom: 10px; }

            .card { border-radius: 8px; }
            .card-body { padding: 0.85rem; }
            .btn { font-size: 0.9rem; }

            .team-icon { width: 58px; height: 58px; font-size: 22px; }

            .sponsor-item { margin: 0 14px; font-size: 0.9rem; }
            .sponsor-item img { width: 300px; }

            .table-responsive { border-radius: 8px; }
            .table { font-size: 0.84rem; min-width: 560px; }
            .table td, .table th { padding: 0.45rem; white-space: nowrap; }

            .modal-dialog { margin: 0.5rem; }
            .modal-body { padding: 0.7rem; }
            #popupAdImage { max-width: 100% !important; max-height: 70vh !important; }
            #popupFallbackText { font-size: 0.92rem; line-height: 1.4; }
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="<?php echo SITE_URL; ?>">🏏 36 LP Cricket Board</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="<?php echo SITE_URL; ?>">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo SITE_URL; ?>teams.php">Teams</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo SITE_URL; ?>groups.php">Groups</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo SITE_URL; ?>schedule.php">Schedule</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo SITE_URL; ?>results.php">Results</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo SITE_URL; ?>about.php">About Us</a></li>
            </ul>
        </div>
    </div>
</nav>

<?php
$popupAds = getActivePopupAds($pdo);
?>
<div class="modal fade" id="popupAdModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Sponsored Ad</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="popupAdImage" src="" alt="Popup Advertisement" style="width:100%; max-width:420px; max-height:80vh; height:auto; object-fit:contain; display:none;">
                <div id="popupFallbackText" class="py-3" style="display:none;">
                    <strong>Advertise Your Business With Us :</strong>
                    📞 Contact (Kishan Patel):
                    <a href="tel:8141042258">8141042258</a>
                    (call / whatsapp)
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var trackerEndpoint = '<?php echo SITE_URL; ?>track_visit.php';
    var visitorStorageKey = 'ct_visitor_id';
    var visitStorageKey = 'ct_active_visit_id';
    var lastActivityKey = 'ct_last_activity_at';
    var inactivityTimeoutMs = 10 * 60 * 1000; // 10 minutes

    function generateVisitorId() {
        return 'v_' + Date.now().toString(36) + '_' + Math.random().toString(36).slice(2, 10);
    }

    function touchActivity() {
        localStorage.setItem(lastActivityKey, String(Date.now()));
    }

    function sendPing(visitId) {
        if (!visitId) return;
        fetch(trackerEndpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'ping', visit_id: Number(visitId) }),
            keepalive: true
        }).catch(function () {});
        touchActivity();
    }

    var visitorId = localStorage.getItem(visitorStorageKey);
    if (!visitorId) {
        visitorId = generateVisitorId();
        localStorage.setItem(visitorStorageKey, visitorId);
    }

    var existingVisitId = parseInt(localStorage.getItem(visitStorageKey) || '0', 10);
    var lastActivityAt = parseInt(localStorage.getItem(lastActivityKey) || '0', 10);
    var now = Date.now();
    var canReuseVisit = existingVisitId > 0 && lastActivityAt > 0 && (now - lastActivityAt) < inactivityTimeoutMs;

    if (canReuseVisit) {
        sendPing(existingVisitId);
    } else {
        fetch(trackerEndpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'start',
                visitor_id: visitorId,
                page_url: window.location.pathname + window.location.search
            }),
            keepalive: true
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data && data.success && data.visit_id) {
                localStorage.setItem(visitStorageKey, String(data.visit_id));
                touchActivity();
            }
        })
        .catch(function () {});
    }

    setInterval(function () {
        var visitId = parseInt(localStorage.getItem(visitStorageKey) || '0', 10);
        if (!visitId) return;
        sendPing(visitId);
    }, 30000);

    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) {
            var visitId = parseInt(localStorage.getItem(visitStorageKey) || '0', 10);
            if (visitId) sendPing(visitId);
        }
    });

    window.addEventListener('beforeunload', touchActivity);
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var popupIntervalMs = 10 * 60 * 1000; // 10 minutes
    var lastShownAt = parseInt(localStorage.getItem('lastPopupShownAt') || '0', 10);
    if (lastShownAt && (Date.now() - lastShownAt) < popupIntervalMs) {
        return;
    }

    var popupAds = <?php
        $popupPayload = array_map(function ($ad) {
            return [
                'id' => (int)$ad['id'],
                'image_path' => $ad['image_path']
            ];
        }, $popupAds);
        echo json_encode($popupPayload);
    ?>;

    var adImage = document.getElementById('popupAdImage');
    var fallbackText = document.getElementById('popupFallbackText');
    if (!adImage || !fallbackText) return;

    if (Array.isArray(popupAds) && popupAds.length > 0) {
        var lastAdId = localStorage.getItem('lastPopupAdId');
        var currentIndex = 0;

        if (lastAdId !== null) {
            var lastIndex = popupAds.findIndex(function (ad) { return String(ad.id) === String(lastAdId); });
            if (lastIndex !== -1) {
                currentIndex = (lastIndex + 1) % popupAds.length;
            }
        }

        var selectedAd = popupAds[currentIndex];
        if (selectedAd && selectedAd.image_path) {
            adImage.src = '<?php echo SITE_URL; ?>' + selectedAd.image_path.replace(/^\/+/, '');
            adImage.style.display = 'inline-block';
            fallbackText.style.display = 'none';
            localStorage.setItem('lastPopupAdId', String(selectedAd.id));
        } else {
            adImage.style.display = 'none';
            fallbackText.style.display = 'block';
        }
    } else {
        adImage.style.display = 'none';
        fallbackText.style.display = 'block';
    }

    var adModalEl = document.getElementById('popupAdModal');
    if (!adModalEl || typeof bootstrap === 'undefined') return;

    var adModal = new bootstrap.Modal(adModalEl);
    adModal.show();
    localStorage.setItem('lastPopupShownAt', String(Date.now()));
});
</script>

<div class="container mt-3">
    <?php if(isset($_SESSION['message'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>