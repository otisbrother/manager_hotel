<?php
require_once 'config.php';

// Demo payment test - Simulate successful payment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingId = $_POST['booking_id'] ?? null;
    $action = $_POST['action'] ?? null;
    
    if ($action === 'simulate_payment' && $bookingId) {
        try {
            // Simulate successful payment
            $stmt = $conn->prepare("UPDATE roombook SET stat = 'Paid' WHERE id = ?");
            $stmt->execute([$bookingId]);
            
            // Get booking info
            $stmt = $conn->prepare("SELECT * FROM roombook WHERE id = ?");
            $stmt->execute([$bookingId]);
            $booking = $stmt->fetch();
            
            if ($booking) {
                // Calculate prices
                $roomPrice = calculateRoomPrice($booking['RoomType']);
                $bedPrice = calculateBedPrice($booking['Bed']);
                $mealPrice = calculateMealPrice($booking['Meal']);
                $totalPrice = calculateTotalPrice($booking);
                
                // Save to payment table
                $stmt = $conn->prepare("INSERT INTO payment (Name, Email, RoomType, Bed, NoofRoom, cin, cout, noofdays, roomtotal, bedtotal, meal, mealtotal, finaltotal, payment_method, transaction_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([
                    $booking['Name'],
                    $booking['Email'],
                    $booking['RoomType'],
                    $booking['Bed'],
                    $booking['NoofRoom'],
                    $booking['cin'],
                    $booking['cout'],
                    $booking['nodays'],
                    $roomPrice,
                    $bedPrice,
                    $booking['Meal'],
                    $mealPrice,
                    $totalPrice,
                    'vietqr',
                    'DEMO_' . time()
                ]);
                
                echo json_encode(['status' => 'success', 'message' => 'Payment simulated successfully!']);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    exit;
}

// Functions
function calculateRoomPrice($roomType) {
    $prices = [
        'Superior Room' => 1000,
        'Deluxe Room' => 1500,
        'Guest House' => 800,
        'Single Room' => 500
    ];
    return $prices[$roomType] ?? 0;
}

function calculateBedPrice($bedType) {
    $prices = [
        'Single' => 10,
        'Double' => 20,
        'Triple' => 30,
        'Quad' => 40
    ];
    return $prices[$bedType] ?? 0;
}

function calculateMealPrice($meal) {
    $prices = [
        'Room only' => 0,
        'Breakfast' => 200,
        'Half Board' => 400,
        'Full Board' => 600
    ];
    return $prices[$meal] ?? 0;
}

function calculateTotalPrice($booking) {
    $roomPrice = calculateRoomPrice($booking['RoomType']);
    $bedPrice = calculateBedPrice($booking['Bed']);
    $mealPrice = calculateMealPrice($booking['Meal']);
    $days = $booking['nodays'];
    
    return ($roomPrice + $bedPrice + $mealPrice) * $days;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo Payment Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4><i class="fas fa-qrcode"></i> Demo Payment Test</h4>
                    </div>
                    <div class="card-body">
                        <p>Đây là trang demo để test thanh toán mà không cần PayOS.</p>
                        
                        <div class="mb-3">
                            <label for="booking_id" class="form-label">Booking ID:</label>
                            <input type="number" class="form-control" id="booking_id" placeholder="Nhập Booking ID">
                        </div>
                        
                        <button type="button" class="btn btn-success" onclick="simulatePayment()">
                            <i class="fas fa-play"></i> Simulate Payment Success
                        </button>
                        
                        <div id="result" class="mt-3"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function simulatePayment() {
            const bookingId = document.getElementById('booking_id').value;
            if (!bookingId) {
                alert('Vui lòng nhập Booking ID!');
                return;
            }
            
            fetch('demo_payment_test.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `booking_id=${bookingId}&action=simulate_payment`
            })
            .then(response => response.json())
            .then(data => {
                const resultDiv = document.getElementById('result');
                if (data.status === 'success') {
                    resultDiv.innerHTML = `
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> ${data.message}
                            <br><br>
                            <strong>Booking ID:</strong> ${bookingId}<br>
                            <strong>Status:</strong> Paid<br>
                            <strong>Data:</strong> Đã lưu vào bảng payment
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i> ${data.message}
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('result').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> Có lỗi xảy ra!
                    </div>
                `;
            });
        }
    </script>
</body>
</html>
