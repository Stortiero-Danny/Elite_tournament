<?php
session_start();

include "../file_accesso/connessione.php";
include "../funzioni.php";
include "../footer.php";
?>

<!DOCTYPE html>
<html>
<head>
    <link href="../style.css" rel="stylesheet">
</head>
<body>

<?php

$base_url = "../";
include "../navbar.php";

$id_torneo = intval($_GET["id"]);
$id_match = intval($_GET["id_match"]);


/* blocca accesso diretto */
if (!isset($_POST['submit_form'])) {
    header("Location: ../home.php");
    exit();
}


$descrizione = $_POST["descrizione"] ?? NULL;


/* =========================
   UPLOAD IMMAGINE
   ========================= */

$percorsoImmagine = NULL;

if (isset($_FILES['immagine']) && $_FILES['immagine']['error'] === 0) {

    // nome unico
    $nomeFile = uniqid() . "_" . basename($_FILES['immagine']['name']);

    // cartella uploads
    $destinazione = "reclami/" . $nomeFile;

    // sposta file
    if(move_uploaded_file($_FILES['immagine']['tmp_name'], "../" . $destinazione)){

        //salva percorso
        $percorsoImmagine = $destinazione;
    }
}


$stmt = $conn->prepare("INSERT INTO reclami(id_match, id_autore, img, descrizione) VALUES(?, ?, ?, ?)");
$stmt->bind_param("iiss", $id_match, $_SESSION["id"], $percorsoImmagine, $descrizione);

if ($stmt->execute()) {

    header("Location: reclami.php?id=$id_torneo&id_match=$id_match");
    exit();

} else {
    echo "<p>Errore: " . $stmt->error . "</p>";
    echo "<h2><a href='aggiungi_reclamo.php?id=$id_torneo&id_match=$id_match'>Riprova</a></h2>";

    echo "<br><br>";
    insert_immagine_reazione("../img_errore/");
}


?>


</body>
</html>