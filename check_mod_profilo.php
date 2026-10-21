<?php
session_start();
require_once __DIR__ . "file_accesso/connessione.php";
require_once __DIR__ . "funzioni.php";

$base_url = "";
include "navbar.php";

/* blocca accesso diretto */
if (!isset($_POST['submit_form'])) {
    header("Location: profilo.php");
    exit();
}

?>

<!DOCTYPE html>
<head>
    <link href="style.css" rel = "stylesheet">
</head>
<body>

    <?php
include "footer.php";

/* DATI UTENTE */
$id = $_SESSION["id"];

$vecchioAvatar = $_SESSION["avatar"] ?? null;

$nickname = $_POST['nickname'];
$Nome = $_POST['nome'];
$Cognome = $_POST['cognome'];
$Email = $_POST['email'];
$data_nascita = $_POST['data'];
$id_sesso = $_POST["id_sesso"];
$id_nazione = $_POST["nazione"];

/* CONTROLLO NICKNAME */
$check = "SELECT id FROM utenti WHERE nickname = ? AND id != ?";
$stmt_check = $conn->prepare($check);
$stmt_check->bind_param("si", $nickname, $id);
$stmt_check->execute();
$result = $stmt_check->get_result();

if ($result->num_rows > 0) {
    echo "<h2 style='color:red;'>Nickname già esistente!</h2>";
    echo "<a href='mod_profilo.php'>Torna indietro</a>";
    exit;
}



/* CONTROLLO email */

$checkEmail = "SELECT id FROM utenti WHERE email = ? AND id != ?";
$stmt_email = $conn->prepare($checkEmail);
$stmt_email->bind_param("si", $Email, $id);
$stmt_email->execute();
$res_email = $stmt_email->get_result();

if ($res_email->num_rows > 0) {
    echo "<h2 style='color:red;'>Email già registrata!</h2>";
    echo "<a href='mod_profilo.php'>Torna indietro</a>";
    exit;
}



/*  UPLOAD AVATAR */
$percorsoAvatar = null;

if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {

    $nomeFile = time() . "_" . basename($_FILES['avatar']['name']);
    $destinazione = "uploads/" . $nomeFile;

    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $destinazione)) {

        $percorsoAvatar = $destinazione;

        // ELIMINA IL VECCHIO AVATAR
        if (!empty($vecchioAvatar) &&  $vecchioAvatar != "utente_generico.jpg" && file_exists($vecchioAvatar)) {
            unlink($vecchioAvatar);
        }
    }
}

/* QUERY UPDATE */

if ($percorsoAvatar) {

    $sql = "UPDATE utenti SET 
        nickname = ?, 
        nome = ?, 
        cognome = ?, 
        email = ?, 
        data_nascita = ?, 
        id_sesso = ?, 
        id_nazione = ?, 
        avatar = ?
        WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssissi",
        $nickname,
        $Nome,
        $Cognome,
        $Email,
        $data_nascita,
        $id_sesso,
        $id_nazione,
        $percorsoAvatar,
        $id
    );

} else {

    $sql = "UPDATE utenti SET 
        nickname = ?, 
        nome = ?, 
        cognome = ?, 
        email = ?, 
        data_nascita = ?, 
        id_sesso = ?, 
        id_nazione = ?
        WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssiii",
        $nickname,
        $Nome,
        $Cognome,
        $Email,
        $data_nascita,
        $id_sesso,
        $id_nazione,
        $id
    );
}

/* ESECUZIONE */

if ($stmt->execute()) {

    /* aggiorna sessione */
    $_SESSION["nickname"] = $nickname;
    $_SESSION["nome"] = $Nome;
    $_SESSION["cognome"] = $Cognome;
    $_SESSION["email"] = $Email;
    $_SESSION["data_nascita"] = $data_nascita;
    $_SESSION["id_sesso"] = $id_sesso;
    $_SESSION["id_nazione"] = $id_nazione;

    if ($percorsoAvatar) {
        $_SESSION["avatar"] = $percorsoAvatar;
    }

    header("Location: profilo.php");
    exit();

} else {
    echo "<h2>Errore: " . $stmt->error . "</h2>";
    echo "<a href='mod_profilo.php'>Riprova</a>";

    echo "<br><br>";
    insert_immagine_reazione("../img_errore/");
}
?>

</body>
</html>