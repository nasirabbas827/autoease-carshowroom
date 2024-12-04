<?php
session_start();
include('config.php');

// Check if the user is logged in as an admin
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

$upload_dir = 'uploads/';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $brand = $_POST["brand"];
    $model = $_POST["model"];
    $price = $_POST["price"];
    $category_id = $_POST["category_id"];
    $features = $_POST["features"];
    $availability_status = $_POST["availability_status"];
    $image_url = "";

    // Handle file upload
    if (isset($_FILES["image"]["name"]) && $_FILES["image"]["error"] == 0) {
        $file_name = basename($_FILES["image"]["name"]);
        $file_tmp = $_FILES["image"]["tmp_name"];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_exts = ["jpg", "jpeg", "png"];

        if (in_array($file_ext, $allowed_exts)) {
            $image_url = $upload_dir . uniqid() . "." . $file_ext;
            if (!move_uploaded_file($file_tmp, $image_url)) {
                $error_message = "Failed to upload image.";
            }
        } else {
            $error_message = "Invalid file type. Only JPG and PNG are allowed.";
        }
    }

    if (!isset($error_message)) {
        // Insert car into the database
        $sql = "INSERT INTO Cars (brand, model, price, image_url, category_id, features, availability_status) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssss", $brand, $model, $price, $image_url, $category_id, $features, $availability_status);

        if ($stmt->execute()) {
            $success_message = "Car added successfully!";
        } else {
            $error_message = "Failed to add car. Please try again.";
        }
        $stmt->close();
    }
}

// Fetch categories for dropdown
$sql_categories = "SELECT id, name FROM categories";
$result_categories = $conn->query($sql_categories);
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Car</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }

        .add-car-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .add-car-container h2 {
            text-align: center;
            font-size: 1.8em;
            margin-bottom: 30px;
        }

        .form-group label {
            font-weight: bold;
        }

        .form-group input, .form-group select, .form-group textarea {
            border-radius: 5px;
            padding: 10px;
            font-size: 1rem;
        }
        .btn-group-center {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
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
    <div class="add-car-container">
        <h2>Add New Car</h2>

        <!-- Display success or error message -->
        <?php if (isset($success_message)) { ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php } elseif (isset($error_message)) { ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php } ?>

        <!-- Add Car Form -->
        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="brand">Car Brand:</label>
                <input type="text" class="form-control" id="brand" name="brand" required>
            </div>
            <div class="form-group">
                <label for="model">Car Model:</label>
                <input type="text" class="form-control" id="model" name="model" required>
            </div>
            <div class="form-group">
                <label for="price">Price:</label>
                <input type="number" class="form-control" id="price" name="price" step="0.01" required>
            </div>
            <div class="form-group ">
                <label for="image">Car Image (JPG/PNG):</label>
                <input type="file" class="form-control" id="image" name="image" required>
            </div>
            <div class="form-group">
                <label for="category_id">Category:</label>
                <select class="form-control" id="category_id" name="category_id" required>
                    <option value="" disabled selected>Select Category</option>
                    <?php if ($result_categories->num_rows > 0) {
                        while ($row = $result_categories->fetch_assoc()) { ?>
                            <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
                    <?php }
                    } ?>
                </select>
            </div>
            <div class="form-group">
                <label for="features">Features:</label>
                <textarea class="form-control" id="features" name="features" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <label for="availability_status">Availability Status:</label>
                <select class="form-control" id="availability_status" name="availability_status" required>
                    <option value="available">Available</option>
                    <option value="unavailable">Unavailable</option>
                </select>
            </div>
            <div class="btn-group-center">
            <button type="submit" class="btn btn-primary">Add Car</button>
            <a class="btn btn-dark" href="view_cars.php">View Cars</a>
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
