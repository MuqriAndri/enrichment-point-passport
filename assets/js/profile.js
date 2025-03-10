// profile.js - Self-initializing profile functionality

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
            body: JSON.stringify(formDataObject)
        });

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
        console.log('Profile: Sending avatar to server');
        const response = await fetch(`${baseUrl}/controllers/upload-profile-picture.php`, {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            console.log('Profile: Avatar updated successfully');
            updateAvatarPreview(result.data.avatarUrl);
            if (typeof showNotification === 'function') {
                showNotification(result.data.message || 'Profile picture updated successfully');
            } else {
                showProfileNotification(result.data.message || 'Profile picture updated successfully');
            }

            // Update navigation avatar if it exists
            const navAvatar = document.querySelector('.nav-right .user-avatar img');
            if (navAvatar) {
                navAvatar.src = result.data.avatarUrl;
            }
        } else {
            console.log('Profile: Failed to upload avatar:', result.data.error);
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
    console.log('Profile: Updating avatar preview');
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

    img.src = `${url}?t=${new Date().getTime()}`;
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