<?php
session_start();
include('config.php');

// Check if the user is logged in as an admin
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Fetch all users from the database
$users_query = "SELECT * FROM users";
$users_result = $conn->query($users_query);

// Check if a user ID is passed to fetch their order history
$order_history = [];
if (isset($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']);
    // Fetch the orders for the selected user
    $orders_query = "SELECT * FROM orders WHERE user_id = $user_id";
    $orders_result = $conn->query($orders_query);
    while ($order = $orders_result->fetch_assoc()) {
        $order_history[] = $order;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Users and Orders</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include('admin_navbar.php'); ?>

<div class="container mt-5">
    <h1>Admin Dashboard - Manage Users</h1>

    <!-- Users Table -->
    <h2 class="mt-5">Users List</h2>
    <table class="table table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Full Name</th>
                <th>Bank Details</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $users_result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['phone']); ?></td>
                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['bank_details']); ?></td>
                    <td>
                        <!-- View Order History Button -->
                        <a href="admin_users_orders.php?user_id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm">View Order History</a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <!-- Show Order History for selected user -->
    <?php if (!empty($order_history)) { ?>
        <h2 class="mt-5">Order History</h2>
        <table class="table table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>Order ID</th>
                    <th>Car ID</th>
                    <th>City</th>
                    <th>Payment Method</th>
                    <th>Delivery Status</th>
                    <th>Order Status</th>
                    <th>Total Price</th>
                    <th>Delivery Charge</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order_history as $order) { ?>
                    <tr>
                        <td><?php echo $order['order_id']; ?></td>
                        <td><?php echo $order['car_id']; ?></td>
                        <td><?php echo htmlspecialchars($order['city']); ?></td>
                        <td><?php echo htmlspecialchars($order['payment_method']); ?></td>
                        <td><?php echo ucfirst($order['delivery_status']); ?></td>
                        <td><?php echo ucfirst($order['order_status']); ?></td>
                        <td>PKR <?php echo number_format($order['total_price'], 2); ?></td>
                        <td>PKR <?php echo number_format($order['delivery_charge'], 2); ?></td>
                        <td><?php echo date('Y-m-d H:i:s', strtotime($order['created_at'])); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } ?>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
