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
</head>
<body>

<?php

$base_url = "";
include "navbar.php";
?>

<h1> Tornei precedenti </h1>

<button class = "input-schermo" onclick="window.location.href='tornei_gioco.php?id=<?php echo $id_gioco ?>'">Indietro</button><br><br>

<?php

$sql = "SELECT t.*, t.id as id_torneo, g.id, g.nome as nome_gioco, g.immagine, u.nickname as nome_creatore, st.nome as stato
         FROM tornei t 
         INNER JOIN giochi g ON t.id_gioco = g.id
         INNER JOIN utenti u ON t.id_creatore = u.id
         INNER JOIN stato_torneo st ON t.id_stato = st.id
         WHERE g.id = ". $id_gioco . " AND st.nome = 'Terminato'
         ORDER BY t.data DESC";


$result = mysqli_query($conn, $sql);

?><div class = "contenitore-tornei"><?php

if(mysqli_num_rows($result) > 0){

    while($row = mysqli_fetch_assoc($result)){

    ?>
    <div class="riquadro-home" onclick="window.location.href='info_torneo.php?id=<?php echo $row['id_torneo']; ?> &from=tornei_precedenti'">
    <?php

        echo "<h2>" . $row["nome_gioco"] . "</h2>";
        echo "<img src='" . $row["immagine"] . "' width='300'>";

        echo "<br><br> Data: " . formattaData($row["data"]);
        echo "<br> Ora: " . $row["ora"];

        echo "<br> Stato: <span style='color: red;'>" . $row["stato"] . "</span>";
        

        echo "<br><br><br>";

    ?>
    </div>
    <?php

    }
}
?>

</div>

</body>
</html>