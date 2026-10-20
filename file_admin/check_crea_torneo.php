<?php
    session_start();
?>

<!DOCTYPE html>
<head>
    <link href="../style.css" rel = "stylesheet">
</head>
<body>


<?php
include "../file_accesso/connessione.php";
include "../funzioni.php";
include "../footer.php";

$base_url = "../";
include "../navbar.php";

if (!isset($_POST['submit_form'])) {
    header("Location: ../home.php");
    exit();
}

$id_creatore = $_SESSION["id"];
$id_gioco = $_POST["id_gioco"];
$data = $_POST["data"];
$ora = $_POST["ora"];
$modalita = $_POST["modalita"];
$max_players_squadre = $_POST["player_squadra"] ?? null;
$n_posti = $_POST["n_posti"];
$n_round = $_POST["n_round"];
$regolamento = $_POST["regolamento"];

if($modalita != 4){
    $max_players_squadre = $modalita;
}

$stmt = $conn->prepare("INSERT INTO tornei(
    id_creatore, data, ora, id_gioco, regolamento, n_posti, n_round, id_mod_gioco, max_players_squadre, id_stato
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");

$stmt->bind_param(
    "issisiiii",
    $id_creatore,
    $data,
    $ora,
    $id_gioco,
    $regolamento,
    $n_posti,
    $n_round,
    $modalita,
    $max_players_squadre
);

if ($stmt->execute()) {
    header("Location: ../esito_operazione.php?from=crea_torneo");
    exit();
} else {
    echo "Errore: " . $stmt->error;

    echo "<br><br>";
    insert_immagine_reazione("../img_errore/");
}
?>

</body>
</html>