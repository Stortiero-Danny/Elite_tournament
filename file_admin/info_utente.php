<?php
session_start();

include "../file_accesso/connessione.php";
include "../funzioni.php";
include "../footer.php";

?>

<!DOCTYPE html>
<html>

<head>
    <link href="../style.css" rel="stylesheet">
    <title>Info utente</title>
</head>

<body>

<?php

$base_url = "../";
include "../navbar.php";


/* =========================
   CONTROLLO LOGIN
========================= */

if (!isset($_SESSION["LOGGED"]) || $_SESSION["LOGGED"] !== true) {
    header("Location: ../file_accesso/login.php");
    exit();
}


/* =========================
   CONTROLLO ID UTENTE
========================= */

if (!isset($_GET['id'])) {
    die("Utente non trovato");
}

$id = intval($_GET['id']);

$from = $_GET["from"] ?? "lista_utenti";

if($_SESSION["from"] == "tornei_gioco"){
    
    $_SESSION["from"] = "tornei_gioco_utente";

}else{
    $_SESSION["from"] = $from;
}


//AGGIUNGI AMICO

if(isset($_POST["aggiungi"])) {

    $id_amico = intval($_POST["id_amico"]);
    $id_utente = $_SESSION["id"];

    /* =========================
       CONTROLLO ESISTENZA
    ========================= */

    $sql_check = "
        SELECT data_creazione
        FROM amicizie
        WHERE
        (utente_id1 = ? AND utente_id2 = ?)
        OR
        (utente_id1 = ? AND utente_id2 = ?)
    ";

    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("iiii", $id_utente, $id_amico, $id_amico, $id_utente);
    $stmt_check->execute();

    $result = $stmt_check->get_result();

    if($result->num_rows == 0) {

    
        /* =========================
           Richiesta di amicizia
        ========================= */


        invia_richiesta($conn, $id_utente, $id_amico);
        
    }
}





/* =========================
   GESTIONE CAMBIO RUOLO
========================= */

if (isset($_POST['cambia_ruolo'])) {

    $id_utente = intval($_POST['id_utente']);
    $id_ruolo = intval($_POST['id_ruolo']);

    if ($id_ruolo == 1) {

        $nuovo_ruolo = 2;

        $messaggio = "Sei stato promosso ad Admin da " .
                     $_SESSION["nickname"] . " :)";

    } else {

        $nuovo_ruolo = 1;

        $messaggio = "Sei stato retrocesso ad Utente da " .
                     $_SESSION["nickname"];
    }


    /* =========================
       CREAZIONE NOTIFICA
    ========================= */

    $sql_notifica = "INSERT INTO notifiche
    (
        tipologia,
        data_invio,
        messaggio,
        id_mittente,
        id_riferimento
    )

    VALUES
    (
        ?,
        CURRENT_TIMESTAMP,
        ?,
        ?,
        NULL
    )";

    $stmt_notifica = $conn->prepare($sql_notifica);

    $tipologia = "comunicazione";
    $id_mittente = $_SESSION["id"];

    $stmt_notifica->bind_param(
        "ssi",
        $tipologia,
        $messaggio,
        $id_mittente
    );

    $stmt_notifica->execute();


    /* =========================
       RECUPERO ID NOTIFICA
    ========================= */

    $id_notifica = $conn->insert_id;


    /* =========================
       ASSOCIA UTENTE-NOTIFICA
    ========================= */

    $sql_associazione = "INSERT INTO utenti_notifiche
    (
        id_notifica,
        id_utente_destinatario,
        stato,
        data_lettura
    )

    VALUES
    (
        ?,
        ?,
        ?,
        NULL
    )";

    $stmt_associazione = $conn->prepare($sql_associazione);

    $stato = "Non letta";

    $stmt_associazione->bind_param(
        "iis",
        $id_notifica,
        $id_utente,
        $stato
    );

    $stmt_associazione->execute();


    /* =========================
       UPDATE RUOLO
    ========================= */

    $sql_ruolo = "UPDATE utenti
                  SET id_ruolo = ?
                  WHERE id = ?";

    $stmt_ruolo = $conn->prepare($sql_ruolo);

    $stmt_ruolo->bind_param(
        "ii",
        $nuovo_ruolo,
        $id_utente
    );

    $stmt_ruolo->execute();


    header("Location: info_utente.php?id=" . $id_utente);
    exit();
}


/* =========================
   QUERY UTENTE
========================= */

