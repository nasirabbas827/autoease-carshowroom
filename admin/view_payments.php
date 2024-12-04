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

// Fetch all payments from the database
$payment_query = "SELECT * FROM payments";
$payment_result = $conn->query($payment_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Users and Payments</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include('admin_navbar.php'); ?>

<div class="container mt-5">

    <!-- Payments Table -->
    <h2 class="mt-5">Payments Records</h2>
    <table class="table table-bordered" id="paymentTable">
        <thead class="thead-dark">
            <tr>
                <th>Payment ID</th>
                <th>Order ID</th>
                <th>Payment Type</th>
                <th>Amount</th>
                <th>Payment Status</th>
                <th>Due Date</th>
                <th>Paid At</th>
                <th>Transaction Image</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $payment_result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $row['payment_id']; ?></td>
                    <td><?php echo $row['order_id']; ?></td>
                    <td><?php echo htmlspecialchars($row['payment_type']); ?></td>
                    <td>PKR <?php echo number_format($row['amount'], 2); ?></td>
                    <td><?php echo ucfirst($row['payment_status']); ?></td>
                    <td><?php echo date('Y-m-d', strtotime($row['due_date'])); ?></td>
                    <td><?php echo date('Y-m-d', strtotime($row['paid_at'])); ?></td>
                    <td>
                        <?php if ($row['transaction_image']) { ?>
                            <img src="../<?php echo $row['transaction_image']; ?>" width="100" alt="Transaction Image">
                        <?php } else { ?>
                            No Image
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <!-- Button to generate and print the report -->
    <button class="btn btn-primary" onclick="window.print()">Generate & Print Report</button>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
