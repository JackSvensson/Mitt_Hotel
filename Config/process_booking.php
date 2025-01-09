<?php
// process_booking.php

require_once 'config/db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Collect and sanitize input
        $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $check_in = $_POST['check_in'];
        $check_out = $_POST['check_out'];
        $room_type = filter_var($_POST['room_type'], FILTER_SANITIZE_STRING);
        $guests = (int)$_POST['guests'];

        // Prepare SQL statement
        $sql = "INSERT INTO bookings (name, email, check_in, check_out, room_type, guests) 
                VALUES (:name, :email, :check_in, :check_out, :room_type, :guests)";

        $stmt = $pdo->prepare($sql);

        // Bind parameters
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':check_in', $check_in);
        $stmt->bindParam(':check_out', $check_out);
        $stmt->bindParam(':room_type', $room_type);
        $stmt->bindParam(':guests', $guests);

        // Execute the statement
        if ($stmt->execute()) {
            // Send confirmation email
            $to = $email;
            $subject = "Booking Confirmation - Glass Onion Hotel";
            $message = "Dear $name,\n\n"
                . "Thank you for booking with Glass Onion Hotel.\n"
                . "Booking Details:\n"
                . "Room Type: " . ucfirst($room_type) . "\n"
                . "Check-in: $check_in\n"
                . "Check-out: $check_out\n"
                . "Number of Guests: $guests\n\n"
                . "Best regards,\n"
                . "Glass Onion Hotel";

            $headers = "From: bookings@glassonionhotel.com";

            mail($to, $subject, $message, $headers);

            // Redirect with success message
            header("Location: booking.php?status=success");
            exit();
        }
    } catch (PDOException $e) {
        // Log error and redirect with error message
        error_log("Booking Error: " . $e->getMessage());
        header("Location: booking.php?status=error");
        exit();
    }
} else {
    header("Location: booking.php");
    exit();
}
