document.addEventListener('DOMContentLoaded', function () {
    console.log('Profile: DOM loaded, initializing');
    initializeProfileEdit();
    initializeAvatarUpload();
});

function initializeProfileEdit() {
    console.log('Profile: Initializing profile edit');
    const editButton = document.getElementById('editProfileBtn');
    const form = document.getElementById('profileInfoForm');

    if (!form || !editButton) {
        console.log('Profile: Edit button or form not found');
        return;
    }

    const formInputs = form.querySelectorAll('input, textarea');

    editButton.addEventListener('click', function () {
        const isEditing = editButton.classList.contains('editing');
        console.log(`Profile: Edit button clicked, current state: ${isEditing ? 'editing' : 'not editing'}`);

        if (!isEditing) {
            // Enable editing with animation
            formInputs.forEach((input, index) => {
                if (input.id !== 'role' && input.id !== 'student_id') { // Don't enable editing for role and student_id
                    setTimeout(() => {
                        input.disabled = false;
                        input.classList.add('editable');
                    }, index * 50);
                }
            });

            editButton.innerHTML = `
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                    <polyline points="17 21 17 13 7 13 7 21"></polyline>
                    <polyline points="7 3 7 8 15 8"></polyline>
                </svg>
                Save Changes
            `;
            editButton.classList.add('editing');
        } else {
            if (validateAllFields()) {
                saveProfileChanges();
            }
        }
    });
    console.log('Profile: Profile edit initialized');
}

