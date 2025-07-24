<?php
session_start();
require_once 'db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>window.location.href='index.php';</script>";
    exit;
}

$ad_id = (int)$_GET['id'];
try {
    $stmt = $conn->prepare("SELECT ads.*, users.username, users.phone, users.id AS seller_id, categories.name AS category 
                            FROM ads 
                            JOIN users ON ads.user_id = users.id 
                            JOIN categories ON ads.category_id = categories.id 
                            WHERE ads.id = ? AND ads.status = 'Active'");
    $stmt->execute([$ad_id]);
    $ad = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ad) {
        echo "<script>window.location.href='index.php';</script>";
        exit;
    }
} catch (PDOException $e) {
    echo "<script>alert('Error fetching ad: " . addslashes($e->getMessage()) . "'); window.location.href='index.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Ad - OLX Clone</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #002f34;
        }
        .ad-details img {
            max-width: 100%;
            border-radius: 5px;
        }
        .contact {
            margin-top: 20px;
        }
        .contact button {
            padding: 10px 20px;
            background-color: #23e5db;
            border: none;
            color: white;
            cursor: pointer;
            border-radius: 5px;
        }
        @media (max-width: 600px) {
            .container {
                width: 90%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2><?php echo htmlspecialchars($ad['title']); ?></h2>
        <div class="ad-details">
            <?php if ($ad['image']): ?>
                <img src="<?php echo htmlspecialchars($ad['image']); ?>" alt="Ad Image">
            <?php endif; ?>
            <p><strong>Price:</strong> $<?php echo htmlspecialchars($ad['price']); ?></p>
            <p><strong>Category:</strong> <?php echo htmlspecialchars($ad['category']); ?></p>
            <p><strong>Description:</strong> <?php echo htmlspecialchars($ad['description']); ?></p>
            <p><strong>Location:</strong> <?php echo htmlspecialchars($ad['location']); ?></p>
            <p><strong>Condition:</strong> <?php echo htmlspecialchars($ad['condition']); ?></p>
            <p><strong>Posted by:</strong> <?php echo htmlspecialchars($ad['username']); ?></p>
            <div class="contact">
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($ad['phone']); ?></p>
                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $ad['user_id']): ?>
                    <button onclick="startChat(<?php echo $ad['seller_id']; ?>, <?php echo $ad['id']; ?>)">Chat with Seller</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script>
        function startChat(sellerId, adId) {
            window.location.href = 'chat.php?seller_id=' + sellerId + '&ad_id=' + adId;
        }
    </script>
</body>
</html>
