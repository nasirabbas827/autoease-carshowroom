<?php
session_start();
include('config.php');

// Check if the user is logged in as an admin
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Handle delete request
if (isset($_GET['delete_id'])) {
    $car_id = $_GET['delete_id'];

    // Delete the car from the database
    $sql_delete = "DELETE FROM Cars WHERE car_id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $car_id);
    if ($stmt_delete->execute()) {
        $success_message = "Car deleted successfully!";
    } else {
        $error_message = "Failed to delete car. Please try again.";
    }
    $stmt_delete->close();
}

// Fetch all cars from the database
$sql = "SELECT Cars.*, categories.name AS category_name FROM Cars 
        LEFT JOIN categories ON Cars.category_id = categories.id";
$result = $conn->query($sql);
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Cars</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }

        .container {
            margin-top: 30px;
        }

        .table {
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .table th, .table td {
            text-align: center;
            vertical-align: middle;
        }

        .btn {
            margin: 0 5px;
        }

        .alert {
            font-size: 1rem;
        }
    </style>
</head>

<body>

<!-- Navbar -->
<?php include('admin_navbar.php'); ?>

<div class="container">
    <h2 class="text-center mb-4">Manage Cars</h2>

    <!-- Display success or error message -->
    <?php if (isset($success_message)) { ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php } elseif (isset($error_message)) { ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php } ?>

    <!-- Cars Table -->
    <table class="table table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>ID</th>
                <th>Brand</th>
                <th>Model</th>
                <th>Price</th>
                <th>Category</th>
                <th>Features</th>
                <th>Status</th>
                <th>Image</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row['car_id']; ?></td>
                        <td><?php echo $row['brand']; ?></td>
                        <td><?php echo $row['model']; ?></td>
                        <td><?php echo number_format($row['price'], 2); ?></td>
                        <td><?php echo $row['category_name']; ?></td>
                        <td><?php echo $row['features']; ?></td>
                        <td><?php echo ucfirst($row['availability_status']); ?></td>
                        <td>
                            <img src="<?php echo $row['image_url']; ?>" alt="Car Image" style="width: 100px; height: auto;">
                        </td>
                        <td>
                            <a href="edit_car.php?car_id=<?php echo $row['car_id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="?delete_id=<?php echo $row['car_id']; ?>" 
                               onclick="return confirm('Are you sure you want to delete this car?');" 
                               class="btn btn-danger btn-sm">Delete</a>
                        </td>
                    </tr>
                <?php }
            } else { ?>
                <tr>
                    <td colspan="9" class="text-center">No cars available.</td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<!-- Bootstrap JS & dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
