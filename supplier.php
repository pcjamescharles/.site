<?php
session_start();
include_once 'db/function.php';
$function = new DBFunctions();
$suppliers = $function->select('suppliers', '*');

// Database connection for logs
$mysqli = new mysqli("localhost", "root", "", "inventotrack");

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Fetch user session values from the sidebar
$userId = $_SESSION['userid'] ?? null;
$username = $_SESSION['name'] ?? 'Unknown';

$userDepartment = '';

// Ensure user ID is valid before querying
if ($userId !== null) {
    $stmt = $mysqli->prepare("SELECT department FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($userDepartment);
    $stmt->fetch();
    $stmt->close();
}

// Check if user is a staff member
$isStaff = (strpos(strtolower($userDepartment), 'staff') !== false);
// Ensure user ID is valid before inserting logs
function insertLog($mysqli, $userId, $username, $actionType, $module, $description) {
    if ($userId === null) {
        return; // Avoid inserting logs with an invalid user_id
    }

    $stmt = $mysqli->prepare("INSERT INTO logs (user_id, username, action_type, module, description, log_timestamp) VALUES (?, ?, ?, ?, ?, NOW())");
    
    if (!$stmt) {
        die("Prepare failed: " . $mysqli->error); // Debugging
    }

    $stmt->bind_param("issss", $userId, $username, $actionType, $module, $description);

    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error); // Debugging
    }

    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'addsupplier') {
        $data = [
            'supplier_name'   => $_POST['supplierName'],
            'contact_person'  => $_POST['contactPerson'],
            'contact_number'  => $_POST['contactNumber'],
            'email'           => $_POST['email'],
            'address'         => $_POST['address'],
            'store_name'      => $_POST['storeName'], // New column
            'category'        => $_POST['category'],  // New column
            'notes'           => $_POST['notes']
        ];
    
        if ($function->insert('suppliers', $data)) {
            insertLog($mysqli, $userId, $username, 'INSERT', 'Suppliers', 'Added supplier: ' . $_POST['supplierName']);
            echo "<script>alert('Supplier added successfully'); window.location.href = 'supplier.php';</script>";
        } else {
            echo "Failed to add supplier.";
        }
    
    
    }elseif ($action === 'editsupplier') {
        $supplierId = $_POST['supplierId'];
        $data = [
            'supplier_name'   => $_POST['supplierName'],
            'contact_person'  => $_POST['contactPerson'],
            'contact_number'  => $_POST['contactNumber'],
            'email'           => $_POST['email'],
            'address'         => $_POST['address'],
            'store_name'      => $_POST['storeName'], // New column
            'category'        => $_POST['category'],  // New column
            'notes'           => $_POST['notes']
        ];
    
        $conditions = ['id' => $supplierId];
    
        if ($function->update('suppliers', $data, $conditions)) {
            insertLog($mysqli, $userId, $username, 'UPDATE', 'Suppliers', 'Updated supplier ID: ' . $supplierId);
            echo "<script>alert('Supplier updated successfully'); window.location.href = 'supplier.php';</script>";
        } else {
            echo "Failed to update supplier.";
        }
    }
}    

$mysqli->close();
?>




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suppliers - Admin Dashboard</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
</head>

<body>

    <?php include 'sidebar.php'; ?>

    <div class="content p-4">
        <h2>Suppliers</h2>
        <?php if (!$isStaff): ?>
    <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addSupplierModal">Add Supplier</button>
<?php endif; ?>

        <button id="generateReport" class="btn btn-success mb-3">Generate Report</button>

        <script>
            document.getElementById('generateReport').addEventListener('click', function () {
                var table = document.getElementById('suppliersTable');
                var newWindow = window.open('', '', 'width=800,height=600');

                // Remove the last column (Action column) from the table
                var rows = table.getElementsByTagName('tr');
                for (var i = 0; i < rows.length; i++) {
                    var cells = rows[i].getElementsByTagName('td');
                    if (cells.length > 0) {
                        rows[i].deleteCell(cells.length - 1); // Remove the last td
                    }
                    var headers = rows[i].getElementsByTagName('th');
                    if (headers.length > 0) {
                        rows[i].deleteCell(headers.length - 1); // Remove the last th (header)
                    }
                }

                // Adding CSS for landscape orientation and table borders
                newWindow.document.write('<html><head><title>Report</title>');
                newWindow.document.write('<style>');
                newWindow.document.write('body { font-family: Arial, sans-serif; }');
                newWindow.document.write('table { width: 100%; border-collapse: collapse; border: 1px solid #ddd; }');
                newWindow.document.write('th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }');
                newWindow.document.write('th { background-color: #f2f2f2; }');
                newWindow.document.write('@media print {');
                newWindow.document.write('@page { size: landscape; }'); // Landscape orientation
                newWindow.document.write('body { margin: 0; }'); // Remove any default margin
                newWindow.document.write('}'); // End print media query
                newWindow.document.write('</style>');
                newWindow.document.write('</head><body>');

                newWindow.document.write('<h2>Supplier Report</h2>');
                newWindow.document.write(table.outerHTML); // Copy the table's HTML content
                newWindow.document.write('</body></html>');

                newWindow.document.close();
                newWindow.print(); // Trigger print dialog
            });
        </script>


        <table id="suppliersTable" class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Supplier Name</th>
                    <th>Contact Person</th>
                    <th>Contact Number</th>
                    <th>Email</th>
                    <th>Address</th>
                    <th>Store name</th>
                    <th>Category</th>
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
                        <td><?php echo htmlspecialchars($supplier['store_name']); ?></td>
                        <td><?php echo htmlspecialchars($supplier['category']); ?></td>
                        <td><?php echo htmlspecialchars($supplier['notes']); ?></td>
                        <td>
    <?php if (!$isStaff): ?>
        <div class="dropdown">
            <button class="btn btn-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                Actions
            </button>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#editSupplierModal"
                    data-supplier-name="<?php echo htmlspecialchars($supplier['supplier_name']); ?>"
                    data-contact-person="<?php echo htmlspecialchars($supplier['contact_person']); ?>"
                    data-contact-number="<?php echo htmlspecialchars($supplier['contact_number']); ?>"
                    data-email="<?php echo htmlspecialchars($supplier['email']); ?>"
                    data-address="<?php echo htmlspecialchars($supplier['address']); ?>"
                    data-store-name="<?php echo htmlspecialchars($supplier['store_name']); ?>"  
                    data-category="<?php echo htmlspecialchars($supplier['category']); ?>"
                    data-notes="<?php echo htmlspecialchars($supplier['notes']); ?>"
                    data-id="<?php echo $supplier['id']; ?>" onclick="populateEditModal(this)">Edit</a>
                <a href="supplier.php?delete=<?php echo $supplier['id'] ?>&table=suppliers"
                    class="dropdown-item">Delete</a>
            </div>
        </div>
    <?php endif; ?>
