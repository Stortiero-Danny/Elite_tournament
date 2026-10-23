<?php
session_start();
require_once __DIR__ . "/connessione.php";
require_once __DIR__ . "/../funzioni.php";


/* blocca accesso diretto */
if (!isset($_POST['submit_form'])) {
    header("Location: registrazione.php");
    exit();
}


// DATI
$nickname = $_POST['nickname'];
$Nome = $_POST['nome'];
$Cognome = $_POST['cognome'];
$Email = $_POST['email'];
$data_nascita = $_POST['data'];
$id_sesso = $_POST["id_sesso"];
$id_nazione = $_POST["nazione"];
$Password = $_POST['password'];

/* =========================
   CONTROLLO NICKNAME
   ========================= */
$check = "SELECT id FROM utenti WHERE nickname = ? LIMIT 1";
$stmt_check = $conn->prepare($check);
$stmt_check->bind_param("s", $nickname);
$stmt_check->execute();
$result = $stmt_check->get_result();

if ($result->num_rows > 0) {
    echo "<h2 style='color:red;'>Nickname già esistente!</h2>";
    echo "<a href='registrazione.php'>Torna indietro</a>";
    exit;
}


/* =========================
   CONTROLLO email
   ========================= */

$checkEmail = "SELECT id FROM utenti WHERE email = ? LIMIT 1";
$stmt_email = $conn->prepare($checkEmail);
$stmt_email->bind_param("s", $Email);
$stmt_email->execute();
$res_email = $stmt_email->get_result();

if ($res_email->num_rows > 0) {
    echo "<h2 style='color:red;'>Email già registrata!</h2>";
    echo "<a href='registrazione.php'>Torna indietro</a>";
    exit;
}

/* =========================
   UPLOAD IMMAGINE
   ========================= */

$percorsoAvatar = NULL;

if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {

    // nome unico
    $nomeFile = time() . "_" . basename($_FILES['avatar']['name']);

    // cartella uploads
    $destinazione = "uploads/" . $nomeFile;

    // sposta file
    move_uploaded_file($_FILES['avatar']['tmp_name'], "../" . $destinazione);

    // salva percorso nel DB
    $percorsoAvatar = $destinazione;
}

/* =========================
   INSERT CON PREPARED
   ========================= */

$stmt = $conn->prepare("INSERT INTO utenti(nickname, nome, cognome, email, password, data_nascita, id_sesso, id_nazione, avatar, id_ruolo) VALUES (?, ?, ?, ?, MD5(?), ?, ?, ?, ?, 1)");

$stmt->bind_param(
    "ssssssiis",
    $nickname,
    $Nome,
    $Cognome,
    $Email,
    $Password,
    $data_nascita,
    $id_sesso,
    $id_nazione,
    $percorsoAvatar
);

?>

<!DOCTYPE html>
<head>
    <link href="../style.css" rel = "stylesheet">
</head>
<body>

<?php

$base_url = "../";
include "../navbar.php";

if ($stmt->execute()) {

    header("Location: ../esito_operazione.php?from=registrazione");
    exit();

} else {
    echo "<p>Errore: " . $stmt->error . "</p>";
    echo "<h2><a href='registrazione.php'> Riprova </a></h2>";

    echo "<br><br>";
    insert_immagine_reazione("../img_errore/");
}


include "../footer.php";

?>

</body>
</html>