<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL);
    exit();
}

// Make sure we have club details
if (!isset($pageData['details']) || !isset($pageData['is_officer']) || !$pageData['is_officer']) {
    header("Location: " . BASE_URL . "/cca");
    exit();
}

$clubDetails = $pageData['details'];
$gallery = $pageData['gallery'] ?? [];
$activities = $pageData['activities'] ?? [];
$locations = $pageData['locations'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrichment Point Passport - <?php echo htmlspecialchars($clubDetails['club_name'] ?? 'Club Editor'); ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/dashboard.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/cca-details.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/cca-edit.css">
</head>

<body>
    <div class="dashboard-container">
        <!-- Top Navigation Bar -->
        <nav class="top-nav">
            <div class="nav-left">
                <img src="<?php echo BASE_URL; ?>/assets/images/logo/politeknik-brunei-logo.png" alt="PB Logo" class="nav-logo">
                <h2>Enrichment Point Passport</h2>
            </div>
            <div class="nav-right">
                <div class="search-bar">
                    <input type="text" placeholder="Search activities..." aria-label="Search activities">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </div>
                <div class="nav-actions">
                    <button class="notification-btn" aria-label="Notifications">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                        <span class="notification-badge" aria-label="3 notifications">3</span>
                    </button>
                    <div class="profile-dropdown">
                        <div class="profile-trigger" tabindex="0" role="button" aria-haspopup="true" aria-expanded="false">
                            <div class="user-avatar">
                                <?php if (isset($_SESSION['profile_picture'])): ?>
                                    <img src="<?php echo $_SESSION['profile_picture']; ?>" alt="Profile Picture">
                                <?php else: ?>
                                    <?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?>
                                <?php endif; ?>
                            </div>
                            <span class="user-name"><?php echo explode(' ', $_SESSION['full_name'])[0]; ?></span>
                        </div>
                        <div class="dropdown-menu" role="menu">
                            <a href="<?php echo BASE_URL; ?>/profile" class="dropdown-item" role="menuitem">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                My Profile
                            </a>
                            <a href="<?php echo BASE_URL; ?>/settings" class="dropdown-item" role="menuitem">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="3"></circle>
                                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                                </svg>
                                Settings
                            </a>
                            <div class="dropdown-divider" role="separator"></div>
                            <a href="<?php echo BASE_URL; ?>/logout" class="dropdown-item" role="menuitem">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                    <polyline points="16 17 21 12 16 7"></polyline>
                                    <line x1="21" y1="12" x2="9" y2="12"></line>
                                </svg>
                                Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <div class="main-content">
            <div class="main-wrapper">
                <div class="tab-navigation">
                    <a href="<?php echo BASE_URL; ?>/dashboard" class="tab-item">Dashboard</a>
                    <a href="<?php echo BASE_URL; ?>/ep" class="tab-item">Enrichment Point</a>
                    <a href="<?php echo BASE_URL; ?>/events" class="tab-item">Events</a>
                    <a href="<?php echo BASE_URL; ?>/cca" class="tab-item active">CCAs</a>
                    <a href="<?php echo BASE_URL; ?>/history" class="tab-item">History</a>
                </div>
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <?php
                        echo $_SESSION['success'];
                        unset($_SESSION['success']);
                        ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-error">
                        <?php
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                        ?>
                    </div>
                <?php endif; ?>

                <!-- Club Edit Header Section -->
                <div class="club-edit-header <?php echo strtolower(str_replace(' ', '-', $clubDetails['category'] ?? '')); ?>">
                    <div class="back-link">
                        <?php
                        // Get club slug and handle URL construction properly
                        $clubSlug = $pageData['clubSlug'] ?? '';
                        
                        // If we have a direct slug from pageData, use it
                        if (!empty($clubSlug)) {
                            $clubUrl = BASE_URL . "/cca/" . $clubSlug;
                        } 
                        // Otherwise try to get it from the mapping
                        else if (isset($clubDetails['club_id'])) {
                            // Fallback to direct URL using club_id if no mapping is available
                            $clubUrl = BASE_URL . "/cca?club_id=" . $clubDetails['club_id'];
                            
                            // If we have the club mapping data, use the nice URL format
                            if (isset($pageData['clubMapping']) && 
                                isset($clubDetails['category']) && 
                                isset($clubDetails['club_name'])) {
                                
                                $category = strtolower($clubDetails['category']);
                                $clubName = $clubDetails['club_name'];
                                
                                if (isset($pageData['clubMapping'][$category][$clubName])) {
                                    $clubNameSlug = $pageData['clubMapping'][$category][$clubName];
                                    $clubUrl = BASE_URL . "/cca/" . $clubNameSlug;
                                }
                            }
                        } else {
                            // Fallback to CCA main page if we can't determine the club
                            $clubUrl = BASE_URL . "/cca";
                        }
                        ?>
                        <a href="<?php echo $clubUrl; ?>" class="back-link-anchor" onclick="window.location.href='<?php echo $clubUrl; ?>'; return false;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M19 12H5M12 19l-7-7 7-7" />
                            </svg>
                            <span>Back to Club Page</span>
                        </a>
                    </div>
                    <div class="club-header-content">
                        <div class="club-logo-large">
                            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <?php
                                // Display different icon based on category
                                $category = strtolower($clubDetails['category'] ?? '');
                                switch ($category) {
                                    case 'sports':
                                        echo '<circle cx="12" cy="12" r="10"/>';
                                        break;
                                    case 'arts':
                                        echo '<path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>';
                                        break;
                                    case 'culture':
                                        echo '<path d="M3 21h18M3 10h18M3 18h18M3 7h18"/>';
                                        break;
                                    case 'academic':
                                        echo '<path d="M12 2l10 6.5v7L12 22 2 15.5v-7L12 2z"/>';
                                        break;
                                    case 'martial arts':
                                    case 'martial-arts':
                                        echo '<path d="M14 14l-4 4M18 10l-4 4M10 18l-4 4"/>';
                                        break;
                                    default:
                                        echo '<circle cx="12" cy="12" r="10"/>';
                                }
                                ?>
                            </svg>
                        </div>
                        <div class="club-info-main">
                            <h1>Manage: <?php echo htmlspecialchars($clubDetails['club_name'] ?? 'Club'); ?></h1>
                            <div class="club-meta">
                                <span class="category-badge"><?php echo htmlspecialchars($clubDetails['category'] ?? ''); ?></span>
                                <span class="member-count">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                        <circle cx="9" cy="7" r="4" />
                                    </svg>
                                    <?php echo $clubDetails['member_count'] ?? 0; ?> members
                                </span>
                                <span class="points-info">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                    </svg>
                                    2 points/session
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Edit Navigation Tabs -->
                <div class="edit-tabs">
                    <button class="edit-tab active" data-tab="info">Basic Info</button>
                    <button class="edit-tab" data-tab="gallery">Gallery</button>
                    <button class="edit-tab" data-tab="activities">Activities</button>
                    <button class="edit-tab" data-tab="locations">Locations</button>
                </div>
                
                <!-- Tab Panels -->
                <div id="info-panel" class="edit-panel active">
                    <div class="edit-form">
                        <h2>Basic Club Information</h2>
                        <form method="POST" action="<?php echo BASE_URL; ?>/cca">
                            <input type="hidden" name="action" value="cca_manage">
                            <input type="hidden" name="club_id" value="<?php echo $clubDetails['club_id']; ?>">
                            <input type="hidden" name="operation" value="update_info">
                            <input type="hidden" name="redirect_to_details" value="1">
                            
                            <!-- Read-only fields -->
                            <div class="edit-form-row">
                                <div class="edit-form-group">
                                    <label class="edit-form-label">Club Name (Read-only)</label>
                                    <input type="text" class="edit-form-input" value="<?php echo htmlspecialchars($clubDetails['club_name'] ?? ''); ?>" readonly disabled>
                                </div>
                                <div class="edit-form-group">
                                    <label class="edit-form-label">Category (Read-only)</label>
                                    <input type="text" class="edit-form-input" value="<?php echo htmlspecialchars($clubDetails['category'] ?? ''); ?>" readonly disabled>
                                </div>
                            </div>
                            
                            <!-- Editable fields -->
                            <div class="edit-form-group">
                                <label class="edit-form-label" for="description">Description</label>
                                <textarea id="description" name="description" class="edit-form-textarea" rows="5"><?php echo htmlspecialchars($clubDetails['description'] ?? ''); ?></textarea>
                                <div class="form-hint">Provide a detailed description of your club, its goals, and activities.</div>
                            </div>
                            
                            <div class="edit-form-row">
                                <div class="edit-form-group">
                                    <label class="edit-form-label" for="advisor">Club Advisor</label>
                                    <input type="text" id="advisor" name="advisor" class="edit-form-input" value="<?php echo htmlspecialchars($clubDetails['advisor'] ?? ''); ?>">
                                </div>
                                <div class="edit-form-group">
                                    <label class="edit-form-label" for="president">Club President</label>
                                    <input type="text" id="president" name="president" class="edit-form-input" value="<?php echo htmlspecialchars($clubDetails['president'] ?? ''); ?>">
                                </div>
                            </div>
                            
                            <div class="edit-form-row">
                                <div class="edit-form-group">
                                    <label class="edit-form-label" for="contact_email">Contact Email</label>
                                    <input type="email" id="contact_email" name="contact_email" class="edit-form-input" value="<?php echo htmlspecialchars($clubDetails['contact_email'] ?? ''); ?>">
                                </div>
                                <div class="edit-form-group">
                                    <label class="edit-form-label" for="membership_fee">Membership Fee</label>
                                    <input type="text" id="membership_fee" name="membership_fee" class="edit-form-input" value="<?php echo htmlspecialchars($clubDetails['membership_fee'] ?? ''); ?>">
                                    <div class="form-hint">Example: $5/semester, Free, etc.</div>
                                </div>
                            </div>
                            
                            <div class="edit-form-group">
                                <label class="edit-form-label" for="meeting_schedule">Meeting Schedule</label>
                                <input type="text" id="meeting_schedule" name="meeting_schedule" class="edit-form-input" value="<?php echo htmlspecialchars($clubDetails['meeting_schedule'] ?? ''); ?>">
                                <div class="form-hint">Example: Every Monday, 2–4 p.m.; Bi-weekly on Fridays, etc.</div>
                            </div>
                            
                            <button type="submit" class="submit-btn primary-btn" onclick="this.form.submit()">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                    <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                    <polyline points="7 3 7 8 15 8"></polyline>
                                </svg>
                                Save Changes
                            </button>
                        </form>
                    </div>
                </div>
                
                <div id="gallery-panel" class="edit-panel">
                    <div class="edit-form">
                        <h2>Club Gallery</h2>
                        
                        <!-- Gallery Upload Form -->
                        <form method="POST" action="<?php echo BASE_URL; ?>/cca" enctype="multipart/form-data" class="gallery-upload-form">
                            <input type="hidden" name="action" value="cca_manage">
                            <input type="hidden" name="club_id" value="<?php echo $clubDetails['club_id']; ?>">
                            <input type="hidden" name="operation" value="add_gallery">
                            <input type="hidden" name="redirect_to_details" value="1">
                            
                            <div class="gallery-upload" id="gallery-upload-area">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7"></path>
                                    <path d="M16 5h6v6"></path>
                                    <path d="M8 12l5 5 8-8"></path>
                                </svg>
                                <div class="gallery-upload-text">Drag and drop an image or click to upload</div>
                                <div class="gallery-upload-hint">Supported formats: JPEG, PNG, GIF. Max size: 5MB</div>
                                <input type="file" name="gallery_image" id="gallery-upload-input" accept="image/*" class="hidden-input">
                            </div>
                            
                            <div id="gallery-upload-preview" class="gallery-preview" style="display: none;"></div>
                            
                            <div class="upload-actions">
                                <button type="submit" class="submit-btn primary-btn" onclick="this.form.submit()">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="17 8 12 3 7 8"></polyline>
                                        <line x1="12" y1="3" x2="12" y2="15"></line>
                                    </svg>
                                    Upload Image
                                </button>
                            </div>
                        </form>
                        
                        <!-- Gallery Display -->
                        <div class="gallery-section">
                            <h3>Current Gallery Images</h3>
                            
                            <?php if (empty($gallery)): ?>
                                <div class="no-items-message">No gallery images have been added yet.</div>
                            <?php else: ?>
                                <div class="gallery-grid">
                                    <?php foreach ($gallery as $image): ?>
                                        <div class="gallery-item">
                                            <img src="<?php echo BASE_URL; ?>/assets/images/<?php echo htmlspecialchars($image['image_path']); ?>" alt="Gallery image">
                                            <div class="gallery-item-overlay">
                                                <form method="POST" action="<?php echo BASE_URL; ?>/cca">
                                                    <input type="hidden" name="action" value="cca_manage">
                                                    <input type="hidden" name="club_id" value="<?php echo $clubDetails['club_id']; ?>">
                                                    <input type="hidden" name="operation" value="delete_gallery">
                                                    <input type="hidden" name="image_id" value="<?php echo $image['image_id']; ?>">
                                                    <input type="hidden" name="redirect_to_details" value="1">
                                                    <button type="submit" class="gallery-item-action" onclick="return confirm('Are you sure you want to delete this image?')">
                                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <polyline points="3 6 5 6 21 6"></polyline>
                                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                            <line x1="10" y1="11" x2="10" y2="17"></line>
                                                            <line x1="14" y1="11" x2="14" y2="17"></line>
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div id="activities-panel" class="edit-panel">
                    <div class="edit-form">
                        <h2>Club Activities</h2>
                        
                        <!-- Add Activity Form -->
                        <form method="POST" action="<?php echo BASE_URL; ?>/cca" id="add-activity-form">
                            <input type="hidden" name="action" value="cca_manage">
                            <input type="hidden" name="club_id" value="<?php echo $clubDetails['club_id']; ?>">
                            <input type="hidden" name="operation" value="add_activity">
                            <input type="hidden" name="redirect_to_details" value="1">
                            
                            <div class="edit-form-group">
                                <label class="edit-form-label" for="activity-title">Activity Title</label>
                                <input type="text" id="activity-title" name="title" class="edit-form-input" required>
                            </div>
                            
                            <div class="edit-form-row">
                                <div class="edit-form-group">
                                    <label class="edit-form-label" for="activity-start-date">Start Date & Time</label>
                                    <input type="datetime-local" id="activity-start-date" name="start_datetime" class="edit-form-input datetime-input" required>
                                </div>
                                <div class="edit-form-group">
                                    <label class="edit-form-label" for="activity-end-date">End Date & Time</label>
                                    <input type="datetime-local" id="activity-end-date" name="end_datetime" class="edit-form-input datetime-input" required>
                                </div>
                            </div>
                            
                            <div class="edit-form-group">
                                <label class="edit-form-label" for="activity-location">Location</label>
                                <input type="text" id="activity-location" name="location" class="edit-form-input">
                            </div>
                            
                            <div class="edit-form-group">
                                <label class="edit-form-label" for="activity-description">Description</label>
                                <textarea id="activity-description" name="description" class="edit-form-textarea" rows="4"></textarea>
                            </div>
                            
                            <div class="edit-form-row">
                                <div class="edit-form-group">
                                    <label class="edit-form-label" for="activity-points">Enrichment Points</label>
                                    <input type="number" id="activity-points" name="points" class="edit-form-input" min="0" value="2">
                                    <div class="form-hint">Number of enrichment points awarded for participation</div>
                                </div>
                                <div class="edit-form-group">
                                    <label class="edit-form-label" for="activity-capacity">Capacity</label>
                                    <input type="number" id="activity-capacity" name="capacity" class="edit-form-input" min="0">
                                    <div class="form-hint">Maximum number of participants (leave empty for unlimited)</div>
                                </div>
                            </div>
                            
                            <button type="submit" class="submit-btn primary-btn" onclick="this.form.submit()">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="12" y1="8" x2="12" y2="16"></line>
                                    <line x1="8" y1="12" x2="16" y2="12"></line>
                                </svg>
                                Add Activity
                            </button>
                        </form>
                        
                        <!-- Activities List -->
                        <div class="activity-section">
                            <h3>Club Activities</h3>
                            
                            <?php if (empty($activities)): ?>
                                <div class="no-items-message">No activities have been added yet.</div>
                            <?php else: ?>
                                <div class="activity-list">
                                    <?php foreach ($activities as $activity): ?>
                                        <div class="activity-item <?php echo strtolower($activity['status'] ?? 'upcoming'); ?>">
                                            <div class="activity-status-badge"><?php echo htmlspecialchars($activity['status'] ?? 'Upcoming'); ?></div>
                                            <div class="activity-header">
                                                <h4 class="activity-title"><?php echo htmlspecialchars($activity['title']); ?></h4>
                                                <div class="activity-actions">
                                                    <span class="attendance-code-badge" title="Attendance Code">
                                                        <?php echo htmlspecialchars($activity['attendance_code'] ?? 'N/A'); ?>
                                                    </span>
                                                    <form method="POST" action="<?php echo BASE_URL; ?>/cca" style="display: inline;">
                                                        <input type="hidden" name="action" value="cca_manage">
                                                        <input type="hidden" name="club_id" value="<?php echo $clubDetails['club_id']; ?>">
                                                        <input type="hidden" name="operation" value="delete_activity">
                                                        <input type="hidden" name="activity_id" value="<?php echo $activity['activity_id']; ?>">
                                                        <input type="hidden" name="redirect_to_details" value="1">
                                                        <button type="submit" class="activity-action delete" title="Delete" onclick="return confirm('Are you sure you want to delete this activity?')">
                                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                            </svg>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                            <div class="activity-meta">
                                                <span class="activity-meta-item">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                                        <line x1="16" y1="2" x2="16" y2="6"></line>
                                                        <line x1="8" y1="2" x2="8" y2="6"></line>
                                                        <line x1="3" y1="10" x2="21" y2="10"></line>
                                                    </svg>
                                                    <?php echo date('M d, Y', strtotime($activity['start_datetime'])); ?>
                                                </span>
                                                <span class="activity-meta-item">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <circle cx="12" cy="12" r="10"></circle>
                                                        <polyline points="12 6 12 12 16 14"></polyline>
                                                    </svg>
                                                    <?php echo date('g:i A', strtotime($activity['start_datetime'])); ?> - 
                                                    <?php echo date('g:i A', strtotime($activity['end_datetime'])); ?>
                                                </span>
                                                <?php if (!empty($activity['location'])): ?>
                                                <span class="activity-meta-item">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                                        <circle cx="12" cy="10" r="3"></circle>
                                                    </svg>
                                                    <?php echo htmlspecialchars($activity['location']); ?>
                                                </span>
                                                <?php endif; ?>
                                                <span class="activity-meta-item">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                                    </svg>
                                                    <?php echo $activity['points_awarded'] ?? 2; ?> points
                                                </span>
                                                <?php if (!empty($activity['capacity'])): ?>
                                                <span class="activity-meta-item">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                                        <circle cx="9" cy="7" r="4"></circle>
                                                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                                    </svg>
                                                    Capacity: <?php echo htmlspecialchars($activity['capacity']); ?>
                                                </span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if (!empty($activity['description'])): ?>
                                            <p class="activity-description"><?php echo htmlspecialchars($activity['description']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div id="locations-panel" class="edit-panel">
                    <div class="edit-form">
                        <h2>Club Location</h2>
                        <form method="POST" action="<?php echo BASE_URL; ?>/cca">
                            <input type="hidden" name="action" value="cca_manage">
                            <input type="hidden" name="club_id" value="<?php echo $clubDetails['club_id']; ?>">
                            <input type="hidden" name="operation" value="update_location">
                            <input type="hidden" name="redirect_to_details" value="1">
                            
                            <div class="edit-form-group">
                                <label class="edit-form-label" for="location">Meeting Location</label>
                                <input type="text" id="location" name="location" class="edit-form-input" value="<?php echo htmlspecialchars($clubDetails['location'] ?? ''); ?>">
                                <div class="form-hint">Enter the primary meeting location of the club (e.g., building name, room number)</div>
                            </div>
                            
                            <div class="map-container">
                                <div id="location-map"></div>
                                <div class="map-instructions">
                                    <p>Click on the map to set the meeting location coordinates, or manually enter them below.</p>
                                </div>
                            </div>
                            
                            <div class="edit-form-row">
                                <div class="edit-form-group">
                                    <label class="edit-form-label" for="latitude">Latitude</label>
                                    <input type="text" id="latitude" name="latitude" class="edit-form-input" value="<?php echo htmlspecialchars($clubDetails['latitude'] ?? '4.890534'); ?>">
                                </div>
                                <div class="edit-form-group">
                                    <label class="edit-form-label" for="longitude">Longitude</label>
                                    <input type="text" id="longitude" name="longitude" class="edit-form-input" value="<?php echo htmlspecialchars($clubDetails['longitude'] ?? '114.940826'); ?>">
                                </div>
                            </div>
                            
                            <button type="submit" class="submit-btn primary-btn" onclick="this.form.submit()">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                    <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                    <polyline points="7 3 7 8 15 8"></polyline>
                                </svg>
                                Save Location
                            </button>
                        </form>
                    </div>
                    
                    <?php if (!empty($locations)): ?>
                    <div class="additional-locations">
                        <h3>Additional Meeting Locations</h3>
                        <div class="locations-list">
                            <?php foreach ($locations as $loc): ?>
                                <div class="location-item">
                                    <div class="location-details">
                                        <h4><?php echo htmlspecialchars($loc['location_name']); ?></h4>
                                        <p><?php echo htmlspecialchars($loc['address']); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Map Scripts -->
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap" async defer></script>
    <script>
        // Initialize the map
        function initMap() {
            // Default to PB location if no coordinates are set
            const lat = parseFloat(document.getElementById('latitude').value) || 4.890534;
            const lng = parseFloat(document.getElementById('longitude').value) || 114.940826;
            
            const mapOptions = {
                center: { lat, lng },
                zoom: 15,
            };
            
            const map = new google.maps.Map(document.getElementById('location-map'), mapOptions);
            
            // Add a marker at the current position
            let marker = new google.maps.Marker({
                position: { lat, lng },
                map: map,
                draggable: true,
                title: 'Club Meeting Location'
            });
            
            // Update coordinates when marker is dragged
            google.maps.event.addListener(marker, 'dragend', function() {
                const position = marker.getPosition();
                document.getElementById('latitude').value = position.lat();
                document.getElementById('longitude').value = position.lng();
            });
            
            // Allow clicking on map to move marker
            google.maps.event.addListener(map, 'click', function(event) {
                marker.setPosition(event.latLng);
                document.getElementById('latitude').value = event.latLng.lat();
                document.getElementById('longitude').value = event.latLng.lng();
            });
        }
    </script>
    
    <script src="<?php echo BASE_URL; ?>/assets/js/profile-dropdown.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/js/cca-edit.js"></script>
</body>
</html> 