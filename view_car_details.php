<?php
include('config.php');

session_start();

// Check if a valid car ID is provided
if (!isset($_GET['car_id']) || empty($_GET['car_id'])) {
    header("Location: home.php");
    exit;
}

// Fetch car details from the database
$car_id = intval($_GET['car_id']);
$sql = "SELECT Cars.*, categories.name AS category_name 
        FROM Cars 
        LEFT JOIN categories ON Cars.category_id = categories.id 
        WHERE car_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $car_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    // Redirect if car not found
    header("Location: user_dashboard.php?error=Car not found");
    exit;
}

$car = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Car Details - <?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }
        .car-details-container {
            max-width: 900px;
            margin: 50px auto;
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .car-image-container {
            position: relative;
            height: 400px;
            overflow: hidden;
        }
        .car-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .car-title {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
            color: #fff;
            padding: 20px;
        }
        .car-details {
            padding: 30px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e9ecef;
            padding: 15px 0;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: bold;
            color: #495057;
        }
        .detail-value {
            color: #6c757d;
        }
        .btn-container {
            text-align: center;
            padding: 30px;
            background-color: #f8f9fa;
        }
        .btn-container .btn {
            padding: 12px 30px;
            font-size: 1.1rem;
            margin: 0 10px;
            border-radius: 30px;
            transition: all 0.3s ease;
        }
        .btn-container .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        .availability-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: bold;
        }
        .features-list {
            list-style-type: none;
            padding-left: 0;
        }
        .features-list li {
            margin-bottom: 10px;
        }
        .features-list i {
            margin-right: 10px;
            color: #28a745;
        }
    </style>
</head>

<body>
    <?php include('navbar.php'); ?>

    <div class="container">
        <div class="car-details-container">
            <div class="car-image-container">
                <img src="admin/<?php echo htmlspecialchars($car['image_url']); ?>" alt="<?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?>" class="car-image">
                <div class="car-title">
                    <h1 class="display-4"><?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></h1>
                </div>
            </div>
            <div class="car-details">
                <div class="detail-row">
                    <span class="detail-label">Brand:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($car['brand']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Model:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($car['model']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Price:</span>
                    <span class="detail-value">Pkr <?php echo number_format($car['price'], 2); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Category:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($car['category_name']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Availability:</span>
                    <span class="detail-value">
                        <?php if ($car['availability_status'] === 'available'): ?>
                            <span class="availability-badge bg-success text-white">Available</span>
                        <?php else: ?>
                            <span class="availability-badge bg-danger text-white">Unavailable</span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Features:</span>
                    <ul class="features-list">
                        <?php
                        $features = explode("\n", $car['features']);
                        foreach ($features as $feature):
                            if (!empty(trim($feature))):
                        ?>
                            <li><i class="fas fa-check-circle"></i><?php echo htmlspecialchars(trim($feature)); ?></li>
                        <?php
                            endif;
                        endforeach;
                        ?>
                    </ul>
                </div>
            </div>
            <div class="btn-container">
                <a href="home.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Home
                </a>
                <a href="make_order.php?car_id=<?php echo $car_id; ?>" class="btn btn-success">
                    <i class="fas fa-shopping-cart mr-2"></i>Make Order
                </a>
            </div>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>