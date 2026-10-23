<?php
session_start();

require_once __DIR__ . "/../file_accesso/connessione.php";
require_once __DIR__ . "/../funzioni.php";

/* blocca accesso diretto */
if (!isset($_POST['submit_form'])) {
    header("Location: ../home.php");
    exit();
}


?>

<!DOCTYPE html>
<html>

<head>
    <link href="../style.css" rel="stylesheet">

    <style>
    </style>
</head>

<body>

<?php

$base_url = "../";
include "../navbar.php";

    $id_utente = $_SESSION["id"];
    $nome = $_POST["nome"];


    /* =========================
        CONTROLLO NOME SQUADRA
       ========================= */
$check = "SELECT nome FROM squadre WHERE nome = ? LIMIT 1";
$stmt_check = $conn->prepare($check);
$stmt_check->bind_param("s", $nome);
$stmt_check->execute();
$result = $stmt_check->get_result();

if ($result->num_rows > 0) {
    echo "<h2 style='color:red;'>Nome già esistente!</h2>";
    echo "<a href='crea_squadra.php'>Torna indietro</a>";

    echo "<br><br>";
    insert_immagine_reazione("../img_errore/");
    exit;
}else{


    /* =========================
   UPLOAD IMMAGINE
   ========================= */

$percorsoLogo = NULL;

if (isset($_FILES['logo']) && $_FILES['logo']['error'] === 0) {

    // nome unico
    $nomeFile = time() . "_" . basename($_FILES['logo']['name']);

    // cartella uploads
    $destinazione = "img_squadre/" . $nomeFile;

    // sposta file
    if(move_uploaded_file($_FILES['logo']['tmp_name'], $destinazione)){

    $percorsoLogo = $destinazione;

}else{

    die("Errore upload immagine");

}

    // salva percorso nel DB
    $percorsoLogo = $destinazione;
}


$stmt = $conn->prepare("INSERT INTO squadre(nome, id_creatore, logo_squadra) VALUES(?, ?, ?)");

$stmt->bind_param("sis", $nome, $id_utente, $percorsoLogo);


if ($stmt->execute()) {

    $sql = "SELECT id FROM squadre WHERE nome = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $nome);
    $stmt->execute();

    $result = $stmt->get_result();

    $stmt->close();

    if($result->num_rows > 0) {

    $row = $result->fetch_assoc();

    $id_squadra = $row["id"];

    aggiungi_membro($conn, $id_utente, $id_squadra);

    header("Location: squadre.php");
    exit();

    }else{
        echo "<p>Errore: id_squadra non trovato </p>";
        echo "<h2><a href='crea_squadra.php'> Riprova </a></h2>";

        echo "<br><br>";
        insert_immagine_reazione("../img_errore/");

    }

} else {
    echo "<p>Errore: " . $stmt->error . "</p>";
    echo "<h2><a href='crea_squadra.php'> Riprova </a></h2>";

    echo "<br><br>";
    insert_immagine_reazione("../img_errore/");
}


}


include "../footer.php";

?>

</body>
</html>