document.addEventListener('DOMContentLoaded', function() {
    initializeClubSearch();
    initializeClubRegistration();
    checkUserClubStatus();
});

function initializeClubSearch() {
    const searchInput = document.querySelector('.search-bar input');
    if (!searchInput) return;

    searchInput.addEventListener('input', debounce(function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const clubCards = document.querySelectorAll('.club-card');

        clubCards.forEach(card => {
            const clubName = card.querySelector('h3').textContent.toLowerCase();
            const isVisible = clubName.includes(searchTerm);
            card.style.display = isVisible ? 'flex' : 'none';
        });

        document.querySelectorAll('.club-category').forEach(category => {
            const visibleClubs = category.querySelectorAll('.club-card[style="display: flex"]').length;
            const emptyMessage = category.querySelector('.empty-category-message');
            
            if (visibleClubs === 0) {
                if (!emptyMessage) {
                    const message = document.createElement('p');
                    message.className = 'empty-category-message';
                    message.textContent = 'No clubs found in this category';
                    category.appendChild(message);
                }
            } else {
                if (emptyMessage) {
                    emptyMessage.remove();
                }
            }
        });
    }, 300));
}

function initializeClubRegistration() {
    const clubList = document.querySelector('.clubs-grid');
    if (!clubList) return;

    clubList.addEventListener('click', async (e) => {
        const joinButton = e.target.closest('.join-btn');
        if (!joinButton) return;

        const clubId = joinButton.dataset.clubId;
        
        try {
            const response = await registerForClub(clubId);
            if (response.success) {
                updateJoinButton(joinButton, true);
                showNotification('Successfully joined the club!', 'success');
            } else {
                showNotification(response.message || 'Failed to join club', 'error');
            }
        } catch (error) {
            showNotification('An error occurred. Please try again.', 'error');
            console.error('Error joining club:', error);
        }
    });
}

async function checkUserClubStatus() {
    try {
        const response = await fetch('/api/clubs/user-memberships');
        if (response.ok) {
            const memberships = await response.json();
            
            // Update buttons for clubs user is already a member of
            memberships.forEach(membership => {
                const button = document.querySelector(`.join-btn[data-club-id="${membership.club_id}"]`);
                if (button) {
                    updateJoinButton(button, true);
                }
            });
        }
    } catch (error) {
        console.error('Error fetching user memberships:', error);
    }
}

// API call to register for a club
async function registerForClub(clubId) {
    const response = await fetch('/api/clubs/register', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ club_id: clubId })
    });

    return await response.json();
}

// Update join button state
function updateJoinButton(button, joined) {
    button.textContent = joined ? 'Joined' : 'Join Club';
    button.classList.toggle('joined', joined);
    button.disabled = joined;
}

// Show notification
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

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}