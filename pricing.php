<?php

declare(strict_types=1);

// Room rates per night
function getRoomPrices()
{
    return [
        'budget' => 10,
        'standard' => 12,
        'luxury' => 15
    ];
}

// Activity prices with names
function getActivityPrices()
{
    return [
        'pool' => ['name' => 'The Enigma Pool', 'cost' => 3],
        'pingpong' => ['name' => "Detective's Ping Pong Table", 'cost' => 1],
        'bar' => ['name' => 'Glass Onion Bar', 'cost' => 2]
    ];
}

// Calculate total price including room, activities, and discounts
function calculateTotalPrice($room_type, $nights, $selected_activities = [])
{
    $room_prices = getRoomPrices();
    $activity_prices = getActivityPrices();

    // Calculate base room price
    $base_room_price = $room_prices[$room_type] * $nights;

    // Apply 25% discount for stays of 3 or more nights
    $discount = ($nights >= 3) ? 0.25 : 0;
    $discounted_room_price = $base_room_price * (1 - $discount);

    // Calculate activities total and prepare features array
    $activities_total = 0;
    $features_array = [];
    foreach ($selected_activities as $activity) {
        if (isset($activity_prices[$activity])) {
            $activities_total += $activity_prices[$activity]['cost'];
            $features_array[] = [
                'name' => $activity_prices[$activity]['name'],
                'cost' => $activity_prices[$activity]['cost']
            ];
        }
    }

    return [
        'base_room_price' => $base_room_price,
        'room_discount' => $base_room_price * $discount,
        'discounted_room_price' => $discounted_room_price,
        'activities_total' => $activities_total,
        'total_price' => $discounted_room_price + $activities_total,
        'features' => $features_array
    ];
}
