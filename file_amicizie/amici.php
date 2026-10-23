<?php
session_start();

require_once __DIR__ . "/../file_accesso/connessione.php";
require_once __DIR__ . "/../funzioni.php";


if(isset($_POST["rimuovi"])){

    $id_amico = intval($_POST["id_amico"]);
    $id_utente = $_SESSION["id"];

    $tipologia = "amicizia";
    $messaggio = $_SESSION["nickname"] . " ti ha rimosso dagli amici :(";
    $id_mittente = $_SESSION["id"];


    invia_notifica($conn, $tipologia, $messaggio, $id_mittente, $id_amico, NULL);


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
        $id_amico,
        $id_amico,
        $id_utente
    );

    $stmt->execute();
    $stmt->close();

    // refresh pagina
    header("Location: amici.php");
    exit();

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
    </style>
</head>

<body>

<?php

$base_url = "../";
include "../navbar.php";

$from = $_GET["from"] ?? "profilo";

if($from === "profilo"){

    $id_utente = $_SESSION["id"];

}else{

    $id_utente = intval($_GET["id"]);


}



$totale_amici = conta_amici($conn, $id_utente);

echo "<h2>Amici totali: " . $totale_amici . "</h2>";

if($from === "profilo"){

    echo "<br>";

    ?><a href = "../profilo.php"><?php Insert_immagine2($_SESSION["avatar"], $base_url, 150); ?></a><?php

?>

<div class="azioni-amicizia">

    <div class="aggiungi-amico-btn">
        <a href="aggiungi_amico.php">
            <?php Insert_immagine2("new_friend.png", "", 100); ?>
        </a>
    </div>

    <?php if(check_richiesta($conn, $_SESSION["id"]) > 0){
        ?>

        <div class="aggiungi-amico-btn">
        <a href="richieste_amicizia.php">
            <?php Insert_immagine2("richieste_amicizia_piene.png", "", 100); ?>
        </a>
        </div>

        <?php
    }else{
        ?>

        <div class="aggiungi-amico-btn">
        <a href="richieste_amicizia.php">
            <?php Insert_immagine2("richieste_amicizia.png", "", 100); ?>
        </a>
        </div>

        <?php
    }
    ?>

    <div class="aggiungi-amico-btn">
        <a href="utenti_bloccati.php">
            <?php Insert_immagine2("lock_utente.png", "", 100); ?>
        </a>
    </div>

</div>

<?php



}else{

    $_SESSION["id_amico"] = $id_utente;

    //per restituire l'avatar nel caso stiamo visualizzando gli amici di qualcun'altro
    Insert_immagine2(stampAvatar($conn, $id_utente), $base_url, 150);

?>
    
    <button class="input-schermo"
    onclick="window.location.href='../file_admin/info_utente.php?id=<?php echo $id_utente ?>&from=<?php echo $from ?>'">

        Indietro

    </button>

    <?php
}



/* =========================
   QUERY AMICI
========================= */

$sql = "SELECT

            CASE
                WHEN a.utente_id1 = ?
                THEN a.utente_id2

                ELSE a.utente_id1
            END AS id_amico,

            a.data_creazione,

            u.nickname,
            u.avatar

        FROM amicizie a

        INNER JOIN utenti u
        ON u.id = CASE
                    WHEN a.utente_id1 = ?
                    THEN a.utente_id2

                    ELSE a.utente_id1
                  END

        WHERE (a.utente_id1 = ?
        OR a.utente_id2 = ?)";


$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "iiii",
    $id_utente,
    $id_utente,
    $id_utente,
    $id_utente
);

$stmt->execute();

$result = $stmt->get_result();



/* =========================
   VISUALIZZAZIONE
========================= */

echo "<div class='contenitore-amici'>";


if ($result && $result->num_rows > 0) {

    while ($row = $result->fetch_assoc()) {

    //se passi come profilo dai la possibilità di cliccare l'utente successivo
    if($from === "profilo"){

        ?> <div class="amico-card"
            onclick="window.location.href='../file_admin/info_utente.php?id=<?php echo $row['id_amico']; ?>&from=<?php echo $from ?>-amici'"> <?php
    }else{

        ?> <div class="amico-card"><?php

    }
?>

    <div class="amico-left">

        <?php
        Insert_immagine2($row["avatar"],"../",90);
        ?>

        <div class="amico-info">

            <h2>
                <?php echo htmlspecialchars($row["nickname"]); ?>
            </h2>

            <span>
                Amici dal
                <?php echo formattaData($row["data_creazione"]); ?>
            </span>

        </div>

    </div>

    <div class="rimuovi-right">

    <?php if($from === "profilo"){ ?>
        <form method="POST">

            <input type="hidden"
                   name="id_amico"
                   value="<?php echo $row['id_amico']; ?>">

            <button class="btn-elimina"
                    type="submit"
                    name="rimuovi"
                    onclick="event.stopPropagation(); return conferma_eliminazione('<?php echo htmlspecialchars($row['nickname']); ?>')">

                Rimuovi

            </button>

        </form>
    <?php } ?>

    </div>

</div>

<?php
    }

} else {
?>
            <span>
                Nessuna amicizia presente
            </span>

<?php
}

echo "</div>";
?>

<br>

<?php
include "../footer.php";
?>

</body>
</html>



<script>

function conferma_eliminazione(nickname){

    return confirm(
        "Vuoi rimuovere " + nickname + " dagli amici?"
    );

}

</script>