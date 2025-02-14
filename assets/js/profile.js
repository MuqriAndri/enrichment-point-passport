document.addEventListener('DOMContentLoaded', function() {
    initializeProfileEdit();
    initializeAvatarUpload();
    setupFormValidation();
    initializeNotifications();
});

function initializeNotifications() {
    const notificationContainer = document.createElement('div');
    notificationContainer.className = 'notification-container';
    document.body.appendChild(notificationContainer);
}

function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    
    const icon = document.createElement('span');
    icon.className = 'notification-icon';
    icon.innerHTML = type === 'success' 
        ? '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>'
        : '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>';
    
    const text = document.createElement('span');
    text.textContent = message;
    
    const closeBtn = document.createElement('button');
    closeBtn.className = 'notification-close';
    closeBtn.innerHTML = '×';
    closeBtn.onclick = () => notification.remove();
    
    notification.appendChild(icon);
    notification.appendChild(text);
    notification.appendChild(closeBtn);
    
    document.querySelector('.notification-container').appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentElement) {
            notification.classList.add('fade-out');
            setTimeout(() => notification.remove(), 300);
        }
    }, 5000);
}

function initializeProfileEdit() {
    const editButton = document.getElementById('editProfileBtn');
    const form = document.getElementById('profileInfoForm');
    
    if (!form || !editButton) return;
    
    const formInputs = form.querySelectorAll('input, textarea');
    
    editButton.addEventListener('click', function() {
        const isEditing = editButton.classList.contains('editing');
        
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
}

async function saveProfileChanges() {
    const form = document.getElementById('profileInfoForm');
    if (!form) return;

    try {
        showLoadingState();
        
        const formData = new FormData(form);
        const formDataObject = {};
        formData.forEach((value, key) => {
            formDataObject[key] = value;
        });

        const response = await fetch(`${window.location.origin}/enrichment-point-passport/handlers/update-profile.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formDataObject)
        });
        
        const result = await response.json();
        
        if (result.success) {
            handleSaveSuccess();
            showNotification(result.message || 'Profile updated successfully');
        } else {
            throw new Error(result.error || 'Failed to save changes');
        }
    } catch (error) {
        showNotification(error.message, 'error');
    } finally {
        hideLoadingState();
    }
}

function initializeAvatarUpload() {
    const uploadButton = document.querySelector('.avatar-upload-btn');
    const avatar = document.querySelector('.profile-avatar');
    if (!uploadButton || !avatar) return;

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
        fileInput.click();
    });
    
    fileInput.addEventListener('change', async (e) => {
        if (e.target.files && e.target.files[0]) {
            const file = e.target.files[0];
            if (file.size > 5 * 1024 * 1024) {
                showNotification('Image size should be less than 5MB', 'error');
                return;
            }
            await handleAvatarUpload(file);
        }
    });
}

async function handleAvatarUpload(file) {
    const formData = new FormData();
    formData.append('avatar', file);
    
    try {
        showLoadingState();
        
        const baseUrl = '/enrichment-point-passport';
        const response = await fetch(`${baseUrl}/handlers/upload-profile-picture.php`, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            updateAvatarPreview(result.data.avatarUrl);
            showNotification(result.data.message || 'Profile picture updated successfully');
            
            // Update navigation avatar if it exists
            const navAvatar = document.querySelector('.nav-right .user-avatar img');
            if (navAvatar) {
                navAvatar.src = result.data.avatarUrl;
            }
        } else {
            throw new Error(result.data.error || 'Failed to upload profile picture');
        }
    } catch (error) {
        console.error('Upload error:', error);
        showNotification(error.message, 'error');
    } finally {
        hideLoadingState();
    }
}

function updateAvatarPreview(url) {
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
    const form = document.getElementById('profileInfoForm');
    if (!form) return true;

    const inputs = form.querySelectorAll('input:not([disabled]), textarea:not([disabled])');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!validateField(input)) {
            isValid = false;
        }
    });
    
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
    const errorElement = field.parentNode.querySelector('.error-message');
    
    if (isValid) {
        field.classList.remove('error');
        field.classList.add('success');
        if (errorElement) errorElement.remove();
    } else {
        field.classList.remove('success');
        field.classList.add('error');
        
        if (!errorElement) {
            const error = document.createElement('div');
            error.className = 'error-message';
            error.textContent = errorMessage;
            field.parentNode.appendChild(error);
        }
    }
}

function showLoadingState() {
    const sections = document.querySelectorAll('.profile-section');
    sections.forEach(section => {
        section.classList.add('loading');
        const spinner = document.createElement('div');
        spinner.className = 'loading-spinner';
        section.appendChild(spinner);
    });
}

function hideLoadingState() {
    const sections = document.querySelectorAll('.profile-section');
    sections.forEach(section => {
        section.classList.remove('loading');
        const spinner = section.querySelector('.loading-spinner');
        if (spinner) spinner.remove();
    });
}

function handleSaveSuccess() {
    const editButton = document.getElementById('editProfileBtn');
    const formInputs = document.querySelectorAll('#profileInfoForm input, #profileInfoForm textarea');
    
    // Disable inputs with animation
    formInputs.forEach((input, index) => {
        setTimeout(() => {
            input.disabled = true;
            input.classList.remove('editable');
        }, index * 50);
    });
    
    // Reset button state
    editButton.innerHTML = `
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
        </svg>
        Edit Profile
    `;
    editButton.classList.remove('editing');
}