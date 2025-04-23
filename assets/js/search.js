/**
 * Search functionality for the Enrichment Point Passport system
 * This script handles search functionality across different pages
 */

document.addEventListener('DOMContentLoaded', function() {
    // Get the search input element
    const searchInput = document.querySelector('.search-bar input');
    if (!searchInput) return;

    // Initialize search based on current page
    initializeSearch(searchInput);

    // Add event listener for the search icon click
    const searchIcon = document.querySelector('.search-bar svg');
    if (searchIcon) {
        searchIcon.addEventListener('click', function() {
            performSearch(searchInput.value);
        });
    }

    // Add event listener for the Enter key
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            performSearch(this.value);
        }
    });
});

/**
 * Initialize search functionality based on current page
 * @param {HTMLElement} searchInput - The search input element
 */
function initializeSearch(searchInput) {
    // Determine which page we're on based on URL or active tab
    const currentPath = window.location.pathname;
    const activeTab = document.querySelector('.tab-item.active');
    let currentPage = 'dashboard';

    if (activeTab) {
        currentPage = activeTab.textContent.toLowerCase().trim();
    } else if (currentPath.includes('/dashboard')) {
        currentPage = 'dashboard';
    } else if (currentPath.includes('/events')) {
        currentPage = 'events';
    } else if (currentPath.includes('/cca')) {
        currentPage = 'ccas';
    } else if (currentPath.includes('/history')) {
        currentPage = 'history';
    } else if (currentPath.includes('/ep')) {
        currentPage = 'enrichment point';
    }

    // Update placeholder text based on current page
    updateSearchPlaceholder(searchInput, currentPage);

    // Add input event listener for real-time filtering
    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        filterPageContent(query, currentPage);
    });
}

/**
 * Update search placeholder text based on current page
 * @param {HTMLElement} searchInput - The search input element
 * @param {string} currentPage - The current page name
 */
function updateSearchPlaceholder(searchInput, currentPage) {
    switch (currentPage) {
        case 'events':
            searchInput.placeholder = 'Search events...';
            break;
        case 'ccas':
            searchInput.placeholder = 'Search clubs...';
            break;
        case 'history':
            searchInput.placeholder = 'Search history...';
            break;
        case 'enrichment point':
            searchInput.placeholder = 'Search enrichment points...';
            break;
        default:
            searchInput.placeholder = 'Search activities...';
    }
}

/**
 * Perform search action
 * @param {string} query - The search query
 */
function performSearch(query) {
    if (!query.trim()) return;
    
    console.log('Searching for:', query);
    
    // Get the current page to determine search context
    const currentPath = window.location.pathname;
    const activeTab = document.querySelector('.tab-item.active');
    let currentPage = 'dashboard';

    if (activeTab) {
        currentPage = activeTab.textContent.toLowerCase().trim();
    } else if (currentPath.includes('/dashboard')) {
        currentPage = 'dashboard';
    } else if (currentPath.includes('/events')) {
        currentPage = 'events';
    } else if (currentPath.includes('/cca')) {
        currentPage = 'ccas';
    } else if (currentPath.includes('/history')) {
        currentPage = 'history';
    } else if (currentPath.includes('/ep')) {
        currentPage = 'enrichment point';
    }

    // Filter page content based on query and current page
    filterPageContent(query.toLowerCase().trim(), currentPage);
}

/**
 * Filter page content based on search query
 * @param {string} query - The search query
 * @param {string} currentPage - The current page name
 */
function filterPageContent(query, currentPage) {
    if (!query) {
        // Show all items if query is empty
        resetPageContent(currentPage);
        return;
    }

    console.log(`Filtering ${currentPage} content with query: ${query}`);

    switch (currentPage) {
        case 'dashboard':
            filterDashboardContent(query);
            break;
        case 'events':
            filterEventsContent(query);
            break;
        case 'ccas':
            filterCcaContent(query);
            break;
        case 'history':
            filterHistoryContent(query);
            break;
        case 'enrichment point':
            filterEpContent(query);
            break;
    }
}

/**
 * Reset page content to show all items
 * @param {string} currentPage - The current page name
 */
function resetPageContent(currentPage) {
    switch (currentPage) {
        case 'dashboard':
            document.querySelectorAll('.activity-item').forEach(item => {
                item.style.display = '';
            });
            break;
        case 'events':
            document.querySelectorAll('.event-slide, .event-item-box').forEach(item => {
                item.style.display = '';
            });
            break;
        case 'ccas':
            document.querySelectorAll('.club-card').forEach(item => {
                item.style.display = '';
            });
            break;
        case 'history':
            document.querySelectorAll('.history-table tr, .details-table tr').forEach(item => {
                if (!item.parentElement.tagName.toLowerCase() === 'thead') {
                    item.style.display = '';
                }
            });
            break;
        case 'enrichment point':
            document.querySelectorAll('.ep-table tr').forEach(item => {
                if (!item.parentElement.tagName.toLowerCase() === 'thead') {
                    item.style.display = '';
                }
            });
            break;
    }
}

