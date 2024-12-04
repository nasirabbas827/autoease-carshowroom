<?php
session_start();
include('config.php');

// Check if the user is logged in as an admin
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Fetch data for the dashboard
// Example: Fetch total orders, total revenue, and order statuses
$total_orders_query = "SELECT COUNT(*) AS total_orders FROM orders";
$total_orders_result = $conn->query($total_orders_query);
$total_orders = $total_orders_result->fetch_assoc()['total_orders'];

$total_revenue_query = "SELECT SUM(total_price) AS total_revenue FROM orders WHERE order_status = 'completed'";
$total_revenue_result = $conn->query($total_revenue_query);
$total_revenue = $total_revenue_result->fetch_assoc()['total_revenue'];

$pending_orders_query = "SELECT COUNT(*) AS pending_orders FROM orders WHERE delivery_status = 'pending'";
$pending_orders_result = $conn->query($pending_orders_query);
$pending_orders = $pending_orders_result->fetch_assoc()['pending_orders'];

$completed_orders_query = "SELECT COUNT(*) AS completed_orders FROM orders WHERE order_status = 'Completed'";
$completed_orders_result = $conn->query($completed_orders_query);
$completed_orders = $completed_orders_result->fetch_assoc()['completed_orders'];

// Fetch order status for chart (e.g., pie chart for order status distribution)
$order_status_query = "SELECT order_status, COUNT(*) AS count FROM orders GROUP BY order_status";
$order_status_result = $conn->query($order_status_query);
$order_status_data = [];
while ($row = $order_status_result->fetch_assoc()) {
    $order_status_data[] = $row;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Dashboard</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<?php include('admin_navbar.php'); ?>

<div class="container mt-5">
    <h1>Admin Dashboard</h1>

    <div class="row mt-4">
        <!-- Total Orders Card -->
        <div class="col-md-3">
            <div class="card text-white bg-primary mb-3">
                <div class="card-header">Total Orders</div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo $total_orders; ?></h5>
                </div>
            </div>
        </div>

        <!-- Total Revenue Card -->
        <div class="col-md-3">
            <div class="card text-white bg-success mb-3">
                <div class="card-header">Total Revenue (PKR)</div>
                <div class="card-body">
                    <h5 class="card-title">PKR <?php echo number_format($total_revenue, 2); ?></h5>
                </div>
            </div>
        </div>

        <!-- Pending Orders Card -->
        <div class="col-md-3">
            <div class="card text-white bg-warning mb-3">
                <div class="card-header">Pending Orders</div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo $pending_orders; ?></h5>
                </div>
            </div>
        </div>

        <!-- Completed Orders Card -->
        <div class="col-md-3">
            <div class="card text-white bg-info mb-3">
                <div class="card-header">Completed Orders</div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo $completed_orders; ?></h5>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row mt-4">
        <div class="col-md-6">
            <h4>Order Status Distribution</h4>
            <canvas id="orderStatusChart"></canvas>
        </div>

        <div class="col-md-6">
            <h4>Orders Over Time</h4>
            <canvas id="ordersOverTimeChart"></canvas>
        </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
// Order Status Distribution Chart (Pie Chart)
var ctx1 = document.getElementById('orderStatusChart').getContext('2d');
var orderStatusData = {
    labels: <?php echo json_encode(array_column($order_status_data, 'order_status')); ?>,
    datasets: [{
        data: <?php echo json_encode(array_column($order_status_data, 'count')); ?>,
        backgroundColor: ['#ff5733', '#33ff57', '#3357ff', '#f0ad4e'],
    }]
};

var orderStatusChart = new Chart(ctx1, {
    type: 'pie',
    data: orderStatusData,
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            },
            tooltip: {
                callbacks: {
                    label: function(tooltipItem) {
                        return tooltipItem.label + ': ' + tooltipItem.raw;
                    }
                }
            }
        }
    }
});

// Orders Over Time (Line Chart)
var ctx2 = document.getElementById('ordersOverTimeChart').getContext('2d');
// Fetching orders over time data (for example by month)
var ordersOverTimeData = {
    labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July'], // Placeholder for months
    datasets: [{
        label: 'Orders',
        data: [120, 150, 200, 180, 250, 300, 320], // Placeholder for order data
        borderColor: '#007bff',
        backgroundColor: 'rgba(0, 123, 255, 0.2)',
        fill: true,
    }]
};

var ordersOverTimeChart = new Chart(ctx2, {
    type: 'line',
    data: ordersOverTimeData,
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            },
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>

</body>
</html>
