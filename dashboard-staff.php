<?php
session_start();
include_once 'db/function.php';
$function = new DBFunctions();
$suppliers = $function->select('suppliers', '*');
$notifications = $function->select('notifications', '*');

// Fetch statistics
$totalProducts = $function->count('products');
$lowStocks = $function->count('products', 'stock < 10');
$outOfStocks = $function->count('products', 'stock = 0');
$totalSuppliers = $function->count('suppliers');

// Fetch data for charts
$db = new Database();
$conn = $db->connect();

// Query for total stock per category
$query = "SELECT category, SUM(stock) AS total_stock FROM products GROUP BY category";
$stmt = $conn->prepare($query);
$stmt->execute();
$categoriesData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Query for user types
$query2 = "SELECT type, COUNT(*) AS user_count FROM users GROUP BY type";
$stmt2 = $conn->prepare($query2);
$stmt2->execute();
$userTypesData = $stmt2->fetchAll(PDO::FETCH_ASSOC);

$categories = [];
$stocks = [];
foreach ($categoriesData as $row) {
    $categories[] = $row['category'];
    $stocks[] = $row['total_stock'];
}

$userTypes = [];
$userCounts = [];
foreach ($userTypesData as $row) {
    $userTypes[] = $row['type'];
    $userCounts[] = $row['user_count'];
}
?>
<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "inventotrack");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$supplierQuery = "SELECT id, supplier_name, contact_person, contact_number, email, address, notes FROM suppliers";
$supplierResult = $conn->query($supplierQuery);

$productQuery = "SELECT id, image, name, stock, description, category, supplier, created_at, updated_at FROM products";
$productResult = $conn->query($productQuery);

$lowStockQuery = "SELECT id, image, name, stock, description, category, supplier, created_at, updated_at FROM products WHERE stock > 0 AND stock <= 10";
$lowStockResult = $conn->query($lowStockQuery);

$outOfStockQuery = "SELECT id, image, name, stock, description, category, supplier, created_at, updated_at FROM products WHERE stock = 0";
$outOfStockResult = $conn->query($outOfStockQuery);
// Set timezone
date_default_timezone_set('Asia/Manila');

// Fetch logs for recent activity
$sql1 = "SELECT logs.username, logs.action_type, logs.description, logs.log_timestamp 
        FROM logs 
        JOIN users ON logs.username = users.username OR logs.username = users.full_name
        WHERE logs.module = 'Inventory Management'
        ORDER BY logs.log_timestamp DESC"; 


$result1 = $conn->query($sql1);
if (!$result1) {
    die("Query failed: " . $conn->error);
}


// Function to format logs
function format_logs($result) {
    $logs = [];
    $current_time = new DateTime();

    while ($row = $result->fetch_assoc()) {
        $log_time = new DateTime($row['log_timestamp']);
        $time_diff = $current_time->diff($log_time);

        // Format time difference
        if ($time_diff->days > 0) {
            $time_display = $time_diff->days . " days ago";
        } elseif ($time_diff->h > 0) {
            $time_display = $time_diff->h . " hours ago";
        } elseif ($time_diff->i > 0) {
            $time_display = $time_diff->i . " minutes ago";
        } else {
            $time_display = "Just now";
        }

        $logs[] = [
            "username" => $row['username'],
            "action_type" => $row['action_type'],
            "description" => $row['description'],
            "time_display" => $time_display
        ];
    }
    return $logs;
}

// Store logs in separate arrays
$logs1 = format_logs($result1);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Responsive Staff Dashboard</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Include Chart.js -->
</head>

<body>
    <?php include 'sidebar.php'; ?>
    <div class="content p-4">
        <h2>Welcome to the Staff Dashboard</h2>


        <!-- Statistics Row -->
        <div class="row">

            <!-- Total Products Card -->
            <div class="col-md-3 mb-4">
    <div class="card text-white bg-primary" data-toggle="modal" data-target="#productModal" style="cursor: pointer;">
        <div class="card-body">
            <h5 class="card-title">Total Products</h5>
            <p class="card-text display-4"><?php echo $totalProducts; ?></p>
        </div>
    </div>
</div>
            <!-- Low Stocks Card -->
            <div class="col-md-3 mb-4">
    <div class="card text-white bg-warning" data-toggle="modal" data-target="#lowStockModal" style="cursor: pointer;">
        <div class="card-body">
            <h5 class="card-title">Low Stocks</h5>
            <p class="card-text display-4"><?php echo $lowStocks; ?></p>
        </div>
    </div>
</div>

