<?php
$page_title = 'About Us';
require_once 'config.php';
include 'includes/header.php';
?>

<style>
    .about-hero {
        background: linear-gradient(135deg, #11315b 0%, #1f4a84 100%);
        color: #fff;
        border-radius: 16px;
        padding: 48px 28px;
        box-shadow: 0 10px 24px rgba(17, 49, 91, 0.22);
        margin-bottom: 24px;
    }
    .about-hero h1 {
        color: #fff;
        margin-bottom: 12px;
        font-size: 2.1rem;
    }
    .about-hero p {
        color: #eaf1ff;
        margin-bottom: 0;
        max-width: 760px;
    }
    .about-section {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.08);
        padding: 24px 22px;
        height: 100%;
    }
    .about-section h3 {
        font-size: 1.15rem;
        margin-bottom: 10px;
        color: #11315b;
    }
    .feature-card {
        background: #fff;
        border: 1px solid #e7edf7;
        border-radius: 12px;
        padding: 18px;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
        height: 100%;
    }
    .feature-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        background: #eff5ff;
        color: #11315b;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        margin-bottom: 10px;
    }
    .contact-block {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.08);
        padding: 24px 22px;
        border-left: 4px solid #11315b;
    }
    @media (max-width: 768px) {
        .about-hero { padding: 30px 16px; border-radius: 12px; }
        .about-hero h1 { font-size: 1.6rem; }
        .about-section, .contact-block { padding: 18px 14px; }
    }
</style>

<div class="about-hero">
    <h1>About 36 LP Cricket Board</h1>
    <p>
        36 LP Cricket Board is a dedicated cricket tournament platform built to make match operations simple, transparent,
        and professional for teams, organizers, and fans.
    </p>
</div>

<div class="row g-3 mb-2">
    <div class="col-lg-4">
        <div class="about-section">
            <h3>What We Do</h3>
            <p class="mb-0">
                We streamline tournament workflows with structured match scheduling, organized team and group management,
                and clear fixture visibility for everyone.
            </p>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="about-section">
            <h3>Our Mission</h3>
            <p class="mb-0">
                Our mission is to improve local cricket management with reliable digital tools that save time,
                increase trust, and keep every stakeholder informed.
            </p>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="about-section">
            <h3>Why Choose Us</h3>
            <p class="mb-0">
                Clean experience, mobile-first design, and practical features built around real tournament needs—
                from operations to audience engagement.
            </p>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6 col-lg-3">
        <div class="feature-card">
            <div class="feature-icon"><i class="fas fa-calendar-check"></i></div>
            <h6 class="mb-1">Match Scheduling</h6>
            <p class="mb-0 small">Plan fixtures with date, time, and ground details in one place.</p>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="feature-card">
            <div class="feature-icon"><i class="fas fa-users-cog"></i></div>
            <h6 class="mb-1">Team Management</h6>
            <p class="mb-0 small">Organize teams, players, and groups with a clean tournament structure.</p>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="feature-card">
            <div class="feature-icon"><i class="fas fa-bolt"></i></div>
            <h6 class="mb-1">Live Updates</h6>
            <p class="mb-0 small">Keep users informed with latest schedule changes and key updates.</p>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="feature-card">
            <div class="feature-icon"><i class="fas fa-trophy"></i></div>
            <h6 class="mb-1">Local Talent Promotion</h6>
            <p class="mb-0 small">Give visibility to local players and teams through a trusted platform.</p>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-12">
        <div class="contact-block">
            <h4 class="mb-2">Contact</h4>
            <p class="mb-1">
                For tournament support, partnerships, and platform-related queries, reach out to the 36 LP Cricket Board team.
            </p>
            <p class="mb-0">
                <strong>Email:</strong> <a href="mailto:kishan95pari@gmail.com">kishan95pari@gmail.com</a>
                &nbsp;|&nbsp;
                <strong>WhatsApp:</strong> <a href="https://wa.me/8141042258" target="_blank">+918141042258</a>
            </p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
