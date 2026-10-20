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
    <title>Notifiche</title>
</head>

<body>

<?php

$base_url = "";
include "navbar.php";

/* controllo login */
if (!isset($_SESSION["LOGGED"]) || $_SESSION["LOGGED"] !== true) {
    header("Location: file_accesso/login.php");
    exit();
}

$id_corrente = $_SESSION["id"];



$tot_notifiche = conta_notifiche($conn, $id_corrente);

if($tot_notifiche > 30){
    
    libera_notifiche($conn, $tot_notifiche, $id_corrente);
    $tot_notifiche = 30;
}


/* =========================
   QUERY NOTIFICHE
========================= */

$sql = "SELECT 
            n.id AS id_notifica,
            n.tipologia AS tipologia_notifica,
            n.data_invio AS data_notifica,
            n.messaggio AS messaggio_notifica,
            n.id_mittente,
            un.stato

        FROM notifiche n

        INNER JOIN utenti_notifiche un
            ON n.id = un.id_notifica

        WHERE un.id_utente_destinatario = $id_corrente

        ORDER BY n.data_invio DESC";

$result = mysqli_query($conn, $sql);

?>

<h1>Notifiche(<?php echo $tot_notifiche ?>)</h1>

<button class="input-schermo"
onclick="window.location.href='home.php'">
    Indietro
</button>

<?php
if ($result && mysqli_num_rows($result) > 0) {
?>

<div class="contenitore-notifiche">

<?php
while ($row = mysqli_fetch_assoc($result)) {

    /* classe dinamica */
    if($row["stato"] == "Non letta"){

        $class = "notifica-card notifica-non-letta";

    }else{
        $class = "notifica-card";
    }

?>

    <div class="<?php echo $class; ?>"
         onclick="window.location.href='info_notifica.php?id=<?php echo $row['id_notifica']; ?>'">

        <div class="notifica-header">

            <span class="tipo-notifica">
                <?php echo htmlspecialchars($row["tipologia_notifica"]); ?>
            </span>

            <span class="data-notifica">
                <?php
                echo $row["data_notifica"];
                ?>
            </span>

        </div>

        <div class="messaggio-notifica">
            <?php echo htmlspecialchars($row["messaggio_notifica"]); ?>
        </div>




    </div>

<?php
}
?>

</div>

<?php
} else {
    echo "<br><br><h2>Nessuna notifica presente</h2>";
}
?>

</body>
</html>