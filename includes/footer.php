</div>

<?php
$sponsors = getActiveSponsors($pdo);
?>
<div class="mt-1 text-center">
    <h6 class="mb-1">Sponsored</h6>

    <?php if(!empty($sponsors)): ?>
        <div class="sponsor-scroll">
            <div style="display: inline-block; animation: scroll 20s linear infinite;">
                <?php foreach($sponsors as $s): ?>
                    <div class="sponsor-item">
                        <?php if($s['image_path'] && file_exists($s['image_path'])): ?>
                            <img src="<?php echo $s['image_path']; ?>" alt="Sponsor">
                        <?php else: ?>
                            <strong><?php echo htmlspecialchars($s['sponsor_text']); ?></strong>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <?php foreach($sponsors as $s): ?>
                    <div class="sponsor-item">
                        <?php if($s['image_path'] && file_exists($s['image_path'])): ?>
                            <img src="<?php echo $s['image_path']; ?>" alt="Sponsor">
                        <?php else: ?>
                            <strong><?php echo htmlspecialchars($s['sponsor_text']); ?></strong>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="sponsor-scroll">
            <div style="display: inline-block; animation: scroll 20s linear infinite;">
                <div class="sponsor-item">
                    <strong>Advertise Your Business With Us:</strong>
                    📞 Contact (Kishan Patel) : <a href="tel:8141042258">8141042258</a>
                    (Call/WhatsApp)
                </div>
                <div class="sponsor-item">
                    <strong>Advertise Your Business With Us:</strong>
                    📞 Contact (Kishan Patel) : <a href="tel:8141042258">8141042258</a>
                    (Call/WhatsApp)
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
    @keyframes scroll {
        0% { transform: translateX(0); }
        100% { transform: translateX(-50%); }
    }
</style>

<footer class="footer">
    <div class="container text-center">
        <p>
  &copy; <?php echo date('Y'); ?> 36 LP Crecket Board | 
  Developed by - 
  <a href="https://instagram.com/itz_kittu_007__" target="_blank">
    Sarthak Patel
  </a>
</p>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>