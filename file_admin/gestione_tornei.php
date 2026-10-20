<?php
session_start();
include "../file_accesso/connessione.php";
include "../funzioni.php";
include "../footer.php";

scheduler_checkin($conn);
?>

<!DOCTYPE html>
<head>
    <link href="../style.css" rel = "stylesheet">

    <style>
        button{
            width: 50%;
        }

        .sistema{
            width: 200px;
            height: 80px;
        }
    </style>
</head>
<body>

<?php

$base_url = "../";
include "../navbar.php";

if(isset($_POST["elimina"])){

    $id_utente = $_SESSION["id"];

    $id_torneo = intval($_POST["id_torneo"]);
    $id_mod = intval($_POST["id_mod_gioco"]);

    if($id_mod == 1){

    comunica_elimina_torneo($conn, $id_utente, $id_torneo);

    $stmt = $conn->prepare("DELETE FROM partecipanti WHERE id_torneo = ?");
    $stmt->bind_param("i", $id_torneo);
    $stmt->execute();
    $stmt->close();
    
    }else{

    comunica_elimina_torneo_squadre($conn, $id_utente, $id_torneo);

    $stmt = $conn->prepare("DELETE FROM squadre_partecipanti WHERE id_torneo = ?");
    $stmt->bind_param("i", $id_torneo);
    $stmt->execute();
    $stmt->close();

    }

    // Elimina le associazioni utenti-notifiche
    $stmt = $conn->prepare("
        DELETE un
        FROM utenti_notifiche un
        INNER JOIN notifiche n ON un.id_notifica = n.id
        WHERE n.id_riferimento = ?
    ");
    $stmt->bind_param("i", $id_torneo);
    $stmt->execute();
    $stmt->close();

    // Elimina le notifiche del torneo
    $stmt = $conn->prepare("
        DELETE FROM notifiche
        WHERE id_riferimento = ?
    ");
    $stmt->bind_param("i", $id_torneo);
    $stmt->execute();
    $stmt->close();

    // Elimina il torneo
    $stmt = $conn->prepare("
        DELETE FROM tornei
        WHERE id = ?
    ");
    $stmt->bind_param("i", $id_torneo);
    $stmt->execute();
    $stmt->close();



    // refresh pagina
    header("Location: gestione_tornei.php");
    exit();

}

?>

<h1>I tuoi tornei (<?php echo conta_tornei_creati($conn, $_SESSION["id"]) ?>)</h1>

<button class = "input-schermo" onclick="window.location.href='crea_torneo.php'">crea torneo</button><br><br>


<button class = "sistema" onclick="window.location.href='tornei_precedenti_organizzatore.php'">Tornei terminati</button><br><br>


<?php

$id_creatore = $_SESSION["id"];

$sql = "SELECT t.*, t.id as id_torneo, g.nome as nome_gioco, g.immagine, u.nickname as nome_creatore, u.id as id_creatore, st.nome as stato
         FROM tornei t 
         INNER JOIN giochi g ON t.id_gioco = g.id
         INNER JOIN utenti u ON t.id_creatore = u.id
         INNER JOIN stato_torneo st ON t.id_stato = st.id
         WHERE id_creatore = $id_creatore AND st.nome != 'Terminato'
         ORDER BY t.data";


$result = mysqli_query($conn, $sql);

?><div class = "contenitore-tornei"><?php

if(mysqli_num_rows($result) > 0){

    while($row = mysqli_fetch_assoc($result)){

    ?>
    <div class = "riquadro-home" onclick="window.location.href='../info_torneo.php?id=<?php echo $row['id']; ?>&from=gestione'">
    <?php

        echo $row["nome_gioco"] . "<br><br>";

        echo "<img src='../" . $row["immagine"] . "' width='300'>.";

        echo "<br> Data: " . formattaData($row["data"]);
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
    

<form method = "POST">

    <input type="hidden" name="id_torneo" value="<?php echo $row['id_torneo']; ?>">

    <input type="hidden" name="id_mod_gioco" value="<?php echo $row['id_mod_gioco']; ?>">

    <?php
    if($row["stato"] === "Fissato"){
    ?>
    <button class="btn-elimina" type = "submit" name = "elimina" onclick= "event.stopPropagation(); return conferma_eliminazione()">Elimina</button>
    <!-- event.stopPropagation() = serve per non concatenare gli eventi visto che elimina si trova all'interno di un altro evento (div)  -->
    <?php
        }
    ?>
</form>

    </div>

    <?php


    }
}

?>

</div>

</body>
</html>



<script>

function conferma_eliminazione(){

    return confirm("Sei sicuro di voler eliminare questo torneo?");

}

</script>