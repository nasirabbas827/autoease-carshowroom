<?php
session_start();
include('config.php');

// Check if user is logged in
if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: index.php");
    exit;
}

// Get the order ID from the query string
if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    header("location: my_orders.php?error=No order selected.");
    exit;
}

$order_id = intval($_GET['order_id']);
$user_id = $_SESSION["id"];

// Fetch the order details
$sql_order = "SELECT o.order_id, o.total_price, o.payment_method, c.brand, c.model 
              FROM Orders o 
              JOIN Cars c ON o.car_id = c.car_id 
              WHERE o.order_id = ? AND o.user_id = ?";
$stmt_order = $conn->prepare($sql_order);
$stmt_order->bind_param("ii", $order_id, $user_id);
$stmt_order->execute();
$result_order = $stmt_order->get_result();

if ($result_order->num_rows === 0) {
    header("location: my_orders.php?error=Invalid order ID.");
    exit;
}

$order = $result_order->fetch_assoc();
$stmt_order->close();

// Fetch the order details
$sql_order = "SELECT o.order_id, o.total_price, o.payment_method, c.brand, c.model 
              FROM Orders o 
              JOIN Cars c ON o.car_id = c.car_id 
              WHERE o.order_id = ? AND o.user_id = ?";
$stmt_order = $conn->prepare($sql_order);
$stmt_order->bind_param("ii", $order_id, $user_id);
$stmt_order->execute();
$result_order = $stmt_order->get_result();

if ($result_order->num_rows === 0) {
    header("location: my_orders.php?error=Invalid order ID.");
    exit;
}

$order = $result_order->fetch_assoc();
$stmt_order->close();

// Fetch payment details
$sql_payments = "SELECT payment_id, payment_type, amount, payment_status, due_date, paid_at, transaction_image 
                 FROM Payments 
                 WHERE order_id = ? 
                 ORDER BY due_date ASC";
$stmt_payments = $conn->prepare($sql_payments);
$stmt_payments->bind_param("i", $order_id);
$stmt_payments->execute();
$result_payments = $stmt_payments->get_result();

$payments = [];
while ($row = $result_payments->fetch_assoc()) {
    $payments[] = $row;
}

$stmt_payments->close();

// Handle image upload and update payment status
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['pay_now'])) {
    if (isset($_FILES['transaction_image']) && $_FILES['transaction_image']['error'] == 0) {
        $upload_dir = 'transactions/';
        $upload_file = $upload_dir . basename($_FILES['transaction_image']['name']);
        
        // Check if the file is a valid image
        $file_ext = pathinfo($upload_file, PATHINFO_EXTENSION);
        $valid_ext = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array(strtolower($file_ext), $valid_ext)) {
            if (move_uploaded_file($_FILES['transaction_image']['tmp_name'], $upload_file)) {
                // Update the payment status and record the payment image
                $payment_id = $_POST['payment_id'];
                $payment_status = 'paid';
                $paid_at = date("Y-m-d H:i:s");

                $sql_update_payment = "UPDATE Payments SET payment_status = ?, paid_at = ?, transaction_image = ? WHERE payment_id = ?";
                $stmt_update = $conn->prepare($sql_update_payment);
                $stmt_update->bind_param("sssi", $payment_status, $paid_at, $upload_file, $payment_id);
                
                if ($stmt_update->execute()) {
                    // Commit the changes and redirect
                    header("location: view_payment.php?order_id=$order_id&success=Payment recorded successfully.");
                } else {
                    $error_message = "Failed to update payment status. Please try again.";
                }
                $stmt_update->close();
            } else {
                $error_message = "Failed to upload transaction image. Please try again.";
            }
        } else {
            $error_message = "Invalid file format. Please upload an image file.";
        }
    } else {
        $error_message = "No file uploaded. Please try again.";
    }
}

$conn->close(); // Close connection only after everything is done

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Payment</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>

<body>
    <?php include('navbar.php'); ?>

    <div class="container mt-5">
        <h2 class="mb-4">Payment Details for Order #<?php echo htmlspecialchars($order['order_id']); ?></h2>

        <div class="mb-3">
            <p><strong>Car:</strong> <?php echo htmlspecialchars($order['brand'] . ' ' . $order['model']); ?></p>
            <p><strong>Total Price:</strong> PKR <?php echo number_format($order['total_price'], 2); ?></p>
            <p><strong>Payment Method:</strong> <?php echo ucfirst($order['payment_method']); ?></p>
        </div>

        <table class="table table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>Payment ID</th>
                    <th>Payment Type</th>
                    <th>Amount</th>
                    <th>Payment Status</th>
                    <th>Due Date</th>
                    <th>Paid At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($payments as $payment): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($payment['payment_id']); ?></td>
                        <td><?php echo ucfirst($payment['payment_type']); ?></td>
                        <td>PKR <?php echo number_format($payment['amount'], 2); ?></td>
                        <td><?php echo ucfirst($payment['payment_status']); ?></td>
                        <td><?php echo date("d-m-Y", strtotime($payment['due_date'])); ?></td>
                        <td>
                            <?php echo $payment['paid_at'] ? date("d-m-Y H:i:s", strtotime($payment['paid_at'])) : 'Not Paid'; ?>
                        </td>
                        <td>
                            <?php if ($payment['payment_status'] == 'pending'): ?>
                                <button class="btn btn-primary" data-toggle="modal" data-target="#payNowModal" data-payment-id="<?php echo $payment['payment_id']; ?>">Pay Now</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($payments)): ?>
                    <tr>
                        <td colspan="7">No payments found for this order.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pay Now Modal -->
    <div class="modal fade" id="payNowModal" tabindex="-1" role="dialog" aria-labelledby="payNowModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="payNowModalLabel">Upload Transaction Image</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="payment_id" id="payment_id">
                        <div class="form-group">
                            <label for="transaction_image">Select Transaction Image:</label>
                            <input type="file" class="form-control-file" name="transaction_image" id="transaction_image" required>
                        </div>
                        <?php if (isset($error_message)): ?>
                            <div class="alert alert-danger"><?php echo $error_message; ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" name="pay_now">Submit Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        $('#payNowModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); // Button that triggered the modal
            var paymentId = button.data('payment-id'); // Extract info from data-* attributes
            var modal = $(this);
            modal.find('#payment_id').val(paymentId);
        });
    </script>

</body>

</html>
