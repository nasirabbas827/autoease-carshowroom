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

// Fetch the user data (username) from the database
$sql = "SELECT username FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows == 1) {
    $stmt->bind_result($username);
    $stmt->fetch();
} else {
    header("location: index.php");
    exit;
}

$stmt->close();

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
$sql_cars = "SELECT * FROM Cars WHERE $where_clause";
$result_cars = $conn->query($sql_cars);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>User Dashboard - Car Rental</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="./css/style.css">

    <style>

        .dashboard-welcome {
            background-color: #ffffff;
            padding: 30px;
            margin-top: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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
        .car-search-container {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
    </style>
</head>

<body>
<?php include('navbar.php'); ?>


    <div class="container mt-4">
        <div class="dashboard-welcome">
            <h2>Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
            <p>Find your perfect ride from our selection of available cars.</p>
        </div>

        <!-- Car Search Form -->
        <div class="car-search-container mt-4">
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

        <!-- Display Cars -->
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

    <!-- Bootstrap JS & dependencies -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>