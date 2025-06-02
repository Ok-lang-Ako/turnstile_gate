<?php
// Database connection
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'qr_scanner';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'delete') {
            $id = $_POST['delete_id'];
            $stmt = $conn->prepare("DELETE FROM authorized_codes WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $success_message = "Record deleted successfully";
            } else {
                $error_message = "Error deleting record: " . $stmt->error;
            }
            $stmt->close();
        } elseif ($_POST['action'] == 'edit') {
            $id = $_POST['edit_id'];
            $name = $_POST['name'];
            $id_number = $_POST['id_number'];
            $contact = $_POST['contact'];
            $qr_hash = $id_number; // QR hash is same as ID number
            
            // Handle file upload for edit
            $photo_sql = "";
            $photo_param = "";
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
                $target_dir = "photos/";
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                
                $file_extension = strtolower(pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION));
                $new_filename = $id_number . '.' . $file_extension;
                $target_file = $target_dir . $new_filename;
                
                if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
                    $photo_sql = ", photo = ?";
                    $photo_param = $target_file;
                }
            }
            
            $sql = "UPDATE authorized_codes SET name = ?, id_number = ?, qr_hash = ?, Contact = ?" . $photo_sql . " WHERE id = ?";
            $stmt = $conn->prepare($sql);
            
            if ($photo_param) {
                $stmt->bind_param("ssssi", $name, $id_number, $qr_hash, $contact, $id);
            } else {
                $stmt->bind_param("sssss", $name, $id_number, $qr_hash, $contact, $photo_param, $id);
            }
            
            if ($stmt->execute()) {
                $success_message = "Record updated successfully";
            } else {
                $error_message = "Error updating record: " . $stmt->error;
            }
            $stmt->close();
        } else {
            // Handle new record
            $name = $_POST['name'];
            $id_number = $_POST['id_number'];
            $contact = $_POST['contact'];
            $qr_hash = $id_number; // QR hash is same as ID number
            
            // Handle file upload
            $photo = '';
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
                $target_dir = "photos/";
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                
                $file_extension = strtolower(pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION));
                $new_filename = $id_number . '.' . $file_extension;
                $target_file = $target_dir . $new_filename;
                
                if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
                    $photo = $target_file;
                }
            }
            
            $stmt = $conn->prepare("INSERT INTO authorized_codes (qr_hash, name, id_number, photo, Contact) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $qr_hash, $name, $id_number, $photo, $contact);
            
            if ($stmt->execute()) {
                $success_message = "Record added successfully";
            } else {
                $error_message = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Fetch existing records
$result = $conn->query("SELECT * FROM authorized_codes ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - QR Access System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .user-photo {
            max-width: 100px;
            max-height: 100px;
            object-fit: cover;
        }
        body {
            background-color: #212529;
            color: #fff;
        }
        .card {
            background-color: #2c3034;
            border-color: #198754;
        }
        .table {
            color: #fff;
        }
        .modal-content {
            background-color: #2c3034;
            color: #fff;
        }
        .form-control {
            background-color: #343a40;
            border-color: #495057;
            color: #fff;
        }
        .form-control:focus {
            background-color: #343a40;
            border-color: #198754;
            color: #fff;
        }
        .form-control::placeholder {
            color: #6c757d;
        }
        .modal-header {
            border-bottom-color: #198754;
        }
        .modal-footer {
            border-top-color: #198754;
        }
        .table-hover tbody tr:hover {
            color: #fff;
            background-color: rgba(25, 135, 84, 0.1);
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-success mb-0">Manage Users</h2>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="bi bi-person-plus"></i> Add New User
            </button>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Photo</th>
                                <th>Name</th>
                                <th>ID Number</th>
                                <th>Contact</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php if ($row['photo']): ?>
                                        <img src="<?php echo htmlspecialchars($row['photo']); ?>" 
                                             class="user-photo rounded" 
                                             alt="User photo"
                                             onerror="this.src='photos/default.jpg'">
                                    <?php else: ?>
                                        <img src="photos/default.jpg" class="user-photo rounded" alt="Default photo">
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['id_number']); ?></td>
                                <td><?php echo htmlspecialchars($row['Contact']); ?></td>
                                <td><?php echo date('M d, Y h:i A', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-success me-1" 
                                            onclick="editUser(<?php echo htmlspecialchars(json_encode($row)); ?>)"
                                            title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" 
                                            onclick="deleteUser(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['name']); ?>')"
                                            title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-success">Add New User</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="id_number" class="form-label">ID Number</label>
                            <input type="text" class="form-control" id="id_number" name="id_number" required>
                            <div class="form-text text-success">This will also be used as the QR code value.</div>
                        </div>
                        <div class="mb-3">
                            <label for="contact" class="form-label">Contact Number</label>
                            <input type="text" class="form-control" id="contact" name="contact" required>
                        </div>
                        <div class="mb-3">
                            <label for="photo" class="form-label">Photo</label>
                            <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Save User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-success">Edit User</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="edit_id" id="edit_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_id_number" class="form-label">ID Number</label>
                            <input type="text" class="form-control" id="edit_id_number" name="id_number" required>
                            <div class="form-text text-success">This will also be used as the QR code value.</div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_contact" class="form-label">Contact Number</label>
                            <input type="text" class="form-control" id="edit_contact" name="contact" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_photo" class="form-label">Photo</label>
                            <input type="file" class="form-control" id="edit_photo" name="photo" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Update User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">Confirm Delete</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="delete_id" id="delete_id">
                    <div class="modal-body">
                        <p>Are you sure you want to delete user: <span id="delete_user_name" class="fw-bold"></span>?</p>
                        <p class="text-danger mb-0">This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editUser(user) {
            document.getElementById('edit_id').value = user.id;
            document.getElementById('edit_name').value = user.name;
            document.getElementById('edit_id_number').value = user.id_number;
            document.getElementById('edit_contact').value = user.Contact;
            new bootstrap.Modal(document.getElementById('editUserModal')).show();
        }

        function deleteUser(id, name) {
            document.getElementById('delete_id').value = id;
            document.getElementById('delete_user_name').textContent = name;
            new bootstrap.Modal(document.getElementById('deleteUserModal')).show();
        }
    </script>
</body>
</html> 