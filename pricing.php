<?php
// pricing.php

// Room rates per night
function getRoomPrices()
{
    return [
        'budget' => 10,
        'standard' => 12,
        'luxury' => 15
    ];
}

// Activity prices
function getActivityPrices()
{
    return [
        'pool' => 3,
        'pingpong' => 1,
        'bar' => 2
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

    // Calculate activities total
    $activities_total = 0;
    foreach ($selected_activities as $activity) {
        if (isset($activity_prices[$activity])) {
            $activities_total += $activity_prices[$activity];
        }
    }

    return [
        'base_room_price' => $base_room_price,
        'room_discount' => $base_room_price * $discount,
        'discounted_room_price' => $discounted_room_price,
        'activities_total' => $activities_total,
        'total_price' => $discounted_room_price + $activities_total
    ];
}
