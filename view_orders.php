<?php
session_start();
include('config.php');

// Set timezone to Pakistan Standard Time (PST)
date_default_timezone_set('Asia/Karachi');

// Check if user is logged in
if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: index.php");
    exit;
}

// Get the user ID from the session
$user_id = $_SESSION["id"];

// Fetch user orders
$sql = "SELECT o.order_id, o.created_at, o.total_price, o.delivery_status, c.brand, c.model 
        FROM Orders o 
        JOIN Cars c ON o.car_id = c.car_id 
        WHERE o.user_id = ? 
        ORDER BY o.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">

</head>

<body>
    <?php include('navbar.php'); ?>

    <div class="container">
        <h2 class="mb-4">My Orders</h2>
        <table class="table table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>Order ID</th>
                    <th>Car</th>
                    <th>Total Price</th>
                    <th>Delivery Status</th>
                    <th>Order Date</th>
                    <th>Cancel Time Remaining</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <?php
                    // Calculate remaining time for cancellation
                    $order_time = strtotime($order['created_at']);
                    $current_time = time();
                    $remaining_time = max(86400 - ($current_time - $order_time), 0); // 24 hours in seconds
                    $can_cancel = $remaining_time > 0;
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                        <td><?php echo htmlspecialchars($order['brand'] . ' ' . $order['model']); ?></td>
                        <td><?php echo number_format($order['total_price'], 2); ?> PKR</td>
                        <td><?php echo ucfirst($order['delivery_status']); ?></td>
                        <td><?php echo date("d-m-Y H:i:s", $order_time); ?></td>
                        <td>
                            <span id="timer-<?php echo $order['order_id']; ?>" class="timer">
                                <?php echo $can_cancel ? '' : 'Cancellation period expired.'; ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($can_cancel): ?>
                                <form method="POST" action="cancel_order.php" style="display:inline;">
                                    <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Cancel Order</button>
                                </form>
                            <?php endif; ?>
                            <a href="view_payment.php?order_id=<?php echo $order['order_id']; ?>" class="mt-2 btn btn-primary btn-sm">View Payment</a>
                        </td>
                    </tr>
                    <script>
                        const timerElement<?php echo $order['order_id']; ?> = document.getElementById("timer-<?php echo $order['order_id']; ?>");
                        let remainingTime<?php echo $order['order_id']; ?> = <?php echo $remaining_time; ?>;

                        const updateTimer<?php echo $order['order_id']; ?> = () => {
                            if (remainingTime<?php echo $order['order_id']; ?> <= 0) {
                                timerElement<?php echo $order['order_id']; ?>.innerText = "Cancellation period expired.";
                                return;
                            }

                            const hours = Math.floor(remainingTime<?php echo $order['order_id']; ?> / 3600);
                            const minutes = Math.floor((remainingTime<?php echo $order['order_id']; ?> % 3600) / 60);
                            const seconds = remainingTime<?php echo $order['order_id']; ?> % 60;

                            timerElement<?php echo $order['order_id']; ?>.innerText = `Time remaining: ${hours}h ${minutes}m ${seconds}s`;
                            remainingTime<?php echo $order['order_id']; ?>--;

                            setTimeout(updateTimer<?php echo $order['order_id']; ?>, 1000);
                        };

                        updateTimer<?php echo $order['order_id']; ?>();
                    </script>
                <?php endforeach; ?>
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="7">No orders found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
