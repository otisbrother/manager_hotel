<?php
require_once 'config.php';
require_once 'auth.php';
require_once 'middleware.php';

$error = '';

// Kiểm tra timeout
if (isset($_GET['timeout'])) {
    $error = 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['login'])) {
        $email = sanitize_input($_POST['email']);
        $password = $_POST['password'];
        $role = sanitize_input($_POST['role']);
        
        $result = $auth->login($email, $password, $role);
        
        if ($result['success']) {
            redirect($result['redirect']);
        } else {
            $error = $result['message'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BlueBird Hotel - Đăng nhập</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 900px;
            display: flex;
        }
        
        .login-image {
            flex: 1;
            background: url('./image/hotel1.jpg') center/cover;
            position: relative;
        }
        
        .login-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.3);
        }
        
        .login-form {
            flex: 1;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin-bottom: 15px;
        }
        
        .logo h1 {
            color: #333;
            font-weight: 700;
            margin: 0;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 15px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 15px;
            font-size: 16px;
            font-weight: 600;
            color: white;
            transition: all 0.3s;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        .role-selection {
            text-align: center;
        }
        
        .role-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        
        .role-btn {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 15px 20px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            min-width: 120px;
        }
        
        .role-btn:hover {
            border-color: #667eea;
            transform: translateY(-2px);
        }
        
        .role-btn.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: #667eea;
            color: white;
        }
        
        .role-btn i {
            font-size: 1.5rem;
        }
        
        .role-btn span {
            font-weight: 600;
            font-size: 14px;
        }
        
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                margin: 20px;
            }
            
            .login-image {
                height: 200px;
            }
            
            .login-form {
                padding: 30px;
            }
            
            .role-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .role-btn {
                min-width: 150px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-image"></div>
        <div class="login-form">
                            <div class="logo">
                    <img src="./image/bluebirdlogo.png" alt="BlueBird Hotel">
                    <h1>BlueBird Hotel</h1>
                    <p class="text-muted">Chào mừng bạn đến với chúng tôi</p>
                </div>
                
                <!-- Role Selection -->
                <div class="role-selection mb-4">
                    <div class="role-buttons">
                        <button type="button" class="role-btn active" data-role="user">
                            <i class="fas fa-user"></i>
                            <span>Khách hàng</span>
                        </button>
                        <button type="button" class="role-btn" data-role="staff">
                            <i class="fas fa-user-tie"></i>
                            <span>Admin</span>
                        </button>
                    </div>
                </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
                            <form method="POST" action="">
                    <input type="hidden" name="role" id="selected_role" value="user">
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope"></i> Email
                        </label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock"></i> Mật khẩu
                        </label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <button type="submit" name="login" class="btn btn-login w-100">
                        <i class="fas fa-sign-in-alt"></i> Đăng nhập
                    </button>
                </form>
            
            <div class="text-center mt-4">
                <p class="text-muted">Chưa có tài khoản? 
                    <a href="register.php" class="text-decoration-none">Đăng ký ngay</a>
                </p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Role selection functionality
        const roleButtons = document.querySelectorAll('.role-btn');
        const selectedRoleInput = document.getElementById('selected_role');
        
        roleButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                roleButtons.forEach(btn => btn.classList.remove('active'));
                
                // Add active class to clicked button
                this.classList.add('active');
                
                // Update hidden input value
                const role = this.getAttribute('data-role');
                selectedRoleInput.value = role;
            });
        });
    </script>
</body>
</html>

