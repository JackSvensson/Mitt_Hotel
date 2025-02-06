<?php
require_once __DIR__ . '/Config/db_config.php';

// Always set content type to JSON
header('Content-Type: application/json');

function checkAvailability($pdo, $room_type, $check_in, $check_out)
{
    $query = "SELECT COUNT(*) FROM bookings b
              JOIN rooms r ON b.room_id = r.id
              JOIN room_types rt ON r.room_type_id = rt.id
              WHERE LOWER(rt.name) LIKE :room_type 
              AND r.id IN (
                  SELECT room_id FROM bookings
                  WHERE (check_in <= :check_in AND check_out > :check_in)
                  OR (check_in < :check_out AND check_out >= :check_out)
                  OR (check_in >= :check_in AND check_out <= :check_out)
              )";

    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':room_type' => strtolower(explode(' ', $room_type)[0]) . '%',
        ':check_in' => $check_in,
        ':check_out' => $check_out
    ]);

    $bookedRoomsCount = $stmt->fetchColumn();

    // Get total number of rooms of this type
    $query = "SELECT COUNT(*) FROM rooms r
              JOIN room_types rt ON r.room_type_id = rt.id
              WHERE LOWER(rt.name) LIKE :room_type";

    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':room_type' => strtolower(explode(' ', $room_type)[0]) . '%'
    ]);

    $totalRoomsCount = $stmt->fetchColumn();

    // Room is available if there are more total rooms than booked rooms
    return $bookedRoomsCount < $totalRoomsCount;
}

function validateTransferCode($transferCode)
{
    $uuid_pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';
    return preg_match($uuid_pattern, $transferCode) === 1;
}

function getRoomTypeId($pdo, $room_type)
{
    $query = "SELECT id FROM room_types WHERE LOWER(name) LIKE :room_type";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['room_type' => strtolower(explode(' ', $room_type)[0]) . '%']);
    return $stmt->fetchColumn();
}

function getAvailableRoomId($pdo, $room_type_id, $check_in, $check_out)
{
    $query = "SELECT r.id FROM rooms r
              WHERE r.room_type_id = :room_type_id
              AND r.id NOT IN (
                  SELECT room_id FROM bookings
                  WHERE (check_in <= :check_in AND check_out > :check_in)
                  OR (check_in < :check_out AND check_out >= :check_out)
                  OR (check_in >= :check_in AND check_out <= :check_out)
              )
              LIMIT 1";

    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':room_type_id' => $room_type_id,
        ':check_in' => $check_in,
        ':check_out' => $check_out
    ]);
    return $stmt->fetchColumn();
}

