<?php
require_once 'db.php';
try {
    $hotels = $pdo->query("SELECT * FROM hotels LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
    if (empty($hotels)) {
        echo "<p style='text-align: center; color: red;'>No hotels found in the database. Please check the 'hotels' table.</p>";
    }
} catch (PDOException $e) {
    echo "<p style='text-align: center; color: red;'>Database error: " . $e->getMessage() . "</p>";
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
        .search-container {
            background: url('https://source.unsplash.com/1600x400/?hotel') no-repeat center;
            background-size: cover;
            padding: 50px 20px;
            text-align: center;
        }
        .search-box {
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 10px;
            display: inline-block;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .search-box input, .search-box button {
            padding: 10px;
            margin: 5px;
            border: none;
            border-radius: 5px;
        }
        .search-box input {
            width: 200px;
            font-size: 1em;
        }
        .search-box button {
            background: #1a2a44;
            color: white;
            cursor: pointer;
            transition: background 0.3s;
        }
        .search-box button:hover {
            background: #2a4066;
        }
        .filters {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 20px 0;
        }
        .filters select {
            padding: 10px;
            border-radius: 5px;
            font-size: 1em;
        }
        .featured {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .featured h2 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 2em;
            color: #1a2a44;
        }
        .hotel-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin: 10px;
            display: inline-block;
            width: calc(33.33% - 20px);
        }
        .hotel-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .hotel-card h3 {
            font-size: 1.5em;
            padding: 10px;
            color: #1a2a44;
        }
        .hotel-card p {
            padding: 0 10px 10px;
            color: #555;
        }
        @media (max-width: 768px) {
            .hotel-card {
                width: calc(50% - 20px);
            }
        }
        @media (max-width: 480px) {
            .hotel-card {
                width: 100%;
            }
            .search-box input {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Hilton Hotels</h1>
        <p>Find Your Perfect Stay</p>
    </header>
    <div class="search-container">
        <div class="search-box">
            <input type="text" id="destination" placeholder="Destination">
            <input type="date" id="checkin">
            <input type="date" id="checkout">
            <button onclick="searchHotels()">Search</button>
        </div>
    </div>
    <div class="filters">
        <select id="price">
            <option value="">Price Range</option>
            <option value="100-200">$100 - $200</option>
            <option value="200-300">$200 - $300</option>
            <option value="300+">$300+</option>
        </select>
        <select id="rating">
            <option value="">Rating</option>
            <option value="4">4+ Stars</option>
            <option value="3">3+ Stars</option>
        </select>
        <select id="amenities">
            <option value="">Amenities</option>
            <option value="WiFi">WiFi</option>
            <option value="Pool">Pool</option>
            <option value="Gym">Gym</option>
        </select>
    </div>
    <div class="featured">
        <h2>Featured Hotels</h2>
        <?php if (!empty($hotels)): ?>
            <?php foreach ($hotels as $hotel): ?>
                <div class="hotel-card">
                    <img src="<?php echo htmlspecialchars($hotel['image']); ?>" alt="<?php echo htmlspecialchars($hotel['name']); ?>">
                    <h3><?php echo htmlspecialchars($hotel['name']); ?></h3>
                    <p><?php echo htmlspecialchars($hotel['location']); ?> - $<?php echo htmlspecialchars($hotel['price']); ?>/night</p>
                    <p>Rating: <?php echo htmlspecialchars($hotel['rating']); ?> ★</p>
                    <button onclick="goToBooking(<?php echo $hotel['id']; ?>)">Book Now</button>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center; color: red;">No hotels available to display.</p>
        <?php endif; ?>
    </div>
    <script>
        function searchHotels() {
            const destination = document.getElementById('destination').value;
            const checkin = document.getElementById('checkin').value;
            const checkout = document.getElementById('checkout').value;
            const price = document.getElementById('price').value;
            const rating = document.getElementById('rating').value;
            const amenities = document.getElementById('amenities').value;
            const params = new URLSearchParams({
                destination, checkin, checkout, price, rating, amenities
            });
            window.location.href = `hotels.php?${params.toString()}`;
        }
        function goToBooking(hotelId) {
            const checkin = document.getElementById('checkin').value;
            const checkout = document.getElementById('checkout').value;
            console.log('Navigating to booking for hotel ID:', hotelId);
            window.location.href = `booking.php?hotel_id=${hotelId}&checkin=${checkin}&checkout=${checkout}`;
        }
    </script>
</body>
</html>
