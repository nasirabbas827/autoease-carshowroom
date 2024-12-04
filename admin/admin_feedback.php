<?php
session_start();
include('config.php');

// Check if the user is logged in as an admin
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}


// Fetch all feedback from the database
$feedbackQuery = "SELECT f.feedback_id, f.feedback, f.rating, f.timestamp, u.username
                 FROM feedback f
                 JOIN users u ON f.user_id = u.id
                 ORDER BY f.timestamp DESC";
$feedbackResult = mysqli_query($conn, $feedbackQuery);

// Handle feedback deletion
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['delete_feedback']) && is_numeric($_GET['delete_feedback'])) {
    $feedback_id = $_GET['delete_feedback'];

    // Delete the feedback from the database
    $deleteFeedbackQuery = "DELETE FROM feedback WHERE feedback_id = ?";
    $stmt = mysqli_prepare($conn, $deleteFeedbackQuery);
    mysqli_stmt_bind_param($stmt, "i", $feedback_id);
    if (mysqli_stmt_execute($stmt)) {
        header("Location: admin_feedback.php"); // Redirect back to the feedback list page after deleting
        exit;
    } else {
        echo "Error deleting feedback: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel - Feedback</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>
<?php include('admin_navbar.php'); ?>

<div class="container mt-5">
    <h3>User Feedback</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Feedback ID</th>
                <th>User</th>
                <th>Feedback</th>
                <th>Rating</th>
                <th>Timestamp</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($feedbackResult)) { ?>
            <tr>
                <td><?php echo $row['feedback_id']; ?></td>
                <td><?php echo $row['username']; ?></td>
                <td><?php echo $row['feedback']; ?></td>
                <td><?php echo $row['rating']; ?></td>
                <td><?php echo $row['timestamp']; ?></td>
                <td>
                    <a href="admin_feedback.php?delete_feedback=<?php echo $row['feedback_id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this feedback?')">Delete</a>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