// Wrap entire script in a try-catch to ensure JSON response
try {
    // Check if it's a POST request
    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        throw new Exception("Invalid request method");
    }

    $data = $_POST;

    // Validate required fields
    $required_fields = ['first_name', 'last_name', 'email', 'room_type', 'check_in', 'check_out', 'transfer_code', 'guests'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Sanitize input
    $first_name = filter_var($data['first_name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $last_name = filter_var($data['last_name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
    $room_type = filter_var($data['room_type'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $check_in = $data['check_in'];
    $check_out = $data['check_out'];
    $transfer_code = $data['transfer_code'];
    $selected_activities = isset($_POST['features']) ? $_POST['features'] : [];

    // Check room availability
    if (!checkAvailability($pdo, $room_type, $check_in, $check_out)) {
        throw new Exception("Selected room is not available for these dates");
    }

    // Validate transfer code
    if (!validateTransferCode($transfer_code)) {
        throw new Exception("Invalid transfer code format");
    }

    // Calculate nights
    $nights = (strtotime($check_out) - strtotime($check_in)) / (60 * 60 * 24);

    // Get room price
    $query = "SELECT base_price FROM room_types WHERE LOWER(name) LIKE :room_type";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['room_type' => strtolower(explode(' ', $room_type)[0]) . '%']);
    $room_price = $stmt->fetchColumn();
    $base_cost = $room_price * $nights;

    // Apply discount for 3+ nights
    if ($nights >= 3) {
        $query = "SELECT discount_value FROM discounts WHERE name = '3+ Nights Special' AND active = 1";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $discount_percent = $stmt->fetchColumn() / 100;
        $base_cost *= (1 - $discount_percent);
    }

    $total_cost = $base_cost;
    $features_array = [];

    // Process selected activities
    $activities_cost = 0;
    if (!empty($selected_activities)) {
        foreach ($selected_activities as $activity_name) {
            $query = "SELECT name, price FROM activities WHERE name = :activity_name";
            $stmt = $pdo->prepare($query);
            $stmt->execute([':activity_name' => $activity_name]);
            $activity = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($activity) {
                $features_array[] = [
                    'name' => $activity['name'],
                    'cost' => $activity['price']
                ];
                $activities_cost += $activity['price'];
            }
        }
    }

    $total_cost += $activities_cost;

    // Start transaction
    $pdo->beginTransaction();

    try {
        // Check if guest already exists
        $stmt = $pdo->prepare("SELECT id FROM guests WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $existing_guest_id = $stmt->fetchColumn();

        if ($existing_guest_id) {
            // Use existing guest ID
            $guest_id = $existing_guest_id;
        } else {
            // Insert new guest
            $stmt = $pdo->prepare("INSERT INTO guests (first_name, last_name, email) VALUES (:first_name, :last_name, :email)");
            $stmt->execute([
                ':first_name' => $first_name,
                ':last_name' => $last_name,
                ':email' => $email
            ]);
            $guest_id = $pdo->lastInsertId();
        }

        // Get room type and available room
        $room_type_id = getRoomTypeId($pdo, $room_type);
        $room_id = getAvailableRoomId($pdo, $room_type_id, $check_in, $check_out);

        // Insert booking
        $stmt = $pdo->prepare("INSERT INTO bookings (
            guest_id, room_id, check_in, check_out, total_price, status
        ) VALUES (
            :guest_id, :room_id, :check_in, :check_out, :total_price, 'confirmed'
        )");

        $stmt->execute([
            ':guest_id' => $guest_id,
            ':room_id' => $room_id,
            ':check_in' => $check_in,
            ':check_out' => $check_out,
            ':total_price' => $total_cost
        ]);
        $booking_id = $pdo->lastInsertId();

        // Insert booking activities
        if (!empty($selected_activities)) {
            $activity_stmt = $pdo->prepare("INSERT INTO booking_activities (
                booking_id, activity_id, scheduled_date, price, status
            ) VALUES (
                :booking_id, 
                (SELECT id FROM activities WHERE name = :activity_name),
                :scheduled_date,
                (SELECT price FROM activities WHERE name = :activity_name),
                'scheduled'
            )");

            foreach ($selected_activities as $activity_name) {
                $activity_stmt->execute([
                    ':booking_id' => $booking_id,
                    ':activity_name' => $activity_name,
                    ':scheduled_date' => $check_in // Schedule on check-in date
                ]);
            }
        }

        // Commit transaction
        $pdo->commit();

        // Return booking confirmation
        echo json_encode([
            'island' => "Mystery Island",
            'hotel' => "Glass Onion Hotel",
            'arrival_date' => $check_in,
            'departure_date' => $check_out,
            'total_cost' => number_format($total_cost, 2, '.', ''),
            'stars' => "3",
            'features' => $features_array,
            'additional_info' => [
                'greeting' => "Welcome to the Glass Onion Hotel, where every stay is a mystery waiting to be solved...",
                'transfer_code' => $transfer_code,
                'guest_name' => $first_name . ' ' . $last_name
            ]
        ], JSON_PRETTY_PRINT);
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
} catch (Exception $e) {
    // Ensure all errors are returned as JSON
    echo json_encode([
        'island' => "Mystery Island",
        'hotel' => "Glass Onion Hotel",
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
    exit;
}
