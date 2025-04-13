document.addEventListener('DOMContentLoaded', function() {
    // Get the map container
    const mapContainer = document.getElementById('mapContainer');
    
    // Check if map container exists
    if (!mapContainer) return;
    
    // Get latitude, longitude, and location name from data attributes
    const lat = parseFloat(mapContainer.dataset.lat) || 4.8856;
    const lng = parseFloat(mapContainer.dataset.lng) || 114.9370;
    const locationName = mapContainer.dataset.name || 'Location';
    
    // Initialize the map
    const map = L.map('mapContainer').setView([lat, lng], 15);
    
    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);
    
    // Add a marker at the specified location
    const marker = L.marker([lat, lng]).addTo(map);
    
    // Add a popup with the location name
    marker.bindPopup(`<b>${locationName}</b>`).openPopup();
    
    // Add a circle around the marker to highlight the area
    L.circle([lat, lng], {
        color: '#1a365d',
        fillColor: '#1a365d',
        fillOpacity: 0.1,
        radius: 100
    }).addTo(map);
    
    // Handle responsive resizing
    window.addEventListener('resize', function() {
        map.invalidateSize();
    });
}); 