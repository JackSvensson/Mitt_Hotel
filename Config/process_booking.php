<?php
// process_booking.php
require_once __DIR__ . '/db_config.php';
require_once __DIR__ . '/../pricing.php';


header('Content-Type: application/json');

// Function to check room availability
function checkAvailability($pdo, $room_type, $check_in, $check_out)
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

    return $stmt->fetchColumn() === 0; // Returns true if room is available
}

function validateTransferCode($transferCode)
{
    // Check if the code matches UUID v4 format
    $uuid_pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';
    return preg_match($uuid_pattern, $transferCode) === 1;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $data = $_POST;

        $selected_features = isset($_POST['selected_features']) ? (array)$_POST['selected_features'] : [];

        // Validate required fields
        $required_fields = ['first_name', 'email', 'room_type', 'check_in', 'check_out', 'transfer_code', 'guests'];
        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new Exception("Missing required field: $field");
            }
        }

        // Sanitize and validate input
        $first_name = filter_var($data['first_name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
        $room_type = filter_var($data['room_type'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $check_in = $data['check_in'];
        $check_out = $data['check_out'];
        $transfer_code = $data['transfer_code'];
        $selected_activities = isset($_POST['features']) ? $_POST['features'] : [];

        // Check room availability
        if (!checkAvailability($pdo, $room_type, $check_in, $check_out)) {
            echo json_encode([
                "island" => "Mystery Island",
                "hotel" => "Glass Onion Hotel",
                "error" => "Selected room is not available for these dates"
            ]);
            exit;
        }

        // Validate transfer code
        if (!validateTransferCode($transfer_code)) {
            echo json_encode([
                "island" => "Mystery Island",
                "hotel" => "Glass Onion Hotel",
                "error" => "Invalid transfer code format"
            ]);
            exit;
        }

        // Get selected features from POST data
        $selected_features = isset($_POST['features']) ? (array)$_POST['features'] : [];

        // Calculate total cost and prepare features array
        $activity_costs = [
            'pool' => ['name' => 'The Enigma Pool', 'cost' => 3],
            'pingpong' => ['name' => "Detective's Ping Pong Table", 'cost' => 1],
            'bar' => ['name' => 'Glass Onion Bar', 'cost' => 2]
        ];

        // Calculate nights and base cost
        $nights = (strtotime($check_out) - strtotime($check_in)) / (60 * 60 * 24);
        $room_prices = [
            'budget' => 10,
            'standard' => 12,
            'luxury' => 15
        ];
        $base_cost = $room_prices[$room_type] * $nights;

        // Apply discount if applicable
        if ($nights >= 3) {
            $base_cost = $base_cost * 0.75; // 25% discount
        }

        $total_cost = $base_cost;
        $features_array = [];

        // Process selected features
        foreach ($selected_features as $feature) {
            if (isset($activity_costs[$feature])) {
                $features_array[] = [
                    'name' => $activity_costs[$feature]['name'],
                    'cost' => $activity_costs[$feature]['cost']
                ];
                $total_cost += $activity_costs[$feature]['cost'];
            }
        }

        // Insert booking into database
        $stmt = $pdo->prepare("INSERT INTO bookings (
            first_name, email, room_type, check_in, check_out, 
            transfer_code, activities, total_price, guests
        ) VALUES (
            :first_name, :email, :room_type, :check_in, :check_out, 
            :transfer_code, :activities, :total_price, :guests
        )");

        $stmt->execute([
            ':first_name' => $first_name,
            ':email' => $email,
            ':room_type' => $room_type,
            ':check_in' => $check_in,
            ':check_out' => $check_out,
            ':transfer_code' => $transfer_code,
            ':activities' => json_encode($selected_activities),
            ':total_price' => $total_cost,
            ':guests' => $data['guests']
        ]);

        // Return success response in required format
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