$sql = "SELECT
            u.id,
            u.nickname,
            u.nome AS nome_utente,
            u.cognome AS cognome_utente,
            u.data_nascita,
            u.id_sesso,
            u.email,
            u.id_nazione,
            n.nome as nome_nazione,
            u.avatar,
            u.id_ruolo,
            r.nome AS ruolo

        FROM utenti u

        INNER JOIN ruoli r
            ON u.id_ruolo = r.id
        INNER JOIN nazioni n
            ON n.id = u.id_nazione

        WHERE u.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

$user = $result->fetch_assoc();

if (!$user) {
    die("Utente inesistente");
}

?>


<div class="profilo-utente-container">

    <!-- =========================
         HEADER PROFILO
    ========================= -->

    <div class="profilo-header">

        <h1 class="profilo-nickname">
            <?php echo htmlspecialchars($user["nickname"]); ?>
        </h1>

        <div class="profilo-avatar">
            <?php insert_immagine2($user["avatar"], "../", 250); ?>
        </div>

        <div class="profilo-bandiera">
            <?php insert_bandiera($conn, $user["id_nazione"], "../"); ?>
        </div>


        <br>
        <?php

        //CONTROLLO AMICIZIA
        if(check_amico($conn, $_SESSION["id"], $user["id"]) != "non_amico"){

            echo check_amico($conn, $_SESSION["id"], $user["id"]);
        }else{

        ?>
            <form method="POST">

            <input type="hidden"
               name="id_amico"
               value="<?php echo $user['id']; ?>">

            <button class = "aggiungi-amico-btn"
                type="submit"
                name="aggiungi"
                style="background:none;border:none;cursor:pointer;"
                onclick="event.stopPropagation();">

                <?php Insert_immagine2("new_friend.png", "../file_amicizie/", 80); ?>

            </button>

            </form>
        <?php

        }
        
        ?>

    </div>



    <!-- =========================
         CONTENUTO PROFILO
    ========================= -->

    <div class="profilo-contenuto">

        <!-- =========================
             INFO SINISTRA
        ========================= -->

        <div class="profilo-box">

            <h2>Informazioni utente</h2>

            <div class="profilo-info-riga">
                <span class="profilo-label">Nome</span>

                <span class="profilo-valore">
                    <?php echo htmlspecialchars($user["nome_utente"]); ?>
                </span>
            </div>

            <div class="profilo-info-riga">
                <span class="profilo-label">Cognome</span>

                <span class="profilo-valore">
                    <?php echo htmlspecialchars($user["cognome_utente"]); ?>
                </span>
            </div>

            <div class="profilo-info-riga">
                <span class="profilo-label">Data di nascita</span>

                <span class="profilo-valore">
                    <?php echo formattaData($user["data_nascita"]); ?>
                </span>
            </div>

            <div class="profilo-info-riga">

                <span class="profilo-label">Sesso</span>

                <span class="profilo-valore">

                    <?php
                    $sesso = mostra_sesso($conn, $user["id_sesso"]);
                    echo htmlspecialchars($sesso);
                    ?>

                </span>

            </div>

            <div class="profilo-info-riga">

                <span class="profilo-label">Email</span>

                <span class="profilo-valore">
                    <?php echo htmlspecialchars($user["email"]); ?>
                </span>

            </div>

            <div class="profilo-info-riga">

                <span class="profilo-label">Nazione</span>

                <span class="profilo-valore">
                    <?php echo htmlspecialchars($user["nome_nazione"]); ?>
                </span>

            </div>

            <div class="profilo-info-riga">

                <span class="profilo-label">Ruolo</span>

                <span class="profilo-valore">
                    <?php echo htmlspecialchars($user["ruolo"]); ?>
                </span>

            </div>

        </div>



        <!-- =========================
             STATISTICHE DESTRA
        ========================= -->

        <?php

        $tot_tornei = conta_tornei_partecipati($conn, $user["id"]) ?? 0;

        $tot_tornei_vinti = conta_tornei_vinti($conn, $user["id"]) ?? 0;

        $tot_amici = conta_amici($conn, $user["id"]) ?? 0;

        $tot_squadre = conta_squadre($conn, $user["id"]) ?? 0;


        ?>
        

        <div class="profilo-box">

            <h2>Statistiche</h2>

            <div class="profilo-stat"
                onclick="window.location.href='../tornei_vinti.php?id=<?php echo $user['id'] ?>&nickname_utente=<?php echo $user['nickname'] ?>'">
                
                <span class="profilo-stat-numero"><?php echo $tot_tornei_vinti ?></span>
                <span class="profilo-stat-testo">Tornei vinti</span>
            </div>

            <div class="profilo-stat"
                onclick="window.location.href='../tornei_partecipati.php?id=<?php echo $user['id'] ?>&nickname_utente=<?php echo $user['nickname'] ?>'">

                <span class="profilo-stat-numero"><?php echo $tot_tornei ?></span>
                <span class="profilo-stat-testo">Tornei partecipati</span>
            </div>

            
        <div class="stats-riga">

            <div class="profilo-stat"
                onclick="window.location.href='../file_amicizie/amici.php?from=<?php echo $from?>&id=<?php echo $user['id'] ?>'">

                <span class="profilo-stat-numero"><?php echo $tot_amici ?></span>
                <span class="profilo-stat-testo">Amici</span>

            </div>

            <div class="profilo-stat"
            onclick="window.location.href='../file_squadre/visualizza_squadre.php?from=<?php echo $_SESSION['from']?>&id=<?php echo $user['id'] ?>&nickname_utente=<?php echo $user['nickname'] ?>'">

                <span class="profilo-stat-numero"><?php echo $tot_squadre ?></span>
                <span class="profilo-stat-testo">Squadre</span>

            </div>

        </div>

        </div>

    </div>

