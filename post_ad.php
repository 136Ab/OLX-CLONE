<?php
session_start();
require_once 'db.php';

// Ensure session and user validation
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}

// Verify that the user_id exists in the users table
$stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user_exists = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user_exists) {
    session_destroy();
    echo "<script>alert('User not found. Please log in again.'); window.location.href='login.php';</script>";
    exit;
}

// Fetch categories
$stmt = $conn->query("SELECT * FROM categories");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category_id = $_POST['category_id'];
    $location = $_POST['location'];
    $condition = $_POST['condition'];
    $image = '';

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['name']) {
        $upload_dir = __DIR__ . '/Uploads/';
        $image_name = basename($_FILES['image']['name']);
        $image_path = $upload_dir . $image_name;
        $image = 'Uploads/' . $image_name;

        // Create Uploads directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0755, true)) {
                echo "<script>alert('Failed to create Uploads directory.'); window.location.href='post_ad.php';</script>";
                exit;
            }
        }

        // Check if directory is writable
        if (!is_writable($upload_dir)) {
            echo "<script>alert('Uploads directory is not writable. Please check permissions.'); window.location.href='post_ad.php';</script>";
            exit;
        }

        // Validate file type and size
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB
        $file_type = mime_content_type($_FILES['image']['tmp_name']);
        $file_size = $_FILES['image']['size'];

        if (!in_array($file_type, $allowed_types)) {
            echo "<script>alert('Invalid file type. Only JPEG, PNG, and GIF are allowed.'); window.location.href='post_ad.php';</script>";
            exit;
        }

        if ($file_size > $max_size) {
            echo "<script>alert('File size exceeds 5MB limit.'); window.location.href='post_ad.php';</script>";
            exit;
        }

        // Move the uploaded file
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
            echo "<script>alert('Failed to upload image. Please try again.'); window.location.href='post_ad.php';</script>";
            exit;
        }
    }

    try {
        $stmt = $conn->prepare("INSERT INTO ads (user_id, category_id, title, description, price, location, `condition`, image) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $category_id, $title, $description, $price, $location, $condition, $image]);
        header("Location: profile.php?success=Ad posted successfully");
        exit;
    } catch (PDOException $e) {
        echo "<script>alert('Error posting ad: " . addslashes($e->getMessage()) . "'); window.location.href='post_ad.php';</script>";
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Ad - OLX Clone</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
        }
        .container {
            max-width: 600px;
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
        .form-group input, .form-group select, .form-group textarea {
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
        @media (max-width: 600px) {
            .container {
                width: 90%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Post a New Ad</h2>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" required></textarea>
            </div>
            <div class="form-group">
                <label>Price</label>
                <input type="number" name="price" step="0.01" required>
            </div>
            <div class="form-group">
                <label>Category</label>
                <select name="category_id" required>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Location</label>
                <input type="text" name="location">
            </div>
            <div class="form-group">
                <label>Condition</label>
                <select name="condition" required>
                    <option value="New">New</option>
                    <option value="Used">Used</option>
                    <option value="Refurbished">Refurbished</option>
                </select>
            </div>
            <div class="form-group">
                <label>Image</label>
                <input type="file" name="image" accept="image/*">
            </div>
            <div class="form-group">
                <button type="submit">Post Ad</button>
            </div>
        </form>
    </div>
</body>
</html>
