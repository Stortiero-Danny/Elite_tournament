<?php

header("Content-Type: text/plain; charset=utf-8");

// Carica la connessione MySQL
require_once __DIR__ . "/../file_accesso/connessione.php";

// Esegue una semplice query
$result = mysqli_query($conn, "SELECT COUNT(*) AS totale FROM giochi");

if (!$result) {
    http_response_code(500);
    exit("Errore durante la lettura dei giochi.");
}

$row = mysqli_fetch_assoc($result);

echo "Elite Tournament - Connessione Aiven riuscita!\n";
echo "Giochi presenti nel database: " . $row["totale"];

?>