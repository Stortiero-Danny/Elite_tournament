<?php
session_start();

include "file_accesso/connessione.php";
include "funzioni.php";
include "footer.php";


?>

<!DOCTYPE html>
<html>
<head>

    <link href="style.css" rel="stylesheet">

</head>

<body>

<?php
include "navbar.php";

$id_utente = $_GET["id"];
$nickname = $_GET["nickname_utente"];

$sql = "SELECT t.*, t.id as id_torneo, g.id, g.nome as nome_gioco, g.immagine, st.nome as stato
                                FROM `tornei` t INNER JOIN partecipanti p ON t.id = p.id_torneo
                                INNER JOIN giochi g ON t.id_gioco = g.id
                                INNER JOIN stato_torneo st ON t.id_stato = st.id
            WHERE p.id_utente = $id_utente AND (t.id_stato = 2 OR t.id_stato = 3)";

$result = mysqli_query($conn, $sql);



if($_SESSION["from"] == "profilo"){
    ?><h1> Tornei a cui hai partecipato (<?php echo conta_tornei_partecipati($conn, $id_utente) ?>)</h1>
    
    <button class = "input-schermo" onclick="window.location.href='profilo.php'"> indietro </button>
    <?php

    
}else{
    ?><h1> Tornei a cui <?php echo $nickname ?> ha partecipato (<?php echo conta_tornei_partecipati($conn, $id_utente) ?>)</h1>
    
    <button class = "input-schermo" onclick="window.location.href='file_admin/info_utente.php?from=<?php echo $_SESSION['from'] ?>&id=<?php echo $id_utente ?>'"> indietro </button>
    <?php
}
?>


<div class = "contenitore-tornei"><?php

if(mysqli_num_rows($result) > 0){

    while($row = mysqli_fetch_assoc($result)){

    ?>
    <div class="riquadro-home" onclick="window.location.href='info_torneo.php?id=<?php echo $row['id_torneo']; ?> &from=tornei_partecipati&id_utente=<?php echo $id_utente; ?>&nickname_utente=<?php echo $nickname; ?>'">
    <?php

        echo "<h2>" . $row["nome_gioco"] . "</h2>";
        echo "<img src='" . $row["immagine"] . "' width='300'>";

        echo "<br><br> Data: " . formattaData($row["data"]);
        echo "<br> Ora: " . $row["ora"];

        if($row["stato"] == "In corso"){
            echo "<br> Stato: <span style='color: green;'>" . $row["stato"] . "</span>";
        }else{
            echo "<br> Stato: <span style='color: red;'>" . $row["stato"] . "</span>";
        }
        

        echo "<br><br><br>";

    ?>
    </div>
    <?php

    }
}else{

    ?><h2> Ancora nessuno al momento </h2><?php

}
?>

</div>

</body>
</html>