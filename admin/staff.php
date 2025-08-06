<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include 'config_admin.php';

// Xử lý thêm nhân viên
if (isset($_POST['addstaff'])) {
    $staffname = $_POST['staffname'];
    $staffwork = $_POST['staffwork'];

    if (!empty($staffname) && !empty($staffwork)) {
        $sql = "INSERT INTO staff(name,work) VALUES ('$staffname', '$staffwork')";
        $result = mysqli_query($conn, $sql);

        if ($result) {
            header("Location: staff.php?success=added");
            exit();
        } else {
            $error_message = "Lỗi khi thêm nhân viên!";
        }
    } else {
        $warning_message = "Vui lòng điền đầy đủ thông tin!";
    }
}

// Xử lý cập nhật nhân viên
if (isset($_POST['updatestaff'])) {
    $staff_id = $_POST['staff_id'];
    $staffname = $_POST['staffname'];
    $staffwork = $_POST['staffwork'];

    if (!empty($staffname) && !empty($staffwork)) {
        $sql = "UPDATE staff SET name='$staffname', work='$staffwork' WHERE id=$staff_id";
        $result = mysqli_query($conn, $sql);

        if ($result) {
            header("Location: staff.php?success=updated");
            exit();
        } else {
            $error_message = "Lỗi khi cập nhật nhân viên!";
        }
    } else {
        $warning_message = "Vui lòng điền đầy đủ thông tin!";
    }
}

// Xử lý xóa nhân viên
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_sql = "DELETE FROM staff WHERE id = $delete_id";
    $delete_result = mysqli_query($conn, $delete_sql);
    
    if ($delete_result) {
        header("Location: staff.php?success=deleted");
        exit();
    } else {
        header("Location: staff.php?error=delete_failed");
        exit();
    }
}

// Lấy thông tin nhân viên để edit
$edit_staff = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $edit_sql = "SELECT * FROM staff WHERE id = $edit_id";
    $edit_result = mysqli_query($conn, $edit_sql);
    if ($edit_result && mysqli_num_rows($edit_result) > 0) {
        $edit_staff = mysqli_fetch_array($edit_result);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BlueBird - Admin</title>
    <!-- fontowesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- boot -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .container {
            padding: 30px;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }
        
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
        }
        
        .btn-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            color: white;
            padding: 12px 25px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .table {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .table thead th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px;
        }
        
        .table tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        .alert {
            border-radius: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Thông báo -->
        <?php if (isset($_GET['success']) && $_GET['success'] == 'added'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Thành công!</strong> Đã thêm nhân viên mới.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['success']) && $_GET['success'] == 'deleted'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Thành công!</strong> Đã xóa nhân viên.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['success']) && $_GET['success'] == 'updated'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Thành công!</strong> Đã cập nhật nhân viên.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error']) && $_GET['error'] == 'delete_failed'): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Lỗi!</strong> Không thể xóa nhân viên.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Form thêm/cập nhật nhân viên -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <?php if ($edit_staff): ?>
                        <i class="fas fa-edit"></i> Cập nhật nhân viên
                    <?php else: ?>
                        <i class="fas fa-user-plus"></i> Thêm nhân viên mới
                    <?php endif; ?>
                </h5>
            </div>
            <div class="card-body">
                <form action="" method="POST">
                    <?php if ($edit_staff): ?>
                        <input type="hidden" name="staff_id" value="<?php echo $edit_staff['id']; ?>">
                    <?php endif; ?>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="staffname" class="form-label">Tên nhân viên:</label>
                                <input type="text" name="staffname" class="form-control" value="<?php echo $edit_staff ? $edit_staff['name'] : ''; ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="staffwork" class="form-label">Công việc:</label>
                                <select name="staffwork" class="form-control" required>
                                    <option value="">Chọn công việc</option>
                                    <option value="Manager" <?php echo ($edit_staff && $edit_staff['work'] == 'Manager') ? 'selected' : ''; ?>>Manager</option>
                                    <option value="Cook" <?php echo ($edit_staff && $edit_staff['work'] == 'Cook') ? 'selected' : ''; ?>>Cook</option>
                                    <option value="Helper" <?php echo ($edit_staff && $edit_staff['work'] == 'Helper') ? 'selected' : ''; ?>>Helper</option>
                                    <option value="cleaner" <?php echo ($edit_staff && $edit_staff['work'] == 'cleaner') ? 'selected' : ''; ?>>Cleaner</option>
                                    <option value="weighter" <?php echo ($edit_staff && $edit_staff['work'] == 'weighter') ? 'selected' : ''; ?>>Weighter</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <?php if ($edit_staff): ?>
                            <button type="submit" class="btn btn-custom" name="updatestaff">
                                <i class="fas fa-save"></i> Cập nhật nhân viên
                            </button>
                            <a href="staff.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Hủy
                            </a>
                        <?php else: ?>
                            <button type="submit" class="btn btn-custom" name="addstaff">
                                <i class="fas fa-plus"></i> Thêm nhân viên
                            </button>
                        <?php endif; ?>
                    </div>
                </form>

                <?php
                // Hiển thị thông báo lỗi
                if (isset($error_message)) {
                    echo "<div class='alert alert-danger mt-3'>$error_message</div>";
                }
                
                // Hiển thị thông báo cảnh báo
                if (isset($warning_message)) {
                    echo "<div class='alert alert-warning mt-3'>$warning_message</div>";
                }
                ?>
            </div>
        </div>

        <!-- Bảng danh sách nhân viên -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-users"></i> Danh sách nhân viên</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tên nhân viên</th>
                                <th>Công việc</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT * FROM staff ORDER BY id DESC";
                            $result = mysqli_query($conn, $sql);
                            
                            if (mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_array($result)) {
                                    echo "<tr>";
                                    echo "<td>" . $row['id'] . "</td>";
                                    echo "<td><strong>" . $row['name'] . "</strong></td>";
                                    echo "<td>" . $row['work'] . "</td>";
                                    echo "<td>";
                                    echo "<div class='btn-group' role='group'>";
                                    echo "<a href='staff.php?edit_id=" . $row['id'] . "' class='btn btn-primary btn-sm me-1'>";
                                    echo "<i class='fas fa-edit'></i> Sửa";
                                    echo "</a>";
                                    echo "<a href='staff.php?delete_id=" . $row['id'] . "' onclick='return confirm(\"Bạn có chắc muốn xóa nhân viên này?\")' class='btn btn-danger btn-sm'>";
                                    echo "<i class='fas fa-trash'></i> Xóa";
                                    echo "</a>";
                                    echo "</div>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4' class='text-center'>Chưa có nhân viên nào</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>

</html>