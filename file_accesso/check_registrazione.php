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
   UPLOAD IMMAGINE  (SOSPESO)
   ========================= */

$percorsoAvatar = NULL;

/*

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

*/

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

// Esecuzione INSERT prima di qualsiasi HTML
try {

    $registrazione_riuscita = $stmt->execute();

} catch (mysqli_sql_exception $e) {

    error_log("Errore registrazione: " . $e->getMessage());
    $registrazione_riuscita = false;
}

$stmt->close();

if ($registrazione_riuscita) {

    header("Location: ../esito_operazione.php?from=registrazione");
    exit();
}

http_response_code(500);

?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <link href="../style.css" rel="stylesheet">
    <title>Errore registrazione</title>
</head>

<body>

<?php
$base_url = "../";
include __DIR__ . "/../navbar.php";
?>

<h2>Errore durante la registrazione.</h2>

<a href="registrazione.php">Riprova</a>

<?php include __DIR__ . "/../footer.php"; ?>

</body>
</html>