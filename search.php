<?php
session_start();
require_once 'db.php';

$query = isset($_GET['query']) ? $_GET['query'] : '';
$category_id = isset($_GET['category_id']) ? $_GET['category_id'] : '';
$min_price = isset($_GET['min_price']) ? $_GET['min_price'] : '';
$max_price = isset($_GET['max_price']) ? $_GET['max_price'] : '';
$condition = isset($_GET['condition']) ? $_GET['condition'] : '';

$sql = "SELECT ads.*, users.username, categories.name AS category 
        FROM ads 
        JOIN users ON ads.user_id = users.id 
        JOIN categories ON ads.category_id = categories.id 
        WHERE ads.status = 'Active'";
$params = [];

if ($query) {
    $sql .= " AND ads.title LIKE ?";
    $params[] = "%$query%";
}
if ($category_id) {
    $sql .= " AND ads.category_id = ?";
    $params[] = $category_id;
}
if ($min_price) {
    $sql .= " AND ads.price >= ?";
    $params[] = $min_price;
}
if ($max_price) {
    $sql .= " AND ads.price <= ?";
    $params[] = $max_price;
}
if ($condition) {
    $sql .= " AND ads.condition = ?";
    $params[] = $condition;
}

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$ads = $stmt->fetchAll(PDO::FETCH_ASSOC);

$categories = $conn->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search - OLX Clone</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
        }
        .container {
            max-width: 1000px;
            margin: 20px auto;
            display: flex;
            gap: 20px;
        }
        .filters {
            width: 250px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .ad-list {
            flex: 1;
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .ad-card {
            background: white;
            border: 1px solid #ddd;
            border-radius: 10px;
            width: 200px;
            padding: 10px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .ad-card img {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
        }
        .filters form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .filters input, .filters select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .filters button {
            padding: 10px;
            background-color: #23e5db;
            border: none;
            color: white;
            cursor: pointer;
            border-radius: 5px;
        }
        @media (max-width: 600px) {
            .container {
                flex-direction: column;
                width: 90%;
            }
            .filters {
                width: 100%;
            }
            .ad-card {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="filters">
            <h3>Filters</h3>
            <form method="GET">
                <input type="text" name="query" value="<?php echo htmlspecialchars($query); ?>" placeholder="Search...">
                <select name="category_id">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>" <?php if ($category_id == $category['id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="number" name="min_price" value="<?php echo htmlspecialchars($min_price); ?>" placeholder="Min Price">
                <input type="number" name="max_price" value="<?php echo htmlspecialchars($max_price); ?>" placeholder="Max Price">
                <select name="condition">
                    <option value="">All Conditions</option>
                    <option value="New" <?php if ($condition == 'New') echo 'selected'; ?>>New</option>
                    <option value="Used" <?php if ($condition == 'Used') echo 'selected'; ?>>Used</option>
                    <option value="Refurbished" <?php if ($condition == 'Refurbished') echo 'selected'; ?>>Refurbished</option>
                </select>
                <button type="submit">Apply Filters</button>
            </form>
        </div>
        <div class="ad-list">
            <?php foreach ($ads as $ad): ?>
                <div class="ad-card">
                    <?php if ($ad['image']): ?>
                        <img src="<?php echo htmlspecialchars($ad['image']); ?>" alt="Ad Image">
                    <?php endif; ?>
                    <h3><?php echo htmlspecialchars($ad['title']); ?></h3>
                    <p>Price: $<?php echo htmlspecialchars($ad['price']); ?></p>
                    <p>Category: <?php echo htmlspecialchars($ad['category']); ?></p>
                    <p>Posted by: <?php echo htmlspecialchars($ad['username']); ?></p>
                    <a href="view_ad.php?id=<?php echo $ad['id']; ?>">View Details</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
