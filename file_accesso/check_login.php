<?php
session_start();
include "connessione.php";
include "../funzioni.php";
include "../footer.php";

$base_url = "../";
include "../navbar.php";

/* blocca accesso diretto */
if (!isset($_POST['submit_form'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>

<head>
    <link href="../style.css" rel = "stylesheet">
</head>
<body>


<?php

$email = $_POST["email"];
$password = md5($_POST["password"]);

$sql = "SELECT id, nome, cognome, nickname, email, data_nascita, id_sesso, id_nazione, avatar, id_ruolo FROM utenti 
        WHERE email = '$email' AND password = '$password'";

$result = mysqli_query($conn, $sql);

$accesso_riuscito = false;
$utente = null;

if (mysqli_num_rows($result) == 1) {

    $utente = mysqli_fetch_assoc($result);
    $_SESSION["LOGGED"] = true;
    $_SESSION['last_activity'] = time();//inizializza l'orario

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


    header("location: ../home.php");
    exit();

} else {
    $_SESSION["LOGGED"] = false;

    echo "<h1> Accesso negato </h1>";
    echo "<h2> Email o password non corretti. </h2>";
    echo '<a href="login.php">Torna al login</a>';


    echo "<br><br>";
    insert_immagine_reazione("../img_errore/");
    
}
?>

</body>
</html>