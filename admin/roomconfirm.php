<?php

include '../config.php';

$id = $_GET['id'];

// Lấy thông tin đặt phòng
$stmt = $conn->prepare("SELECT * FROM roombook WHERE id = ?");
$stmt->execute([$id]);
$row = $stmt->fetch();

if ($row) {
    $Name = $row['Name'];
    $Email = $row['Email'];
    $Country = $row['Country'];
    $Phone = $row['Phone'];
    $RoomType = $row['RoomType'];
    $Bed = $row['Bed'];
    $NoofRoom = $row['NoofRoom'];
    $Meal = $row['Meal'];
    $cin = $row['cin'];
    $cout = $row['cout'];
    $noofday = $row['nodays'];
    $stat = $row['stat'];
}

if($stat == "NotConfirm")
{
    $st = "Confirm";

    // Cập nhật trạng thái
    $stmt = $conn->prepare("UPDATE roombook SET stat = ? WHERE id = ?");
    $result = $stmt->execute([$st, $id]);

    if($result){

        $type_of_room = 0;      
        if($RoomType=="Superior Room")
        {
            $type_of_room = 3000;
        }
        else if($RoomType=="Deluxe Room")
        {
            $type_of_room = 2000;
        }
        else if($RoomType=="Guest House")
        {
            $type_of_room = 1500;
        }
        else if($RoomType=="Single Room")
        {
            $type_of_room = 1000;
        }
        
        
        if($Bed=="Single")
        {
            $type_of_bed = $type_of_room * 1/100;
        }
        else if($Bed=="Double")
        {
            $type_of_bed = $type_of_room * 2/100;
        }
        else if($Bed=="Triple")
        {
            $type_of_bed = $type_of_room * 3/100;
        }
        else if($Bed=="Quad")
        {
            $type_of_bed = $type_of_room * 4/100;
        }
            else if($Bed=="None")
        {
            $type_of_bed = $type_of_room * 0/100;
        }

        if($Meal=="Room only")
        {
            $type_of_meal=$type_of_bed * 0;
        }
        else if($Meal=="Breakfast")
        {
            $type_of_meal=$type_of_bed * 2;
        }
        else if($Meal=="Half Board")
        {
            $type_of_meal=$type_of_bed * 3;
        }
        else if($Meal=="Full Board")
        {
            $type_of_meal=$type_of_bed * 4;
        }
                                                            
        $ttot = $type_of_room *  $noofday * $NoofRoom;
        $mepr = $type_of_meal *  $noofday;
        $btot = $type_of_bed * $noofday;

        $fintot = $ttot + $mepr + $btot;

        // Thêm vào bảng payment
        $psql = "INSERT INTO payment(id,Name,Email,RoomType,Bed,NoofRoom,cin,cout,noofdays,roomtotal,bedtotal,meal,mealtotal,finaltotal) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $pstmt = $conn->prepare($psql);
        $pstmt->execute([$id, $Name, $Email, $RoomType, $Bed, $NoofRoom, $cin, $cout, $noofday, $ttot, $btot, $Meal, $mepr, $fintot]);

        header("Location:roombook.php");
        exit();
    }
}
// else
// {
//     echo "<script>alert('Guest Already Confirmed')</script>";
//     header("Location:roombook.php");
// }

?>