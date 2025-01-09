<?php
// process_booking.php
require_once 'config/db_config.php';

// Set header to return JSON
header('Content-Type: application/json');

// Function to check room availability
function checkRoomAvailability($pdo, $room_type, $check_in, $check_out)
{
    $query = "SELECT COUNT(*) FROM bookings 
              WHERE room_type = :room_type 
              AND (
                  (check_in <= :check_in AND check_out > :check_in)
                  OR 
                  (check_in < :check_out AND check_out >= :check_out)
                  OR 
                  (check_in >= :check_in AND check_out <= :check_out)
              )";

    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':room_type' => $room_type,
        ':check_in' => $check_in,
        ':check_out' => $check_out
    ]);

    return $stmt->fetchColumn() === 0;
}

// Function to validate transfer code
function validateTransferCode($transfer_code)
{
    // Example validation rules for transfer code:
    // - Must be 10 characters long
    // - Must start with 'TR'
    // - Must contain only alphanumeric characters
    // - Must end with a checksum digit
    if (strlen($transfer_code) !== 10) {
        return false;
    }

    if (substr($transfer_code, 0, 2) !== 'TR') {
        return false;
    }

    if (!ctype_alnum($transfer_code)) {
        return false;
    }

    // Calculate checksum (example implementation)
    $code_numbers = array_map('intval', str_split(substr($transfer_code, 2, 7)));
    $checksum = array_sum($code_numbers) % 10;

    return $checksum === intval(substr($transfer_code, -1));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Decode JSON input
        $json_input = file_get_contents('php://input');
        $data = json_decode($json_input, true);

        // Validate required fields
        $required_fields = ['name', 'email', 'room_type', 'check_in', 'check_out', 'transfer_code'];
        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new Exception("Missing required field: $field");
            }
        }

        // Sanitize and validate input
        $name = filter_var($data['name'], FILTER_SANITIZE_STRING);
        $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
        $room_type = filter_var($data['room_type'], FILTER_SANITIZE_STRING);
        $check_in = $data['check_in'];
        $check_out = $data['check_out'];
        $transfer_code = filter_var($data['transfer_code'], FILTER_SANITIZE_STRING);

        // Validate dates
        if (!strtotime($check_in) || !strtotime($check_out)) {
            throw new Exception("Invalid dates provided");
        }

        // Check if dates are in January 2025
        if (
            date('Y-m', strtotime($check_in)) !== '2025-01' ||
            date('Y-m', strtotime($check_out)) !== '2025-01'
        ) {
            throw new Exception("Bookings are only available for January 2025");
        }

        // Check room availability
        if (!checkRoomAvailability($pdo, $room_type, $check_in, $check_out)) {
            echo json_encode([
                'success' => false,
                'message' => 'Room is not available for the selected dates',
                'error_code' => 'ROOM_UNAVAILABLE'
            ]);
            exit;
        }

        // Validate transfer code
        if (!validateTransferCode($transfer_code)) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid transfer code',
                'error_code' => 'INVALID_TRANSFER'
            ]);
            exit;
        }

        // Insert booking into database
        $stmt = $pdo->prepare("INSERT INTO bookings (name, email, room_type, check_in, check_out, transfer_code) 
                              VALUES (:name, :email, :room_type, :check_in, :check_out, :transfer_code)");

        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':room_type' => $room_type,
            ':check_in' => $check_in,
            ':check_out' => $check_out,
            ':transfer_code' => $transfer_code
        ]);

        // Calculate total nights
        $nights = (strtotime($check_out) - strtotime($check_in)) / (60 * 60 * 24);

        // Return success response
        echo json_encode([
            'success' => true,
            'message' => 'Booking confirmed successfully',
            'booking_details' => [
                'booking_id' => $pdo->lastInsertId(),
                'name' => $name,
                'email' => $email,
                'room_type' => $room_type,
                'check_in' => $check_in,
                'check_out' => $check_out,
                'total_nights' => $nights
            ]
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage(),
            'error_code' => 'SYSTEM_ERROR'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method',
        'error_code' => 'INVALID_METHOD'
    ]);
}
