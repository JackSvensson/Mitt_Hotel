document.addEventListener('DOMContentLoaded', function() {
    // Select essential elements
    const calendar = document.querySelector('.calendar');
    const selectedDatesDiv = document.getElementById('selected-dates');
    const dateSelectionDiv = document.getElementById('date-selection');
    const guestDetailsDiv = document.getElementById('guest-details');
    const featureCheckboxes = document.querySelectorAll('input[name="features[]"]');
    const priceBreakdown = document.getElementById('price-breakdown');
    const roomTypeSelect = document.getElementById('room_type');
    const bookNowBtn = document.querySelector('.book-now-btn');
    
    // Variables to track dates and prices
    let checkInDate = null;
    let checkOutDate = null;

    // Add change event listener for room type
    roomTypeSelect.addEventListener('change', function() {
        const selectedOption = roomTypeSelect.options[roomTypeSelect.selectedIndex];
        
        // Reset and hide guest details when room type changes
        guestDetailsDiv.classList.add('hidden');
        document.getElementById('first_name').value = '';
        document.getElementById('last_name').value = '';
        
        document.getElementById('base-price').textContent = selectedOption.dataset.price;
        
        // Reset dates and calendar highlights
        checkInDate = null;
        checkOutDate = null;
        clearHighlights();
        selectedDatesDiv.innerHTML = 'Please select your check-in date.';
        dateSelectionDiv.classList.add('hidden');
        priceBreakdown.classList.add('hidden');
    });

    // Add click event to the calendar
    calendar.addEventListener('click', function(e) {
        // Target the date cell or its parent that contains the date
        const cell = e.target.closest('td:not(.blank)');
        if (!cell) return;

        const selectedDay = parseInt(cell.textContent.trim());
        
        // Create a local date at start of day
        const selectedDate = new Date(2025, 0, selectedDay);
        selectedDate.setHours(0, 0, 0, 0);

        // First click: Set check-in date
        if (!checkInDate) {
            checkInDate = selectedDate;
            clearHighlights();
            cell.classList.add('selected');
            selectedDatesDiv.innerHTML = `Check-in: ${formatDate(selectedDate)} <a href="#" id="clear-dates">Clear Dates</a>`;
            dateSelectionDiv.classList.remove('hidden');
        } 
        // Second click: Set check-out date if after check-in
        else if (!checkOutDate && selectedDate > checkInDate) {
            checkOutDate = selectedDate;
            highlightDateRange();
            selectedDatesDiv.innerHTML = `
                Check-in: ${formatDate(checkInDate)} | 
                Check-out: ${formatDate(checkOutDate)}
                <a href="#" id="clear-dates">Clear Dates</a>
            `;
            
            // Show guest details after date selection
            guestDetailsDiv.classList.remove('hidden');
            
            updatePriceBreakdown();
        } 
        // Reset selection if clicking a new date
        else {
            checkInDate = selectedDate;
            checkOutDate = null;
            clearHighlights();
            cell.classList.add('selected');
            selectedDatesDiv.innerHTML = `Check-in: ${formatDate(selectedDate)} <a href="#" id="clear-dates">Clear Dates</a>`;
            
            // Hide guest details
            guestDetailsDiv.classList.add('hidden');
        }
    });

    // Add event listeners to feature checkboxes
    featureCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updatePriceBreakdown);
    });

    // Book Now button click handler
    bookNowBtn.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Validate form inputs
        const firstName = document.getElementById('first_name').value.trim();
        const lastName = document.getElementById('last_name').value.trim();
        const email = document.getElementById('email').value.trim();
        const transferCode = document.getElementById('transfer_code').value.trim();
        const roomType = roomTypeSelect.value;
        const guests = document.getElementById('guests').value;

        // Validate ALL fields
        if (!firstName || !lastName || !email || !transferCode || !roomType || !checkInDate || !checkOutDate) {
            alert('Please fill in all required fields and select your dates.');
            return;
        }

        // Get selected features
        const selectedFeatures = Array.from(featureCheckboxes)
            .filter(checkbox => checkbox.checked)
            .map(checkbox => {
                // Map to feature names compatible with backend
                const featureMap = {
                    'enigma-pool': 'The Enigma Pool',
                    'ping-pong': 'The Detective\'s Ping Pong Table',
                    'glass-onion-bar': 'The Glass Onion Bar'
                };
                return featureMap[checkbox.value] || checkbox.value;
            });

        // Prepare form data
        const formData = new FormData();
        formData.append('first_name', firstName);
        formData.append('last_name', lastName);
        formData.append('email', email);
        formData.append('room_type', roomType);
        formData.append('check_in', formatDateForBackend(checkInDate));
        formData.append('check_out', formatDateForBackend(checkOutDate));
        formData.append('transfer_code', transferCode);
        formData.append('guests', guests);
        
        // Add selected features
        selectedFeatures.forEach((feature, index) => {
            formData.append(`features[${index}]`, feature);
        });

        // Send booking request
        fetch('process_booking.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Display raw JSON response in a new window
            displayJsonResponse(data);
            
            // Optionally, you can still reset the form on the original page
            resetBookingForm();
        })
        .catch(error => {
            console.error('Booking Error:', error);
            alert('An error occurred while processing your booking. Please try again.');
        });
    });

    // Helper function to format date for backend (YYYY-MM-DD)
    function formatDateForBackend(date) {
        return date.toISOString().split('T')[0];
    }

    // Format date for display
    function formatDate(date) {
        const options = {
            month: 'long',
            day: 'numeric',
            year: 'numeric',
            timeZone: 'UTC'
        };
        return date.toLocaleDateString('en-US', options);
    }

    // Reset booking form
    function resetBookingForm() {
        checkInDate = null;
        checkOutDate = null;
        clearHighlights();
        selectedDatesDiv.innerHTML = 'Please select your check-in date.';
        dateSelectionDiv.classList.add('hidden');
        priceBreakdown.classList.add('hidden');
        guestDetailsDiv.classList.add('hidden');
        
        // Reset form inputs
        document.getElementById('first_name').value = '';
        document.getElementById('last_name').value = '';
        document.getElementById('email').value = '';
        document.getElementById('transfer_code').value = '';
        roomTypeSelect.selectedIndex = 0;
        document.getElementById('guests').selectedIndex = 0;
        
        // Uncheck feature checkboxes
        featureCheckboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
    }

    // Clear calendar highlights
    function clearHighlights() {
        const cells = calendar.querySelectorAll('td:not(.blank)');
        cells.forEach(c => {
            c.classList.remove('selected', 'range');
        });
    }

    // Highlight selected date range
    function highlightDateRange() {
        const cells = calendar.querySelectorAll('td:not(.blank)');
        
        cells.forEach(cell => {
            const day = parseInt(cell.textContent.trim());
            const currentDate = new Date(2025, 0, day);
            currentDate.setHours(0, 0, 0, 0);

            if (currentDate >= checkInDate && currentDate <= checkOutDate) {
                if (currentDate.getTime() === checkInDate.getTime() || 
                    currentDate.getTime() === checkOutDate.getTime()) {
                    cell.classList.add('selected');
                } else {
                    cell.classList.add('range');
                }
            }
        });
    }

    // Calculate number of nights between two dates
    function calculateNights(startDate, endDate) {
        return Math.round((endDate - startDate) / (1000 * 60 * 60 * 24));
    }

    // Calculate total price for features
    function calculateFeaturesTotal() {
        return Array.from(featureCheckboxes)
            .filter(checkbox => checkbox.checked)
            .reduce((total, checkbox) => total + parseFloat(checkbox.dataset.price), 0);
    }

    // Function to update price breakdown
    function updatePriceBreakdown() {
        if (checkInDate && checkOutDate) {
            const selectedOption = roomTypeSelect.options[roomTypeSelect.selectedIndex];
            const basePrice = parseFloat(selectedOption.dataset.price);
            const nightsStay = calculateNights(checkInDate, checkOutDate);
            let roomTotal = basePrice * nightsStay;
            const featuresTotal = calculateFeaturesTotal();
            
            // Apply discount for 3 or more nights
            let discountAmount = 0;
            let discountDetails = '';
            if (nightsStay >= 3) {
                // 25% discount for 3 or more nights
                discountAmount = roomTotal * 0.25;
                discountDetails = `25% Long Stay Discount: -$${discountAmount.toFixed(2)}`;
                roomTotal -= discountAmount;
            }
            
            const finalTotal = roomTotal + featuresTotal;
            
            document.getElementById('base-price').textContent = basePrice.toFixed(2);
            document.getElementById('nights-count').textContent = nightsStay;
            document.getElementById('room-total').textContent = roomTotal.toFixed(2);
            document.getElementById('features-total').textContent = featuresTotal.toFixed(2);
            document.getElementById('final-total').textContent = finalTotal.toFixed(2);
            
            // Remove any existing discount details
            const existingDiscountEl = document.getElementById('discount-details');
            if (existingDiscountEl) {
                existingDiscountEl.remove();
            }
            
            // Add discount details if applicable
            if (discountDetails) {
                const discountEl = document.createElement('p');
                discountEl.id = 'discount-details';
                discountEl.textContent = discountDetails;
                discountEl.style.color = 'green';
                
                const priceSummary = document.querySelector('.price-summary');
                priceSummary.appendChild(discountEl);
            }
            
            priceBreakdown.classList.remove('hidden');
        }
    }

    // Clear dates functionality
    document.addEventListener('click', function(e) {
        if (e.target && e.target.id === 'clear-dates') {
            e.preventDefault();
            checkInDate = null;
            checkOutDate = null;
            clearHighlights();
            selectedDatesDiv.innerHTML = 'Please select your check-in date.';
            dateSelectionDiv.classList.add('hidden');
            priceBreakdown.classList.add('hidden');
            guestDetailsDiv.classList.add('hidden');
        }
    });

    // Function to display JSON response in a new window
    function displayJsonResponse(data) {
        // Convert the data to a JSON string
        const jsonString = JSON.stringify(data, null, 2);
        
        // Create a new window or tab
        const newWindow = window.open('', '_blank');
        
        // Write the JSON to the new window
        newWindow.document.write('<html><head><title>Booking Confirmation</title>');
        newWindow.document.write('<style>');
        newWindow.document.write('body { font-family: Arial, sans-serif; padding: 20px; }');
        newWindow.document.write('pre { background-color: #f4f4f4; padding: 15px; border-radius: 5px; overflow-x: auto; }');
        newWindow.document.write('</style>');
        newWindow.document.write('</head><body>');
        newWindow.document.write('<h2>Booking Confirmation</h2>');
        newWindow.document.write('<pre>' + jsonString + '</pre>');
        newWindow.document.write('</body></html>');
        newWindow.document.close();
    }
});