<div class="col-md-3 mb-4">
    <div class="card text-white bg-danger" data-toggle="modal" data-target="#outOfStockModal" style="cursor: pointer;">
        <div class="card-body">
            <h5 class="card-title">Out of Stocks</h5>
            <p class="card-text display-4"><?php echo $outOfStocks; ?></p>
        </div>
    </div>
</div>


            <!-- Suppliers Card -->
            <div class="col-md-3 mb-4">
    <div class="card text-white bg-success" style="cursor: pointer;" data-toggle="modal" data-target="#supplierModal">
        <div class="card-body">
            <h5 class="card-title">Total Suppliers</h5>
            <p class="card-text display-4">
                <?php echo $supplierResult->num_rows; ?>
            </p>
        </div>
    </div>
</div>

        <!-- Second Row of Cards -->
        <div class="row">
    <!-- Recent Activity Column -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Recent Activity</h5>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    <ul class="list-group">
                        <?php 
                        if (count($logs1) > 0) {
                            foreach ($logs1 as $log) {
                                echo "<li class='list-group-item'>
                                        <strong>{$log['username']}</strong> {$log['action_type']} - {$log['description']} 
                                        <span class='badge badge-secondary float-right'>{$log['time_display']}</span>
                                      </li>";
                            }
                        } else {
                            echo "<li class='list-group-item'>No recent activity</li>";
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Supplier Information Column -->
    <div class="col-md-6 mb-7">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Supplier Information</h5>
                <table id="suppliersTable" class="table table-striped table-bordered table-responsive">
                    <thead>
                        <tr>
                            <th>Supplier Name</th>
                            <th>Contact Person</th>
                            <th>Contact Number</th>
                            <th>Email</th>
                            <th>Address</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($suppliers as $supplier): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($supplier['supplier_name']); ?></td>
                                <td><?php echo htmlspecialchars($supplier['contact_person']); ?></td>
                                <td><?php echo htmlspecialchars($supplier['contact_number']); ?></td>
                                <td><?php echo htmlspecialchars($supplier['email']); ?></td>
                                <td><?php echo htmlspecialchars($supplier['address']); ?></td>
                                <td><?php echo htmlspecialchars($supplier['notes']); ?></td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-secondary dropdown-toggle" type="button"
                                            data-toggle="dropdown">
                                            Actions
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="#" data-toggle="modal"
                                                data-target="#editSupplierModal"
                                                data-supplier-name="<?php echo htmlspecialchars($supplier['supplier_name']); ?>"
                                                data-contact-person="<?php echo htmlspecialchars($supplier['contact_person']); ?>"
                                                data-contact-number="<?php echo htmlspecialchars($supplier['contact_number']); ?>"
                                                data-email="<?php echo htmlspecialchars($supplier['email']); ?>"
                                                data-address="<?php echo htmlspecialchars($supplier['address']); ?>"
                                                data-notes="<?php echo htmlspecialchars($supplier['notes']); ?>"
                                                data-id="<?php echo $supplier['id']; ?>"
                                                onclick="populateEditModal(this)">Edit</a>
                                            <a href="supplier.php?delete=<?php echo $supplier['id'] ?>&table=suppliers"
                                                class="dropdown-item">Delete</a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

        <div class="row" style="height: 5px; !important">
            <div class="col-md-12">
            <canvas id="stockChart" style="width: 100% !important; height: 500px !important;"></canvas> <!-- Chart for category stock -->
            </div>

        </div>


    </div>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#suppliersTable').DataTable();
            $('#notificationsTable').DataTable({
                "pageLength": 5 // Limit the table to 5 rows per page
            });
        });

    </script>

    <script>
        // Data for the Stock Chart
        var stockData = {
            labels: <?php echo json_encode($categories); ?>, // Categories from database
            datasets: [{
                label: 'Total Stock per Category',
                data: <?php echo json_encode($stocks); ?>, // Stock data from database
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        };

        var ctx1 = document.getElementById('stockChart').getContext('2d');
        var stockChart = new Chart(ctx1, {
            type: 'bar',
            data: stockData,
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Data for the User Type Chart
        var userTypeData = {
            labels: <?php echo json_encode($userTypes); ?>, // User types from database
            datasets: [{
                label: 'User Count by Type',
                data: <?php echo json_encode($userCounts); ?>, // User count data from database
                backgroundColor: [
                    'rgba(255, 99, 132, 0.2)',  // Color for Admin
                    'rgba(54, 162, 235, 0.2)',  // Color for Staff
                    'rgba(255, 206, 86, 0.2)',  // Add other colors if needed for additional user types
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',    // Border color for Admin
                    'rgba(54, 162, 235, 1)',    // Border color for Staff
                    'rgba(255, 206, 86, 1)',    // Border color for other types
                ],
                borderWidth: 1
            }]
        };



    </script>
<!-- Supplier Details Modal -->
<div class="modal fade" id="supplierModal" tabindex="-1" role="dialog" aria-labelledby="supplierModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="supplierModalLabel">Supplier Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                
                <h5 class="text-success">Supplier List</h5>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Supplier Name</th>
                            <th>Contact Person</th>
                            <th>Contact Number</th>
                            <th>Email</th>
                            <th>Address</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $supplierResult->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo $row['supplier_name']; ?></td>
                                <td><?php echo $row['contact_person']; ?></td>
                                <td><?php echo $row['contact_number']; ?></td>
                                <td><?php echo $row['email']; ?></td>
                                <td><?php echo $row['address']; ?></td>
                                <td><?php echo $row['notes']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>
<!-- Product Details Modal -->
<div class="modal fade" id="productModal" tabindex="-1" role="dialog" aria-labelledby="productModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productModalLabel">Product Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                
                <h5 class="text-success">Product List</h5>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Stock</th>
                            <th>Description</th>
                            <th>Category</th>
                            <th>Supplier</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $productResult->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><img src="<?php echo $row['image']; ?>" alt="Product Image" width="50"></td>
                                <td><?php echo $row['name']; ?></td>
                                <td><?php echo $row['stock']; ?></td>
                                <td><?php echo $row['description']; ?></td>
                                <td><?php echo $row['category']; ?></td>
                                <td><?php echo $row['supplier']; ?></td>
                                <td><?php echo $row['created_at']; ?></td>
                                <td><?php echo $row['updated_at']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- Low Stocks Modal -->
<div class="modal fade" id="lowStockModal" tabindex="-1" role="dialog" aria-labelledby="lowStockModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="lowStockModalLabel">Low Stock Products</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <h5 class="text-warning">Low Stock Product List</h5>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $lowStockResult->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><img src="<?php echo $row['image']; ?>" alt="Product Image" width="50"></td>
                                <td><?php echo $row['name']; ?></td>
                                <td><?php echo $row['stock']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Out of Stocks Modal -->
<div class="modal fade" id="outOfStockModal" tabindex="-1" role="dialog" aria-labelledby="outOfStockModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="outOfStockModalLabel">Out of Stock Products</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <h5 class="text-danger">Out of Stock Product List</h5>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $outOfStockResult->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><img src="<?php echo $row['image']; ?>" alt="Product Image" width="50"></td>
                                <td><?php echo $row['name']; ?></td>
                                <td><?php echo $row['stock']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php
// Fetch product stock levels
$query = "SELECT id, name, stock FROM products ORDER BY id ASC";
$result = mysqli_query($conn, $query);

$lowStock = [];
$noStock = [];

while ($row = mysqli_fetch_assoc($result)) {
    if ($row['stock'] == 0) {
        $noStock[] = $row['name'];
    } elseif ($row['stock'] < 10) { // Define low stock threshold
        $lowStock[] = $row['name'];
    }
}

mysqli_close($conn);

// Convert arrays to JSON for JavaScript
$lowStockJSON = json_encode($lowStock);
$noStockJSON = json_encode($noStock);
?>

<!-- Bootstrap Modal for Stock Notification -->
<div class="modal fade" id="stockModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg"> <!-- Smaller modal for a notification look -->
        <div class="modal-content">
            <div class="modal-body text-center">
                <h6 class="fw-bold">Stock Alert</h6>
                <hr>
                <div id="modalBody"></div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        let lowStock = <?php echo $lowStockJSON; ?>;
        let noStock = <?php echo $noStockJSON; ?>;
        let modalBody = document.getElementById("modalBody");

        if (noStock.length === 0 && lowStock.length === 0) {
            return; // No need to show the modal if stock is fine
        }

        let message = "";
        if (noStock.length > 0) {
            message += `<div class="alert alert-danger p-1"><strong>Out of Stock:</strong> ${noStock.join(", ")}</div>`;
        }
        if (lowStock.length > 0) {
            message += `<div class="alert alert-warning p-1"><strong>Low Stock:</strong> ${lowStock.join(", ")}<br>You need to replenish!</div>`;
        }

        modalBody.innerHTML = message;

        // Show modal as a notification
        new bootstrap.Modal(document.getElementById("stockModal")).show();
    });
</script>



</body>

</html>