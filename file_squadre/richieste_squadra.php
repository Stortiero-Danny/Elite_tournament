<?php
session_start();

include "../file_accesso/connessione.php";
include "../funzioni.php";
include "../footer.php";


if (isset($_POST["accetta"])) {

    $id_utente = $_SESSION["id"];
    $id_mittente = intval($_POST["id_mittente"]); 
    $id_squadra = intval($_POST["id_squadra"]);
    $nome_squadra = $_POST["nome_squadra"];

    $sql = "INSERT INTO utenti_squadre (id_utente, id_squadra) VALUES (?, ?)";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("ii", $id_utente, $id_squadra);

    $stmt->execute();

    if($stmt->error){
        die("Errore execute: " . $stmt->error);
    }

    $stmt->close();


    invia_notifica(
        $conn,
        "squadra",
        $_SESSION["nickname"] . " ha accettato di entrare nella squadra: ". $nome_squadra ." :)",
        $id_utente,
        $id_mittente,
        NULL
    );

    elimina_richiesta_squadra($conn, $id_utente, $id_squadra);

    header_squadre($conn, $id_utente);
}


if (isset($_POST["rifiuta"])) {

    $id_utente = $_SESSION["id"];
    $id_mittente = intval($_POST["id_mittente"]); 
    $id_squadra = intval($_POST["id_squadra"]);
    $nome_squadra = $_POST["nome_squadra"];

    invia_notifica(
        $conn,
        "squadra",
        $_SESSION["nickname"] . " ha rifiutato l'entrata nella squadra: ". $nome_squadra ." :(",
        $id_utente,
        $id_mittente,
        NULL
    );

    elimina_richiesta_squadra($conn, $id_utente, $id_squadra);

    header_squadre($conn, $id_utente);
}


if (isset($_POST["rimuovi_amico"])) {

    $id_utente = $_SESSION["id"];
    $id_mittente = intval($_POST["id_mittente"]);
    $id_squadra = intval($_POST["id_squadra"]);

    invia_notifica(
        $conn,
        "amicizia",
        $_SESSION["nickname"] . " ti ha rimosso dagli amici :(",
        $id_utente,
        $id_mittente,
        NULL
    );

    elimina_richiesta_squadra($conn, $id_utente, $id_squadra);

    //eliminazione amicizia

    $stmt = $conn->prepare("
        DELETE FROM amicizie
        WHERE
        (utente_id1 = ? AND utente_id2 = ?)
        OR
        (utente_id1 = ? AND utente_id2 = ?)
    ");

    $stmt->bind_param(
        "iiii",
        $id_utente,
        $id_mittente,
        $id_mittente,
        $id_utente
    );

    $stmt->execute();

    $stmt->close();

    // refresh pagina
    header_squadre($conn, $id_utente);
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

$_SESSION["from"] = "richieste";

$id_destinatario = $_SESSION["id"];

$sql = "SELECT u.avatar, u.nickname as nickname, r.data_richiesta, r.id_mittente, s.id as id_squadra, s.logo_squadra, s.nome as nome_squadra
        FROM richieste_squadra r INNER JOIN utenti u ON r.id_mittente = u.id
                                 INNER JOIN squadre s ON r.id_squadra = s.id
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

<h1> Richieste Squadra </h1>


<button class="input-schermo"
    onclick="window.location.href='squadre.php'">

        Indietro

</button>


<div class='contenitore-amici'>

<?php

if ($result && $result->num_rows > 0) {

    while ($row = $result->fetch_assoc()) {

        ?>
    <div class="amico-card" onclick="window.location.href='../file_squadre/info_squadra.php?id=<?php echo $row['id_squadra']; ?>&nome=<?php echo $row['nome_squadra']; ?>'">
            
    <div class="amico-left">

        <?php
        Insert_logo_squadra($row["logo_squadra"],"",120);
        ?>

        <div class="amico-info">

            <h2>
                <?php echo htmlspecialchars($row["nome_squadra"]); ?>
            </h2>

            <span>
                Inviata il 
                <?php echo formattaData($row["data_richiesta"]); ?>
            </span>

            <br>
            
            <span>
                Da 
                <?php echo $row["nickname"]; ?>
            </span>

        </div>

    </div>

    <div class="rimuovi-right">

    <form method = "POST">

        <input type="hidden"
                   name="id_mittente"
                   value="<?php echo $row['id_mittente']; ?>">

        <input type="hidden"
                   name="id_squadra"
                   value="<?php echo $row['id_squadra']; ?>">

        <input type="hidden"
                   name="nome_squadra"
                   value="<?php echo $row['nome_squadra']; ?>">
        
        <div class = "azioni">
            <button class = "accetta" type="submit" name = "accetta" onclick="event.stopPropagation();"> Accetta </button><br>
            <button class = "rifiuta" type="submit" name = "rifiuta" onclick="event.stopPropagation();"> Rifiuta </button><br>
        </div>

        <?php $nickname = $row["nickname"]; ?>

        <button class = "blocca" type="submit" name = "rimuovi_amico" onclick = "event.stopPropagation(); return controllo('<?php echo $nickname ?>');"> Rimuovi Amico </button><br>

        </form>


    </div>
</div>

    <?php


    }

}else{
    echo "<br><br>";
    echo "<h2> Nessuna richiesta al momento </h2>";
}

?>

</div>

</body>
</html>



<script>

function controllo(nickname){

return confirm(
        "Sei sicuro di voler rimuovere " + nickname + " dai tuoi amici?"
    );

}

</script>