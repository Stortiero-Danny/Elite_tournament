<?php

session_start();

require_once __DIR__ . "/connessione.php";
require_once __DIR__ . "/../funzioni.php";

// Blocca l'accesso diretto
if ($_SERVER["REQUEST_METHOD"] !== "POST" ||
    !isset($_POST["submit_form"])) {

    header("Location: login.php");
    exit();
}

$email = $_POST["email"] ?? "";
$password = md5($_POST["password"] ?? "");

// Query preparata
$sql = "SELECT id, nome, cognome, nickname, email,
               data_nascita, id_sesso, id_nazione,
               avatar, id_ruolo
        FROM utenti
        WHERE email = ? AND password = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $email, $password);
$stmt->execute();

$result = $stmt->get_result();

$accesso_riuscito = false;

if ($result && $result->num_rows === 1) {

    $utente = $result->fetch_assoc();

    // Rigenera l'ID della sessione dopo il login
    session_regenerate_id(true);

    $_SESSION["LOGGED"] = true;
    $_SESSION["last_activity"] = time();

    $_SESSION["id"] = $utente["id"];
    $_SESSION["nickname"] = $utente["nickname"];
    $_SESSION["email"] = $utente["email"];
    $_SESSION["nome"] = $utente["nome"];
    $_SESSION["cognome"] = $utente["cognome"];
    $_SESSION["data_nascita"] = $utente["data_nascita"];
    $_SESSION["id_sesso"] = $utente["id_sesso"];
    $_SESSION["id_nazione"] = $utente["id_nazione"];
    $_SESSION["avatar"] = $utente["avatar"];
    $_SESSION["id_ruolo"] = $utente["id_ruolo"];

    $accesso_riuscito = true;

    $stmt->close();

    // Reindirizzamento prima di qualsiasi output HTML
    header("Location: ../home.php");
    exit();
}

$stmt->close();

$_SESSION["LOGGED"] = false;

?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Accesso negato</title>
    <link href="../style.css" rel="stylesheet">
</head>

<body>

<?php

$base_url = "../";
include "../navbar.php";

?>

<h1>Accesso negato</h1>
<h2>Email o password non corretti.</h2>

<a href="login.php">Torna al login</a>

<br><br>

<?php
insert_immagine_reazione("../img_errore/");
include "../footer.php";
?>

</body>
</html>