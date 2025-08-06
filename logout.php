<?php
require_once 'auth.php';

$result = $auth->logout();
header("Location: " . $result['redirect']);
exit();
?>