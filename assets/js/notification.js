// notification.js - Self-initializing notification functionality

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('Notifications: DOM loaded, initializing');
    initializeNotifications();
});

async function initializeNotifications() {
    console.log('Notifications: Starting initialization');
    const notificationBtn = document.querySelector('.notification-btn');
    
    if (!notificationBtn) {
        console.log('Notifications: Button not found in the DOM');
        return;
    }

    try {
        console.log('Notifications: Fetching notifications');
        const response = await fetchNotifications();
        if (response.ok) {
            const notifications = await response.json();
            console.log(`Notifications: Found ${notifications.length} notifications`);
            updateNotificationBadge(notifications.length);
        } else {
            console.log('Notifications: Failed to fetch', response.status);
        }
    } catch (error) {
        console.error('Notifications: Error initializing:', error);
    }

    console.log('Notifications: Setting up click handler');
    notificationBtn.addEventListener('click', handleNotificationClick);
}

async function fetchNotifications() {
    return await fetch('/api/notifications', {
        headers: {
            'Accept': 'application/json'
        }
    });
}

function updateNotificationBadge(count) {
    const badge = document.querySelector('.notification-badge');
    if (!badge) {
        console.log('Notifications: Badge element not found');
        return;
    }
    
    console.log(`Notifications: Updating badge: ${count} notifications`);
    badge.textContent = count;
    badge.style.display = count > 0 ? 'block' : 'none';
    badge.setAttribute('aria-label', `${count} unread notifications`);
}

async function handleNotificationClick() {
    console.log('Notifications: Button clicked');
    try {
        const response = await fetchNotifications();
        if (response.ok) {
            const notifications = await response.json();
            updateNotificationBadge(notifications.length);
            
            console.log('Notifications: Showing notification panel/popup');
            // Additional notification handling logic here
        }
    } catch (error) {
        console.error('Notifications: Error handling notifications:', error);
    }
}

// Global notification function (for showing notification messages)
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;

    // Remove existing notifications
    document.querySelectorAll('.notification').forEach(n => n.remove());

    // Add new notification
    document.body.appendChild(notification);

    // Remove notification after 3 seconds
    setTimeout(() => {
        notification.remove();
    }, 3000);
}