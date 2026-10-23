<?php
session_start();

include "../file_accesso/connessione.php";
include "../funzioni.php";
include "../footer.php";


if (isset($_POST["sblocca"])) {

    $id_utente = $_SESSION["id"];
    $id_utente_bloccato = intval($_POST["id_utente_bloccato"]);


    $sql = "DELETE FROM utenti_bloccati WHERE utente_che_blocca = ? AND utente_bloccato = ?";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("ii", $id_utente, $id_utente_bloccato);

    $stmt->execute();

    if($stmt->error){
        die("Errore execute: " . $stmt->error);
    }
    $stmt->close();


    invia_notifica(
        $conn,
        "amicizia",
        $_SESSION["nickname"] . " Ti ha sbloccato! (fatto pace?)",
        $id_utente,
        $id_utente_bloccato,
        NULL
    );

    header_utenti_bloccati($conn, $id_utente);
}


?>

<!DOCTYPE html>
<html>

<head>
    <link href="../style.css" rel="stylesheet">
</head>

<body>

<?php

$base_url = "../";
include "../navbar.php";

$id_utente = $_SESSION["id"];

$_SESSION["from"] = "utente_bloccato";

$sql = "SELECT utente_bloccato, data_blocco, u.avatar, u.nickname FROM utenti_bloccati ub INNER JOIN utenti u ON ub.utente_bloccato = u.id  
        WHERE utente_che_blocca = ?";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
        "i",
        $id_utente
    );

$stmt->execute();
$result = $stmt->get_result();



?>


<h1> Utenti bloccati </h1>

<button class="input-schermo"
    onclick="window.location.href='amici.php'">

        Indietro

</button>


<div class='contenitore-amici'>

<?php

if ($result && $result->num_rows > 0) {

    while ($row = $result->fetch_assoc()) {

        ?>
         <div class="amico-card" onclick="window.location.href='../file_admin/info_utente.php?id=<?php echo $row['utente_bloccato']; ?>&from=utente_bloccato'">
            
         <div class="amico-left">

        <?php
        Insert_immagine2($row["avatar"], $base_url, 120);
        ?>

        <div class="amico-info">

            <h2>
                <?php echo htmlspecialchars($row["nickname"]); ?>
            </h2>

            <span>
                Bloccato il 
                <?php echo formattaData($row["data_blocco"]); ?>
            </span>

        </div>

    </div>

    <div class="rimuovi-right">

    <form method = "POST">

        <input type="hidden"
                   name="id_utente_bloccato"
                   value="<?php echo $row['utente_bloccato']; ?>">

        <div class = "azioni">

        <?php $nickname = $row["nickname"]; ?>

        <button class = "sblocca" type="submit" name = "sblocca" onclick="event.stopPropagation(); return controllo('<?php echo $nickname ?>');"> sblocca </button><br>
        </div>

    </form>
    </div>

</div>
    

<?php
    }
}else{

    echo "<br><br>";
    echo "<h2> Nessun utente bloccato (per ora....) </h2>";

}

?>


</div>


</body>
</html>



<script>

function controllo(nickname){

return confirm(
        "Vuoi sbloccare " + nickname + "?"
    );

}

</script>