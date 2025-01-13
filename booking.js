document.addEventListener('DOMContentLoaded', function() {
    // First verify elements exist
    const featureCheckboxes = document.querySelectorAll('input[name="features[]"]');
    const roomTotalElement = document.getElementById('room-total');
    const featuresTotalElement = document.getElementById('features-total');
    const finalTotalElement = document.getElementById('final-total');
    
    // Guard clause to prevent errors if elements are missing
    if (!featureCheckboxes.length || !roomTotalElement || 
        !featuresTotalElement || !finalTotalElement) {
        console.error('Required elements not found');
        return;
    }
    
    // Parse room total with error handling
    const roomTotal = parseFloat(roomTotalElement.textContent) || 0;
    
    function updateTotals() {
        let featuresTotal = 0;
        featureCheckboxes.forEach(checkbox => {
            if (checkbox.checked && checkbox.dataset.price) {
                const price = parseFloat(checkbox.dataset.price) || 0;
                featuresTotal += price;
            }
        });
        
        featuresTotalElement.textContent = featuresTotal.toFixed(2);
        finalTotalElement.textContent = (roomTotal + featuresTotal).toFixed(2);
    }
    
    featureCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateTotals);
    });
    
    // Initialize totals on page load
    updateTotals();
});