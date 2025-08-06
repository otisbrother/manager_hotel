<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include 'config_admin.php';

// Xử lý thêm phòng
if (isset($_POST['addroom'])) {
    $typeofroom = $_POST['troom'];
    $typeofbed = $_POST['bed'];

    if (!empty($typeofroom) && !empty($typeofbed)) {
        $sql = "INSERT INTO room(type,bedding) VALUES ('$typeofroom', '$typeofbed')";
        $result = mysqli_query($conn, $sql);

        if ($result) {
            header("Location: room.php?success=added");
            exit();
        } else {
            $error_message = "Lỗi khi thêm phòng!";
        }
    } else {
        $warning_message = "Vui lòng điền đầy đủ thông tin!";
    }
}

// Xử lý cập nhật phòng
if (isset($_POST['updateroom'])) {
    $room_id = $_POST['room_id'];
    $typeofroom = $_POST['troom'];
    $typeofbed = $_POST['bed'];

    if (!empty($typeofroom) && !empty($typeofbed)) {
        $sql = "UPDATE room SET type='$typeofroom', bedding='$typeofbed' WHERE id=$room_id";
        $result = mysqli_query($conn, $sql);

        if ($result) {
            header("Location: room.php?success=updated");
            exit();
        } else {
            $error_message = "Lỗi khi cập nhật phòng!";
        }
    } else {
        $warning_message = "Vui lòng điền đầy đủ thông tin!";
    }
}

// Xử lý xóa phòng
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_sql = "DELETE FROM room WHERE id = $delete_id";
    $delete_result = mysqli_query($conn, $delete_sql);
    
    if ($delete_result) {
        header("Location: room.php?success=deleted");
        exit();
    } else {
        header("Location: room.php?error=delete_failed");
        exit();
    }
}

// Lấy thông tin phòng để edit
$edit_room = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $edit_sql = "SELECT * FROM room WHERE id = $edit_id";
    $edit_result = mysqli_query($conn, $edit_sql);
    if ($edit_result && mysqli_num_rows($edit_result) > 0) {
        $edit_room = mysqli_fetch_array($edit_result);
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
                <strong>Thành công!</strong> Đã thêm phòng mới.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['success']) && $_GET['success'] == 'deleted'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Thành công!</strong> Đã xóa phòng.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['success']) && $_GET['success'] == 'updated'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Thành công!</strong> Đã cập nhật phòng.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error']) && $_GET['error'] == 'delete_failed'): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Lỗi!</strong> Không thể xóa phòng.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Form thêm/cập nhật phòng -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <?php if ($edit_room): ?>
                        <i class="fas fa-edit"></i> Cập nhật phòng
                    <?php else: ?>
                        <i class="fas fa-plus"></i> Thêm phòng mới
                    <?php endif; ?>
                </h5>
            </div>
            <div class="card-body">
                <form action="" method="POST">
                    <?php if ($edit_room): ?>
                        <input type="hidden" name="room_id" value="<?php echo $edit_room['id']; ?>">
                    <?php endif; ?>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="troom" class="form-label">Loại phòng:</label>
                                <select name="troom" class="form-control" required>
                                    <option value="">Chọn loại phòng</option>
                                    <option value="Superior Room" <?php echo ($edit_room && $edit_room['type'] == 'Superior Room') ? 'selected' : ''; ?>>Superior Room</option>
                                    <option value="Deluxe Room" <?php echo ($edit_room && $edit_room['type'] == 'Deluxe Room') ? 'selected' : ''; ?>>Deluxe Room</option>
                                    <option value="Guest House" <?php echo ($edit_room && $edit_room['type'] == 'Guest House') ? 'selected' : ''; ?>>Guest House</option>
                                    <option value="Single Room" <?php echo ($edit_room && $edit_room['type'] == 'Single Room') ? 'selected' : ''; ?>>Single Room</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="bed" class="form-label">Loại giường:</label>
                                <select name="bed" class="form-control" required>
                                    <option value="">Chọn loại giường</option>
                                    <option value="Single" <?php echo ($edit_room && $edit_room['bedding'] == 'Single') ? 'selected' : ''; ?>>Single</option>
                                    <option value="Double" <?php echo ($edit_room && $edit_room['bedding'] == 'Double') ? 'selected' : ''; ?>>Double</option>
                                    <option value="Triple" <?php echo ($edit_room && $edit_room['bedding'] == 'Triple') ? 'selected' : ''; ?>>Triple</option>
                                    <option value="Quad" <?php echo ($edit_room && $edit_room['bedding'] == 'Quad') ? 'selected' : ''; ?>>Quad</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <?php if ($edit_room): ?>
                            <button type="submit" class="btn btn-custom" name="updateroom">
                                <i class="fas fa-save"></i> Cập nhật phòng
                            </button>
                            <a href="room.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Hủy
                            </a>
                        <?php else: ?>
                            <button type="submit" class="btn btn-custom" name="addroom">
                                <i class="fas fa-plus"></i> Thêm phòng
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

        <!-- Bảng danh sách phòng -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-bed"></i> Danh sách phòng</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Loại phòng</th>
                                <th>Loại giường</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT * FROM room ORDER BY id DESC";
                            $result = mysqli_query($conn, $sql);
                            
                            if (mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_array($result)) {
                                    echo "<tr>";
                                    echo "<td>" . $row['id'] . "</td>";
                                    echo "<td><strong>" . $row['type'] . "</strong></td>";
                                    echo "<td>" . $row['bedding'] . "</td>";
                                    echo "<td>";
                                    echo "<div class='btn-group' role='group'>";
                                    echo "<a href='room.php?edit_id=" . $row['id'] . "' class='btn btn-primary btn-sm me-1'>";
                                    echo "<i class='fas fa-edit'></i> Sửa";
                                    echo "</a>";
                                    echo "<a href='room.php?delete_id=" . $row['id'] . "' onclick='return confirm(\"Bạn có chắc muốn xóa phòng này?\")' class='btn btn-danger btn-sm'>";
                                    echo "<i class='fas fa-trash'></i> Xóa";
                                    echo "</a>";
                                    echo "</div>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4' class='text-center'>Chưa có phòng nào</td></tr>";
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