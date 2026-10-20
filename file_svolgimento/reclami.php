<?php
session_start();

include "../file_accesso/connessione.php";
include "../funzioni.php";
include "../footer.php";
?>

<!DOCTYPE html>
<html>
<head>
    <link href="../style.css" rel="stylesheet">
    <style>
        .mod{
            width: 70;
            height: 70;
            font-size: 30px;
            border-radius: 20%;
        }
    </style>
</head>
<body>

<?php

$base_url = "../";
include "../navbar.php";

$id_torneo = intval($_GET["id"]);
$id_match = intval($_GET["id_match"]);

$sql = "SELECT u.nickname, u.avatar, r.id_autore, r.img, r.descrizione, r.data_creazione FROM reclami r
        INNER JOIN utenti u ON r.id_autore = u.id
        WHERE id_match = $id_match";

$result = mysqli_query($conn, $sql);


$result_stato = mysqli_query($conn, "SELECT stato FROM match_torneo WHERE id = $id_match");

?>

<h1> Reclami del match </h1>

<h2> Manda gli screenshot dei risultati delle partite oppure segnala atteggiamenti inadeguati </h2>

<button class="input-schermo"
    onclick="window.location.href='info_match.php?id=<?php echo $id_torneo; ?>&id_match=<?php echo $id_match; ?>'">
    Indietro
</button>

<br>

<?php
if(check_presenza_match($conn, $id_match, $_SESSION['id'])){
    if($result_stato && mysqli_num_rows($result_stato) > 0){

    $row_stato = mysqli_fetch_assoc($result_stato); 

        if($row_stato["stato"] != "giocato"){

        ?>
            <button class="input-schermo"
            onclick="window.location.href='aggiungi_reclamo.php?id=<?php echo $id_torneo; ?>&id_match=<?php echo $id_match; ?>'">
                Aggiungi reclamo
            </button>
        <?php
        }

    }

}
?>

<br>


<?php
if($result && mysqli_num_rows($result) > 0){

    echo '<div class="chat-container">';

while($row = mysqli_fetch_assoc($result)){

    $isMine = ($row["id_autore"] == $_SESSION["id"]);

    $bubbleClass = $isMine ? "bubble bubble-right" : "bubble bubble-left";

    echo "<div class='$bubbleClass'>";

    echo "<div class='user'>";
    Insert_immagine2($row["avatar"], "../", 32);
    echo htmlspecialchars($row["nickname"]);
    echo "</div>";

    echo "<div class='time'>" . formattaDataTime($row["data_creazione"]) . "</div>";

    if(!empty($row["img"])){
        echo "<div class='reclamo-img'>";
        Insert_immagine2($row["img"], "../", 350);
        echo "</div>";
    }

    if(!empty($row["descrizione"])){
        ?>
        <div class="regolamento-box">
            <?php echo htmlspecialchars(trim($row["descrizione"])); ?>
        </div>
        <?php
    }

    echo "</div>";
}

echo '</div>';

}else{
    echo "(Al momento nessuno presente)";
}
?>


</body>
</html>