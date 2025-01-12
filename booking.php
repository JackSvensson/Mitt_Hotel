<?php
// booking.php
require_once __DIR__ . '/Config/db_config.php';

// Function to check if a date is booked
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
    <style>
        .calendar {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .calendar th,
        .calendar td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }

        .calendar th {
            background-color: #f5f5f5;
            font-weight: bold;
        }

        .date-select {
            display: block;
            width: 100%;
            height: 100%;
            text-decoration: none;
            color: #333;
            padding: 5px;
        }

        .date-display {
            display: block;
            padding: 5px;
        }

        .booked {
            background-color: #ffebee;
        }

        .booked .date-display {
            color: #d32f2f;
        }

        .selected-range {
            background-color: #e3f2fd;
        }

        .check-in {
            background-color: #bbdefb;
        }

        .check-out {
            background-color: #bbdefb;
        }

        .date-info {
            margin: 20px 0;
            padding: 15px;
            background-color: #f5f5f5;
            border-radius: 5px;
        }

        .clear-dates {
            display: inline-block;
            margin-top: 10px;
            padding: 5px 10px;
            background-color: #f44336;
            color: white;
            text-decoration: none;
            border-radius: 3px;
        }
    </style>
</head>

<body>
    <header>
        <nav>
            <div class="logo">Glass Onion Hotel</div>
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
            <form method="get" class="room-selection">
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
                    <form method="POST" action="process_booking.php">
                        <input type="hidden" name="room_type" value="<?php echo htmlspecialchars($selected_room_type); ?>">
                        <input type="hidden" name="check_in" value="<?php echo htmlspecialchars($check_in); ?>">
                        <input type="hidden" name="check_out" value="<?php echo htmlspecialchars($check_out); ?>">

                        <div class="form-group">
                            <label for="name">Full Name:</label>
                            <input type="text" id="name" name="name" required>
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

                        <button type="submit" class="submit-btn">Book Now</button>
                    </form>
                <?php elseif ($check_in): ?>
                    <p>Please select your check-out date.</p>
                <?php else: ?>
                    <p>Please select your check-in date.</p>
                <?php endif; ?>
            <?php else: ?>
                <p>Please select a room type to view availability.</p>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <p>&copy; 2025 Glass Onion Hotel. All rights reserved.</p>
    </footer>
</body>

</html>