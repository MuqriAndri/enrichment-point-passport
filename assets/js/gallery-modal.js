document.addEventListener('DOMContentLoaded', function() {
    // Gallery Read More buttons
    const readMoreButtons = document.querySelectorAll('.gallery-read-more');
    if (readMoreButtons.length > 0) {
        readMoreButtons.forEach(button => {
            button.addEventListener('click', function() {
                const galleryItem = this.closest('.gallery-item');
                const overlay = this.closest('.gallery-item-overlay');
                
                // Get image details
                const imgElement = galleryItem.querySelector('img');
                const titleElement = galleryItem.querySelector('.gallery-item-title');
                
                const image = imgElement.getAttribute('src');
                const title = titleElement ? titleElement.textContent : 'Gallery Image';
                const description = overlay.getAttribute('data-description') || '';
                
                // Populate modal
                const modalImageTitle = document.getElementById('modal-image-title');
                const modalImage = document.getElementById('modal-image');
                const modalImageDescription = document.getElementById('modal-image-description');
                
                modalImageTitle.textContent = title;
                modalImage.src = image;
                
                // Handle description - hide if empty
                if (description.trim() === '') {
                    modalImageDescription.style.display = 'none';
                } else {
                    modalImageDescription.style.display = 'block';
                    modalImageDescription.textContent = description;
                }
                
                // Show modal
                document.getElementById('gallery-modal').style.display = 'block';
                
                // Set focus to the modal for accessibility
                setTimeout(() => {
                    document.querySelector('.gallery-modal-close').focus();
                }, 100);
            });
        });
        
        // Close Gallery Modal
        const galleryCloseButton = document.querySelector('.gallery-modal-close');
        if (galleryCloseButton) {
            galleryCloseButton.addEventListener('click', function() {
                document.getElementById('gallery-modal').style.display = 'none';
            });
        }
        
        // Close modal on outside click
        window.addEventListener('click', function(e) {
            const galleryModal = document.getElementById('gallery-modal');
            if (galleryModal && e.target === galleryModal) {
                galleryModal.style.display = 'none';
            }
        });
        
        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const galleryModal = document.getElementById('gallery-modal');
                if (galleryModal && galleryModal.style.display === 'block') {
                    galleryModal.style.display = 'none';
                }
            }
        });
    }
}); 