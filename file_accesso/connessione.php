<?php

$server = getenv("DB_HOST") ?: "localhost";
$user = getenv("DB_USER") ?: "root";
$password = getenv("DB_PASSWORD");

if ($password === false) {
    $password = "";
}

$dbname = getenv("DB_NAME") ?: "torneo_videogiochi";
$port = (int) (getenv("DB_PORT") ?: 3306);

$conn = mysqli_connect(
    $server,
    $user,
    $password,
    $dbname,
    $port
);

if (!$conn) {
    error_log("Errore connessione MySQL: " . mysqli_connect_error());
    http_response_code(500);
    exit("Impossibile collegarsi al database.");
}

mysqli_set_charset($conn, "utf8mb4");

?>