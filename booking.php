<?php
require_once 'db.php';

$hotel_id = isset($_GET['hotel_id']) ? (int)$_GET['hotel_id'] : 0;
$checkin = isset($_GET['checkin']) ? trim($_GET['checkin']) : '';
$checkout = isset($_GET['checkout']) ? trim($_GET['checkout']) : '';
$success = false;
$error_message = '';

if ($hotel_id <= 0) {
    $error_message = 'Invalid hotel ID.';
}

// Fetch hotel details
try {
    $stmt = $pdo->prepare("SELECT * FROM hotels WHERE id = ?");
    $stmt->execute([$hotel_id]);
    $hotel = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$hotel) {
        $error_message = 'Hotel not found in the database.';
    }
} catch (PDOException $e) {
    $error_message = 'Database error fetching hotel: ' . $e->getMessage();
}

// Handle booking form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error_message) {
    $user_name = trim($_POST['user_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $check_in = trim($_POST['check_in'] ?? '');
    $check_out = trim($_POST['check_out'] ?? '');
    $total_price = trim($_POST['total_price'] ?? 0);

    // Validate form inputs
    if (empty($user_name) || empty($email) || empty($check_in) || empty($check_out) || $total_price <= 0) {
        $error_message = 'Please fill in all required fields correctly.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO bookings (hotel_id, user_name, email, check_in, check_out, total_price) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$hotel_id, $user_name, $email, $check_in, $check_out, $total_price]);
            $success = true;

            // Update available rooms
            $pdo->prepare("UPDATE hotels SET available_rooms = available_rooms - 1 WHERE id = ? AND available_rooms > 0")->execute([$hotel_id]);
        } catch (PDOException $e) {
            $error_message = 'Booking failed: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hilton Hotels - Book Your Stay</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }
        body {
            background-color: #f4f4f9;
        }
        header {
            background: linear-gradient(90deg, #1a2a44, #2a4066);
            color: white;
            padding: 20px;
            text-align: center;
        }
        header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        .booking-container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .booking-container h2 {
            color: #1a2a44;
            margin-bottom: 20px;
        }
        .booking-container input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1em;
        }
        .booking-container button {
            width: 100%;
            padding: 10px;
            background: #1a2a44;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .booking-container button:hover {
            background: #2a4066;
        }
        .success-message {
            color: green;
            text-align: center;
            margin: 20px 0;
        }
        .error-message {
            color: red;
            text-align: center;
            margin: 20px 0;
        }
        .hotel-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .back-button {
            display: inline-block;
            margin: 10px 0;
            padding: 10px 20px;
            background: #555;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }
        .back-button:hover {
            background: #777;
        }
        @media (max-width: 480px) {
            .booking-container {
                margin: 10px;
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Hilton Hotels - Booking</h1>
        <a href="index.php" class="back-button">Back to Home</a>
    </header>
    <div class="booking-container">
        <?php if ($success): ?>
            <p class="success-message">Booking confirmed! You'll receive an email soon.</p>
            <a href="index.php" class="back-button">Return to Home</a>
        <?php elseif ($error_message): ?>
            <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
            <a href="search_result.php" class="back-button">Back to Search</a>
        <?php elseif ($hotel): ?>
            <h2>Book <?php echo htmlspecialchars($hotel['name']); ?></h2>
            <img src="<?php echo htmlspecialchars($hotel['image']); ?>" alt="<?php echo htmlspecialchars($hotel['name']); ?>" class="hotel-image" onerror="this.src='https://source.unsplash.com/400x300/?hotel';">
            <p>Location: <?php echo htmlspecialchars($hotel['location']); ?></p>
            <p>Price per Night: $<?php echo htmlspecialchars($hotel['price']); ?></p>
            <p>Amenities: <?php echo htmlspecialchars($hotel['amenities']); ?></p>
            <form method="POST">
                <input type="text" name="user_name" placeholder="Your Name" required>
                <input type="email" name="email" placeholder="Your Email" required>
                <input type="date" name="check_in" value="<?php echo htmlspecialchars($checkin); ?>" required>
                <input type="date" name="check_out" value="<?php echo htmlspecialchars($checkout); ?>" required>
                <input type="hidden" name="total_price" value="<?php echo htmlspecialchars($hotel['price']); ?>">
                <button type="submit">Confirm Booking</button>
            </form>
        <?php else: ?>
            <p class="error-message">Hotel not found or an error occurred.</p>
            <a href="search_result.php" class="back-button">Back to Search</a>
        <?php endif; ?>
    </div>
    <script>
        function goBack() {
            window.location.href = 'search_result.php';
        }
    </script>
</body>
</html>
