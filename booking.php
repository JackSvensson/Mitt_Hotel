<?php

require_once __DIR__ . '/Config/db_config.php';

require_once __DIR__ . '/pricing.php';


function isDateBooked($pdo, $date, $room_type)
{
    $query = "SELECT COUNT(*) FROM bookings 
              WHERE room_type = ? 
              AND ? BETWEEN check_in AND date(check_out, '-1 day')";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$room_type, $date]);
    return $stmt->fetchColumn() > 0;
}

// Function to generate calendar
function generateCalendar($pdo, $selected_room_type = '', $check_in = '', $check_out = '')
{
    $year = 2025;
    $month = 1; // January

    $first_day = mktime(0, 0, 0, $month, 1, $year);
    $total_days = date('t', $first_day);
    $start_day = date('w', $first_day);

    $calendar = '<table class="calendar">';
    $calendar .= '<tr class="calendar-header">';
    $calendar .= '<th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th>';
    $calendar .= '</tr><tr>';

    // Empty cells before start of month
    for ($i = 0; $i < $start_day; $i++) {
        $calendar .= '<td></td>';
    }

    $current_day = 1;
    $day_of_week = $start_day;

    while ($current_day <= $total_days) {
        if ($day_of_week == 7) {
            $calendar .= '</tr><tr>';
            $day_of_week = 0;
        }

        $date = sprintf('%04d-%02d-%02d', $year, $month, $current_day);
        $css_class = [];

        // Check if date is booked
        if ($selected_room_type && isDateBooked($pdo, $date, $selected_room_type)) {
            $css_class[] = 'booked';
        }

        // Highlight selected range
        if ($check_in && $check_out && $date >= $check_in && $date <= $check_out) {
            $css_class[] = 'selected-range';
        } elseif ($date === $check_in) {
            $css_class[] = 'check-in';
        } elseif ($date === $check_out) {
            $css_class[] = 'check-out';
        }

        $calendar .= '<td class="' . implode(' ', $css_class) . '">';
        if (empty($css_class) || ($check_in === '' && $check_out === '')) {
            $calendar .= '<a href="?room_type=' . $selected_room_type . '&date=' . $date .
                '&check_in=' . $check_in . '&check_out=' . $check_out . '" 
                        class="date-select">' . $current_day . '</a>';
        } else {
            $calendar .= '<span class="date-display">' . $current_day . '</span>';
        }
        $calendar .= '</td>';

        $current_day++;
        $day_of_week++;
    }

    // Fill remaining cells
    while ($day_of_week < 7) {
        $calendar .= '<td></td>';
        $day_of_week++;
    }

    $calendar .= '</tr></table>';
    return $calendar;
}

// Handle date selection
$selected_room_type = isset($_GET['room_type']) ? $_GET['room_type'] : '';
$selected_date = isset($_GET['date']) ? $_GET['date'] : '';
$check_in = isset($_GET['check_in']) ? $_GET['check_in'] : '';
$check_out = isset($_GET['check_out']) ? $_GET['check_out'] : '';

