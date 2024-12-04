<?php
include('config.php');

session_start();

// Check if user is logged in, if not, redirect to login page
if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: index.php");
    exit;
}

// Get the user ID from the session
$user_id = $_SESSION["id"];

// Fetch user details from the database
$sql = "SELECT id, username, email, age, full_name, bio, bank_details FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $fetched_id, $username, $email, $age, $full_name, $bio, $bank_details);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

// Update user profile
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newUsername = $_POST["username"];
    $newEmail = $_POST["email"];
    $newAge = $_POST["age"];
    $newFullName = $_POST["full_name"];
    $newBio = $_POST["bio"];
    $newBankDetails = $_POST["bank_details"];

    // Update user details in the database
    $update_query = "UPDATE users 
                     SET username = ?, email = ?, age = ?, full_name = ?, bio = ?, bank_details = ? 
                     WHERE id = ?";
    
    $update_stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($update_stmt, "ssssssi", $newUsername, $newEmail, $newAge, $newFullName, $newBio, $newBankDetails, $user_id);
    
    if (mysqli_stmt_execute($update_stmt)) {
        $success_message = "Profile updated successfully!";
        // Update session data if needed
        $_SESSION["username"] = $newUsername;
    } else {
        $error_message = "Error updating profile: " . mysqli_error($conn);
    }

    mysqli_stmt_close($update_stmt);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Update Profile</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
    <style>
        /* Custom styles for the profile update page */
        body {
            background-color: #f4f6f9;
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 800px;
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-top: 50px;
        }

        h2 {
            color: #007bff;
            font-size: 2rem;
            margin-bottom: 30px;
        }

        .form-group label {
            font-weight: bold;
        }

        .form-control {
            border-radius: 8px;
            box-shadow: none;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }

        button {
            border-radius: 8px;
            transition: all 0.3s ease;
            padding: 10px 20px;
        }

        button:hover {
            background-color: #0056b3;
        }

        .alert {
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <?php include('navbar.php'); ?>

    <div class="container">
        <h2>Update Profile</h2>

        <?php if (isset($success_message)) { ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php } ?>

        <?php if (isset($error_message)) { ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php } ?>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" class="form-control" name="username" value="<?php echo $username; ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" class="form-control" name="email" value="<?php echo $email; ?>" required>
            </div>
            <div class="form-group">
                <label for="age">Age:</label>
                <input type="number" class="form-control" name="age" value="<?php echo $age; ?>" required>
            </div>
            <div class="form-group">
                <label for="full_name">Full Name:</label>
                <input type="text" class="form-control" name="full_name" value="<?php echo $full_name; ?>" required>
            </div>
            <div class="form-group">
                <label for="bio">Bio:</label>
                <textarea class="form-control" name="bio" rows="4"><?php echo $bio; ?></textarea>
            </div>
            <div class="form-group">
                <label for="bank_details">Bank Details:</label>
                <input type="text" class="form-control" name="bank_details" value="<?php echo $bank_details; ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Profile</button>
        </form>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
