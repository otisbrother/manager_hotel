<?php
/**
 * MoMo IPN (Instant Payment Notification) Handler
 * Xử lý callback từ MoMo khi thanh toán hoàn tất
 */

require_once 'config.php';
require_once 'momo_api.php';

// Log callback data
$logFile = 'momo_callback.log';
$callbackData = $_POST;
$logData = date('Y-m-d H:i:s') . " - Callback received: " . json_encode($callbackData) . "\n";
file_put_contents($logFile, $logData, FILE_APPEND);

// Khởi tạo MoMo Payment
$momoPayment = new MomoPayment();

// Xác thực callback
if ($momoPayment->verifyCallback($callbackData)) {
    $orderId = $callbackData['orderId'];
    $resultCode = $callbackData['resultCode'];
    $amount = $callbackData['amount'];
    $transId = $callbackData['transId'];
    $message = $callbackData['message'];
    
    // Log verification success
    $logData = date('Y-m-d H:i:s') . " - Callback verified successfully for order: " . $orderId . "\n";
    file_put_contents($logFile, $logData, FILE_APPEND);
    
    if ($resultCode == 0) {
        // Thanh toán thành công
        try {
            // Cập nhật trạng thái đặt phòng
            $stmt = $conn->prepare("UPDATE roombook SET stat = 'Confirm' WHERE id = ?");
            $stmt->execute([$orderId]);
            
            // Thêm vào bảng payment
            $stmt = $conn->prepare("INSERT INTO payment (id, Name, Email, RoomType, Bed, NoofRoom, cin, cout, noofdays, roomtotal, bedtotal, meal, mealtotal, finaltotal, payment_method, transaction_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'MoMo', ?)");
            
            // Lấy thông tin đặt phòng
            $bookingStmt = $conn->prepare("SELECT * FROM roombook WHERE id = ?");
            $bookingStmt->execute([$orderId]);
            $booking = $bookingStmt->fetch();
            
            if ($booking) {
                // Tính toán giá
                $roomPrices = [
                    'Superior Room' => 1000,
                    'Deluxe Room' => 1500,
                    'Guest House' => 800
                ];
                
                $bedPrices = [
                    'Single' => 10,
                    'Double' => 20,
                    'Triple' => 30,
                    'Quad' => 40
                ];
                
                $mealPrices = [
                    'Room only' => 0,
                    'Breakfast' => 200,
                    'Half Board' => 400,
                    'Full Board' => 600
                ];
                
                $roomTotal = $roomPrices[$booking['RoomType']] * $booking['NoofRoom'] * $booking['nodays'];
                $bedTotal = $bedPrices[$booking['Bed']] * $booking['NoofRoom'] * $booking['nodays'];
                $mealTotal = $mealPrices[$booking['Meal']] * $booking['NoofRoom'] * $booking['nodays'];
                $finalTotal = $roomTotal + $bedTotal + $mealTotal;
                
                $stmt->execute([
                    $orderId,
                    $booking['Name'],
                    $booking['Email'],
                    $booking['RoomType'],
                    $booking['Bed'],
                    $booking['NoofRoom'],
                    $booking['cin'],
                    $booking['cout'],
                    $booking['nodays'],
                    $roomTotal,
                    $bedTotal,
                    $booking['Meal'],
                    $mealTotal,
                    $finalTotal,
                    $transId
                ]);
                
                // Log success
                $logData = date('Y-m-d H:i:s') . " - Payment successful for order: " . $orderId . ", Amount: " . $amount . "\n";
                file_put_contents($logFile, $logData, FILE_APPEND);
                
                // Trả về response thành công cho MoMo
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Payment processed successfully'
                ]);
            } else {
                // Log error - booking not found
                $logData = date('Y-m-d H:i:s') . " - Error: Booking not found for order: " . $orderId . "\n";
                file_put_contents($logFile, $logData, FILE_APPEND);
                
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Booking not found'
                ]);
            }
            
        } catch (Exception $e) {
            // Log database error
            $logData = date('Y-m-d H:i:s') . " - Database error: " . $e->getMessage() . "\n";
            file_put_contents($logFile, $logData, FILE_APPEND);
            
            echo json_encode([
                'status' => 'error',
                'message' => 'Database error'
            ]);
        }
        
    } else {
        // Thanh toán thất bại
        $logData = date('Y-m-d H:i:s') . " - Payment failed for order: " . $orderId . ", Result code: " . $resultCode . ", Message: " . $message . "\n";
        file_put_contents($logFile, $logData, FILE_APPEND);
        
        echo json_encode([
            'status' => 'failed',
            'message' => 'Payment failed: ' . $message
        ]);
    }
    
} else {
    // Xác thực thất bại
    $logData = date('Y-m-d H:i:s') . " - Callback verification failed\n";
    file_put_contents($logFile, $logData, FILE_APPEND);
    
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid signature'
    ]);
}
?> 