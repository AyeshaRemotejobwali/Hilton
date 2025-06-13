<?php
require_once 'db.php';

// Get search parameters
$destination = isset($_GET['destination']) ? trim($_GET['destination']) : '';
$price = isset($_GET['price']) ? trim($_GET['price']) : '';
$rating = isset($_GET['rating']) ? trim($_GET['rating']) : '';
$amenities = isset($_GET['amenities']) ? trim($_GET['amenities']) : '';
$sort = isset($_GET['sort']) ? trim($_GET['sort']) : 'price_asc';
$checkin = isset($_GET['checkin']) ? trim($_GET['checkin']) : '';
$checkout = isset($_GET['checkout']) ? trim($_GET['checkout']) : '';

// Build the SQL query
$query = "SELECT * FROM hotels WHERE 1=1";
$params = [];
if ($destination) {
    $query .= " AND location LIKE ?";
    $params[] = "%$destination%";
}
if ($price) {
    $range = explode('-', $price);
    if (count($range) == 2) {
        $query .= " AND price BETWEEN ? AND ?";
        $params[] = $range[0];
        $params[] = $range[1];
    } else {
        $query .= " AND price >= ?";
        $params[] = str_replace('+', '', $price);
    }
}
if ($rating) {
    $query .= " AND rating >= ?";
    $params[] = $rating;
}
if ($amenities) {
    $query .= " AND amenities LIKE ?";
    $params[] = "%$amenities%";
}
if ($sort == 'price_desc') {
    $query .= " ORDER BY price DESC";
} else {
    $query .= " ORDER BY price ASC";
}

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $hotels = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $debug_message = empty($hotels) ? "No hotels found. Query: $query, Params: " . implode(', ', $params) : "Found " . count($hotels) . " hotels.";
} catch (PDOException $e) {
    $debug_message = "Database error: " . $e->getMessage();
    $hotels = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hilton Hotels - Search Results</title>
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
            padding: 30px 20px;
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
        .hotel-list {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .hotel-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: calc(33.33% - 20px);
            transition: transform 0.3s;
        }
        .hotel-card:hover {
            transform: translateY(-5px);
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
        .hotel-card button {
            margin: 10px;
            padding: 10px;
            background: #1a2a44;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .hotel-card button:hover {
            background: #2a4066;
        }
        .debug {
            text-align: center;
            color: red;
            margin: 20px;
        }
        @media (max-width: 768px) {
            .hotel-card {
                width: calc(50% - 20px);
            }
            .search-box input {
                width: 100%;
            }
        }
        @media (max-width: 480px) {
            .hotel-card {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Hilton Hotels - Search Results</h1>
        <p>Find Your Perfect Stay</p>
    </header>
    <div class="search-container">
        <div class="search-box">
            <input type="text" id="destination" value="<?php echo htmlspecialchars($destination); ?>" placeholder="Destination">
            <input type="date" id="checkin" value="<?php echo htmlspecialchars($checkin); ?>">
            <input type="date" id="checkout" value="<?php echo htmlspecialchars($checkout); ?>">
            <button onclick="searchHotels()">Search</button>
        </div>
    </div>
    <div class="filters">
        <select id="price" onchange="applyFilters()">
            <option value="">Price Range</option>
            <option value="100-200" <?php echo $price == '100-200' ? 'selected' : ''; ?>>$100 - $200</option>
            <option value="200-300" <?php echo $price == '200-300' ? 'selected' : ''; ?>>$200 - $300</option>
            <option value="300+" <?php echo $price == '300+' ? 'selected' : ''; ?>>$300+</option>
        </select>
        <select id="rating" onchange="applyFilters()">
            <option value="">Rating</option>
            <option value="4" <?php echo $rating == '4' ? 'selected' : ''; ?>>4+ Stars</option>
            <option value="3" <?php echo $rating == '3' ? 'selected' : ''; ?>>3+ Stars</option>
        </select>
        <select id="amenities" onchange="applyFilters()">
            <option value="">Amenities</option>
            <option value="WiFi" <?php echo $amenities == 'WiFi' ? 'selected' : ''; ?>>WiFi</option>
            <option value="Pool" <?php echo $amenities == 'Pool' ? 'selected' : ''; ?>>Pool</option>
            <option value="Gym" <?php echo $amenities == 'Gym' ? 'selected' : ''; ?>>Gym</option>
        </select>
        <select id="sort" onchange="applyFilters()">
            <option value="price_asc" <?php echo $sort == 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
            <option value="price_desc" <?php echo $sort == 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
        </select>
    </div>
    <div class="hotel-list">
        <?php if (!empty($hotels)): ?>
            <?php foreach ($hotels as $hotel): ?>
                <div class="hotel-card">
                    <img src="<?php echo htmlspecialchars($hotel['image']); ?>" alt="<?php echo htmlspecialchars($hotel['name']); ?>" onerror="this.src='https://source.unsplash.com/400x300/?hotel';">
                    <h3><?php echo htmlspecialchars($hotel['name']); ?></h3>
                    <p><?php echo htmlspecialchars($hotel['location']); ?> - $<?php echo htmlspecialchars($hotel['price']); ?>/night</p>
                    <p>Rating: <?php echo htmlspecialchars($hotel['rating']); ?> ★</p>
                    <p>Amenities: <?php echo htmlspecialchars($hotel['amenities']); ?></p>
                    <p><?php echo htmlspecialchars($hotel['description']); ?></p>
                    <button onclick="goToBooking(<?php echo $hotel['id']; ?>, '<?php echo htmlspecialchars($checkin); ?>', '<?php echo htmlspecialchars($checkout); ?>')">Book Now</button>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="debug">No hotels found. <?php echo htmlspecialchars($debug_message); ?></p>
        <?php endif; ?>
    </div>
    <script>
        function applyFilters() {
            const destination = document.getElementById('destination').value;
            const checkin = document.getElementById('checkin').value;
            const checkout = document.getElementById('checkout').value;
            const price = document.getElementById('price').value;
            const rating = document.getElementById('rating').value;
            const amenities = document.getElementById('amenities').value;
            const sort = document.getElementById('sort').value;
            const params = new URLSearchParams({
                destination, checkin, checkout, price, rating, amenities, sort
            });
            window.location.href = `search_result.php?${params.toString()}`;
        }
        function searchHotels() {
            const destination = document.getElementById('destination').value;
            const checkin = document.getElementById('checkin').value;
            const checkout = document.getElementById('checkout').value;
            const params = new URLSearchParams({
                destination, checkin, checkout,
                price: '<?php echo $price; ?>',
                rating: '<?php echo $rating; ?>',
                amenities: '<?php echo $amenities; ?>',
                sort: '<?php echo $sort; ?>'
            });
            window.location.href = `search_result.php?${params.toString()}`;
        }
        function goToBooking(hotelId, checkin, checkout) {
            console.log('Navigating to booking for hotel ID:', hotelId);
            window.location.href = `booking.php?hotel_id=${hotelId}&checkin=${checkin}&checkout=${checkout}`;
        }
    </script>
</body>
</html>
