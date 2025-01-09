<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book a Room - Glass Onion Hotel</title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/booking.css">
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
            <h2>Book Your Stay</h2>

            <?php if (isset($booking_success) && $booking_success): ?>
                <div class="success-message">
                    Booking successfully submitted! We'll contact you shortly to confirm your reservation.
                </div>
            <?php endif; ?>

            <?php if (isset($error) && $error): ?>
                <div class="error-message">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="form-group">
                    <label for="name">Full Name:</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="check_in">Check-in Date:</label>
                    <input type="date" id="check_in" name="check_in" required>
                </div>

                <div class="form-group">
                    <label for="check_out">Check-out Date:</label>
                    <input type="date" id="check_out" name="check_out" required>
                </div>

                <div class="form-group">
                    <label for="room_type">Room Type:</label>
                    <select id="room_type" name="room_type" required>
                        <option value="">Select a room type</option>
                        <option value="budget">Budget Single Room</option>
                        <option value="standard">Standard Single Room</option>
                        <option value="luxury">Luxury Single Room</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="guests">Number of Guests:</label>
                    <select id="guests" name="guests" required>
                        <option value="1">1</option>
                    </select>
                </div>

                <button type="submit" class="submit-btn">Book Now</button>
            </form>
        </div>
    </main>

    <footer>
        <p>&copy; 2025 Glass Onion Hotel. All rights reserved.</p>
    </footer>
</body>

</html>