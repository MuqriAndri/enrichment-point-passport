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
        button.addEventListener('click', function() {
            const tabName = this.dataset.tab;
            
            // Hide all panels
            tabPanels.forEach(panel => {
                panel.classList.remove('active');
            });
            
            // Deactivate all tabs
            tabButtons.forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Activate the selected tab and panel
            this.classList.add('active');
            document.getElementById(`${tabName}-panel`).classList.add('active');
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
    const uploadInput = document.getElementById('gallery-upload-input');
    const uploadArea = document.getElementById('gallery-upload-area');
    const previewArea = document.getElementById('gallery-upload-preview');
    const imageDetailsFields = document.getElementById('image-details-fields');
    
    if (uploadInput && uploadArea && previewArea) {
        uploadInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    previewArea.innerHTML = `
                        <div class="preview-image">
                            <img src="${e.target.result}" alt="Preview">
                        </div>
                        <div class="preview-info">
                            <p><strong>File Name:</strong> ${file.name}</p>
                            <p><strong>Size:</strong> ${Math.round(file.size / 1024)} KB</p>
                            <p class="image-details-message"><strong>Please provide image details below</strong></p>
                            <button type="button" class="reset-upload-btn" id="reset-upload">Cancel Selection</button>
                        </div>
                    `;
                    previewArea.style.display = 'block';
                    uploadArea.style.display = 'none';
                    
                    // Show image details fields
                    if (imageDetailsFields) {
                        imageDetailsFields.style.display = 'block';
                        imageDetailsFields.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        
                        // Highlight the fields briefly to draw attention
                        imageDetailsFields.classList.add('highlight-fields');
                        setTimeout(() => {
                            imageDetailsFields.classList.remove('highlight-fields');
                        }, 1500);
                        
                        // Focus on the title field
                        const titleField = document.getElementById('image_title');
                        if (titleField) {
                            setTimeout(() => titleField.focus(), 100);
                        }
                    }
                    
                    document.getElementById('reset-upload').addEventListener('click', function() {
                        uploadInput.value = '';
                        previewArea.style.display = 'none';
                        uploadArea.style.display = 'block';
                        
                        // Hide and reset image details fields
                        if (imageDetailsFields) {
                            imageDetailsFields.style.display = 'none';
                            const titleField = document.getElementById('image_title');
                            const descField = document.getElementById('image_description');
                            if (titleField) titleField.value = '';
                            if (descField) descField.value = '';
                        }
                    });
                };
                
                reader.readAsDataURL(file);
            }
        });
        
        // Drag and drop
        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('highlight');
        });
        
        uploadArea.addEventListener('dragleave', function() {
            this.classList.remove('highlight');
        });
        
        uploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('highlight');
            
            if (e.dataTransfer.files.length) {
                uploadInput.files = e.dataTransfer.files;
                const event = new Event('change');
                uploadInput.dispatchEvent(event);
            }
        });
        
        uploadArea.addEventListener('click', function() {
            uploadInput.click();
        });
    }
    
    // Handle custom datetime inputs with dropdowns
    function setupCustomDatetimeInputs() {
        // Start date elements
        const startMonthSelect = document.getElementById('activity-start-month');
        const startDaySelect = document.getElementById('activity-start-day');
        const startYearSelect = document.getElementById('activity-start-year');
        const startHourSelect = document.getElementById('activity-start-hour');
        const startMinuteSelect = document.getElementById('activity-start-minute');
        const startPeriodSelect = document.getElementById('activity-start-period');
        const startDatetimeHidden = document.getElementById('activity-start-datetime-hidden');
        
        // End date elements
        const endMonthSelect = document.getElementById('activity-end-month');
        const endDaySelect = document.getElementById('activity-end-day');
        const endYearSelect = document.getElementById('activity-end-year');
        const endHourSelect = document.getElementById('activity-end-hour');
        const endMinuteSelect = document.getElementById('activity-end-minute');
        const endPeriodSelect = document.getElementById('activity-end-period');
        const endDatetimeHidden = document.getElementById('activity-end-datetime-hidden');
        
        // Populate year options (current year + 5 years)
        const currentYear = new Date().getFullYear();
        const yearSelects = [startYearSelect, endYearSelect];
        
        yearSelects.forEach(select => {
            for (let year = currentYear; year <= currentYear + 5; year++) {
                const option = document.createElement('option');
                option.value = year;
                option.textContent = year;
                select.appendChild(option);
            }
        });
        
        // Function to update days based on month and year
        function updateDays(monthSelect, daySelect, yearSelect) {
            const selectedMonth = parseInt(monthSelect.value);
            const selectedYear = parseInt(yearSelect.value);
            
            // Clear existing options
            daySelect.innerHTML = '';
            
            // Calculate days in month (accounting for leap years)
            let daysInMonth = 31;
            
            if ([4, 6, 9, 11].includes(selectedMonth)) {
                daysInMonth = 30;
            } else if (selectedMonth === 2) {
                // Check for leap year
                const isLeapYear = (selectedYear % 4 === 0 && selectedYear % 100 !== 0) || (selectedYear % 400 === 0);
                daysInMonth = isLeapYear ? 29 : 28;
            }
            
            // Populate days
            for (let day = 1; day <= daysInMonth; day++) {
                const option = document.createElement('option');
                option.value = day.toString().padStart(2, '0');
                option.textContent = day;
                daySelect.appendChild(option);
            }
        }
        
        // Function to update hidden datetime input
        function updateHiddenDatetime(monthSelect, daySelect, yearSelect, hourSelect, minuteSelect, periodSelect, hiddenInput) {
            const month = monthSelect.value;
            const day = daySelect.value.padStart(2, '0');
            const year = yearSelect.value;
            
            let hour = parseInt(hourSelect.value);
            if (periodSelect.value === 'PM' && hour < 12) {
                hour += 12;
            } else if (periodSelect.value === 'AM' && hour === 12) {
                hour = 0;
            }
            
            const hourStr = hour.toString().padStart(2, '0');
            const minute = minuteSelect.value.padStart(2, '0');
            
            // Combine into ISO format for the hidden input
            const datetime = `${year}-${month}-${day}T${hourStr}:${minute}:00`;
            hiddenInput.value = datetime;
        }
        
        // Set up event listeners for the start date/time selectors
        [startMonthSelect, startYearSelect].forEach(select => {
            select.addEventListener('change', () => {
                updateDays(startMonthSelect, startDaySelect, startYearSelect);
                updateHiddenDatetime(
                    startMonthSelect, startDaySelect, startYearSelect,
                    startHourSelect, startMinuteSelect, startPeriodSelect,
                    startDatetimeHidden
                );
            });
        });
        
        [startDaySelect, startHourSelect, startMinuteSelect, startPeriodSelect].forEach(select => {
            select.addEventListener('change', () => {
                updateHiddenDatetime(
                    startMonthSelect, startDaySelect, startYearSelect,
                    startHourSelect, startMinuteSelect, startPeriodSelect,
                    startDatetimeHidden
                );
            });
        });
        
        // Set up event listeners for the end date/time selectors
        [endMonthSelect, endYearSelect].forEach(select => {
            select.addEventListener('change', () => {
                updateDays(endMonthSelect, endDaySelect, endYearSelect);
                updateHiddenDatetime(
                    endMonthSelect, endDaySelect, endYearSelect,
                    endHourSelect, endMinuteSelect, endPeriodSelect,
                    endDatetimeHidden
                );
            });
        });
        
        [endDaySelect, endHourSelect, endMinuteSelect, endPeriodSelect].forEach(select => {
            select.addEventListener('change', () => {
                updateHiddenDatetime(
                    endMonthSelect, endDaySelect, endYearSelect,
                    endHourSelect, endMinuteSelect, endPeriodSelect,
                    endDatetimeHidden
                );
            });
        });
        
        // Set default values - Current date and time for start, 2 hours later for end
        const now = new Date();
        
        // Start date default values
        startMonthSelect.value = (now.getMonth() + 1).toString().padStart(2, '0');
        startYearSelect.value = now.getFullYear().toString();
        updateDays(startMonthSelect, startDaySelect, startYearSelect);
        startDaySelect.value = now.getDate().toString().padStart(2, '0');
        
        let hours = now.getHours();
        const minutes = Math.floor(now.getMinutes() / 5) * 5; // Round to nearest 5 minutes
        let period = 'AM';
        
        if (hours >= 12) {
            period = 'PM';
            if (hours > 12) {
                hours -= 12;
            }
        }
        if (hours === 0) {
            hours = 12;
        }
        
        startHourSelect.value = hours.toString();
        startMinuteSelect.value = minutes.toString();
        startPeriodSelect.value = period;
        
        // End date default values (2 hours later)
        const endTime = new Date(now.getTime() + 2 * 60 * 60 * 1000);
        
        endMonthSelect.value = (endTime.getMonth() + 1).toString().padStart(2, '0');
        endYearSelect.value = endTime.getFullYear().toString();
        updateDays(endMonthSelect, endDaySelect, endYearSelect);
        endDaySelect.value = endTime.getDate().toString().padStart(2, '0');
        
        let endHours = endTime.getHours();
        const endMinutes = Math.floor(endTime.getMinutes() / 5) * 5;
        let endTimePeriod = 'AM';
        
        if (endHours >= 12) {
            endTimePeriod = 'PM';
            if (endHours > 12) {
                endHours -= 12;
            }
        }
        if (endHours === 0) {
            endHours = 12;
        }
        
        endHourSelect.value = endHours.toString();
        endMinuteSelect.value = endMinutes.toString();
        endPeriodSelect.value = endTimePeriod;
        
        // Initial update of hidden inputs
        updateHiddenDatetime(
            startMonthSelect, startDaySelect, startYearSelect,
            startHourSelect, startMinuteSelect, startPeriodSelect,
            startDatetimeHidden
        );
        
        updateHiddenDatetime(
            endMonthSelect, endDaySelect, endYearSelect,
            endHourSelect, endMinuteSelect, endPeriodSelect,
            endDatetimeHidden
        );
    }
    
    // Activity Form Validation with custom datetime inputs
    const addActivityForm = document.getElementById('add-activity-form');
    
    if (addActivityForm) {
        // Setup custom datetime inputs when form exists
        setupCustomDatetimeInputs();
        
        addActivityForm.addEventListener('submit', function(e) {
            // Get hidden inputs with combined datetime values
            const startDatetimeHidden = document.getElementById('activity-start-datetime-hidden');
            const endDatetimeHidden = document.getElementById('activity-end-datetime-hidden');
            
            if (!startDatetimeHidden.value || !endDatetimeHidden.value) {
                e.preventDefault();
                alert('Please select both date and time for start and end.');
                return false;
            }
            
            const startDate = new Date(startDatetimeHidden.value);
            const endDate = new Date(endDatetimeHidden.value);
            
            if (endDate < startDate) {
                e.preventDefault();
                alert('End date and time cannot be before start date and time.');
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
    
    // Handle datetime input styling
    const datetimeInputs = document.querySelectorAll('input[type="datetime-local"]');
    
    datetimeInputs.forEach(input => {
        // Force primary color style by injecting inline style for the calendar picker
        const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--primary-color').trim() || '#2d4a7c';
        
        // Apply the styling directly to the input
        input.style.colorScheme = 'light';
        
        // Create a style element to target the calendar picker specifically for this input
        const styleId = 'datetime-style-' + Math.random().toString(36).substr(2, 9);
        const styleEl = document.createElement('style');
        styleEl.id = styleId;
        styleEl.textContent = `
            #${input.id}::-webkit-calendar-picker-indicator {
                filter: invert(30%) sepia(90%) saturate(1000%) hue-rotate(175deg) !important;
                opacity: 1 !important;
            }
            #${input.id}:hover::-webkit-calendar-picker-indicator {
                filter: invert(25%) sepia(95%) saturate(1500%) hue-rotate(175deg) !important;
                opacity: 1 !important;
            }
        `;
        document.head.appendChild(styleEl);
        
        // Apply consistent styling on focus/blur
        input.addEventListener('focus', function() {
            this.style.borderColor = primaryColor;
            this.style.boxShadow = `0 0 0 3px ${primaryColor}25`;
        });
        
        input.addEventListener('blur', function() {
            this.style.borderColor = 'var(--border-color)';
            this.style.boxShadow = 'none';
        });
        
        // Force the color on load
        setTimeout(() => {
            // This helps ensure the style is applied after browser styles
            const pickerIcon = input.querySelector('::-webkit-calendar-picker-indicator');
            if (pickerIcon) {
                pickerIcon.style.filter = 'invert(30%) sepia(90%) saturate(1000%) hue-rotate(175deg)';
            }
        }, 100);
    });
    
    // Date-Time picker functionality
    setupDateTimeSelectors();
    
    // Initialize date pickers with current date
    function setupDateTimeSelectors() {
        // Populate years (current year + 5 years ahead)
        const startYearSelect = document.getElementById('activity-start-year');
        const endYearSelect = document.getElementById('activity-end-year');
        
        if (startYearSelect && endYearSelect) {
            const currentYear = new Date().getFullYear();
            
            for (let year = currentYear; year < currentYear + 5; year++) {
                const startYearOption = document.createElement('option');
                startYearOption.value = year;
                startYearOption.textContent = year;
                
                const endYearOption = document.createElement('option');
                endYearOption.value = year;
                endYearOption.textContent = year;
                
                startYearSelect.appendChild(startYearOption);
                endYearSelect.appendChild(endYearOption);
            }
            
            // Set default to current year
            startYearSelect.value = currentYear;
            endYearSelect.value = currentYear;
            
            // Populate days (depends on month and year)
            populateDays('activity-start-month', 'activity-start-day', 'activity-start-year');
            populateDays('activity-end-month', 'activity-end-day', 'activity-end-year');
            
            // Add change event listeners for month and year to update days
            document.getElementById('activity-start-month').addEventListener('change', function() {
                populateDays('activity-start-month', 'activity-start-day', 'activity-start-year');
            });
            
            document.getElementById('activity-start-year').addEventListener('change', function() {
                populateDays('activity-start-month', 'activity-start-day', 'activity-start-year');
            });
            
            document.getElementById('activity-end-month').addEventListener('change', function() {
                populateDays('activity-end-month', 'activity-end-day', 'activity-end-year');
            });
            
            document.getElementById('activity-end-year').addEventListener('change', function() {
                populateDays('activity-end-month', 'activity-end-day', 'activity-end-year');
            });
            
            // Set default time values - current time for start, +2 hours for end
            const now = new Date();
            
            // Start time defaults
            const startHour = document.getElementById('activity-start-hour');
            const startMinute = document.getElementById('activity-start-minute');
            const startPeriod = document.getElementById('activity-start-period');
            
            let hours = now.getHours();
            const minutes = now.getMinutes();
            let period = 'AM';
            
            if (hours >= 12) {
                period = 'PM';
                if (hours > 12) {
                    hours -= 12;
                }
            }
            if (hours === 0) {
                hours = 12;
            }
            
            startHour.value = hours;
            startMinute.value = minutes;
            startPeriod.value = period;
            
            // End time defaults (+2 hours)
            const endHour = document.getElementById('activity-end-hour');
            const endMinute = document.getElementById('activity-end-minute');
            const endPeriodSelect = document.getElementById('activity-end-period');
            
            const endTime = new Date(now.getTime() + 2 * 60 * 60 * 1000);
            
            let endHours = endTime.getHours();
            const endMinutes = endTime.getMinutes();
            let endTimePeriod = 'AM';
            
            if (endHours >= 12) {
                endTimePeriod = 'PM';
                if (endHours > 12) {
                    endHours -= 12;
                }
            }
            if (endHours === 0) {
                endHours = 12;
            }
            
            endHour.value = endHours;
            endMinute.value = endMinutes;
            endPeriodSelect.value = endTimePeriod;
            
            // Add validation for time inputs
            setupTimeInputValidation('activity-start-hour', 1, 12);
            setupTimeInputValidation('activity-start-minute', 0, 59);
            setupTimeInputValidation('activity-end-hour', 1, 12);
            setupTimeInputValidation('activity-end-minute', 0, 59);
            
            // Add event listeners to all selector elements to update the hidden datetime input
            const startSelectors = [
                'activity-start-month', 'activity-start-day', 'activity-start-year',
                'activity-start-hour', 'activity-start-minute', 'activity-start-period'
            ];
            
            const endSelectors = [
                'activity-end-month', 'activity-end-day', 'activity-end-year',
                'activity-end-hour', 'activity-end-minute', 'activity-end-period'
            ];
            
            startSelectors.forEach(id => {
                document.getElementById(id).addEventListener('change', function() {
                    updateHiddenDatetime('start');
                });
                
                // For number inputs, also listen for input events
                if (id.includes('hour') || id.includes('minute')) {
                    document.getElementById(id).addEventListener('input', function() {
                        updateHiddenDatetime('start');
                    });
                }
            });
            
            endSelectors.forEach(id => {
                document.getElementById(id).addEventListener('change', function() {
                    updateHiddenDatetime('end');
                });
                
                // For number inputs, also listen for input events
                if (id.includes('hour') || id.includes('minute')) {
                    document.getElementById(id).addEventListener('input', function() {
                        updateHiddenDatetime('end');
                    });
                }
            });
            
            // Initial population of hidden inputs
            updateHiddenDatetime('start');
            updateHiddenDatetime('end');
            
            // Set up the form submission handler
            setupFormSubmitHandler();
        }
    }
    
    // Function to populate days based on month and year
    function populateDays(monthSelectId, daySelectId, yearSelectId) {
        const monthSelect = document.getElementById(monthSelectId);
        const daySelect = document.getElementById(daySelectId);
        const yearSelect = document.getElementById(yearSelectId);
        
        if (monthSelect && daySelect && yearSelect) {
            const month = parseInt(monthSelect.value);
            const year = parseInt(yearSelect.value);
            
            // Determine the last day of the month
            const lastDay = new Date(year, month, 0).getDate();
            
            // Store the current selection if it exists
            const currentDay = daySelect.value;
            
            // Clear the day select
            daySelect.innerHTML = '';
            
            // Populate with new days
            for (let day = 1; day <= lastDay; day++) {
                const option = document.createElement('option');
                option.value = day;
                option.textContent = String(day).padStart(2, '0');
                daySelect.appendChild(option);
            }
            
            // Try to restore the previous selection if valid
            if (currentDay && currentDay <= lastDay) {
                daySelect.value = currentDay;
            } else {
                daySelect.value = 1; // Default to first day
            }
        }
    }
    
    // Setup validation for time inputs
    function setupTimeInputValidation(inputId, min, max) {
        const input = document.getElementById(inputId);
        if (input) {
            // Ensure value is within range on blur
            input.addEventListener('blur', function() {
                let value = parseInt(this.value);
                
                if (isNaN(value)) {
                    value = inputId.includes('hour') ? 12 : 0;
                } else if (value < min) {
                    value = min;
                } else if (value > max) {
                    value = max;
                }
                
                this.value = value;
                
                // Update the hidden datetime field
                const type = inputId.includes('start') ? 'start' : 'end';
                updateHiddenDatetime(type);
            });
            
            // Prevent non-numeric input
            input.addEventListener('keydown', function(e) {
                // Allow: backspace, delete, tab, escape, enter, ctrl+A
                if ([46, 8, 9, 27, 13, 110].indexOf(e.keyCode) !== -1 ||
                    // Allow: Ctrl+A/Ctrl+C/Ctrl+V/Ctrl+X
                    (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
                    (e.keyCode === 67 && (e.ctrlKey === true || e.metaKey === true)) ||
                    (e.keyCode === 86 && (e.ctrlKey === true || e.metaKey === true)) ||
                    (e.keyCode === 88 && (e.ctrlKey === true || e.metaKey === true)) ||
                    // Allow: home, end, left, right
                    (e.keyCode >= 35 && e.keyCode <= 39)) {
                    return;
                }
                
                // Ensure that it's a number and stop the keypress if not
                if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && 
                    (e.keyCode < 96 || e.keyCode > 105)) {
                    e.preventDefault();
                }
            });
        }
    }
    
    // Update hidden datetime input from selectors
    function updateHiddenDatetime(type) {
        const month = document.getElementById(`activity-${type}-month`).value;
        const day = document.getElementById(`activity-${type}-day`).value;
        const year = document.getElementById(`activity-${type}-year`).value;
        
        // Get hour and minute from the input fields
        let hour = parseInt(document.getElementById(`activity-${type}-hour`).value) || 12;
        let minute = parseInt(document.getElementById(`activity-${type}-minute`).value) || 0;
        const period = document.getElementById(`activity-${type}-period`).value;
        
        // Validate hour and minute
        if (hour < 1) hour = 1;
        if (hour > 12) hour = 12;
        if (minute < 0) minute = 0;
        if (minute > 59) minute = 59;
        
        // Convert to 24-hour format
        if (period === 'PM' && hour < 12) {
            hour += 12;
        } else if (period === 'AM' && hour === 12) {
            hour = 0;
        }
        
        // Format the date string in YYYY-MM-DDTHH:MM format
        const dateString = `${year}-${month}-${String(day).padStart(2, '0')}T${String(hour).padStart(2, '0')}:${String(minute).padStart(2, '0')}`;
        
        // Update the hidden input
        document.getElementById(`activity-${type}-datetime-hidden`).value = dateString;
    }
    
    // Setup form submission handler
    function setupFormSubmitHandler() {
        const form = document.getElementById('add-activity-form');
        
        if (form) {
            form.addEventListener('submit', function(e) {
                // Validate inputs before submission
                const startHour = document.getElementById('activity-start-hour');
                const startMinute = document.getElementById('activity-start-minute');
                const endHour = document.getElementById('activity-end-hour');
                const endMinute = document.getElementById('activity-end-minute');
                
                // Validate required time inputs
                if (!startHour.value || !startMinute.value || !endHour.value || !endMinute.value) {
                    e.preventDefault();
                    alert('Please enter both hour and minute values for start and end times.');
                    return;
                }
                
                // Update both hidden datetime inputs before submission
                updateHiddenDatetime('start');
                updateHiddenDatetime('end');
                
                // Ensure end time is after start time
                const startDatetime = new Date(document.getElementById('activity-start-datetime-hidden').value);
                const endDatetime = new Date(document.getElementById('activity-end-datetime-hidden').value);
                
                if (endDatetime <= startDatetime) {
                    e.preventDefault();
                    alert('End time must be after start time.');
                    return;
                }
                
                // Form will submit normally if all validation passes
            });
        }
    }
}); 