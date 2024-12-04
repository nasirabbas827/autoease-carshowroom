<?php
include('config.php');

session_start();
// Fetch all categories for search dropdown
$sql_categories = "SELECT id, name FROM categories";
$result_categories = $conn->query($sql_categories);

// Handle car search
$where_clause = "availability_status = 'available'"; // Default: show only available cars
if ($_SERVER["REQUEST_METHOD"] == "GET" && (isset($_GET["search_query"]) || isset($_GET["category_id"]))) {
    $search_query = $_GET["search_query"] ?? '';
    $category_id = $_GET["category_id"] ?? '';

    if (!empty($search_query)) {
        $where_clause .= " AND (brand LIKE '%" . $conn->real_escape_string($search_query) . "%' OR model LIKE '%" . $conn->real_escape_string($search_query) . "%')";
    }
    if (!empty($category_id)) {
        $where_clause .= " AND category_id = " . intval($category_id);
    }
}

// Fetch cars based on search criteria
$sql_cars = "SELECT * FROM Cars WHERE $where_clause LIMIT 6"; // Limit to 6 cars for the landing page
$result_cars = $conn->query($sql_cars);

// Handle order tracking
$order_details = null;
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["track_order_id"])) {
    $order_id = intval($_POST["track_order_id"]);
    $sql_order = "SELECT * FROM orders WHERE order_id = ?";
    $stmt = $conn->prepare($sql_order);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result_order = $stmt->get_result();
    if ($result_order->num_rows > 0) {
        $order_details = $result_order->fetch_assoc();
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Car Showroom</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="./css/style.css">

    <style>

        .jumbotron {
            height: 500px;
            background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('./images/hotel.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            margin-bottom: 0;
        }
        .jumbotron h1 {
            font-size: 3.5rem;
            font-weight: bold;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        .jumbotron p {
            font-size: 1.5rem;
            margin-bottom: 30px;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            padding: 10px 20px;
            font-size: 1.2rem;
            transition: all 0.3s;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
            transform: translateY(-2px);
        }
        .car-search-container {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-top: -50px;
            position: relative;
            z-index: 10;
        }
        .car-card {
            background-color: #ffffff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .car-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        .car-card img {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }
        .car-card .card-body {
            padding: 1.25rem;
        }
        .car-card .btn {
            width: 48%;
            border-radius: 20px;
        }
        .section-title {
            text-align: center;
            margin-bottom: 40px;
            font-size: 2.5rem;
            font-weight: bold;
            color: #333;
        }
        .track-order-container {
            background-color: #f8f9fa;
            padding: 40px 0;
        }
        footer {
            background-color: #333;
            color: white;
            padding: 20px 0;
        }
    </style>
</head>
<body>

<?php include('navbar.php'); ?>

<div class="jumbotron text-center">
    <h1>Welcome to Car Showroom</h1>
    <p>Find your perfect ride from our selection of premium cars.</p>
    <a href="login.php" class="btn btn-primary btn-lg">Login to Explore</a>
</div>

<div class="container">
    <div class="car-search-container">
        <form method="get" class="form-inline justify-content-center">
            <div class="form-group mr-2 mb-2">
                <input type="text" class="form-control" name="search_query" placeholder="Search by brand or model" value="<?php echo htmlspecialchars($_GET['search_query'] ?? ''); ?>">
            </div>
            <div class="form-group mr-2 mb-2">
                <select name="category_id" class="form-control">
                    <option value="">All Categories</option>
                    <?php while ($row = $result_categories->fetch_assoc()) { ?>
                        <option value="<?php echo $row['id']; ?>" <?php if ((int)($_GET['category_id'] ?? '') == $row['id']) echo "selected"; ?>>
                            <?php echo htmlspecialchars($row['name']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary mb-2">
                <i class="fas fa-search"></i> Search
            </button>
        </form>
    </div>
</div>

<div class="container mt-5">
    <h2 class="section-title">Featured Cars</h2>
    <div class="row">
        <?php if ($result_cars->num_rows > 0) { ?>
            <?php while ($car = $result_cars->fetch_assoc()) { ?>
                <div class="col-md-4 mb-4">
                    <div class="car-card">
                        <img src="admin/<?php echo htmlspecialchars($car['image_url']); ?>" alt="<?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?>" class="card-img-top">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></h5>
                            <p class="card-text">
                                <strong>Price:</strong> Pkr <?php echo number_format($car['price'], 2); ?><br>
                            </p>
                            <div class="d-flex justify-content-between mt-3">
                                <a href="view_car_details.php?car_id=<?php echo $car['car_id']; ?>" class="btn btn-info">
                                    <i class="fas fa-info-circle"></i> Details
                                </a>
                                <a href="make_order.php?car_id=<?php echo $car['car_id']; ?>" class="btn btn-success">
                                    <i class="fas fa-car"></i> Order Now
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        <?php } else { ?>
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No available cars found matching your search criteria.
                </div>
            </div>
        <?php } ?>
    </div>
</div>

<div class="track-order-container">
    <div class="container">
        <h2 class="section-title">Track Your Order</h2>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <form method="post" class="card p-4">
                    <div class="form-group">
                        <label for="track_order_id">Order ID:</label>
                        <input type="number" class="form-control" id="track_order_id" name="track_order_id" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Track Order</button>
                </form>

                <?php if ($order_details): ?>
                    <div class="mt-4">
                        <h4>Order Details</h4>
                        <p><strong>Order ID:</strong> <?php echo $order_details['order_id']; ?></p>
                        <p><strong>Delivery Status:</strong> <?php echo $order_details['delivery_status']; ?></p>
                        <p><strong>Order Status:</strong> <?php echo $order_details['order_status']; ?></p>
                        <p><strong>Total Price:</strong> Pkr <?php echo number_format($order_details['total_price'], 2); ?></p>
                    </div>
                <?php elseif ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
                    <div class="alert alert-warning mt-4">
                        No order found with the provided ID.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<footer class="mt-5">
    <div class="container text-center">
        <p>&copy; 2024 Car Showroom. All rights reserved.</p>
    </div>
</footer>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>