</div>



<?php


/* =========================
   CAMBIO RUOLO
========================= */

if ($user["id_ruolo"] != 3 &&
    $_SESSION["id_ruolo"] != 1) {
?>

<br>

<form method="POST">

    <input type="hidden"
           name="id_utente"
           value="<?php echo $user['id']; ?>">

    <input type="hidden"
           name="id_ruolo"
           value="<?php echo $user['id_ruolo']; ?>">

    <button class="input-schermo"
            type="submit"
            name="cambia_ruolo">

        Cambia ruolo

    </button>

</form>

<?php
}
?>



<?php

/* =========================
   TORNEI CREATI
========================= */

$id_utente = $user["id"];

$sql_tornei = "SELECT t.id
               FROM tornei t
               WHERE t.id_creatore = ?";

$stmt_tornei = $conn->prepare($sql_tornei);

$stmt_tornei->bind_param("i", $id_utente);

$stmt_tornei->execute();

$result_tornei = $stmt_tornei->get_result();


if ($result_tornei->num_rows > 0) {

    $id_torneo = isset($_GET["id_torneo"])
        ? intval($_GET["id_torneo"])
        : 0;
?>

<form method="POST"
      action="info_tornei_creati.php?from=<?php echo $from; ?>&id_torneo=<?php echo $id_torneo; ?>">

    <input type="hidden"
           name="id_utente"
           value="<?php echo $id_utente; ?>">

    <button class="input-schermo"
            type="submit">

        Tornei Creati

    </button>

</form>

<?php
}


/* =========================
   BOTTONE INDIETRO
========================= */

$from = $_GET["from"] ?? "lista_utenti";

if ($from === "lista_utenti") {
?>

<button class="input-schermo"
onclick="window.location.href='Lista_utenti.php'">

    Indietro

</button>

<?php
}elseif($from === "info_notifica_mittente"){
?>

<button class="input-schermo"
onclick="window.location.href='../info_notifica.php?from=<?php echo $from; ?>&id=<?php echo $_SESSION['id_notifica'] ?>'">

    Indietro

</button>

<?php

}elseif($from === "profilo" || $from === "profilo-amici"){

if($from === "profilo-amici"){
    $from = "profilo";
}

?>

<button class="input-schermo"
onclick="window.location.href='../file_amicizie/amici.php?from=<?php echo $from ?>'">

    Indietro

</button>

<?php

}elseif($from == "ricerca_utenti"){

?>

<button class="input-schermo"
onclick="window.location.href='../file_amicizie/aggiungi_amico.php?from=<?php echo $from ?>'">

    Indietro

</button>

<?php

}elseif($from == "richiesta"){

?>

<button class="input-schermo"
onclick="window.location.href='../file_amicizie/richieste_amicizia.php?from=<?php echo $from ?>'">

    Indietro

</button>

<?php


}elseif($from == "utente_bloccato"){

?>

<button class="input-schermo"
onclick="window.location.href='../file_amicizie/utenti_bloccati.php?from=<?php echo $from ?>'">

    Indietro

</button>

<?php

}elseif($from == "info_squadra"){

?>

<button class="input-schermo"
onclick="window.location.href='../file_squadre/info_squadra.php?id=<?php echo $_SESSION['id_squadra'] ?>&nome=<?php echo $_SESSION['nome_squadra'] ?>'">

    Indietro

</button>

<?php


}elseif($from == "squadre_utente"){

?>

<button class="input-schermo"
onclick="window.location.href='../file_squadre/squadre.php'">

    Indietro

</button>

<?php


} else {

?>

<button class="input-schermo"
onclick="window.location.href='../info_torneo.php?from=<?php echo $from; ?>&id=<?php echo $_SESSION['id_torneo']; ?>'">

    Indietro

</button>

<?php
}

?>

</body>
</html>