<?php
session_start();

require_once __DIR__ . "/../file_accesso/connessione.php";
require_once __DIR__ . "/../funzioni.php";


/* blocca accesso diretto */
if ($_SERVER["REQUEST_METHOD"] !== "POST" ||
    !isset($_POST["submit_form"])) {

    header("Location: lista_giochi.php");
    exit();
}

// Verifica che l'utente sia autenticato
if (empty($_SESSION["LOGGED"]) || !isset($_SESSION["id"])) {
    header("Location: ../file_accesso/login.php");
    exit();
}

/* =========================
   CONTROLLI BASE
========================= */

if (!isset($_POST["id_gioco"])) {
    die("ID gioco mancante");
}

$id_gioco = intval($_POST["id_gioco"]);
$nome_gioco = trim($_POST["nome_gioco"]);
$categorie = $_POST["categorie"] ?? [];

/* =========================
   RECUPERA IMMAGINE ATTUALE
========================= */

$sql = "SELECT immagine FROM giochi WHERE id = $id_gioco";
$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    die("Gioco non trovato");

    echo "<br><br>";
    insert_immagine_reazione("../img_errore/");
}

$row = mysqli_fetch_assoc($result);
$immagine_vecchia = $row["immagine"];

/* =========================
   UPLOAD NUOVA IMMAGINE (OPZIONALE)
========================= */

$percorsoImmagine = null;

if (isset($_FILES['immagine_gioco']) && $_FILES['immagine_gioco']['error'] === 0) {

    $estensione = pathinfo($_FILES['immagine_gioco']['name'], PATHINFO_EXTENSION);
    $nomeFile = time() . "." . $estensione;

    $destinazione = "immagini_games/" . $nomeFile;
    $percorsoCompleto = "../" . $destinazione;

    if (move_uploaded_file($_FILES['immagine_gioco']['tmp_name'], $percorsoCompleto)) {
        $percorsoImmagine = $destinazione;
    } else {
        die("Errore durante upload immagine");

        echo "<br><br>";
        insert_immagine_reazione("../img_errore/");
    }
}

/* =========================
   UPDATE GIOCO
========================= */

if ($percorsoImmagine !== null) {

    // aggiorna anche immagine
    $stmt = $conn->prepare("UPDATE giochi SET nome = ?, immagine = ? WHERE id = ?");
    $stmt->bind_param("ssi", $nome_gioco, $percorsoImmagine, $id_gioco);

    if ($stmt->execute()) {

        // elimina immagine vecchia SOLO dopo update riuscito
        $path_vecchio = "../" . $immagine_vecchia;

        if (!empty($immagine_vecchia) && file_exists($path_vecchio)) {
            unlink($path_vecchio);
        }

    } else {
        die("Errore update gioco: " . $stmt->error);

        echo "<br><br>";
        insert_immagine_reazione("../img_errore/");
    }

} else {

    // aggiorna solo nome
    $stmt = $conn->prepare("UPDATE giochi SET nome = ? WHERE id = ?");
    $stmt->bind_param("si", $nome_gioco, $id_gioco);

    if (!$stmt->execute()) {
        die("Errore update gioco: " . $stmt->error);

        echo "<br><br>";
        insert_immagine_reazione("../img_errore/");
    }
}

/* =========================
   AGGIORNA TABELLA ASSOCIATIVA
========================= */

/* cancella vecchie categorie */
$stmt_del = $conn->prepare("DELETE FROM giochi_categorie WHERE id_gioco = ?");
$stmt_del->bind_param("i", $id_gioco);
$stmt_del->execute();

/* inserisci nuove categorie */
if (!empty($categorie)) {

    $stmt_ins = $conn->prepare("INSERT INTO giochi_categorie (id_gioco, id_categoria) VALUES (?, ?)");

    foreach ($categorie as $id_categoria) {
        $id_categoria = intval($id_categoria);
        $stmt_ins->bind_param("ii", $id_gioco, $id_categoria);
        $stmt_ins->execute();
    }
}

/* =========================
   REDIRECT FINALE
========================= */

header("Location: ../lista_giochi.php");
exit();
?>