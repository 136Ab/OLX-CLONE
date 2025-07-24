<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}

$ad_id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM ads WHERE id = ? AND user_id = ?");
$stmt->execute([$ad_id, $_SESSION['user_id']]);
$ad = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ad) {
    echo "<script>window.location.href='profile.php';</script>";
    exit;
}

$categories = $conn->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $stmt = $conn->prepare("UPDATE ads SET status = 'Deleted' WHERE id = ?");
    $stmt->execute([$ad_id]);
    echo "<script>window.location.href='profile.php';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category_id = $_POST['category_id'];
    $location = $_POST['location'];
    $condition = $_POST['condition'];
    $status = $_POST['status'];
    $image = $ad['image'];

    if ($_FILES['image']['name']) {
        $image = 'uploads/' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $image);
    }

    $stmt = $conn->prepare("UPDATE ads SET title = ?, description = ?, price = ?, category_id = ?, location = ?, condition = ?, image = ?, status = ? WHERE id = ?");
    $stmt->execute([$title, $description, $price, $category_id, $location, $condition, $image, $status, $ad_id]);
    echo "<script>alert('Ad updated successfully!'); window.location.href='profile.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Ad - OLX Clone</title>
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
        <h2>Edit Ad</h2>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" value="<?php echo htmlspecialchars($ad['title']); ?>" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" required><?php echo htmlspecialchars($ad['description']); ?></textarea>
            </div>
            <div class="form-group">
                <label>Price</label>
                <input type="number" name="price" step="0.01" value="<?php echo htmlspecialchars($ad['price']); ?>" required>
            </div>
            <div class="form-group">
                <label>Category</label>
                <select name="category_id" required>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>" <?php if ($category['id'] == $ad['category_id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Location</label>
                <input type="text" name="location" value="<?php echo htmlspecialchars($ad['location']); ?>">
            </div>
            <div class="form-group">
                <label>Condition</label>
                <select name="condition" required>
                    <option value="New" <?php if ($ad['condition'] == 'New') echo 'selected'; ?>>New</option>
                    <option value="Used" <?php if ($ad['condition'] == 'Used') echo 'selected'; ?>>Used</option>
                    <option value="Refurbished" <?php if ($ad['condition'] == 'Refurbished') echo 'selected'; ?>>Refurbished</option>
                </select>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status" required>
                    <option value="Active" <?php if ($ad['status'] == 'Active') echo 'selected'; ?>>Active</option>
                    <option value="Sold" <?php if ($ad['status'] == 'Sold') echo 'selected'; ?>>Sold</option>
                </select>
            </div>
            <div class="form-group">
                <label>Image</label>
                <input type="file" name="image" accept="image/*">
                <?php if ($ad['image']): ?>
                    <img src="<?php echo htmlspecialchars($ad['image']); ?>" alt="Current Image" style="max-width: 100px;">
                <?php endif; ?>
            </div>
            <div class="form-group">
                <button type="submit">Update Ad</button>
            </div>
        </form>
    </div>
</body>
</html>
