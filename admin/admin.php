<?php
require_once 'config_admin.php';

// Kiểm tra đăng nhập
if (!is_logged_in()) {
    header("Location: ../index.php");
    exit();
}

// Kiểm tra nếu user không phải staff (admin)
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'staff') {
    header("Location: ../index.php");
    exit();
}

// Lấy thông tin user hiện tại
$user_name = $_SESSION['user_name'] ?? 'Admin';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/admin.css">
    <!-- loading bar -->
    <script src="https://cdn.jsdelivr.net/npm/pace-js@latest/pace.min.js"></script>
    <link rel="stylesheet" href="../css/flash.css">
    <!-- fontowesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <title>BlueBird - Admin</title>
</head>

<body>
    <!-- nav bar -->
    <nav class="uppernav">
        <div class="logo">
            <img class="bluebirdlogo" src="../image/bluebirdlogo.png" alt="logo">
            <p>BLUEBIRD</p>
        </div>
        <div class="user-info">
            <span style="color: white; margin-right: 15px;">
                <i class="fas fa-user-circle"></i> <?php echo $user_name; ?>
            </span>
            <a href="../logout.php"><button class="btn btn-primary">Đăng xuất</button></a>
        </div>
        <button id="sidebarToggle" class="sidebar-toggle-btn" aria-label="Mở menu"><i class="fas fa-bars"></i></button>
    </nav>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <nav class="sidenav" id="sidenav">
        <ul>
            <li class="pagebtn active"><img src="../image/icon/dashboard.png">&nbsp;&nbsp;&nbsp; Dashboard</li>
            <li class="pagebtn"><img src="../image/icon/bed.png">&nbsp;&nbsp;&nbsp; Room Booking</li>
            <li class="pagebtn"><img src="../image/icon/wallet.png">&nbsp;&nbsp;&nbsp; Payment</li>
            <li class="pagebtn"><img src="../image/icon/bedroom.png">&nbsp;&nbsp;&nbsp; Rooms</li>
            <li class="pagebtn"><img src="../image/icon/staff.png">&nbsp;&nbsp;&nbsp; Staff</li>
        </ul>
    </nav>
    <div class="mainscreen">
        <iframe class="frames frame1 active" src="./dashboard.php" frameborder="0"></iframe>
        <iframe class="frames frame2" src="./roombook.php" frameborder="0"></iframe>
        <iframe class="frames frame3" src="./payment.php" frameborder="0"></iframe>
        <iframe class="frames frame4" src="./room.php" frameborder="0"></iframe>
        <iframe class="frames frame4" src="./staff.php" frameborder="0"></iframe>
    </div>
<script src="./javascript/script.js"></script>
<script>
// Toggle sidebar for mobile
const sidebar = document.getElementById('sidenav');
const overlay = document.getElementById('sidebarOverlay');
document.getElementById('sidebarToggle').onclick = function() {
    sidebar.classList.toggle('open');
    overlay.classList.toggle('show');
};
overlay.onclick = function() {
    sidebar.classList.remove('open');
    overlay.classList.remove('show');
};
</script>
</body>
</html>
