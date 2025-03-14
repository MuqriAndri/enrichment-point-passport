/**
 * Club Join JavaScript
 * Handles the interactive elements of the club join functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Club Join: Initializing');
    initializeJoinButtons();
    initializeApplicationModal();
});

/**
 * Initialize all join buttons on the page
 */
function initializeJoinButtons() {
    const joinButtons = document.querySelectorAll('.join-btn');
    
    if (joinButtons.length === 0) {
        console.log('Club Join: No join buttons found on the page');
        return;
    }
    
    console.log(`Club Join: Found ${joinButtons.length} join buttons`);
    
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
                console.error('Club Join: No club ID found for join button');
                return;
            }
            
            console.log(`Club Join: Join button clicked for club ${clubId} (${clubName})`);
            
            // Show the application modal
            showApplicationModal(clubId, clubName);
        });
    });
}

/**
 * Show the application modal with club information
 */
function showApplicationModal(clubId, clubName) {
    const modal = document.getElementById('clubApplicationModal');
    if (!modal) {
        console.error('Club Join: Application modal not found in the DOM');
        return;
    }
    
    // Set club ID in hidden field
    const clubIdInput = modal.querySelector('#applicationClubId');
    if (clubIdInput) {
        clubIdInput.value = clubId;
    }
    
    // Update modal title with club name
    const modalTitle = modal.querySelector('.modal-header h2');
    if (modalTitle) {
        modalTitle.textContent = `Application for ${clubName}`;
    }
    
    // Pre-fill form from session data if available
    const form = modal.querySelector('#clubApplicationForm');
    if (form) {
        // Reset form first
        form.reset();
        
        // Try to pre-fill form fields from PHP session values
        if (typeof window.sessionUserData !== 'undefined') {
            const userData = window.sessionUserData;
            
            const fields = {
                'full_name': userData.full_name,
                'student_id': userData.student_id,
                'student_email': userData.user_email,
                'school': userData.school,
                'course': userData.programme,
                'group_code': userData.group_code,
                'intake': userData.intake
            };
            
            // Populate fields
            for (const [fieldId, value] of Object.entries(fields)) {
                if (value) {
                    const field = form.querySelector(`#${fieldId}`);
                    if (field) {
                        if (field.tagName === 'SELECT') {
                            // For select elements, find and select the matching option
                            const option = Array.from(field.options).find(opt => opt.value === value);
                            if (option) option.selected = true;
                        } else {
                            // For text inputs
                            field.value = value;
                        }
                    }
                }
            }
        }
    }
    
    // Show the modal
    modal.style.display = 'block';
    
    // Focus on the first input
    setTimeout(() => {
        const firstInput = modal.querySelector('input:not([type="hidden"])');
        if (firstInput) {
            firstInput.focus();
        }
    }, 100);
}

/**
 * Initialize modal functionality
 */
function initializeApplicationModal() {
    const modal = document.getElementById('clubApplicationModal');
    if (!modal) {
        console.log('Club Join: Application modal not found - it might be loaded later');
        return;
    }
    
    console.log('Club Join: Initializing application modal');
    
    // Close button functionality
    const closeButton = modal.querySelector('.close-modal');
    if (closeButton) {
        closeButton.addEventListener('click', hideApplicationModal);
    }
    
    // Cancel button functionality
    const cancelButton = modal.querySelector('.cancel-application-btn');
    if (cancelButton) {
        cancelButton.addEventListener('click', hideApplicationModal);
    }
    
    // Close when clicking outside modal
    window.addEventListener('click', function(e) {
        if (e.target === modal) {
            hideApplicationModal();
        }
    });
    
    // Handle form submission
    const form = modal.querySelector('#clubApplicationForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Only prevent default if validation fails
            if (!validateApplicationForm(this)) {
                e.preventDefault();
                return false;
            }
            
            // Show loading state on button
            const submitButton = this.querySelector('.submit-application-btn');
            if (submitButton) {
                submitButton.innerHTML = '<span class="spinner"></span> Submitting...';
                submitButton.disabled = true;
            }
            
            // Allow the form to submit normally (POST to server)
            return true;
        });
    }
    
    // Hide modal initially
    modal.style.display = 'none';
}

/**
 * Hide the application modal
 */
function hideApplicationModal() {
    const modal = document.getElementById('clubApplicationModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

/**
 * Validate the application form
 */
function validateApplicationForm(form) {
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    // Remove existing error indicators
    form.querySelectorAll('.field-error').forEach(el => el.classList.remove('field-error'));
    
    // Check each required field
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('field-error');
            isValid = false;
        }
        
        // Email validation
        if (field.type === 'email' && field.value.trim()) {
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(field.value.trim())) {
                field.classList.add('field-error');
                isValid = false;
            }
        }
    });
    
    // Show error message if validation fails
    if (!isValid) {
        showNotification('Please fill in all required fields correctly', 'error');
    }
    
    return isValid;
}

/**
 * Show a notification message
 */
function showNotification(message, type = 'success') {
    console.log(`Club Join: ${type} notification - ${message}`);
    
    // Check if the app has a global notification function
    if (typeof window.showNotification === 'function' && window.showNotification !== showNotification) {
        window.showNotification(message, type);
        return;
    }
    
    // Create notification element if no global function exists
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    // Add to document
    document.body.appendChild(notification);
    
    // Show with animation
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    
    // Auto-hide after 3 seconds
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}