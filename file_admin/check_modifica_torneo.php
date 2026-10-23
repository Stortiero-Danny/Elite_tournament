<?php
session_start();

require_once __DIR__ . "/../file_accesso/connessione.php";
require_once __DIR__ . "/../funzioni.php";

?>

<!DOCTYPE html>
<html>
<head>
    <link href="../style.css" rel="stylesheet">
    <title>Modifica Torneo</title>
</head>

<body>

<?php

$base_url = "../";
include "../navbar.php";

$id_torneo = $_POST["id_torneo"];
$from = $_POST["from"];

$id_gioco = $_POST["id_gioco"];
$data = $_POST["data"];
$ora = $_POST["ora"];
$n_posti = $_POST["n_posti"];
$n_round = $_POST["n_round"];
$regolamento = $_POST["regolamento"];
$player_squadra = $_POST["player_squadra"] ?? null;

$sql = "UPDATE tornei SET
        id_gioco = ?,
        data = ?,
        ora = ?,
        regolamento = ?,
        n_posti = ?,
        n_round = ?,
        max_players_squadre = ?
        WHERE id = ?";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "isssiiii",
    $id_gioco,
    $data,
    $ora,
    $regolamento,
    $n_posti,
    $n_round,
    $player_squadra,
    $id_torneo
);

if($stmt->execute()){
    header("Location: ../info_torneo.php?id=" . $id_torneo . "&from=" . $from);
    exit();
}else{
    echo "<h2>Errore: " . $stmt->error . "</h2>";
    echo "<a href='modifica_torneo.php?id_torneo=$id_torneo'>Riprova</a>";

    echo "<br><br>";
    insert_immagine_reazione("../img_errore/");
}

include "../footer.php";
?>

</body>
</html>