<?php
session_start();

include "../file_accesso/connessione.php";
include "../funzioni.php";


if (isset($_POST["accetta"])) {

    $id_utente = $_SESSION["id"];
    $id_mittente = intval($_POST["id_mittente"]);


    $sql = "INSERT INTO amicizie (utente_id1, utente_id2) VALUES (?, ?)";

    $stmt = $conn->prepare($sql);

    if($id_utente < $id_mittente){
        $stmt->bind_param("ii", $id_utente, $id_mittente);
    }else{
        $stmt->bind_param("ii", $id_mittente, $id_utente);
    }


    $stmt->execute();

    if($stmt->error){
        die("Errore execute: " . $stmt->error);
    }
    $stmt->close();


    invia_notifica(
        $conn,
        "amicizia",
        $_SESSION["nickname"] . " ha accettato la tua richiesta di amicizia :)",
        $id_utente,
        $id_mittente,
        NULL
    );

    elimina_richiesta($conn, $id_utente, $id_mittente);

    header_amici($conn, $id_utente);
}


if (isset($_POST["rifiuta"])) {

    $id_utente = $_SESSION["id"];
    $id_mittente = intval($_POST["id_mittente"]);

    invia_notifica(
        $conn,
        "amicizia",
        $_SESSION["nickname"] . " ha rifiutato la tua richiesta di amicizia :(",
        $id_utente,
        $id_mittente,
        NULL
    );

    elimina_richiesta($conn, $id_utente, $id_mittente);

    header_amici($conn, $id_utente);
}


if (isset($_POST["blocca"])) {

    $id_utente = $_SESSION["id"];
    $id_mittente = intval($_POST["id_mittente"]);


    $sql = "INSERT INTO utenti_bloccati (utente_che_blocca, utente_bloccato) VALUES (?, ?)";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("ii", $id_utente, $id_mittente);

    $stmt->execute();

    if($stmt->error){
        die("Errore execute: " . $stmt->error);
    }
    $stmt->close();


    invia_notifica(
        $conn,
        "amicizia",
        $_SESSION["nickname"] . " ti ha bloccato :(",
        $id_utente,
        $id_mittente,
        NULL
    );

    elimina_richiesta($conn, $id_utente, $id_mittente);

    header_amici($conn, $id_utente);
}

?>

<!DOCTYPE html>
<html>

<head>
    <link href="../style.css" rel="stylesheet">

    <style>
        button{
            display:block;
            margin:30px auto;
        }

        .amico-left{
    display:flex;
    align-items:center;
    gap:20px;
}

.amico-info h2{
    font-size:45px;
    margin-bottom:10px;
}

.amico-info span{
    font-size:25px;
}
    </style>
</head>
<body>

<?php

$base_url = "../";
include "../navbar.php";

$id_destinatario = $_SESSION["id"];

$sql = "SELECT u.avatar, u.nickname as nickname, r.data_richiesta, r.id_mittente  FROM richieste_amicizia r INNER JOIN utenti u ON r.id_mittente = u.id
        WHERE r.id_destinatario = ?";

 $stmt = $conn->prepare($sql);
 $stmt->bind_param(
        "i",
        $id_destinatario
    );

    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

?>

<h1> Richieste di amicizia </h1>


<button class="input-schermo"
    onclick="window.location.href='amici.php'">

        Indietro

</button>


<div class='contenitore-amici'>

<?php

if ($result && $result->num_rows > 0) {

    while ($row = $result->fetch_assoc()) {

        ?>
         <div class="amico-card" onclick="window.location.href='../file_admin/info_utente.php?id=<?php echo $row['id_mittente']; ?>&from=richiesta'">
            
         <div class="amico-left">

        <?php
        Insert_immagine2($row["avatar"],$base_url,120);
        ?>

        <div class="amico-info">

            <h2>
                <?php echo htmlspecialchars($row["nickname"]); ?>
            </h2>

            <span>
                Inviata il 
                <?php echo formattaData($row["data_richiesta"]); ?>
            </span>

        </div>

    </div>

    <div class="rimuovi-right">

    <form method = "POST">

        <input type="hidden"
                   name="id_mittente"
                   value="<?php echo $row['id_mittente']; ?>">
        
        <div class = "azioni">
            <button class = "accetta" type="submit" name = "accetta" onclick="event.stopPropagation();"> Accetta </button><br>
            <button class = "rifiuta" type="submit" name = "rifiuta" onclick="event.stopPropagation();"> Rifiuta </button><br>
        </div>

        <?php $nickname = $row["nickname"]; ?>

        <button class = "blocca" type="submit" name = "blocca" onclick = "event.stopPropagation(); return controllo('<?php echo $nickname ?>');"> Blocca </button><br>

        </form>


    </div>

    <?php


    }

}else{
    echo "<br><br>";
    echo "<h2> Nessuna richiesta al momento </h2>";
}

?>

</div>


<?php
include "../footer.php";
?>

</body>
</html>



<script>

function controllo(nickname){

return confirm(
        "Vuoi bloccare " + nickname + "?"
    );

}

</script>