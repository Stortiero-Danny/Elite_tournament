<?php
session_start();

include "file_accesso/connessione.php";
include "funzioni.php";
include "footer.php";

/* controllo login */
if (!isset($_SESSION["LOGGED"]) || $_SESSION["LOGGED"] !== true) {
    header("Location: file_accesso/login.php");
    exit();
}


?>


<!DOCTYPE html>
<html>

<head>
    <link href="style.css" rel="stylesheet">
    <title>Info_Notifiche</title>

    <style>
        .riquadro-info{
            text-align: center;
        }

        span{
            display:block;
            margin:30px auto;
        }

    </style>
</head>

<body>

<?php

$base_url = "";
include "navbar.php";

$id_notifica = intval($_GET['id']);

$_SESSION["id_notifica"] = $id_notifica;

$_SESSION["from"] = "notifiche";



//Per aggiornare lo stato della notifica
$sql_stato = "SELECT stato, tipologia
              FROM utenti_notifiche un LEFT JOIN notifiche n ON un.id_notifica = n.id
              WHERE n.id = $id_notifica";

$result_stato = mysqli_query($conn, $sql_stato);

if($result_stato && mysqli_num_rows($result_stato) > 0){
        
        $row = mysqli_fetch_assoc($result_stato);


        $sql_aggiorna = "UPDATE utenti_notifiche un INNER JOIN notifiche n ON un.id_notifica = n.id
                         SET un.stato = ?, un.data_lettura = CURRENT_TIMESTAMP WHERE n.id = ? AND un.data_lettura IS NULL";

        $letta = "Letta";

        $stmt = $conn->prepare($sql_aggiorna);
        $stmt->bind_param("si", $letta, $id_notifica);

        $stmt->execute();

        $stmt->close();


}


$sql = "SELECT n.tipologia as tipologia_notifica, n.data_invio as data_notifica, n.messaggio as messaggio_notifica, g.immagine as immagine_gioco, t.id as id_torneo, n.id_mittente as id_mittente, n.id_riferimento
        FROM notifiche n LEFT JOIN tornei t ON n.id_riferimento = t.id
        			     LEFT JOIN giochi g ON t.id_gioco = g.id WHERE n.id = $id_notifica";

$result = mysqli_query($conn, $sql);



if($result && mysqli_num_rows($result) > 0){

    while($row = mysqli_fetch_assoc($result)){

    ?><div class = "riquadro-info">

        <?php

        echo "<h1>" . $row["tipologia_notifica"] . "</h1>";

        if($row["tipologia_notifica"] == "torneo"){
        ?>

        <a href = "info_torneo.php?id=<?php echo $row["id_riferimento"]?>&from=info_notifica&id_notifica=<?php echo $id_notifica ?>"> <?php insert_immagine2($row["immagine_gioco"], "", 500); ?> </a>

        <?php
    }

        if($row["tipologia_notifica"] == "comunicazione" || $row["tipologia_notifica"] == "amicizia"){

        $sql_mittente = "SELECT avatar, nickname FROM utenti WHERE id = " . $row["id_mittente"];
        $result_mittente = mysqli_query($conn, $sql_mittente);

        if($result_mittente && mysqli_num_rows($result_mittente) > 0){

        $avatar_mittente = mysqli_fetch_assoc($result_mittente);

        ?>
        <a href = "file_admin/info_utente.php?from=info_notifica_mittente&id=<?php echo $row["id_mittente"]?>&id_notifica=<?php echo $id_notifica ?>"> <?php Insert_immagine2($avatar_mittente["avatar"], "", 250); ?> </a>
        <?php


        }
        }else if($row["tipologia_notifica"] == "squadra"){

            ?>
                <a href = "file_squadre/squadre.php"> <?php Insert_immagine2("file_squadre/Team.png", "", 230); ?> </a>
            <?php
        
    }
    ?>

        <div class="notifica-info">

            <span>
                Ricevuta il <?php echo formattaDataTime($row["data_notifica"]); ?>
            </span>

        </div>

    <?php

        echo "<h3> Messaggio </h3>";
        echo $row["messaggio_notifica"];

        echo "<br><br>";


    }

}else{
    echo "<h2>Id notifica non trovato</h2>";
}


?>
</div>

<button class = "input-schermo" onclick="window.location.href='notifiche.php'"> Indietro </button><br>

</body>
</html>