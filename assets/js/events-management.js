/**
 * Events Management JavaScript
 * 
 * This file contains additional functionality for the events management page
 * that complements the inline script in the events-management.php template.
 */

document.addEventListener('DOMContentLoaded', function() {
    // Add timestamp for cache busting
    const timestamp = new Date().getTime();
    
    // Fade out alert messages after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    if (alerts.length > 0) {
        setTimeout(function() {
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.style.display = 'none', 500);
            });
        }, 5000);
    }
    
    // Handle table row highlighting
    const tableRows = document.querySelectorAll('.events-table tbody tr');
    tableRows.forEach(row => {
        row.addEventListener('click', function(e) {
            // Only highlight if not clicking a button
            if (!e.target.closest('button')) {
                tableRows.forEach(r => r.classList.remove('highlighted'));
                this.classList.add('highlighted');
            }
        });
    });
    
    // Responsive table
    adjustTableForMobile();
    window.addEventListener('resize', adjustTableForMobile);
    
    // Form validation
    const eventForm = document.getElementById('event-form');
    if (eventForm) {
        eventForm.addEventListener('submit', function(e) {
            if (!validateEventForm()) {
                e.preventDefault();
            }
        });
    }
});

/**
 * Adjust table display for mobile viewports
 */
function adjustTableForMobile() {
    const table = document.querySelector('.events-table');
    if (!table) return;
    
    const isMobile = window.innerWidth < 768;
    const headerRow = table.querySelector('thead tr');
    
    if (isMobile) {
        // Add data attributes to cells for mobile display
        const headers = Array.from(headerRow.children).map(th => th.textContent.trim());
        
        table.querySelectorAll('tbody tr').forEach(row => {
            Array.from(row.children).forEach((cell, index) => {
                if (headers[index]) {
                    cell.setAttribute('data-label', headers[index]);
                }
            });
        });
        
        table.classList.add('mobile-table');
    } else {
        table.classList.remove('mobile-table');
    }
}

/**
 * Validate event form before submission
 * @returns {boolean} Whether the form is valid
 */
function validateEventForm() {
    const form = document.getElementById('event-form');
    if (!form) return true;
    
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    // Reset previous validation
    form.querySelectorAll('.error-message').forEach(msg => msg.remove());
    form.querySelectorAll('.error-field').forEach(field => field.classList.remove('error-field'));
    
    // Check required fields
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            field.classList.add('error-field');
            
            // Add error message
            const errorMsg = document.createElement('div');
            errorMsg.className = 'error-message';
            errorMsg.textContent = 'This field is required';
            field.parentNode.appendChild(errorMsg);
        }
    });
    
    // Validate date and time logic
    const eventDate = document.getElementById('event_date');
    const startTime = document.getElementById('start_time');
    const endTime = document.getElementById('end_time');
    
    if (eventDate && startTime && endTime && eventDate.value && startTime.value && endTime.value) {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        const selectedDate = new Date(eventDate.value);
        selectedDate.setHours(0, 0, 0, 0);
        
        // Check if date is in the past
        if (selectedDate < today) {
            isValid = false;
            eventDate.classList.add('error-field');
            
            const errorMsg = document.createElement('div');
            errorMsg.className = 'error-message';
            errorMsg.textContent = 'Event date cannot be in the past';
            eventDate.parentNode.appendChild(errorMsg);
        }
        
        // Check if end time is before start time
        if (startTime.value >= endTime.value) {
            isValid = false;
            endTime.classList.add('error-field');
            
            const errorMsg = document.createElement('div');
            errorMsg.className = 'error-message';
            errorMsg.textContent = 'End time must be after start time';
            endTime.parentNode.appendChild(errorMsg);
        }
    }
    
    // Validate participants count
    const participantsField = document.getElementById('event_participants');
    if (participantsField && participantsField.value) {
        const participants = parseInt(participantsField.value);
        if (isNaN(participants) || participants <= 0) {
            isValid = false;
            participantsField.classList.add('error-field');
            
            const errorMsg = document.createElement('div');
            errorMsg.className = 'error-message';
            errorMsg.textContent = 'Participants must be a positive number';
            participantsField.parentNode.appendChild(errorMsg);
        }
    }
    
    // Validate enrichment points
    const pointsField = document.getElementById('enrichment_points_awarded');
    if (pointsField && pointsField.value) {
        const points = parseInt(pointsField.value);
        if (isNaN(points) || points < 0) {
            isValid = false;
            pointsField.classList.add('error-field');
            
            const errorMsg = document.createElement('div');
            errorMsg.className = 'error-message';
            errorMsg.textContent = 'Enrichment points cannot be negative';
            pointsField.parentNode.appendChild(errorMsg);
        }
    }
    
    return isValid;
}

// Add mobile-specific styles for tables
document.addEventListener('DOMContentLoaded', function() {
    const style = document.createElement('style');
    style.textContent = `
        @media screen and (max-width: 768px) {
            .mobile-table thead {
                border: none;
                clip: rect(0 0 0 0);
                height: 1px;
                margin: -1px;
                overflow: hidden;
                padding: 0;
                position: absolute;
                width: 1px;
            }
            
            .mobile-table tr {
                display: block;
                margin-bottom: 1rem;
                border: 1px solid var(--border-color);
                border-radius: var(--radius-sm);
            }
            
            .mobile-table td {
                display: block;
                text-align: right;
                position: relative;
                padding-left: 50%;
                border-bottom: 1px solid var(--border-color);
            }
            
            .mobile-table td:last-child {
                border-bottom: none;
            }
            
            .mobile-table td::before {
                content: attr(data-label);
                position: absolute;
                left: 1rem;
                width: 45%;
                white-space: nowrap;
                text-align: left;
                font-weight: 500;
            }
            
            body.dark .mobile-table tr {
                border-color: #4a5568;
            }
            
            body.dark .mobile-table td {
                border-color: #4a5568;
            }
        }
    `;
    document.head.appendChild(style);
}); 