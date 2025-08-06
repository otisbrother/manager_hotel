<?php

include 'config_admin.php';

$id = $_GET['id'];

$roomdeletesql = "DELETE FROM staff WHERE id = $id";

$result = mysqli_query($conn, $roomdeletesql);

header("Location:staff.php");

?>