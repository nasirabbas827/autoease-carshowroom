<?php
session_start();
include('config.php');

// Check if the user is logged in as an admin
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

$car_id = $_GET['car_id'];

// Fetch car details
$sql = "SELECT * FROM Cars WHERE car_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $car_id);
$stmt->execute();
$result = $stmt->get_result();
$car = $result->fetch_assoc();

// Fetch categories for dropdown
$sql_categories = "SELECT id, name FROM categories";
$result_categories = $conn->query($sql_categories);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $brand = $_POST["brand"];
    $model = $_POST["model"];
    $price = $_POST["price"];
    $category_id = $_POST["category_id"];
    $features = $_POST["features"];
    $availability_status = $_POST["availability_status"];

    // Handle image upload
    $image_name = $car['image_url']; // Retain the old image by default
    if (!empty($_FILES["car_image"]["name"])) {
        $target_dir = "uploads/";
        $image_name = $target_dir . basename($_FILES["car_image"]["name"]);
        if (!move_uploaded_file($_FILES["car_image"]["tmp_name"], $image_name)) {
            $error_message = "Failed to upload image. Please try again.";
        }
    }

    $sql_update = "UPDATE Cars SET brand = ?, model = ?, price = ?, category_id = ?, features = ?, availability_status = ?, image_url = ? WHERE car_id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("ssdisssi", $brand, $model, $price, $category_id, $features, $availability_status, $image_name, $car_id);

    if ($stmt_update->execute()) {
        header("Location: view_cars.php?success=Car updated successfully!");
    } else {
        $error_message = "Failed to update car. Please try again.";
    }
    $stmt_update->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Car</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }

        .edit-car-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .btn-group-center {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
        }

        .car-image-preview {
            text-align: center;
            margin-bottom: 20px;
        }

        .car-image-preview img {
            max-width: 100%;
            max-height: 200px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
    </style>
</head>

<body>

<!-- Navbar -->
<?php include('admin_navbar.php'); ?>

<div class="container">
    <div class="edit-car-container">
        <h2 class="text-center">Edit Car</h2>

        <!-- Display error message -->
        <?php if (isset($error_message)) { ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php } ?>

        <!-- Edit Car Form -->
        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="brand">Car Brand:</label>
                <input type="text" class="form-control" id="brand" name="brand" value="<?php echo $car['brand']; ?>" required>
            </div>
            <div class="form-group">
                <label for="model">Car Model:</label>
                <input type="text" class="form-control" id="model" name="model" value="<?php echo $car['model']; ?>" required>
            </div>
            <div class="form-group">
                <label for="price">Price:</label>
                <input type="number" class="form-control" id="price" name="price" value="<?php echo $car['price']; ?>" step="0.01" required>
            </div>
            <div class="form-group">
                <label for="category_id">Category:</label>
                <select class="form-control" id="category_id" name="category_id" required>
                    <option value="" disabled>Select Category</option>
                    <?php while ($row = $result_categories->fetch_assoc()) { ?>
                        <option value="<?php echo $row['id']; ?>" <?php if ($car['category_id'] == $row['id']) echo "selected"; ?>>
                            <?php echo $row['name']; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group">
                <label for="features">Features:</label>
                <textarea class="form-control" id="features" name="features" rows="4" required><?php echo $car['features']; ?></textarea>
            </div>
            <div class="form-group">
                <label for="availability_status">Availability Status:</label>
                <select class="form-control" id="availability_status" name="availability_status" required>
                    <option value="available" <?php if ($car['availability_status'] == 'available') echo "selected"; ?>>Available</option>
                    <option value="unavailable" <?php if ($car['availability_status'] == 'unavailable') echo "selected"; ?>>Unavailable</option>
                </select>
            </div>
            <div class="form-group">
                <label for="car_image">Car Image:</label>
                <input type="file" class="form-control-file" id="car_image" name="car_image">
            </div>
            <div class="car-image-preview">
                <p>Current Image:</p>
                <img src="<?php echo $car['image_url']; ?>" alt="Car Image">
            </div>
            <div class="btn-group-center">
                <button type="submit" class="btn btn-primary">Update Car</button>
                <a href="view_cars.php" class="btn btn-secondary">Back</a>
            </div>
        </form>
    </div>
</div>

<!-- Bootstrap JS & dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
