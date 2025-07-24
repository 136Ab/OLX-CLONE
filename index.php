<?php
session_start();
require_once 'db.php';

$stmt = $conn->query("SELECT ads.*, users.username, categories.name AS category FROM ads 
                      JOIN users ON ads.user_id = users.id 
                      JOIN categories ON ads.category_id = categories.id 
                      WHERE ads.status = 'Active' 
                      ORDER BY ads.created_at DESC LIMIT 10");
$ads = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OLX Clone - Homepage</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: #f4f4f4;
        }
        .header {
            background-color: #002f34;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .search-bar {
            margin: 20px;
            text-align: center;
        }
        .search-bar input {
            padding: 10px;
            width: 300px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .search-bar button {
            padding: 10px 20px;
            background-color: #23e5db;
            border: none;
            color: white;
            cursor: pointer;
            border-radius: 5px;
        }
        .ad-list {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            padding: 20px;
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
        .nav {
            background: #fff;
            padding: 10px;
            text-align: center;
        }
        .nav a {
            margin: 0 15px;
            text-decoration: none;
            color: #002f34;
            font-weight: bold;
        }
        @media (max-width: 600px) {
            .search-bar input {
                width: 100%;
            }
            .ad-card {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>OLX Clone</h1>
        <div class="nav">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="profile.php">Profile</a>
                <a href="post_ad.php">Post Ad</a>
                <a href="chat.php">Messages</a>
                <a href="#" onclick="logout()">Logout</a>
            <?php else: ?>
                <a href="signup.php">Signup</a>
                <a href="login.php">Login</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="search-bar">
        <form action="search.php" method="GET">
            <input type="text" name="query" placeholder="Search for products...">
            <button type="submit">Search</button>
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
    <script>
        function logout() {
            window.location.href = 'logout.php';
        }
    </script>
</body>
</html>
