<?php
session_start();
include "../file_accesso/connessione.php";
include "../funzioni.php";
include "../footer.php";

?>

<!DOCTYPE html>
<head>
    <link href="../style.css" rel = "stylesheet">
</head>

<body>

<?php

$base_url = "../";
include "../navbar.php";

$id_utente = $_POST["id_utente"] ?? $_GET["id_utente"] ?? 0;
$id_utente = intval($id_utente);

$from_lista = $_GET["from"];

$id_torneo = intval($_GET["id_torneo"]) ?? 0;



if ($id_utente == 0) {
    die("ID utente mancante");
}

$sql = "SELECT t.*, t.id as id_torneo, g.id, g.nome as nome_gioco, g.immagine, u.nickname as nome_creatore, st.nome as stato
         FROM tornei t 
         INNER JOIN giochi g ON t.id_gioco = g.id
         INNER JOIN utenti u ON t.id_creatore = u.id
         INNER JOIN stato_torneo st ON t.id_stato = st.id
         WHERE u.id = $id_utente
         ORDER BY t.data";

$result = mysqli_query($conn, $sql);

if($result && mysqli_num_rows($result) > 0){

$row = mysqli_fetch_assoc($result);

?>

    <h1>Tornei di <?php echo $row["nome_creatore"]?> (<?php echo conta_tornei_creati($conn, $id_utente) ?>)</h1>


<div class = "contenitore-tornei">

    <?php

       do {
?>
    <div class="riquadro-home" onclick="window.location.href='../info_torneo.php?id=<?php echo $row['id_torneo']; ?>&from=info_tornei_creati&id_utente=<?php echo $id_utente; ?>&from_lista=<?php echo $from_lista ?>&id_torneo_originale=<?php echo $id_torneo ?>'">
        <h2><?php echo $row["nome_gioco"]; ?></h2>
<?php
        Insert_immagine2($row["immagine"], "../", 300);

        echo "<br><br> Data: " . $row["data"];
        echo "<br> Ora: " . $row["ora"];

        if($row["stato"] === "Terminato"){
            echo "<br> Stato: <span style='color: red;'>" . $row["stato"] . "</span>";
        }elseif($row["stato"] === "In corso"){
            echo "<br> Stato: <span style='color: green;'>" . $row["stato"] . "</span>";
        }else{
            echo "<br> Stato: <span style='color: blue;'>" . $row["stato"] . "</span>";
        }

        echo "<br><br><br>";
?>
    </div>
<?php
} while($row = mysqli_fetch_assoc($result));


}else{
    echo "<h2> Utente non trovato </h2>";
}


?>

</div>
<br>
<button class = "input-schermo" onclick="window.location.href='info_utente.php?id=<?php echo $id_utente?>&from=<?php echo $from_lista ?>&id_torneo=<?php echo $id_torneo ?>'"> Indietro </button>


</body>
</html>