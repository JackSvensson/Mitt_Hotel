<?php
$page_title = 'Our Rooms - Glass Onion Hotel';
$additional_css = 'css/rooms.css';
require_once 'includes/header.php';
?>

<main>
    <section class="rooms-container">
        <h1>Our Single Rooms</h1>
        
        <!-- Budget Single Room -->
        <div class="room-card">
            <div class="room-image" style="background-image: url('img/Budget.room.jpeg');"></div>
            <div class="room-info">
                <h2 class="room-type">The Beachcomber's Quarter</h2>
                <p class="room-price">Cost $10 per night</p>
                <div class="discount-notice">
                    <p>🌟 Stay 3 or more nights and get 25% off! 🌟</p>
                </div>
                <p>Comfortable and economical, our budget single rooms are perfect for travelers who want quality accommodation at an affordable price.</p>
                <ul class="room-features">
                    <li>Single bed</li>
                    <li>24" TV</li>
                    <li>Free Wi-Fi</li>
                    <li>Basic bathroom amenities</li>
                    <li>Air conditioning</li>
                    <li>Daily housekeeping</li>
                </ul>
                <a href="booking.php?room=budget" class="book-now-btn">Book Now</a>
            </div>
        </div>

        <!-- Standard Single Room -->
        <div class="room-card">
            <div class="room-image" style="background-image: url('img/Standard.room.jpeg');"></div>
            <div class="room-info">
                <h2 class="room-type">The Detective's Den</h2>
                <p class="room-price">Cost $12 per night</p>
                <div class="discount-notice">
                    <p>🌟 Stay 3 or more nights and get 25% off! 🌟</p>
                </div>
                <p>Our standard single rooms offer additional comfort and amenities for a more enjoyable stay.</p>
                <ul class="room-features">
                    <li>Premium single bed</li>
                    <li>32" Smart TV</li>
                    <li>High-speed Wi-Fi</li>
                    <li>En-suite bathroom with premium toiletries</li>
                    <li>Climate control</li>
                    <li>Work desk</li>
                    <li>Mini fridge</li>
                    <li>Daily housekeeping</li>
                </ul>
                <a href="booking.php?room=standard" class="book-now-btn">Book Now</a>
            </div>
        </div>

        <!-- Luxury Single Room -->
        <div class="room-card">
            <div class="room-image" style="background-image: url('img/Luxurious.room.jpeg');"></div>
            <div class="room-info">
                <h2 class="room-type">The Glass Onion Suite</h2>
                <p class="room-price">Cost $15 per night</p>
                <div class="discount-notice">
                    <p>🌟 Stay 3 or more nights and get 25% off! 🌟</p>
                </div>
                <p>Experience ultimate comfort in our luxury single rooms, featuring premium amenities and elegant design.</p>
                <ul class="room-features">
                    <li>Luxury king single bed</li>
                    <li>42" 4K Smart TV</li>
                    <li>Premium high-speed Wi-Fi</li>
                    <li>Luxury bathroom with rainfall shower</li>
                    <li>Climate control</li>
                    <li>Executive work station</li>
                    <li>Nespresso coffee machine</li>
                    <li>Mini bar</li>
                    <li>Bathrobe and slippers</li>
                    <li>Evening turndown service</li>
                    <li>Priority housekeeping</li>
                </ul>
                <a href="booking.php?room=luxury" class="book-now-btn">Book Now</a>
            </div>
        </div>
    </section>
</main>

<footer>
    <p>&copy; 2025 Glass Onion Hotel. All rights reserved.</p>
</footer>
</body>
</html>