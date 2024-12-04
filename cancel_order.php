<?php
session_start();
include('config.php');

// Check if the user is logged in
if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("Location: index.php");
    exit;
}

// Check if the order_id is provided
if (!isset($_POST['order_id']) || empty($_POST['order_id'])) {
    header("Location: view_orders.php?error=Invalid order ID.");
    exit;
}

// Get the user ID and order ID
$user_id = $_SESSION["id"];
$order_id = intval($_POST['order_id']);

// Fetch the order details
$sql = "SELECT created_at FROM Orders WHERE order_id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $order = $result->fetch_assoc();
    $created_at = strtotime($order['created_at']);
    $current_time = time();

    // Check if the cancellation is within the 24-hour window
    if (($current_time - $created_at) <= 86400) {
        // Cancel the order
        $delete_sql = "DELETE FROM Orders WHERE order_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $order_id);

        if ($delete_stmt->execute()) {
            header("Location: view_orders.php?success=Order canceled successfully.");
        } else {
            header("Location: view_orders.php?error=Failed to cancel the order. Please try again.");
        }
    } else {
        header("Location: view_orders.php?error=Cancellation period has expired.");
    }
} else {
    header("Location: view_orders.php?error=Order not found.");
}

$stmt->close();
$conn->close();
?>
