<?php
// process_booking.php
require_once 'config/db_config.php';
require_once __DIR__ . '/pricing.php';

header('Content-Type: application/json');

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

function validateTransferCode($transfer_code)
{
    if (strlen($transfer_code) !== 10) {
        return false;
    }

    if (substr($transfer_code, 0, 2) !== 'TR') {
        return false;
    }

    if (!ctype_alnum($transfer_code)) {
        return false;
    }

    $code_numbers = array_map('intval', str_split(substr($transfer_code, 2, 7)));
    $checksum = array_sum($code_numbers) % 10;

    return $checksum === intval(substr($transfer_code, -1));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
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
        $selected_activities = $data['activities'] ?? [];

        // Check room availability
        if (!checkRoomAvailability($pdo, $room_type, $check_in, $check_out)) {
            echo json_encode([
                'island' => "Mystery Island",
                'hotel' => "Glass Onion Hotel",
                'arrival_date' => $check_in,
                'departure_date' => $check_out,
                'error' => "Room is not available for selected dates"
            ]);
            exit;
        }

        // Validate transfer code
        if (!validateTransferCode($transfer_code)) {
            echo json_encode([
                'island' => "Mystery Island",
                'hotel' => "Glass Onion Hotel",
                'arrival_date' => $check_in,
                'departure_date' => $check_out,
                'error' => "Invalid transfer code"
            ]);
            exit;
        }

        // Calculate total cost and prepare features array
        $features_array = [];
        $total_cost = 0;

        // Base room prices
        $room_prices = [
            'budget' => 10,
            'standard' => 12,
            'luxury' => 15
        ];

        // Calculate nights and base cost
        $nights = (strtotime($check_out) - strtotime($check_in)) / (60 * 60 * 24);
        $base_cost = $room_prices[$room_type] * $nights;

        // Apply discount if applicable (3+ nights)
        if ($nights >= 3) {
            $base_cost = $base_cost * 0.75; // 25% discount
        }

        $total_cost = $base_cost;

        // Add selected activities to features array
        $activity_costs = [
            'pool' => ['name' => 'The Enigma Pool', 'cost' => 3],
            'pingpong' => ['name' => "Detective's Ping Pong Table", 'cost' => 1],
            'bar' => ['name' => 'Glass Onion Bar', 'cost' => 2]
        ];

        foreach ($selected_activities as $activity) {
            if (isset($activity_costs[$activity])) {
                $features_array[] = [
                    'name' => $activity_costs[$activity]['name'],
                    'cost' => $activity_costs[$activity]['cost']
                ];
                $total_cost += $activity_costs[$activity]['cost'];
            }
        }

        // Insert into database
        $stmt = $pdo->prepare("INSERT INTO bookings (
            name, email, room_type, check_in, check_out, 
            transfer_code, activities, total_price
        ) VALUES (
            :name, :email, :room_type, :check_in, :check_out, 
            :transfer_code, :activities, :total_price
        )");

        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':room_type' => $room_type,
            ':check_in' => $check_in,
            ':check_out' => $check_out,
            ':transfer_code' => $transfer_code,
            ':activities' => json_encode($selected_activities),
            ':total_price' => $total_cost
        ]);

        // Return required JSON response
        echo json_encode([
            'island' => "Mystery Island",
            'hotel' => "Glass Onion Hotel",
            'arrival_date' => $check_in,
            'departure_date' => $check_out,
            'total_cost' => (string)$total_cost,
            'stars' => "3",
            'features' => $features_array,
            'additional_info' => [
                'greeting' => "Welcome to the Glass Onion Hotel, where every stay is a mystery waiting to be solved...",
            ]
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'island' => "Mystery Island",
            'hotel' => "Glass Onion Hotel",
            'error' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'island' => "Mystery Island",
        'hotel' => "Glass Onion Hotel",
        'error' => "Invalid request method"
    ]);
}
