<?php
require_once 'vendor/autoload.php';

use benhall14\phpCalendar\Calendar;

$page_title = 'Book a Room - Glass Onion Hotel';
$additional_css = 'css/booking.css';
$additional_js = './booking.js';

require_once 'includes/header.php';

// Create calendar instance
$calendar = new Calendar();
$calendar->useSundayStartingDate();
$calendar->useInitialDayNames();
$calendar->setLocale('en_US');

// Set the date for the calendar
$calendarDate = '2025-01-01';
?>

<main>
    <div class="booking-form">
        <h2>Book Your Stay - January 2025</h2>

        <form id="booking-form">
            <div class="form-group">
                <label for="room_type">Select Room Type:</label>
                <select id="room_type" name="room_type" required>
                    <option value="">Select a room type</option>
                    <option value="The Beachcomber's Quarter" data-price="10.00">The Beachcomber's Quarter - $10.00/night</option>
                    <option value="The Detective's Den" data-price="12.00">The Detective's Den - $12.00/night</option>
                    <option value="The Glass Onion Suite" data-price="15.00">The Glass Onion Suite - $15.00/night</option>
                </select>
                <p class="room-type-hint">Please select a room type to view availability.</p>
            </div>

            <div id="calendar-container">
                <?php
                $calendar->stylesheet();
                echo $calendar->draw($calendarDate);
                ?>
            </div>

            <div id="date-selection" class="hidden">
                <div id="selected-dates">Please select your check-in date.</div>

                <div id="guest-details" class="hidden">
                    <div class="form-group">
                        <label for="first_name">First Name:</label>
                        <input type="text" id="first_name" name="first_name" required>
                    </div>

                    <div class="form-group">
                        <label for="last_name">Last Name:</label>
                        <input type="text" id="last_name" name="last_name" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="guests">Number of Guests:</label>
                    <select id="guests" name="guests">
                        <option value="1">1 Guest</option>
                        <option value="2">2 Guests</option>
                        <option value="3">3 Guests</option>
                        <option value="4">4 Guests</option>
                    </select>
                </div>

                <div class="features-section">
                    <h4>Add Features (price per stay):</h4>
                    <div class="features-grid">
                        <div class="feature-card">
                            <h5>The Enigma Pool</h5>
                            <p class="feature-description">Take a mysterious dip in our signature pool</p>
                            <p class="feature-price">$3 for entire stay</p>
                            <label class="feature-checkbox">
                                <input type="checkbox" name="features[]" value="enigma-pool" data-price="3.00">
                                Add to booking
                            </label>
                        </div>

                        <div class="feature-card">
                            <h5>Detective's Ping Pong Table</h5>
                            <p class="feature-description">Challenge your detective skills</p>
                            <p class="feature-price">$1 for entire stay</p>
                            <label class="feature-checkbox">
                                <input type="checkbox" name="features[]" value="ping-pong" data-price="1.00">
                                Add to booking
                            </label>
                        </div>

                        <div class="feature-card">
                            <h5>Glass Onion Bar</h5>
                            <p class="feature-description">Includes welcome drink</p>
                            <p class="feature-price">$2 for entire stay</p>
                            <label class="feature-checkbox">
                                <input type="checkbox" name="features[]" value="glass-onion-bar" data-price="2.00">
                                Add to booking
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="transfer_code">Transfer Code:</label>
                    <input type="text" id="transfer_code" name="transfer_code" required
                        placeholder="Enter your transfer code">
                </div>

                <div id="price-breakdown" class="hidden">
                    <div class="price-summary">
                        <h4>Room & Features Selection</h4>
                        <div id="room-price-details">
                            <p>Base price per night: $<span id="base-price">12.00</span></p>
                            <p>Number of nights: <span id="nights-count">0</span></p>
                        </div>
                        <div id="total-price-summary" class="total-price-summary">
                            <p>Room total: $<span id="room-total">0.00</span></p>
                            <p>Features total: $<span id="features-total">0.00</span></p>
                            <p>Final total: $<span id="final-total">0.00</span></p>
                        </div>
                    </div>
                    <button type="button" class="book-now-btn">Book Now</button>
                </div>
            </div>
        </form>
    </div>
</main>

<footer>
    <p>&copy; 2025 Glass Onion Hotel. All rights reserved.</p>
</footer>

<script src="<?php echo $additional_js; ?>"></script>
</body>

</html>