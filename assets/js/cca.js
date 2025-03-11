// cca.js - Self-initializing CCA functionality

document.addEventListener('DOMContentLoaded', function() {
    console.log('CCA: DOM loaded, initializing');
    initializeClubSearch();
    initializeClubJoinButtons();
});

function initializeClubSearch() {
    console.log('CCA: Initializing club search');
    const searchInput = document.querySelector('.search-bar input');
    if (!searchInput) {
        console.log('CCA: Search input not found');
        return;
    }

    searchInput.addEventListener('input', debounce(function(e) {
        const searchTerm = e.target.value.toLowerCase();
        console.log(`CCA: Searching for "${searchTerm}"`);
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
    console.log('CCA: Club search initialized');
}

function initializeClubJoinButtons() {
    console.log('CCA: Initializing club join buttons');
    
    // Find all join buttons (both in main CCA page and CCA details page)
    const joinButtons = document.querySelectorAll('.join-btn');
    if (joinButtons.length === 0) {
        console.log('CCA: No join buttons found');
        return;
    }
    
    console.log(`CCA: Found ${joinButtons.length} join buttons`);
    
    // Add click handler to all join buttons
    joinButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault(); // Prevent default form submission
            
            // Get club information
            const clubId = this.closest('form') 
                ? this.closest('form').querySelector('input[name="club_id"]').value 
                : this.dataset.clubId;
                
            const clubName = this.dataset.clubName || 
                             (this.closest('.club-header-content') 
                              ? this.closest('.club-header-content').querySelector('h1').textContent 
                              : 'this club');
            
            if (!clubId) {
                console.error('CCA: No club ID found for join button');
                return;
            }
            
            console.log(`CCA: Join button clicked for club ${clubId} (${clubName})`);
            
            // Show application modal
            showApplicationModal(clubId, clubName);
        });
    });
    
    console.log('CCA: Club join buttons initialized');
}

function showApplicationModal(clubId, clubName) {
    // Find the modal element (should be included in the HTML)
    const modal = document.getElementById('clubApplicationModal');
    if (!modal) {
        console.error('CCA: Application modal not found in the DOM');
        return;
    }
    
    // Set club ID in the form
    const clubIdInput = modal.querySelector('#applicationClubId');
    if (clubIdInput) {
        clubIdInput.value = clubId;
    }
    
    // Update modal title to include club name
    const modalTitle = modal.querySelector('.modal-header h2');
    if (modalTitle) {
        modalTitle.textContent = `Application for ${clubName}`;
    }
    
    // Reset form fields
    const form = modal.querySelector('#clubApplicationForm');
    if (form) {
        form.reset();
    }
    
    // Show modal
    modal.style.display = 'block';
    
    // Set focus on first field
    setTimeout(() => {
        const firstField = modal.querySelector('#application_reason');
        if (firstField) {
            firstField.focus();
        }
    }, 100);
}

// Function to hide the application modal - this should be called by close/cancel buttons
function hideApplicationModal() {
    const modal = document.getElementById('clubApplicationModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Add this function to your JavaScript to initialize the modal events
function initializeApplicationModal() {
    const modal = document.getElementById('clubApplicationModal');
    if (!modal) return;
    
    // Add click handlers for close and cancel buttons
    const closeButton = modal.querySelector('.close-modal');
    if (closeButton) {
        closeButton.addEventListener('click', hideApplicationModal);
    }
    
    const cancelButton = modal.querySelector('.cancel-application-btn');
    if (cancelButton) {
        cancelButton.addEventListener('click', hideApplicationModal);
    }
    
    // Close when clicking outside modal
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            hideApplicationModal();
        }
    });
    
    // Handle form submission
    const form = modal.querySelector('#clubApplicationForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent default form submission
            
            const submitButton = this.querySelector('.submit-application-btn');
            if (submitButton) {
                // Show loading state
                submitButton.innerHTML = '<span class="spinner"></span> Sending Request...';
                submitButton.disabled = true;
            }
            
            // Collect form data
            const formData = new FormData(this);
            
            // Send via fetch
            fetch(this.action, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => {
                if (response.redirected) {
                    // If the server responded with a redirect, follow it
                    window.location.href = response.url;
                } else {
                    // Otherwise, reload the current page
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error submitting form:', error);
                if (submitButton) {
                    submitButton.innerHTML = 'Submit Application';
                    submitButton.disabled = false;
                }
                alert('Failed to submit application. Please try again.');
            });
        });
    }
}

// Ensure the modal is initially hidden
document.addEventListener('DOMContentLoaded', function() {
    // Hide modal in case it's shown by default
    const modal = document.getElementById('clubApplicationModal');
    if (modal) {
        modal.style.display = 'none';
    }
    
    initializeApplicationModal();
});

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