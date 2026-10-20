<?php
session_start();

include "file_accesso/connessione.php";
include "funzioni.php";
include "footer.php";

$id_gioco = intval($_GET["id"]);
?>

<!DOCTYPE html>
<head>
    <link href="style.css" rel = "stylesheet">

    <style>
        .sistema{
            width: 200px;
            height: 80px;
        }
    </style>
</head>
<body>

<?php

$base_url = "";
include "navbar.php";

scheduler_checkin($conn);
?>

<h1> Tornei disponibili </h1>

<button class = "input-schermo" onclick="window.location.href='home.php'">Indietro</button><br><br>

<?php
if(check_tornei_terminati($conn, $id_gioco)){
    ?>
        <button class = "sistema" onclick="window.location.href='tornei_precedenti.php?id=<?php echo $id_gioco ?>'">visualizza i tornei precedenti</button>
    <?php
}

?>

<?php

$sql = "SELECT t.*, t.id as id_torneo, t.n_posti, g.id, g.nome as nome_gioco, g.immagine, u.nickname as nome_creatore, st.nome as stato
         FROM tornei t 
         INNER JOIN giochi g ON t.id_gioco = g.id
         INNER JOIN utenti u ON t.id_creatore = u.id
         INNER JOIN stato_torneo st ON t.id_stato = st.id
         WHERE g.id = $id_gioco AND st.nome != 'Terminato'
         ORDER BY t.data";


$result = mysqli_query($conn, $sql);

?><div class = "contenitore-tornei"><?php

if(mysqli_num_rows($result) > 0){

    while($row = mysqli_fetch_assoc($result)){

    ?>
    <div class="riquadro-home" onclick="window.location.href='info_torneo.php?id=<?php echo $row['id_torneo']; ?> &from=tornei_gioco'">
    <?php

        echo "<h2>" . $row["nome_gioco"] . "</h2>";
        echo "<img src='" . $row["immagine"] . "' width='300'>";

        echo "<br><br> Data: " . formattaData($row["data"]);
        echo "<br> Ora: " . $row["ora"];

        if($row["stato"] === "In corso"){
            echo "<br> Stato: <span style='color: green;'>" . $row["stato"] . "</span>";
        }elseif($row["stato"] === "checkin"){
            echo "<br> Stato: <span style='color: yellow;'>" . $row["stato"] . "</span>";
        }else{
            echo "<br> Stato: <span style='color: blue;'>" . $row["stato"] . "</span>";
        }


        if (isset($_SESSION["LOGGED"]) &&
                  $_SESSION["LOGGED"] === true) {

            if(check_iscrizione($conn, $_SESSION["id"], $row["id_torneo"]) || check_iscrizione_squadra($conn, $_SESSION["id"], $row["id_torneo"])){

                echo "<p class = 'pixel' style = 'color: green';> (iscritto) </p>";
            }else{

                if($row["n_posti"] <= 0){
                    echo "<p class = 'pixel' style = 'color: red; text-align: center'> POSTI ESAURITI </p>";
                }else{
                    echo "<br><br><br>";
                }

            }
        }else{

            if($row["n_posti"] <= 0){
                    echo "<p class = 'pixel' style = 'color: red; text-align: center'> POSTI ESAURITI </p>";
                }else{
                    echo "<br><br><br>";
                }
        }

    ?>
    </div>
    <?php

    }
}else{
?>

    <div class="nessun-torneo">

        <h2>Nessun torneo al momento</h2>

        <br><br>

        <?php insert_immagine_reazione("img_errore/"); ?>

    </div>

<?php
}
?>

</div>

</body>
</html>