<?php
include('config.php');
session_start();

// Check if the user is logged in
if (!isset($_SESSION["id"])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION["id"];

// Check if a valid car ID is provided
if (!isset($_GET['car_id']) || empty($_GET['car_id'])) {
    header("Location: user_dashboard.php");
    exit;
}

$car_id = intval($_GET['car_id']);

// Fetch car details
$sql = "SELECT brand, model, price FROM Cars WHERE car_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $car_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: user_dashboard.php?error=Car not found");
    exit;
}

$car = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $city = $_POST['city'];
    $payment_method = $_POST['payment_method'];
    $total_price = $car['price']; // Use car price for now; delivery charge added later

    // Insert the order into the database
    $sql_order = "INSERT INTO Orders (user_id, car_id, city, payment_method, total_price) VALUES (?, ?, ?, ?, ?)";
    $stmt_order = $conn->prepare($sql_order);
    $stmt_order->bind_param("iissd", $user_id, $car_id, $city, $payment_method, $total_price);

    if ($stmt_order->execute()) {
        header("Location: home.php?success=Order placed successfully!");
    } else {
        $error_message = "Failed to place order. Please try again.";
    }

    $stmt_order->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Make Order</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
    <style>
        .order-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .btn-container {
            text-align: center;
            margin-top: 20px;
        }

        .btn-container .btn {
            width: 150px;
        }

        .alert-danger {
            font-size: 0.9rem;
        }
    </style>
</head>

<body>
    <?php include('navbar.php'); ?>

    <div class="container">
        <div class="order-container">
            <h2 class="text-center text-primary">Make Order</h2>
            <p class="text-center"><strong>Car:</strong> <?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></p>
            <p class="text-center"><strong>Price:</strong> Pkr <?php echo number_format($car['price'], 2); ?></p>

            <!-- Warning about delivery charges -->
            <div class="alert alert-danger text-center">
                Note: Delivery charges will be added to the final invoice.
            </div>

            <!-- Display error message -->
            <?php if (isset($error_message)) { ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php } ?>

            <!-- Order Form -->
            <form method="post">
                <div class="form-group">
                    <label for="city">City:</label>
                    <input type="text" class="form-control" id="city" name="city" placeholder="Enter your city" required>
                </div>
                <div class="form-group">
                    <label for="payment_method">Payment Method:</label>
                    <select class="form-control" id="payment_method" name="payment_method" required>
                        <option value="full">Full Payment</option>
                        <option value="installment">Installment</option>
                    </select>
                </div>
                <div class="btn-container">
                    <button type="submit" class="btn btn-success">Place Order</button>
                    <a href="view_car_details.php?car_id=<?php echo $car_id; ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