/**
 * Filter dashboard activity items
 * @param {string} query - The search query
 */
function filterDashboardContent(query) {
    const activityItems = document.querySelectorAll('.activity-item');
    
    activityItems.forEach(item => {
        const title = item.querySelector('h4')?.textContent.toLowerCase() || '';
        const description = item.querySelector('p')?.textContent.toLowerCase() || '';
        
        if (title.includes(query) || description.includes(query)) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });
}

/**
 * Filter events content
 * @param {string} query - The search query
 */
function filterEventsContent(query) {
    // Filter event slides
    const eventSlides = document.querySelectorAll('.event-slide');
    eventSlides.forEach(item => {
        const title = item.querySelector('h3')?.textContent.toLowerCase() || '';
        const description = item.querySelector('.short-description')?.textContent.toLowerCase() || '';
        const fullDescription = item.querySelector('.full-description')?.textContent.toLowerCase() || '';
        
        if (title.includes(query) || description.includes(query) || fullDescription.includes(query)) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });

    // Filter event item boxes
    const eventBoxes = document.querySelectorAll('.event-item-box');
    eventBoxes.forEach(item => {
        const title = item.querySelector('h2')?.textContent.toLowerCase() || '';
        const description = item.querySelector('p')?.textContent.toLowerCase() || '';
        
        if (title.includes(query) || description.includes(query)) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });
}

/**
 * Filter CCA club cards
 * @param {string} query - The search query
 */
function filterCcaContent(query) {
    const clubCards = document.querySelectorAll('.club-card');
    let anyMatches = false;
    
    clubCards.forEach(card => {
        const clubName = card.querySelector('.club-name')?.textContent.toLowerCase() || '';
        const clubInfo = card.querySelector('.club-info')?.textContent.toLowerCase() || '';
        const clubDescription = card.querySelector('.club-description')?.textContent.toLowerCase() || '';
        
        if (clubName.includes(query) || clubInfo.includes(query) || clubDescription.includes(query)) {
            card.style.display = '';
            anyMatches = true;
        } else {
            card.style.display = 'none';
        }
    });

    // Check if any club is visible in each category
    document.querySelectorAll('.club-category').forEach(category => {
        const categoryName = category.querySelector('h2')?.textContent.toLowerCase() || '';
        
        // If category name matches query, show all clubs in that category
        if (categoryName.includes(query)) {
            category.querySelectorAll('.club-card').forEach(card => {
                card.style.display = '';
            });
            category.style.display = '';
        } else {
            // Check if any clubs in this category are visible
            const visibleClubs = Array.from(category.querySelectorAll('.club-card')).some(
                card => card.style.display !== 'none'
            );
            
            // Show or hide the category based on visible clubs
            category.style.display = visibleClubs ? '' : 'none';
        }
    });
    
    // Show no results notification if needed
    if (!anyMatches && query.length > 0) {
        showNoResultsNotification();
    }
}

/**
 * Filter history table rows
 * @param {string} query - The search query
 */
function filterHistoryContent(query) {
    // Filter history table rows
    const historyRows = document.querySelectorAll('.history-table tbody tr');
    
    historyRows.forEach(row => {
        const cells = row.querySelectorAll('td');
        let matched = false;
        
        cells.forEach(cell => {
            if (cell.textContent.toLowerCase().includes(query)) {
                matched = true;
            }
        });
        
        row.style.display = matched ? '' : 'none';
    });
    
    // Filter details table rows if present
    const detailsRows = document.querySelectorAll('.details-table tbody tr');
    
    detailsRows.forEach(row => {
        const cells = row.querySelectorAll('td');
        let matched = false;
        
        cells.forEach(cell => {
            if (cell.textContent.toLowerCase().includes(query)) {
                matched = true;
            }
        });
        
        row.style.display = matched ? '' : 'none';
    });
}

/**
 * Filter enrichment point table rows
 * @param {string} query - The search query
 */
function filterEpContent(query) {
    const epRows = document.querySelectorAll('.ep-table tbody tr');
    
    epRows.forEach(row => {
        const cells = row.querySelectorAll('td');
        let matched = false;
        
        cells.forEach(cell => {
            if (cell.textContent.toLowerCase().includes(query)) {
                matched = true;
            }
        });
        
        row.style.display = matched ? '' : 'none';
    });
}

/**
 * Show a notification when search has no results
 */
function showNoResultsNotification() {
    // Check if we already have a notification system
    if (typeof showNotification === 'function') {
        showNotification('No matches found for your search.', 'info');
        return;
    }
    
    // Create a simple notification if no notification system exists
    const notification = document.createElement('div');
    notification.className = 'notification info';
    notification.textContent = 'No matches found for your search.';
    
    document.body.appendChild(notification);
    
    // Remove notification after 3 seconds
    setTimeout(() => {
        notification.remove();
    }, 3000);
} 