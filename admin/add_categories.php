<?php
session_start();
include('config.php');

// Check if the user is logged in as an admin
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Handle form submission to add a category
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['category_name'])) {
    $category_name = trim($_POST['category_name']);

    // Insert category into the database
    $insert_query = "INSERT INTO categories (name) VALUES (?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("s", $category_name);

    if ($stmt->execute()) {
        $category_added = true;
    } else {
        $category_error = "Error adding category: " . $conn->error;
    }
}

// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    // Delete the category from the database
    $delete_query = "DELETE FROM categories WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $delete_id);

    if ($stmt->execute()) {
        echo "<script>
                alert('Category deleted successfully.');
                window.location.href = 'add_categories.php';
              </script>";
    } else {
        echo "<script>alert('Error deleting category.');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Home</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include('admin_navbar.php'); ?>

<div class="container mt-4">
    <h3>Add Category</h3>
    <?php
    if (isset($category_added)) {
        echo "<p class='text-success'>Category added successfully!</p>";
    } elseif (isset($category_error)) {
        echo "<p class='text-danger'>$category_error</p>";
    }
    ?>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
        <div class="form-group">
            <label for="category_name">Category Name:</label>
            <input type="text" class="form-control" id="category_name" name="category_name" required>
        </div>
        <button type="submit" class="btn btn-primary">Add Category</button>
    </form>

    <hr>

    <h3>Loan Categories</h3>
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Category Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch all categories
                $query = "SELECT * FROM categories ORDER BY id DESC";
                $result = $conn->query($query);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['id']}</td>
                                <td>{$row['name']}</td>
                                <td>
                                    <a href='edit_category.php?id={$row['id']}' class='btn btn-warning btn-sm'>Edit</a>
                                    <a href='add_categories.php?delete_id={$row['id']}' onclick='return confirm(\"Are you sure you want to delete this category?\");' class='btn btn-danger btn-sm'>Delete</a>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='3' class='text-center'>No categories found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
