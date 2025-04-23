<!-- filepath: includes/mobile-menu.php -->
<!-- Mobile Navigation Overlay -->
<div class="mobile-menu-overlay">
    <div class="mobile-menu-content">
        <div class="mobile-menu-header">
            <div class="user-info">
                <div class="user-avatar">
                    <?php if (isset($_SESSION['profile_picture'])): ?>
                        <img src="<?php echo $_SESSION['profile_picture']; ?>" alt="Profile Picture">
                    <?php else: ?>
                        <?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?>
                    <?php endif; ?>
                </div>
                <div class="user-details">
                    <h3><?php echo $_SESSION['full_name']; ?></h3>
                    <p><?php echo $_SESSION['role']; ?></p>
                </div>
            </div>
        </div>
        <div class="mobile-search">
            <input type="text" placeholder="Search activities..." aria-label="Search activities">
            <button type="button" aria-label="Search">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </button>
        </div>
        <nav class="mobile-nav">
            <!-- Use the current page to highlight active links -->
            <?php $current_page = basename($_SERVER['PHP_SELF'], '.php'); ?>
            <a href="<?php echo BASE_URL; ?>/dashboard" class="mobile-nav-item <?php echo ($current_page == 'dashboard') ? 'active' : ''; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
                Dashboard
            </a>
            <!-- Add all your navigation links here -->
        </nav>
        <div class="mobile-menu-footer">
            <!-- Footer links (profile, settings, logout) -->
        </div>
    </div>
</div>