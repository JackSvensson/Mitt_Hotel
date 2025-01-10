<?php
// test_booking.php
?>
<!DOCTYPE html>
<html>

<head>
    <title>Test Booking API</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .test-form {
            margin-bottom: 20px;
        }

        .response {
            background: #f5f5f5;
            padding: 10px;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <h2>Test Booking API</h2>

    <div class="test-form">
        <h3>Test Valid Booking</h3>
        <button onclick="testValidBooking()">Run Test</button>
        <div id="validResponse" class="response"></div>
    </div>

    <div class="test-form">
        <h3>Test Invalid Transfer Code</h3>
        <button onclick="testInvalidTransfer()">Run Test</button>
        <div id="invalidTransferResponse" class="response"></div>
    </div>

    <div class="test-form">
        <h3>Test Room Unavailable</h3>
        <button onclick="testUnavailableRoom()">Run Test</button>
        <div id="unavailableResponse" class="response"></div>
    </div>

    <script>
        function displayResponse(response, elementId) {
            document.getElementById(elementId).innerHTML =
                '<pre>' + JSON.stringify(response, null, 2) + '</pre>';
        }

        async function makeBookingRequest(bookingData) {
            try {
                const response = await fetch('process_booking.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(bookingData)
                });
                return await response.json();
            } catch (error) {
                return {
                    success: false,
                    message: 'Request failed: ' + error.message
                };
            }
        }

        async function testValidBooking() {
            const validBooking = {
                name: 'John Doe',
                email: 'john@example.com',
                room_type: 'standard',
                check_in: '2025-01-15',
                check_out: '2025-01-17',
                transfer_code: 'TR1234567X'
            };

            const response = await makeBookingRequest(validBooking);
            displayResponse(response, 'validResponse');
        }

        async function testInvalidTransfer() {
            const invalidBooking = {
                name: 'Jane Doe',
                email: 'jane@example.com',
                room_type: 'luxury',
                check_in: '2025-01-20',
                check_out: '2025-01-22',
                transfer_code: 'INVALID123'
            };

            const response = await makeBookingRequest(invalidBooking);
            displayResponse(response, 'invalidTransferResponse');
        }

        async function testUnavailableRoom() {
            const unavailableBooking = {
                name: 'Alice Smith',
                email: 'alice@example.com',
                room_type: 'budget',
                check_in: '2025-01-01',
                check_out: '2025-01-03',
                transfer_code: 'TR9876543X'
            };

            const response = await makeBookingRequest(unavailableBooking);
            displayResponse(response, 'unavailableResponse');
        }
    </script>
</body>

</html>