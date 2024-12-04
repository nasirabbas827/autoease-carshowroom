<?php
session_start();
include('config.php');

// Check if the user is logged in as an admin
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Fetch all orders
$sql = "SELECT o.order_id, o.user_id, u.username, o.car_id, c.brand, c.model, o.city, o.payment_method, o.delivery_status, o.order_status, o.total_price, o.delivery_charge, o.created_at
        FROM Orders o
        JOIN Users u ON o.user_id = u.id
        JOIN Cars c ON o.car_id = c.car_id
        ORDER BY o.created_at DESC";
$result = $conn->query($sql);

// Handle updates
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['update_order'])) {
        $order_id = $_POST['order_id'];
        $delivery_status = $_POST['delivery_status'];
        $order_status = $_POST['order_status'];
        $delivery_charge = $_POST['delivery_charge'];

        // Update query
        $sql_update = "UPDATE Orders 
                       SET delivery_status = ?, order_status = ?, delivery_charge = ?, total_price = total_price + ? 
                       WHERE order_id = ?";
        $stmt = $conn->prepare($sql_update);
        $stmt->bind_param("ssdii", $delivery_status, $order_status, $delivery_charge, $delivery_charge, $order_id);

        if ($stmt->execute()) {
            header("Location: manage_orders.php?success=Order updated successfully.");
        } else {
            header("Location: manage_orders.php?error=Failed to update order. Please try again.");
        }
        $stmt->close();
    }

    // Handle installment creation for 'installment' method
    if (isset($_POST['make_installments'])) {
        $order_id = $_POST['order_id'];
        $sql_order = "SELECT total_price FROM Orders WHERE order_id = ?";
        $stmt_order = $conn->prepare($sql_order);
        $stmt_order->bind_param("i", $order_id);
        $stmt_order->execute();
        $result_order = $stmt_order->get_result();
        
        if ($result_order->num_rows == 0) {
            echo "Order not found.";
            exit;
        }

        $order = $result_order->fetch_assoc();
        $total_price = $order['total_price'];
        $installment_amount = $total_price / 2;  // Split into two installments
        
        // Insert the two installments into Payments table
        $sql_insert_installments = "INSERT INTO Payments (order_id, payment_type, amount, payment_status, due_date)
                                    VALUES (?, 'installment', ?, 'pending', ?), (?, 'installment', ?, 'pending', ?)";
        
        // Calculate due dates for the installments
        $due_date_1 = date("Y-m-d", strtotime("+1 month"));
        $due_date_2 = date("Y-m-d", strtotime("+2 months"));
        
        $stmt_installments = $conn->prepare($sql_insert_installments);
        $stmt_installments->bind_param("idssss", $order_id, $installment_amount, $due_date_1, $order_id, $installment_amount, $due_date_2);
        
        if ($stmt_installments->execute()) {
            header("Location: manage_orders.php?success=Installments created successfully.");
        } else {
            header("Location: manage_orders.php?error=Failed to create installments.");
        }

        $stmt_installments->close();
        $stmt_order->close();
    }

    // Handle single installment creation for 'full' method
    if (isset($_POST['add_payment_installment'])) {
        $order_id = $_POST['order_id'];
        $sql_order = "SELECT total_price FROM Orders WHERE order_id = ?";
        $stmt_order = $conn->prepare($sql_order);
        $stmt_order->bind_param("i", $order_id);
        $stmt_order->execute();
        $result_order = $stmt_order->get_result();
        
        if ($result_order->num_rows == 0) {
            echo "Order not found.";
            exit;
        }

        $order = $result_order->fetch_assoc();
        $total_price = $order['total_price'];
        
        // Insert the single installment into Payments table
        $sql_insert_single_installment = "INSERT INTO Payments (order_id, payment_type, amount, payment_status, due_date)
                                          VALUES (?, 'installment', ?, 'pending', ?)";
        
        // Calculate the due date for the installment
        $due_date = date("Y-m-d", strtotime("+1 month"));
        
        $stmt_single_installment = $conn->prepare($sql_insert_single_installment);
        $stmt_single_installment->bind_param("ids", $order_id, $total_price, $due_date);
        
        if ($stmt_single_installment->execute()) {
            header("Location: manage_orders.php?success=Single installment added successfully.");
        } else {
            header("Location: manage_orders.php?error=Failed to add single installment.");
        }

        $stmt_single_installment->close();
        $stmt_order->close();
    }
}