async function saveProfileChanges() {
    console.log('Profile: Saving profile changes');
    const form = document.getElementById('profileInfoForm');
    if (!form) return;

    try {
        showLoadingState();

        const formData = new FormData(form);
        const formDataObject = {};
        formData.forEach((value, key) => {
            formDataObject[key] = value;
        });

        console.log('Profile: Sending profile data to server');
        const response = await fetch(`${window.location.origin}/enrichment-point-passport/controllers/edit-profile.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formDataObject),
            credentials: 'same-origin' // This ensures cookies (including session) are sent
        });

        if (!response.ok) {
            // Check if it's an authentication error
            if (response.status === 401) {
                throw new Error('Your session has expired. Please refresh the page and try again.');
            }
            throw new Error(`Server responded with status: ${response.status}`);
        }

        const result = await response.json();

        if (result.success) {
            console.log('Profile: Profile updated successfully');
            handleSaveSuccess();
            // Use showNotification function from either this file or from notification.js
            // First check if it's defined globally
            if (typeof showNotification === 'function') {
                showNotification(result.message || 'Profile updated successfully');
            } else {
                // Otherwise, use the notification function defined here
                showProfileNotification(result.message || 'Profile updated successfully');
            }
        } else {
            console.log('Profile: Failed to save changes:', result.error);
            throw new Error(result.error || 'Failed to save changes');
        }
    } catch (error) {
        console.error('Profile: Error saving changes:', error);
        // Use showNotification function from either this file or from notification.js
        if (typeof showNotification === 'function') {
            showNotification(error.message, 'error');
        } else {
            showProfileNotification(error.message, 'error');
        }
    } finally {
        hideLoadingState();
    }
}

function initializeAvatarUpload() {
    console.log('Profile: Initializing avatar upload');
    const uploadButton = document.querySelector('.avatar-upload-btn');
    const avatar = document.querySelector('.profile-avatar');
    if (!uploadButton || !avatar) {
        console.log('Profile: Avatar elements not found');
        return;
    }

    const fileInput = document.createElement('input');
    fileInput.type = 'file';
    fileInput.accept = 'image/*';
    fileInput.style.display = 'none';

    uploadButton.parentNode.appendChild(fileInput);

    uploadButton.addEventListener('mouseenter', () => {
        avatar.classList.add('avatar-hover');
    });

    uploadButton.addEventListener('mouseleave', () => {
        avatar.classList.remove('avatar-hover');
    });

    uploadButton.addEventListener('click', () => {
        console.log('Profile: Upload button clicked');
        fileInput.click();
    });

    fileInput.addEventListener('change', async (e) => {
        if (e.target.files && e.target.files[0]) {
            const file = e.target.files[0];
            console.log(`Profile: File selected: ${file.name} (${file.size} bytes)`);
            if (file.size > 5 * 1024 * 1024) {
                if (typeof showNotification === 'function') {
                    showNotification('Image size should be less than 5MB', 'error');
                } else {
                    showProfileNotification('Image size should be less than 5MB', 'error');
                }
                return;
            }
            await handleAvatarUpload(file);
        }
    });
    console.log('Profile: Avatar upload initialized');
}

async function handleAvatarUpload(file) {
    console.log('Profile: Handling avatar upload');
    const formData = new FormData();
    formData.append('avatar', file);

    try {
        showLoadingState();

        const baseUrl = '/enrichment-point-passport';
        console.log('Profile: Sending avatar to server for S3 upload');
        const response = await fetch(`${baseUrl}/controllers/upload-profile-picture.php`, {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            console.log('Profile: Avatar updated successfully to S3');
            updateAvatarPreview(result.data.avatarUrl);
            
            // Ensure notification is shown with a delay to make it more visible
            setTimeout(() => {
                if (typeof showNotification === 'function') {
                    showNotification(result.data.message || 'Profile picture updated successfully');
                } else {
                    showProfileNotification(result.data.message || 'Profile picture updated successfully');
                }
            }, 300);

            // Update navigation avatar if it exists
            const navAvatar = document.querySelector('.nav-right .user-avatar img');
            if (navAvatar) {
                navAvatar.src = result.data.avatarUrl;
            }
        } else {
            console.log('Profile: Failed to upload avatar to S3:', result.data.error);
            throw new Error(result.data.error || 'Failed to upload profile picture');
        }
    } catch (error) {
        console.error('Profile: Upload error:', error);
        if (typeof showNotification === 'function') {
            showNotification(error.message, 'error');
        } else {
            showProfileNotification(error.message, 'error');
        }
    } finally {
        hideLoadingState();
    }
}

function updateAvatarPreview(url) {
    console.log('Profile: Updating avatar preview with S3 URL:', url);
    const avatar = document.querySelector('.profile-avatar');
    if (!avatar) return;

    const placeholder = avatar.querySelector('.avatar-placeholder');
    if (placeholder) {
        placeholder.remove();
    }

    let img = avatar.querySelector('img');
    if (!img) {
        img = document.createElement('img');
        avatar.appendChild(img);
    }

    // Create a temporary image to preload and ensure proper dimensions
    const tempImg = new Image();
    tempImg.onload = () => {
        // Set the image source after we've loaded it to verify dimensions
        if (url.includes('amazonaws.com')) {
            img.src = url;
        } else {
            img.src = `${url}?t=${new Date().getTime()}`;
        }
        
        // Apply CSS to ensure the image fits properly in its container
        img.style.maxWidth = '100%';
        img.style.maxHeight = '100%';
        img.style.width = 'auto';
        img.style.height = 'auto';
        img.style.objectFit = 'cover';
        
        // Also add these styles to the avatar container
        if (avatar) {
            avatar.style.overflow = 'visible'; // Ensure overflow is visible to show the upload button
            avatar.style.display = 'flex';
            avatar.style.alignItems = 'center';
            avatar.style.justifyContent = 'center';
        }
        
        // Make sure upload button is visible and positioned correctly
        const uploadBtn = document.querySelector('.avatar-upload-btn');
        if (uploadBtn) {
            uploadBtn.style.zIndex = '10';
            uploadBtn.style.visibility = 'visible';
        }
        
        // Show a notification again after image is loaded
        setTimeout(() => {
            if (typeof showNotification === 'function') {
                showNotification('Profile picture updated successfully');
            } else {
                showProfileNotification('Profile picture updated successfully');
            }
        }, 500);
    };
    
    // Handle loading errors
    tempImg.onerror = () => {
        console.error('Profile: Error loading image from URL:', url);
        if (typeof showNotification === 'function') {
            showNotification('Image loaded but there was a display error. Please refresh the page.', 'warning');
        } else {
            showProfileNotification('Image loaded but there was a display error. Please refresh the page.', 'warning');
        }
    };
    
    // Start loading the image
    tempImg.src = url.includes('amazonaws.com') ? url : `${url}?t=${new Date().getTime()}`;
    
    img.alt = 'Profile Picture';
}

function validateAllFields() {
    console.log('Profile: Validating all fields');
    const form = document.getElementById('profileInfoForm');
    if (!form) return true;

    const inputs = form.querySelectorAll('input:not([disabled]), textarea:not([disabled])');
    let isValid = true;

    inputs.forEach(input => {
        if (!validateField(input)) {
            isValid = false;
        }
    });

    console.log(`Profile: Validation result: ${isValid ? 'valid' : 'invalid'}`);
    return isValid;
}

function validateField(field) {
    if (field.disabled) return true;

    const value = field.value.trim();
    let isValid = true;
    let errorMessage = '';

    switch (field.id) {
        case 'user_email':
            isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
            errorMessage = 'Please enter a valid email address';
            break;
        case 'school':
            isValid = value.length >= 2;
            errorMessage = 'School name is required';
            break;
        case 'programme':
            isValid = value.length >= 2;
            errorMessage = 'Programme name is required';
            break;
        case 'group_code':
            isValid = value.length >= 2;
            errorMessage = 'Group code is required';
            break;
        default:
            isValid = value.length >= 2;
            errorMessage = 'This field is required';
    }

    updateFieldStatus(field, isValid, errorMessage);
    return isValid;
}

function updateFieldStatus(field, isValid, errorMessage) {
    console.log(`Profile: Updating field status for ${field.id}, valid: ${isValid}`);
    
    // Remove any existing error messages
    const existingError = field.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
    
    // Remove any existing styling
    field.classList.remove('field-invalid', 'field-valid');
    
    if (!isValid) {
        // Add error styling
        field.classList.add('field-invalid');
        
        // Create and append error message
        const errorElement = document.createElement('div');
        errorElement.className = 'field-error';
        errorElement.textContent = errorMessage;
        field.parentNode.appendChild(errorElement);
    } else {
        // Add valid styling
        field.classList.add('field-valid');
    }
    
    return isValid;
}

// Also add the missing showProfileNotification function
function showProfileNotification(message, type = 'success') {
    console.log(`Profile: Showing notification: ${message} (${type})`);
    
    // Remove any existing notifications
    const existingNotification = document.querySelector('.profile-notification');
    if (existingNotification) {
        existingNotification.remove();
    }
    
    // Create notification element
    const notificationElement = document.createElement('div');
    notificationElement.className = `profile-notification ${type}`;
    notificationElement.textContent = message;
    
    // Add to document
    document.body.appendChild(notificationElement);
    
    // Show with animation after a tiny delay to allow for DOM rendering
    setTimeout(() => {
        notificationElement.classList.add('show');
    }, 10);
    
    // Auto hide after 3 seconds
    setTimeout(() => {
        notificationElement.classList.remove('show');
        setTimeout(() => {
            notificationElement.remove();
        }, 300);
    }, 4000);
}

// Add the missing loading state functions
function showLoadingState() {
    console.log('Profile: Showing loading state');
    
    // Disable edit button
    const editButton = document.getElementById('editProfileBtn');
    if (editButton) {
        editButton.disabled = true;
        editButton.classList.add('loading');
    }
    
    // Add loading overlay
    const overlay = document.createElement('div');
    overlay.className = 'profile-loading-overlay';
    overlay.innerHTML = '<div class="spinner"></div>';
    document.body.appendChild(overlay);
}

function hideLoadingState() {
    console.log('Profile: Hiding loading state');
    
    // Enable edit button
    const editButton = document.getElementById('editProfileBtn');
    if (editButton) {
        editButton.disabled = false;
        editButton.classList.remove('loading');
    }
    
    // Remove loading overlay
    const overlay = document.querySelector('.profile-loading-overlay');
    if (overlay) {
        overlay.remove();
    }
}

// Add the missing handleSaveSuccess function
function handleSaveSuccess() {
    console.log('Profile: Handling save success');
    
    const editButton = document.getElementById('editProfileBtn');
    const form = document.getElementById('profileInfoForm');
    
    if (!form || !editButton) return;
    
    const formInputs = form.querySelectorAll('input, textarea');
    
    // Disable form fields with animation
    formInputs.forEach((input, index) => {
        setTimeout(() => {
            input.disabled = true;
            input.classList.remove('editable');
        }, index * 50);
    });
    
    // Update button state
    editButton.innerHTML = `
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
        </svg>
        Edit Profile
    `;
    editButton.classList.remove('editing');
}