<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}

// Validate URL parameters
$seller_id = isset($_GET['seller_id']) && is_numeric($_GET['seller_id']) ? (int)$_GET['seller_id'] : null;
$ad_id = isset($_GET['ad_id']) && is_numeric($_GET['ad_id']) ? (int)$_GET['ad_id'] : null;

if (!$seller_id || !$ad_id || $seller_id == $_SESSION['user_id']) {
    echo "<script>alert('Invalid or missing seller or ad ID, or you cannot chat with yourself.'); window.location.href='index.php';</script>";
    exit;
}

// Verify sender, receiver, and ad exist
try {
    $stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
        session_destroy();
        echo "<script>alert('User not found. Please log in again.'); window.location.href='login.php';</script>";
        exit;
    }

    $stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->execute([$seller_id]);
    if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<script>alert('Seller not found.'); window.location.href='index.php';</script>";
        exit;
    }

    $stmt = $conn->prepare("SELECT id, title FROM ads WHERE id = ? AND status = 'Active'");
    $stmt->execute([$ad_id]);
    $ad = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$ad) {
        echo "<script>alert('Ad not found or is not active.'); window.location.href='index.php';</script>";
        exit;
    }
} catch (PDOException $e) {
    echo "<script>alert('Database error: " . addslashes($e->getMessage()) . "'); window.location.href='index.php';</script>";
    exit;
}

// Fetch messages
try {
    $stmt = $conn->prepare("SELECT messages.*, users.username AS sender_name 
                            FROM messages 
                            JOIN users ON messages.sender_id = users.id 
                            WHERE messages.ad_id = ? 
                            AND (messages.sender_id = ? OR messages.receiver_id = ?) 
                            AND (messages.sender_id = ? OR messages.receiver_id = ?) 
                            ORDER BY messages.created_at");
    $stmt->execute([$ad_id, $_SESSION['user_id'], $_SESSION['user_id'], $seller_id, $seller_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<script>alert('Error fetching messages: " . addslashes($e->getMessage()) . "'); window.location.href='index.php';</script>";
    exit;
}

// Handle message sending
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $message = trim($_POST['message']);
    if (empty($message)) {
        echo "<script>alert('Message cannot be empty.'); window.location.href='chat.php?seller_id=$seller_id&ad_id=$ad_id';</script>";
        exit;
    }
    try {
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, ad_id, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $seller_id, $ad_id, $message]);
        header("Location: chat.php?seller_id=$seller_id&ad_id=$ad_id&success=Message sent successfully");
        exit;
    } catch (PDOException $e) {
        echo "<script>alert('Error sending message: " . addslashes($e->getMessage()) . "'); window.location.href='chat.php?seller_id=$seller_id&ad_id=$ad_id';</script>";
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - OLX Clone</title>
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
        .chat-box {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #ccc;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .message {
            margin: 10px 0;
            padding: 10px;
            border-radius: 5px;
        }
        .message.sent {
            background-color: #23e5db;
            color: white;
            margin-left: 20%;
        }
        .message.received {
            background-color: #ddd;
            margin-right: 20%;
        }
        .form-group {
            display: flex;
            gap: 10px;
        }
        .form-group textarea {
            flex: 1;
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
        <h2>Chat for Ad: <?php echo htmlspecialchars($ad['title']); ?></h2>
        <?php if (isset($_GET['success'])): ?>
            <div class="success-message"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php endif; ?>
        <div class="chat-box">
            <?php if (empty($messages)): ?>
                <p>No messages yet.</p>
            <?php else: ?>
                <?php foreach ($messages as $msg): ?>
                    <div class="message <?php echo $msg['sender_id'] == $_SESSION['user_id'] ? 'sent' : 'received'; ?>">
                        <strong><?php echo htmlspecialchars($msg['sender_name']); ?>:</strong>
                        <p><?php echo htmlspecialchars($msg['message']); ?></p>
                        <small><?php echo $msg['created_at']; ?></small>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <form method="POST">
            <div class="form-group">
                <textarea name="message" placeholder="Type your message..." required></textarea>
                <button type="submit">Send</button>
            </div>
        </form>
    </div>
</body>
</html>
