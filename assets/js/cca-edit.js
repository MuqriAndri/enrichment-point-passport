document.addEventListener('DOMContentLoaded', function() {
    // Tab Navigation
    const tabButtons = document.querySelectorAll('.edit-tab');
    const tabPanels = document.querySelectorAll('.edit-panel');
    
    // Handle back link click
    const backLink = document.querySelector('.back-link-anchor');
    if (backLink) {
        backLink.addEventListener('click', function(e) {
            e.preventDefault();
            const href = this.getAttribute('href');
            console.log('Navigating back to:', href);
            window.location.href = href;
        });
    }
    
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Get the target panel from data-tab attribute
            const targetPanel = button.dataset.tab;
            
            // Remove active class from all buttons and panels
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabPanels.forEach(panel => panel.classList.remove('active'));
            
            // Add active class to current button and panel
            button.classList.add('active');
            document.getElementById(`${targetPanel}-panel`).classList.add('active');
            
            // Store the active tab in sessionStorage
            sessionStorage.setItem('activeClubEditTab', targetPanel);
        });
    });
    
    // Restore active tab from session storage if available
    const activeTab = sessionStorage.getItem('activeClubEditTab');
    if (activeTab) {
        const activeButton = document.querySelector(`.edit-tab[data-tab="${activeTab}"]`);
        if (activeButton) {
            activeButton.click();
        }
    }
    
    // Fix for submit buttons - ensure they trigger form submissions
    const allForms = document.querySelectorAll('form');
    allForms.forEach(form => {
        const submitBtns = form.querySelectorAll('.submit-btn');
        submitBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('Form submit button clicked', form);
                form.submit();
            });
        });
    });
    
    // Gallery Upload Preview
    const galleryUploadInput = document.getElementById('gallery-upload-input');
    const galleryUploadArea = document.getElementById('gallery-upload-area');
    const galleryPreview = document.getElementById('gallery-upload-preview');
    
    if (galleryUploadInput) {
        // Handle file selection
        galleryUploadInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    galleryPreview.innerHTML = `
                        <div class="preview-image">
                            <img src="${e.target.result}" alt="Preview">
                        </div>
                        <div class="preview-info">
                            <p><strong>File:</strong> ${file.name}</p>
                            <p><strong>Size:</strong> ${(file.size / 1024).toFixed(2)} KB</p>
                            <button type="button" class="reset-upload-btn">Clear</button>
                        </div>
                    `;
                    galleryPreview.style.display = 'block';
                    galleryUploadArea.style.display = 'none';
                    
                    // Handle reset button
                    document.querySelector('.reset-upload-btn').addEventListener('click', function() {
                        galleryUploadInput.value = '';
                        galleryPreview.style.display = 'none';
                        galleryUploadArea.style.display = 'block';
                    });
                };
                
                reader.readAsDataURL(file);
            }
        });
        
        // Handle drag and drop
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            galleryUploadArea.addEventListener(eventName, function(e) {
                e.preventDefault();
                e.stopPropagation();
            }, false);
        });
        
        ['dragenter', 'dragover'].forEach(eventName => {
            galleryUploadArea.addEventListener(eventName, function() {
                this.classList.add('highlight');
            }, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            galleryUploadArea.addEventListener(eventName, function() {
                this.classList.remove('highlight');
            }, false);
        });
        
        galleryUploadArea.addEventListener('drop', function(e) {
            const file = e.dataTransfer.files[0];
            if (file) {
                galleryUploadInput.files = e.dataTransfer.files;
                // Trigger change event manually
                const event = new Event('change');
                galleryUploadInput.dispatchEvent(event);
            }
        }, false);
    }
    
    // Activity Form Validation
    const addActivityForm = document.getElementById('add-activity-form');
    
    if (addActivityForm) {
        addActivityForm.addEventListener('submit', function(e) {
            const startDate = new Date(document.getElementById('activity-start-date').value);
            const endDate = new Date(document.getElementById('activity-end-date').value);
            
            if (endDate < startDate) {
                e.preventDefault();
                alert('End date cannot be before start date.');
                return false;
            }
            
            return true;
        });
    }
    
    // Animation for activity items
    const activityItems = document.querySelectorAll('.activity-item');
    
    activityItems.forEach((item, index) => {
        item.style.animationDelay = `${index * 0.05}s`;
        item.classList.add('animate-in');
    });
    
    // Make the map load properly if it's not initially visible
    const mapTab = document.querySelector('.edit-tab[data-tab="locations"]');
    if (mapTab) {
        mapTab.addEventListener('click', function() {
            // Force map to refresh when tab is selected
            setTimeout(() => {
                if (typeof google !== 'undefined' && typeof google.maps !== 'undefined') {
                    const mapEvent = new Event('resize');
                    google.maps.event.trigger(window, 'resize');
                    
                    // Recenter the map if it exists
                    if (window.map) {
                        const center = window.map.getCenter();
                        google.maps.event.trigger(window.map, 'resize');
                        window.map.setCenter(center);
                    }
                }
            }, 100);
        });
    }
}); 