// Process date selection
if ($selected_date && $selected_room_type) {
    if (empty($check_in)) {
        $check_in = $selected_date;
    } elseif (empty($check_out) && $selected_date > $check_in) {
        $check_out = $selected_date;
    } else {
        $check_in = $selected_date;
        $check_out = '';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book a Room - Glass Onion Hotel</title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/booking.css">
    <script src="booking.js" defer></script>
</head>

<body>
    <header>
        <nav>
            <div class="Header-menu">Glass Onion Hotel
                <span class="hotel-stars">★★★</span>
            </div>
            <ul>
                <li><a href="index.html">Home</a></li>
                <li><a href="rooms.html">Rooms</a></li>
                <li><a href="booking.php">Book Now</a></li>
                <li><a href="contact.html">Contact</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="booking-form">
            <h2>Book Your Stay - January 2025</h2>

            <!-- Room type selection form -->
            <form method="GET" class="room-selection">
                <div class="form-group">
                    <label for="room_type">Select Room Type:</label>
                    <select id="room_type" name="room_type" onchange="this.form.submit()">
                        <option value="">Select a room type</option>
                        <option value="budget" <?php echo $selected_room_type == 'budget' ? 'selected' : ''; ?>>Budget Single Room</option>
                        <option value="standard" <?php echo $selected_room_type == 'standard' ? 'selected' : ''; ?>>Standard Single Room</option>
                        <option value="luxury" <?php echo $selected_room_type == 'luxury' ? 'selected' : ''; ?>>Luxury Single Room</option>
                    </select>
                </div>
            </form>

            <?php if ($selected_room_type): ?>
                <div class="date-info">
                    <?php if ($check_in): ?>
                        Check-in: <?php echo date('F j, Y', strtotime($check_in)); ?><br>
                    <?php endif; ?>
                    <?php if ($check_out): ?>
                        Check-out: <?php echo date('F j, Y', strtotime($check_out)); ?><br>
                    <?php endif; ?>
                    <?php if ($check_in || $check_out): ?>
                        <a href="?room_type=<?php echo $selected_room_type; ?>" class="clear-dates">Clear Dates</a>
                    <?php endif; ?>
                </div>

                <div class="calendar-container">
                    <?php echo generateCalendar($pdo, $selected_room_type, $check_in, $check_out); ?>
                </div>

                <?php if ($check_in && $check_out): ?>
                    <form method="POST" action="Config/process_booking.php" class="booking-details-form">
                        <input type="hidden" name="room_type" value="<?php echo htmlspecialchars($selected_room_type); ?>">
                        <input type="hidden" name="check_in" value="<?php echo htmlspecialchars($check_in); ?>">
                        <input type="hidden" name="check_out" value="<?php echo htmlspecialchars($check_out); ?>">


                        <div class="form-group">
                            <label for="first_name">Name:</label>
                            <input type="text" id="first_name" name="first_name" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" required>
                        </div>

                        <div class="form-group">
                            <label for="guests">Number of Guests:</label>
                            <select id="guests" name="guests" required>
                                <option value="1">1</option>
                            </select>
                        </div>

                        <div class="features-selection">
                            <h4>Add Features (price per stay):</h4>
                            <div class="features-grid">
                                <div class="feature-card">
                                    <h5>The Enigma Pool</h5>
                                    <p class="feature-description">Take a mysterious dip in our signature pool</p>
                                    <p class="feature-price">$3 for entire stay</p>
                                    <label class="feature-checkbox">
                                        <input type="checkbox" name="features[]" value="pool" data-price="3">
                                        Add to booking
                                    </label>
                                </div>

                                <div class="feature-card">
                                    <h5>Detective's Ping Pong Table</h5>
                                    <p class="feature-description">Challenge your deductive skills</p>
                                    <p class="feature-price">$1 for entire stay</p>
                                    <label class="feature-checkbox">
                                        <input type="checkbox" name="features[]" value="pingpong" data-price="1">
                                        Add to booking
                                    </label>
                                </div>

                                <div class="feature-card">
                                    <h5>Glass Onion Bar</h5>
                                    <p class="feature-description">Includes welcome drink</p>
                                    <p class="feature-price">$2 for entire stay</p>
                                    <label class="feature-checkbox">
                                        <input type="checkbox" name="features[]" value="bar" data-price="2">
                                        Add to booking
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="transfer_code">Transfer Code:</label>
                            <input type="text" id="transfer_code" name="transfer_code" required>
                        </div>

                        <button type="submit" class="submit-btn">Book Now</button>
                    </form>
                <?php elseif ($check_in): ?>
                    <p class="date-prompt">Please select your check-out date.</p>
                <?php else: ?>
                    <p class="date-prompt">Please select your check-in date.</p>
                <?php endif; ?>
            <?php else: ?>
                <p class="room-prompt">Please select a room type to view availability.</p>
            <?php endif; ?>
        </div>

        <?php if ($selected_room_type && $check_in && $check_out): ?>
            <div class="pricing-section">
                <h3>Room & Features Selection</h3>

                <?php
                // Calculate number of nights
                $check_in_date = new DateTime($check_in);
                $check_out_date = new DateTime($check_out);
                $nights = $check_in_date->diff($check_out_date)->days;

                // Get base room price
                $room_prices = [
                    'budget' => 10,
                    'standard' => 12,
                    'luxury' => 15
                ];
                $base_price = $room_prices[$selected_room_type];
                $total_room_price = $base_price * $nights;

                // Calculate discount if applicable
                $discount = ($nights >= 3) ? 0.25 : 0;
                $discounted_price = $total_room_price * (1 - $discount);
                ?>

                <div class="price-breakdown">
                    <h4>Room Price Breakdown</h4>
                    <p>Base price per night: $<?php echo $base_price; ?></p>
                    <p>Number of nights: <?php echo $nights; ?></p>
                    <?php if ($discount > 0): ?>
                        <p class="discount-note">25% discount applied for 3+ nights stay!</p>
                        <p>Original total: $<?php echo number_format($total_room_price, 2); ?></p>
                        <p>Discounted total: $<?php echo number_format($discounted_price, 2); ?></p>
                    <?php else: ?>
                        <p>Total room cost: $<?php echo number_format($total_room_price, 2); ?></p>
                    <?php endif; ?>
                </div>

                <div class="total-price">
                    <h4>Total Price Summary</h4>
                    <div id="price-summary">
                        <p>Room total: $<span id="room-total"><?php echo number_format($discounted_price, 2); ?></span></p>
                        <p>Features total: $<span id="features-total">0.00</span></p>
                        <p class="final-total">Final total: $<span id="final-total"><?php echo number_format($discounted_price, 2); ?></span></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2025 Glass Onion Hotel. All rights reserved.</p>
    </footer>
</body>

</html>