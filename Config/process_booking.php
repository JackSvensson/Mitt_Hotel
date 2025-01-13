<?php
// process_booking.php
require_once 'config/db_config.php';
require_once __DIR__ . '/pricing.php';

header('Content-Type: application/json');

// Your existing functions remain the same
function checkRoomAvailability($pdo, $room_type, $check_in, $check_out)
{
    // ... existing code ...
}

function validateTransferCode($transfer_code)
{
    // ... existing code ...
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $json_input = file_get_contents('php://input');
        $data = json_decode($json_input, true);

        // Updated required fields to include activities
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
        $selected_activities = $data['activities'] ?? []; // New: Get selected activities

        // ... existing date validation code ...

        // Calculate number of nights and total price
        $nights = (strtotime($check_out) - strtotime($check_in)) / (60 * 60 * 24);
        $price_details = calculateTotalPrice($room_type, $nights, $selected_activities);

        // Check room availability and transfer code (existing code)
        if (!checkRoomAvailability($pdo, $room_type, $check_in, $check_out)) {
            echo json_encode([
                'success' => false,
                'message' => 'Room is not available for the selected dates',
                'error_code' => 'ROOM_UNAVAILABLE'
            ]);
            exit;
        }

        if (!validateTransferCode($transfer_code)) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid transfer code',
                'error_code' => 'INVALID_TRANSFER'
            ]);
            exit;
        }

        // Updated SQL to include activities and price
        $stmt = $pdo->prepare("INSERT INTO bookings (
            name, email, room_type, check_in, check_out, 
            transfer_code, activities, total_price, room_price, 
            activities_price, discount_applied
        ) VALUES (
            :name, :email, :room_type, :check_in, :check_out, 
            :transfer_code, :activities, :total_price, :room_price,
            :activities_price, :discount_applied
        )");

        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':room_type' => $room_type,
            ':check_in' => $check_in,
            ':check_out' => $check_out,
            ':transfer_code' => $transfer_code,
            ':activities' => json_encode($selected_activities),
            ':total_price' => $price_details['total_price'],
            ':room_price' => $price_details['base_room_price'],
            ':activities_price' => $price_details['activities_total'],
            ':discount_applied' => $price_details['room_discount']
        ]);

        // Updated success response with pricing details
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
                'total_nights' => $nights,
                'activities' => $selected_activities,
                'price_breakdown' => [
                    'room_price' => $price_details['base_room_price'],
                    'room_discount' => $price_details['room_discount'],
                    'activities_total' => $price_details['activities_total'],
                    'total_price' => $price_details['total_price']
                ]
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
