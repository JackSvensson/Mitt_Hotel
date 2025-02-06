<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Glass Onion Hotel'; ?></title>
    <link rel="stylesheet" href="css/main.css">
    <?php if (isset($additional_css)): ?>
        <link rel="stylesheet" href="<?php echo $additional_css; ?>">
    <?php endif; ?>
</head>

<body>
    <header class="site-header">
        <nav class="nav-container">
            <div class="hotel-brand">
                <h1>Glass Onion Hotel <span class="hotel-stars">★★★</span></h1>
            </div>
            <nav class="main-nav">
                <ul class="nav-list">
                    <li class="nav-item"><a href="index.php" class="nav-link">Home</a></li>
                    <li class="nav-item"><a href="rooms.php" class="nav-link">Rooms</a></li>
                    <li class="nav-item"><a href="booking.php" class="nav-link">Book Now</a></li>
                    <li class="nav-item"><a href="contact.php" class="nav-link">Contact</a></li>
                </ul>
            </nav>
        </nav>
    </header>