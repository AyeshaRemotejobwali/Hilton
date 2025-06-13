<?php
require_once 'db.php';
$destination = isset($_GET['destination']) ? $_GET['destination'] : '';
$price = isset($_GET['price']) ? $_GET['price'] : '';
$rating = isset($_GET['rating']) ? $_GET['rating'] : '';
$amenities = isset($_GET['amenities']) ? $_GET['amenities'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'price_asc';

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

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$hotels = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        @media (max-width: 768px) {
            .hotel-card {
                width: calc(50% - 20px);
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
    </header>
    <div class="filters">
        <select id="sort" onchange="applyFilters()">
            <option value="price_asc" <?php echo $sort == 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
            <option value="price_desc" <?php echo $sort == 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
        </select>
    </div>
    <div class="hotel-list">
        <?php if (empty($hotels)): ?>
            <p>No hotels found.</p>
        <?php else: ?>
            <?php foreach ($hotels as $hotel): ?>
                <div class="hotel-card">
                    <img src="<?php echo htmlspecialchars($hotel['image']); ?>" alt="<?php echo htmlspecialchars($hotel['name']); ?>">
                    <h3><?php echo htmlspecialchars($hotel['name']); ?></h3>
                    <p><?php echo htmlspecialchars($hotel['location']); ?> - $<?php echo htmlspecialchars($hotel['price']); ?>/night</p>
                    <p>Rating: <?php echo htmlspecialchars($hotel['rating']); ?> ★</p>
                    <p><?php echo htmlspecialchars($hotel['description']); ?></p>
                    <button onclick="goToBooking(<?php echo $hotel['id']; ?>, '<?php echo $_GET['checkin'] ?? ''; ?>', '<?php echo $_GET['checkout'] ?? ''; ?>')">Book Now</button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <script>
        function applyFilters() {
            const sort = document.getElementById('sort').value;
            const params = new URLSearchParams({
                destination: '<?php echo $destination; ?>',
                checkin: '<?php echo $_GET['checkin'] ?? ''; ?>',
                checkout: '<?php echo $_GET['checkout'] ?? ''; ?>',
                price: '<?php echo $price; ?>',
                rating: '<?php echo $rating; ?>',
                amenities: '<?php echo $amenities; ?>',
                sort: sort
            });
            window.location.href = `hotels.php?${params.toString()}`;
        }
        function goToBooking(hotelId, checkin, checkout) {
            window.location.href = `booking.php?hotel_id=${hotelId}&checkin=${checkin}&checkout=${checkout}`;
        }
    </script>
</body>
</html>
