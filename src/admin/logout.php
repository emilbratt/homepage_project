<?php
session_start();
unset($_SESSION['verified_login']);
session_destroy();

// header("Location: ../index.php"); // USE IN PRODUCTION
header("Location: login.php"); // USE IN DEV-ENV
?>
