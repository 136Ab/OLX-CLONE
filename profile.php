<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$ads_stmt = $conn->prepare("SELECT * FROM ads WHERE user_id = ? AND status != 'Deleted'");
$ads_stmt->execute([$_SESSION['user_id']]);
$ads = $ads_stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $phone = $_POST['phone'];
    $stmt = $conn->prepare("UPDATE users SET username = ?, phone = ? WHERE id = ?");
    $stmt->execute([$username, $phone, $_SESSION['user_id']]);
    echo "<script>alert('Profile updated!'); window.location.href='profile.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - OLX Clone</title>
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
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .form-group button {
            padding: 10px 20px;
            background-color: #23e5db;
            border: none;
            color: white;
            cursor: pointer;
            border-radius: 5px;
        }
        .ad-list {
            margin-top: 20px;
        }
        .ad-card {
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
        }
        .ad-card a {
            color: #002f34;
            text-decoration: none;
        }
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
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
        <?php if (isset($_GET['success'])): ?>
            <div class="success-message"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php endif; ?>
        <h2>Your Profile</h2>
        <form method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
            </div>
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
            </div>
            <div class="form-group">
                <button type="submit">Update Profile</button>
            </div>
        </form>
        <h2>Your Ads</h2>
        <div class="ad-list">
            <?php foreach ($ads as $ad): ?>
                <div class="ad-card">
                    <h3><?php echo htmlspecialchars($ad['title']); ?></h3>
                    <p>Price: $<?php echo htmlspecialchars($ad['price']); ?></p>
                    <p>Status: <?php echo htmlspecialchars($ad['status']); ?></p>
                    <a href="edit_ad.php?id=<?php echo $ad['id']; ?>">Edit</a> |
                    <a href="#" onclick="deleteAd(<?php echo $ad['id']; ?>)">Delete</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <script>
        function deleteAd(id) {
            if (confirm('Are you sure you want to delete this ad?')) {
                window.location.href = 'edit_ad.php?id=' + id + '&action=delete';
            }
        }
    </script>
</body>
</html>
