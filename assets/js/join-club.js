/**
 * Club Join JavaScript
 * Handles the interactive elements of the club join functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Club Join: Initializing');
    initializeJoinButtons();
    initializeApplicationModal();
    initializeAutofillButton();
    
    // Join Club Button Handler
    const joinButton = document.querySelector('form.join-btn');
    if (joinButton) {
        joinButton.addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('club-application-modal').style.display = 'block';
        });
    }
    
    // Close Application Modal
    const closeButton = document.querySelector('.application-modal-close');
    if (closeButton) {
        closeButton.addEventListener('click', function() {
            document.getElementById('club-application-modal').style.display = 'none';
        });
    }
    
    // Close modal on outside click
    window.addEventListener('click', function(e) {
        const modal = document.getElementById('club-application-modal');
        if (e.target === modal) {
            modal.style.display = 'none';
        }
        
        const galleryModal = document.getElementById('gallery-modal');
        if (galleryModal && e.target === galleryModal) {
            galleryModal.style.display = 'none';
        }
    });
    
    // Gallery Read More buttons
    const readMoreButtons = document.querySelectorAll('.gallery-read-more');
    if (readMoreButtons.length > 0) {
        readMoreButtons.forEach(button => {
            button.addEventListener('click', function() {
                const imageId = this.getAttribute('data-image-id');
                const galleryItems = document.querySelectorAll('.gallery-item');
                let image, title, description;
                
                // Find the corresponding gallery item
                galleryItems.forEach(item => {
                    if (item.querySelector(`[data-image-id="${imageId}"]`)) {
                        const imgElement = item.querySelector('img');
                        image = imgElement.getAttribute('src');
                        title = item.querySelector('.gallery-item-title')?.textContent || 'Gallery Image';
                        
                        // Get description from data attribute or use a default message
                        const descriptionElement = this.closest('.gallery-item-overlay');
                        description = descriptionElement.getAttribute('data-description') || 'No description available.';
                    }
                });
                
                // Populate modal
                document.getElementById('modal-image-title').textContent = title;
                document.getElementById('modal-image').src = image;
                document.getElementById('modal-image-description').textContent = description;
                
                // Show modal
                document.getElementById('gallery-modal').style.display = 'block';
            });
        });
        
        // Close Gallery Modal
        const galleryCloseButton = document.querySelector('.gallery-modal-close');
        if (galleryCloseButton) {
            galleryCloseButton.addEventListener('click', function() {
                document.getElementById('gallery-modal').style.display = 'none';
            });
        }
    }
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
    
    // Check if there's an error message in the URL (from previous submission)
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('error') && urlParams.get('error') === 'max_clubs') {
        // Show error message
        alert('Error: You cannot join more than 3 clubs!');
        return; // Don't show the modal
    }
    
    // Pre-fill form from session data if available
    const form = modal.querySelector('#clubApplicationForm');
    if (form) {
        // Reset form first
        form.reset();
        
        // Removed automatic form filling - now only happens when autofill button is clicked
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

/**
 * Initialize autofill button functionality
 */
function initializeAutofillButton() {
    const autofillButton = document.getElementById('autofillButton');
    if (!autofillButton) {
        console.log('Club Join: Autofill button not found');
        return;
    }
    
    console.log('Club Join: Initializing autofill button');
    
    autofillButton.addEventListener('click', function() {
        console.log('Club Join: Autofill button clicked');
        autofillFormFromSession();
    });
}

/**
 * Autofill form with session data
 */
function autofillFormFromSession() {
    const form = document.getElementById('clubApplicationForm');
    if (!form) {
        console.error('Club Join: Form not found for autofill');
        return;
    }
    
    // Check if session data is available
    if (typeof window.sessionUserData === 'undefined' || !window.sessionUserData) {
        console.error('Club Join: No session data available for autofill');
        alert('Unable to autofill form. No user data available.');
        return;
    }
    
    // Get user data
    const userData = window.sessionUserData;
    
    // Define field mappings
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
    
    // Show confirmation
    const focusField = form.querySelector('#phone_number');
    if (focusField) {
        focusField.focus();
    }
    
    console.log('Club Join: Form autofilled successfully');
}