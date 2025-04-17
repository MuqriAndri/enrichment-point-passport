document.querySelectorAll('.image-slider').forEach(slider => {
    const images = slider.querySelectorAll('.event-image');
    const indicators = slider.querySelectorAll('.indicator');
    const nextButton = slider.querySelector('.nav-button.right');
    const prevButton = slider.querySelector('.nav-button.left');
    let currentIndex = 0;
    let startX = 0;
    let isDragging = false;

    const showImage = (index) => {
        images.forEach((img, i) => {
            img.style.display = i === index ? 'block' : 'none';
        });
        indicators.forEach((indicator, i) => {
            indicator.classList.toggle('active', i === index);
        });
        currentIndex = index;
    };

    const nextImage = () => {
        const nextIndex = (currentIndex + 1) % images.length;
        showImage(nextIndex);
    };

    const prevImage = () => {
        const prevIndex = (currentIndex - 1 + images.length) % images.length;
        showImage(prevIndex);
    };

    // Add event listeners for navigation buttons
    if (nextButton && prevButton) {
        nextButton.addEventListener('click', nextImage);
        prevButton.addEventListener('click', prevImage);
    }

    // Add swipe functionality for mobile and laptop views
    const startSwipe = (x) => {
        startX = x;
        isDragging = true;
    };

    const endSwipe = (x) => {
        if (!isDragging) return;
        isDragging = false;
        if (startX > x + 50) {
            nextImage(); // Swipe left
        } else if (startX < x - 50) {
            prevImage(); // Swipe right
        }
    };

    // Touch events for mobile
    slider.addEventListener('touchstart', (e) => startSwipe(e.touches[0].clientX));
    slider.addEventListener('touchend', (e) => endSwipe(e.changedTouches[0].clientX));

    // Mouse events for laptop
    slider.addEventListener('mousedown', (e) => {
        startSwipe(e.clientX);
        isDragging = true;
    });

    slider.addEventListener('mousemove', (e) => {
        if (!isDragging) return;
        const currentX = e.clientX;
        if (startX > currentX + 50) {
            nextImage(); // Swipe left
            isDragging = false; // Prevent multiple triggers
        } else if (startX < currentX - 50) {
            prevImage(); // Swipe right
            isDragging = false; // Prevent multiple triggers
        }
    });

    slider.addEventListener('mouseup', (e) => {
        endSwipe(e.clientX);
    });

    slider.addEventListener('mouseleave', () => {
        isDragging = false; // Cancel drag if mouse leaves slider
    });

    // Add click event listeners to indicators for manual sliding
    indicators.forEach(indicator => {
        indicator.addEventListener('click', () => {
            const index = parseInt(indicator.getAttribute('data-index'));
            showImage(index);
        });
    });

    // Initialize the first image
    showImage(currentIndex);
});

document.querySelectorAll('.learn-more-btn').forEach(button => {
    button.addEventListener('click', () => {
        const modal = document.getElementById('event-modal');
        const modalName = document.getElementById('modal-event-name');
        const modalDescription = document.getElementById('modal-event-description');
        const modalDate = document.getElementById('modal-event-date');
        const modalTime = document.getElementById('modal-event-time');
        const modalLocation = document.getElementById('modal-event-location');
        const modalPoints = document.getElementById('modal-event-points');
        const modalRegisterBtn = document.getElementById('modal-register-btn');

        // Populate modal with event details from data attributes
        modalName.textContent = button.getAttribute('data-name');
        modalDescription.textContent = "Description: " + button.getAttribute('data-description');
        modalDate.textContent = "Date: " + button.getAttribute('data-date');
        modalTime.textContent = "Time: " + button.getAttribute('data-time');
        modalLocation.textContent = "Location: " + button.getAttribute('data-location');
        modalPoints.textContent = "Enrichment Points: " + button.getAttribute('data-points');

        // Check event status and update register button behavior
        const status = button.getAttribute('data-status');
        if (status === "Upcoming") {
            modalRegisterBtn.onclick = () => {
                alert("Event is not available yet");
            };
        } else {
            modalRegisterBtn.onclick = () => {
                alert("You have successfully registered for this event!");
            };
        }

        // Show the modal
        modal.style.display = 'flex';
    });
});

// Close modal logic
document.querySelector('.close-btn').addEventListener('click', () => {
    document.getElementById('event-modal').style.display = 'none';
});

// Close modal when clicking outside the modal content
window.addEventListener('click', (e) => {
    const modal = document.getElementById('event-modal');
    if (e.target === modal) {
        modal.style.display = 'none';
    }
});
