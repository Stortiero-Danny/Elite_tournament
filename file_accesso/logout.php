<?php
session_start();

session_unset();

// Distruggi sessione
session_destroy();

// Redirect
header("Location: login.php");
exit();
?>