// Fetch all orders to display
$sql = "SELECT o.order_id, o.user_id, u.username, o.car_id, c.brand, c.model, o.city, o.payment_method, o.delivery_status, o.order_status, o.total_price, o.delivery_charge, o.created_at
        FROM Orders o
        JOIN Users u ON o.user_id = u.id
        JOIN Cars c ON o.car_id = c.car_id
        ORDER BY o.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* Custom styling for the table */
        .table-container {
            margin-top: 30px;
        }

        .btn-update, .btn-sm {
            width: 100%;
            padding: 10px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
<?php include('admin_navbar.php'); ?>

<div class="container table-container">
    <h2 class="mb-4">Manage Orders</h2>

    <?php if (isset($_GET['success'])) { ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
    <?php } elseif (isset($_GET['error'])) { ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
    <?php } ?>

    <table class="table table-bordered table-striped table-hover table-sm">
        <thead class="thead-dark">
            <tr>
                <th>Order ID</th>
                <th>User</th>
                <th>Car</th>
                <th>City</th>
                <th>Payment Method</th>
                <th>Delivery Status</th>
                <th>Order Status</th>
                <th>Total Price</th>
                <th>Delivery Charges</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $row['order_id']; ?></td>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td><?php echo htmlspecialchars($row['brand'] . ' ' . $row['model']); ?></td>
                    <td><?php echo htmlspecialchars($row['city']); ?></td>
                    <td><?php echo htmlspecialchars(ucfirst($row['payment_method'])); ?></td>
                    <td><?php echo htmlspecialchars(ucfirst($row['delivery_status'])); ?></td>
                    <td><?php echo htmlspecialchars(ucfirst($row['order_status'])); ?></td>
                    <td>PKR <?php echo number_format($row['total_price'], 2); ?></td>
                    <td>PKR <?php echo number_format($row['delivery_charge'], 2); ?></td>
                    <td><?php echo date('Y-m-d H:i:s', strtotime($row['created_at'])); ?></td>
                    <td>
                        <!-- Update Form -->
                        <form method="POST" action="manage_orders.php">
                            <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                            <div class="form-group">
                                <select class="form-control mb-2" name="delivery_status" required>
                                    <option value="pending" <?php if ($row['delivery_status'] == 'pending') echo 'selected'; ?>>Pending</option>
                                    <option value="delivered" <?php if ($row['delivery_status'] == 'delivered') echo 'selected'; ?>>Delivered</option>
                                    <option value="processing" <?php if ($row['delivery_status'] == 'processing') echo 'selected'; ?>>Processing</option>
                                    <option value="shipped" <?php if ($row['delivery_status'] == 'shipped') echo 'selected'; ?>>Shipped</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <select class="form-control mb-2" name="order_status" required>
                                    <option value="active" <?php if ($row['order_status'] == 'active') echo 'selected'; ?>>Active</option>
                                    <option value="cancelled" <?php if ($row['order_status'] == 'cancelled') echo 'selected'; ?>>Cancelled</option>
                                    <option value="shipped" <?php if ($row['order_status'] == 'shipped') echo 'selected'; ?>>Shipped</option>
                                    <option value="completed" <?php if ($row['order_status'] == 'completed') echo 'selected'; ?>>Completed</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <input type="number" step="0.01" class="form-control mb-2" name="delivery_charge" value="<?php echo $row['delivery_charge']; ?>" placeholder="Delivery Charges">
                            </div>
                            <button type="submit" name="update_order" class="btn btn-primary btn-update">Update Order</button>
                        </form>
                        
                        <!-- Make Installments Button for installment method -->
                        <?php if ($row['payment_method'] == 'installment') { ?>
                            <form method="POST" action="manage_orders.php" style="margin-top: 10px;">
                                <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                <button type="submit" name="make_installments" class="btn btn-warning btn-sm">Make Installments</button>
                            </form>
                        <?php } ?>

                        <!-- Add Payment Installment Button for full method -->
                        <?php if ($row['payment_method'] == 'full') { ?>
                            <form method="POST" action="manage_orders.php" style="margin-top: 10px;">
                                <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                <button type="submit" name="add_payment_installment" class="btn btn-info btn-sm">Add Payment Installment</button>
                            </form>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
