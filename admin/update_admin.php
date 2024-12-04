<?php
session_start();
include('config.php');

// Check if the user is logged in as an admin
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

$admin_username = $_SESSION["username"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_username = $_POST["new_username"];
    $new_password = $_POST["new_password"];

    $sql_update = "UPDATE admins SET username = ?, password = ? WHERE username = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("sss", $new_username, $new_password, $admin_username);
    $stmt_update->execute();
    $stmt_update->close();
    $success_message = "Profile updated successfully!";
    $_SESSION["username"] = $new_username; // Update the session username
}

$sql_fetch_admin = "SELECT username FROM admins WHERE username = ?";
$stmt_fetch_admin = $conn->prepare($sql_fetch_admin);
$stmt_fetch_admin->bind_param("s", $admin_username);
$stmt_fetch_admin->execute();
$result_fetch_admin = $stmt_fetch_admin->get_result();

if ($result_fetch_admin->num_rows == 1) {
    $row_admin = $result_fetch_admin->fetch_assoc();
    $current_username = $row_admin["username"];
}

$stmt_fetch_admin->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }

        .navbar {
            margin-bottom: 30px;
        }

        .profile-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .profile-container h2 {
            text-align: center;
            font-size: 1.8em;
            margin-bottom: 30px;
        }

        .form-group label {
            font-weight: bold;
        }

        .form-group input {
            border-radius: 5px;
            padding: 12px;
            font-size: 1rem;
        }

        .btn-primary {
            width: 100%;
            padding: 12px;
            font-size: 1.2rem;
        }

        .alert-success {
            margin-bottom: 30px;
        }

        .alert {
            font-size: 1rem;
        }
    </style>
</head>

<body>

<!-- Navbar -->
<?php include('admin_navbar.php'); ?>

<!-- Profile Form -->
<div class="container">
    <div class="profile-container">
        <h2>Admin Profile</h2>

        <!-- Display success message if profile is updated -->
        <?php if (isset($success_message)) { ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php } ?>

        <!-- Profile update form -->
        <form method="post">
            <div class="form-group">
                <label for="new_username">New Username:</label>
                <input type="text" class="form-control" id="new_username" name="new_username" value="<?php echo $current_username; ?>" required>
            </div>
            <div class="form-group">
                <label for="new_password">New Password:</label>
                <input type="password" class="form-control" id="new_password" name="new_password" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Profile</button>
        </form>
    </div>
</div>

<!-- Bootstrap JS & dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
