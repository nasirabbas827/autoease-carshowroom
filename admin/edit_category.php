<?php
session_start();
include('config.php');

// Check if the user is logged in as an admin
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Get category ID from the query parameter
$category_id = $_GET['id'] ?? null;

if (!$category_id) {
    header("Location: add_categories.php");
    exit;
}

// Fetch the current category details
$query = "SELECT * FROM categories WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $category_id);
$stmt->execute();
$result = $stmt->get_result();
$category = $result->fetch_assoc();

if (!$category) {
    header("Location: add_categories.php");
    exit;
}

// Handle form submission for updating the category
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category_name = trim($_POST['category_name']);

    // Update the category in the database
    $update_query = "UPDATE categories SET name = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("si", $category_name, $category_id);

    if ($stmt->execute()) {
        echo "<script>
                alert('Category updated successfully.');
                window.location.href = 'add_categories.php';
              </script>";
        exit;
    } else {
        $error_message = "Error updating category.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Category</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include('admin_navbar.php'); ?>
<div class="container mt-4">
    <h3>Edit Category</h3>
    <?php if (isset($error_message)) echo "<p class='text-danger'>$error_message</p>"; ?>
    <form action="" method="POST">
        <div class="form-group">
            <label for="category_name">Category Name:</label>
            <input type="text" class="form-control" id="category_name" name="category_name" value="<?php echo htmlspecialchars($category['name']); ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Update Category</button>
        <a href="add_categories.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
</body>
</html>
