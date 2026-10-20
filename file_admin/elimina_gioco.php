<?php
session_start();
include "../file_accesso/connessione.php";
include "funzioni.php";
include "../footer.php";

$base_url = "";
include "navbar.php";

?>
<!DOCTYPE html>
<html>
<head>
    <link href="style.css" rel="stylesheet">
    <title>Lista giochi</title>
</head>

<body>
<?php

if (!isset($_POST["id_gioco"])) {
    die("ID mancante");
}

$id_gioco = intval($_POST["id_gioco"]);

/* elimina immagine prima */
$sql = "SELECT immagine FROM giochi WHERE id = $id_gioco";
$result = mysqli_query($conn, $sql);

if ($row = mysqli_fetch_assoc($result)) {
    $path = "../" . $row["immagine"];

    if (!empty($row["immagine"]) && file_exists($path)) {
        unlink($path);
    }
}

/* elimina relazioni */
$stmt = $conn->prepare("DELETE FROM giochi_categorie WHERE id_gioco = ?");
$stmt->bind_param("i", $id_gioco);
$stmt->execute();

/* elimina gioco */
$stmt = $conn->prepare("DELETE FROM giochi WHERE id = ?");
$stmt->bind_param("i", $id_gioco);

if ($stmt->execute()) {
    header("Location: ../Lista_giochi.php");
    exit();
} else {
    echo "Errore eliminazione";
}

?>

</body>
</html>