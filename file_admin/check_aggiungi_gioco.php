<?php
session_start();
require_once __DIR__ . "/../file_accesso/connessione.php";
require_once __DIR__ . "/../funzioni.php";


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

$nome_gioco = trim($_POST["nome_gioco"]);
$categorie = $_POST["categorie"] ?? [];

/* =========================
   CONTROLLO CATEGORIE
========================= */

if (empty($categorie)) {
    echo "<h1>Seleziona almeno una categoria!</h1>";
    echo "<a href='aggiungi_gioco.php'>Riprova</a>";
    exit();
}

/* =========================
   UPLOAD IMMAGINE
========================= */

$percorsoImmagine = null;

if (isset($_FILES['immagine_gioco']) && $_FILES['immagine_gioco']['error'] === 0) {

    $nomeFile = time() . "_" . basename($_FILES['immagine_gioco']['name']);
    $destinazione = "immagini_games/" . $nomeFile;

    if (!move_uploaded_file($_FILES['immagine_gioco']['tmp_name'], "../" . $destinazione)) {
        die("Errore upload immagine");
    }

    $percorsoImmagine = $destinazione;
}

/* =========================
   INSERT GIOCO
========================= */

$stmt = $conn->prepare("INSERT INTO giochi(nome, immagine) VALUES (?, ?)");
$stmt->bind_param("ss", $nome_gioco, $percorsoImmagine);

if ($stmt->execute()) {

    $id_gioco = $conn->insert_id;

    /* =========================
       INSERT TABELLA ASSOCIATIVA
    ========================= */

    $stmt2 = $conn->prepare("INSERT INTO giochi_categorie (id_gioco, id_categoria) VALUES (?, ?)");

    foreach ($categorie as $id_categoria) {
        $id_categoria = intval($id_categoria);
        $stmt2->bind_param("ii", $id_gioco, $id_categoria);
        $stmt2->execute();
    }

    header("Location: ../esito_operazione.php?from=aggiungi_gioco");
    exit();

} else {
    echo "<h2>Errore: " . $stmt->error . "</h2>";

    echo "<br><br>";
    insert_immagine_reazione("../img_errore/");
}

include "../footer.php";

?>

</body>
</html>