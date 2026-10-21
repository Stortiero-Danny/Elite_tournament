<?php

$online = getenv("DB_HOST") !== false;

$server = getenv("DB_HOST") ?: "localhost";
$user = getenv("DB_USER") ?: "root";
$password = getenv("DB_PASSWORD") ?: "";
$dbname = getenv("DB_NAME") ?: "torneo_videogiochi";
$port = (int) (getenv("DB_PORT") ?: 3306);

try {

    $conn = mysqli_init();

    // Configurazione SSL soltanto online
    if ($online) {

        mysqli_ssl_set(
            $conn,
            null,
            null,
            __DIR__ . "/ca.pem",
            null,
            null
        );

        mysqli_options(
            $conn,
            MYSQLI_OPT_SSL_VERIFY_SERVER_CERT,
            true
        );
    }

    // Connessione MySQL
    mysqli_real_connect(
        $conn,
        $server,
        $user,
        $password,
        $dbname,
        $port,
        null,
        $online ? MYSQLI_CLIENT_SSL : 0
    );

    mysqli_set_charset($conn, "utf8mb4");

} catch (mysqli_sql_exception $e) {

    error_log("Errore MySQL: " . $e->getMessage());

    http_response_code(500);

    exit("Impossibile collegarsi al database.");
}

?>