</td>

                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="modal fade" id="editSupplierModal" tabindex="-1" aria-labelledby="editSupplierModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editSupplierModalLabel">Edit Supplier</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="supplier.php" method="POST">
                    <input type="hidden" name="action" value="editsupplier">
                    <input type="hidden" name="supplierId" id="editSupplierId">

                    <div class="form-group">
                        <label for="editSupplierName">Supplier Name</label>
                        <input type="text" class="form-control" id="editSupplierName" name="supplierName" required>
                    </div>
                    <div class="form-group">
                        <label for="editContactPerson">Contact Person</label>
                        <input type="text" class="form-control" id="editContactPerson" name="contactPerson" required>
                    </div>
                    <div class="form-group">
                        <label for="editContactNumber">Contact Number</label>
                        <input type="text" class="form-control" id="editContactNumber" name="contactNumber" required>
                    </div>
                    <div class="form-group">
                        <label for="editEmail">Email</label>
                        <input type="email" class="form-control" id="editEmail" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="editAddress">Address</label>
                        <textarea class="form-control" id="editAddress" name="address" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="editStoreName">Store Name</label>
                        <input type="text" class="form-control" id="editStoreName" name="storeName" required>
                    </div>
                    <div class="form-group">
                        <label for="editCategory">Category</label>
                        <input type="text" class="form-control" id="editCategory" name="category" required>
                    </div>
                    <div class="form-group">
                        <label for="editNotes">Notes</label>
                        <textarea class="form-control" id="editNotes" name="notes"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>



<script>
    function populateEditModal(element) {
        // Populate modal fields with data from the selected row
        document.getElementById('editSupplierId').value = element.getAttribute('data-id');
        document.getElementById('editSupplierName').value = element.getAttribute('data-supplier-name');
        document.getElementById('editContactPerson').value = element.getAttribute('data-contact-person');
        document.getElementById('editContactNumber').value = element.getAttribute('data-contact-number');
        document.getElementById('editEmail').value = element.getAttribute('data-email');
        document.getElementById('editAddress').value = element.getAttribute('data-address');
        document.getElementById('editStoreName').value = element.getAttribute('data-store-name'); 
        document.getElementById('editCategory').value = element.getAttribute('data-category');
        document.getElementById('editNotes').value = element.getAttribute('data-notes');
    }
</script>

    </div>

    <!-- Add Supplier Modal -->


    <div class="modal fade" id="addSupplierModal" tabindex="-1" aria-labelledby="addSupplierModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addSupplierModalLabel">Add Supplier</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="supplier.php" method="POST">
                    <input type="hidden" name="action" value="addsupplier">

                    <div class="form-group">
                        <label for="supplierName">Supplier Name</label>
                        <input type="text" class="form-control" id="supplierName" name="supplierName" required>
                    </div>
                    <div class="form-group">
                        <label for="contactPerson">Contact Person</label>
                        <input type="text" class="form-control" id="contactPerson" name="contactPerson" required>
                    </div>
                    <div class="form-group">
                        <label for="contactNumber">Contact Number</label>
                        <input type="text" class="form-control" id="contactNumber" name="contactNumber" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea class="form-control" id="address" name="address" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="storeName">Store Name</label>
                        <input type="text" class="form-control" id="storeName" name="storeName" required>
                    </div>
                    <div class="form-group">
                        <label for="category">Category</label>
                        <input type="text" class="form-control" id="category" name="category" required>
                    </div>
                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea class="form-control" id="notes" name="notes"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Supplier</button>
                </form>
            </div>
        </div>
    </div>
</div>





    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#suppliersTable').DataTable({
                "responsive": true,
                "autoWidth": false,
                "pageLength": 5
            });
        });
    </script>
</body>

</html>