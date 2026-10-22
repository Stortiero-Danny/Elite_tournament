<?php
session_start();

require_once __DIR__ . "/file_accesso/connessione.php";
require_once __DIR__ . "/funzioni.php";


/* =========================
   ISCRIZIONE
========================= */
if (isset($_POST["iscriviti"])) {

    $id_torneo = intval($_POST["id_torneo"]);
    $id_utente = $_SESSION["id"];

    aggiungi_partecipante($conn, $id_utente, $id_torneo);

    header("location: info_torneo.php?id=" . $id_torneo);
    exit();
}

/* =========================
   CANCELLA ISCRIZIONE
========================= */
if (isset($_POST["annulla_iscrizione"])) {

    $id_torneo = intval($_POST["id_torneo"]);
    $id_utente = $_SESSION["id"];

    togli_partecipante($conn, $id_utente, $id_torneo);

    header("location: info_torneo.php?id=" . $id_torneo);
    exit();
}


if (isset($_POST["annulla_iscrizione_squadra"])) {

    $id_torneo = intval($_POST["id_torneo"]);
    $id_squadra = intval($_POST["id_squadra"]);
    $nome_squadra = $_POST["nome_squadra"];
    $nome_gioco = $_POST["nome_gioco"];

    $id_utente = $_SESSION["id"];

    $sql = "SELECT id_utente
        FROM utenti_squadre
        WHERE id_squadra = ?
        AND id_utente != ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_squadra, $id_utente);
    $stmt->execute();

    $result = $stmt->get_result();

    $stmt->close();

    while($row = $result->fetch_assoc()){

    invia_notifica(
        $conn,
        "torneo",
        $_SESSION["nickname"] . " ha annullato l'iscrizione della squadra " . $nome_squadra . " a questo torneo di " . $nome_gioco,
        $id_utente,
        $row["id_utente"],
        $id_torneo
        );

    }

    togli_squadra($conn, $id_squadra, $id_torneo);

    header("location: info_torneo.php?id=" . $id_torneo);
    exit();
}




if (isset($_POST["conferma_checkin"])) {

    $id_checkin = intval($_POST["id_checkin"]);

    $stmt = $conn->prepare("
        UPDATE checkin
        SET stato = 'verificato'
        WHERE id = ?
        AND stato = 'in_attesa'
    ");

    $stmt->bind_param("i", $id_checkin);
    $stmt->execute();
    $stmt->close();
}


if (isset($_POST["avvia_torneo"])) {

    $id_torneo = intval($_POST["id_torneo"]);

    $sql = "UPDATE tornei SET id_stato = 4 WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_torneo);
    $stmt->execute();
    $stmt->close();
}


?>

<!DOCTYPE html>
<html>
<head>

    <link href="style.css" rel="stylesheet">
    <title>Info torneo</title>

    <style>
        .centro { text-align: center; }

        .accetta, .rifiuta {
            width: 180px;
            height: 100px;
            display: block;
            margin: 20px auto;
        }

        a { font-size: 40px; }

        /* PARTE SINISTRA */
    .amico-left{
        display: flex;
        align-items: center;
        gap: 20px;
    }


    /* IMMAGINE */
    .amico-left img{
        width: 90px;
        height: 90px;
        object-fit: cover;
        border-radius: 50%;
    }
    </style>

</head>

<body>

<?php
include "navbar.php";
include "footer.php";

scheduler_checkin($conn);

/* =========================
   CONTROLLO ID TORNEO
========================= */
if (!isset($_GET["id"])) {
    die("Torneo non trovato");
}

$id = intval($_GET["id"]);

$_SESSION["id_torneo"] = $id;


/* =========================
   ORIGINE PAGINA
========================= */

$from = $_GET["from"] ?? "tornei_gioco";

if($from != "tornei_partecipati" && $from != "tornei_vinti"){
    $_SESSION["from"] = $from;
}


/* =========================
   QUERY TORNEO
========================= */
$sql = "SELECT 
            t.*,
            g.nome AS nome_gioco,
            g.id AS id_gioco,
            g.immagine,
            u.nickname AS nome_creatore,
            u.id AS id_utente,
            u.avatar AS avatar_creatore,
            u.id as id_creatore,
            st.nome AS stato,
            md.nome AS modalità
        FROM tornei t
        INNER JOIN giochi g ON t.id_gioco = g.id
        INNER JOIN utenti u ON t.id_creatore = u.id
        INNER JOIN stato_torneo st ON t.id_stato = st.id
        INNER JOIN mod_giochi md ON t.id_mod_gioco = md.id
        WHERE t.id = $id";

$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    die("Torneo non trovato");
}

$row = mysqli_fetch_assoc($result);


$_SESSION["max_giocatori"] = $row["max_players_squadre"];
$_SESSION["creatore"] = $row["id_creatore"];
$_SESSION["nick_creatore"] = $row["nome_creatore"];
$_SESSION["id_torneo"] = $id;
$_SESSION["nome_gioco"] = $row["nome_gioco"];

?>

<!-- =====================================================
     PAGINA TORNEO
===================================================== -->
<div class="pagina-torneo">

    <!-- =========================
         BOX INFO TORNEO
    ========================= -->
    <div class="riquadro-info">

        <!-- TITOLO + IMMAGINE -->
        <h1><?php echo htmlspecialchars($row["nome_gioco"]); ?></h1>
        <?php insert_immagine2($row["immagine"], "", 500); ?>

        <!-- INFO + CREATOR -->
        <div class="info-doppia-colonna">

            <!-- INFO TORNEO -->
            <div class="info-sezione info-colonna">
                <h2>Informazioni torneo</h2>

                <div class="info-testo">
                    <strong>Data:</strong> <?php echo formattaData($row["data"]); ?>
                    <br><br>

                    <strong>Ora:</strong> <?php echo $row["ora"]; ?>
                    <br><br>

                    <strong>Stato:</strong> 
                    <?php
                    if($row["stato"] === "In corso"){
                        echo "<span style='color: green;'>" . $row["stato"] . "</span>";
                    }elseif($row["stato"] === "checkin"){
                        echo "<span style='color: yellow;'>" . $row["stato"] . "</span>";
                    }elseif($row["stato"] === "Terminato"){
                        echo "<span style='color: red;'>" . $row["stato"] . "</span>";
                    }else{
                        echo "<span style='color: blue;'>" . $row["stato"] . "</span>";
                    }
                    ?>
                    <br><br>

                    <strong>Posti disponibili:</strong> <?php echo $row["n_posti"]; ?>
                    <br><br>

                    <strong>Round:</strong> <?php echo $row["n_round"]; ?>
                    <br><br>

                    <strong>Modalità:</strong> <?php echo htmlspecialchars($row["modalità"]); ?>

                    <?php if ($row["modalità"] == "squadre") { ?>
                        <br><br>
                        <strong>Max squadre:</strong> <?php echo $row["max_players_squadre"]; ?>
                    <?php } ?>
                </div>
            </div>

            <!-- CREATOR -->
            <div class="info-sezione info-colonna">
                <div class="centro">

                    <h2>Creatore</h2>
                    <h2><?php echo htmlspecialchars($row["nome_creatore"]); ?></h2>

                    <br>

                    <?php
                    if (($from != "info_tornei_creati") &&
                        isset($_SESSION["LOGGED"]) &&
                        $_SESSION["LOGGED"] === true) {

                        if ($_SESSION["id"] == $row["id_creatore"]) {
                            echo '<a href="profilo.php">';
                            insert_immagine2($row["avatar_creatore"], "", 180);
                            echo '</a>';
                        } else {
                            $_SESSION["id_torneo"] = $id;
                            ?>
                            <a href="file_admin/info_utente.php?from=<?php echo $from ?>&id=<?php echo $row["id_creatore"] ?>&id_torneo=<?php echo $id ?>">
                                <?php insert_immagine2($row["avatar_creatore"], "", 180); ?>
                            </a>
                            <?php
                        }

                    } else {
                        insert_immagine2($row["avatar_creatore"], "", 180);
                    }
                    ?>

                </div>
            </div>

        </div><!-- info-doppia-colonna -->
    

    <!-- =========================
         REGOLAMENTO
    ========================= -->
    <div class="info-sezione">
        <h2>Regolamento</h2>
        <div class="regolamento-box">
            <?php echo htmlspecialchars(trim($row["regolamento"])); ?>
        </div>
    </div>

    <!-- =========================
         AZIONI (ISCRIZIONE / MODIFICA)
    ========================= -->
    <div class="info-sezione">



        <?php

        if($row["stato"] == "Fissato"){

        
        if (isset($_SESSION["LOGGED"]) &&
                  $_SESSION["LOGGED"] === true &&
                  $row["stato"] == "Fissato") { 

            if ($_SESSION["id"] == $row["id_creatore"]) { ?>

                <button class="accetta"
                        style="background:#1d5f7a;color:white"
                        onclick="window.location.href='file_admin/modifica_torneo.php?id_torneo=<?php echo $id; ?>&from=<?php echo $from; ?>'">
                    Modifica
                </button>

                <form method = "POST">

                <input type="hidden" name="id_torneo" value="<?php echo $row["id"]; ?>">

                <button class="accetta"
                        type = "submit"
                        style="color:white"
                        name = "avvia_torneo">
                    Avvia torneo
                </button>
                </form>

            <?php } else {

            if($row["n_posti"] > 0 || check_iscrizione($conn, $_SESSION["id"], $row["id"]) || check_iscrizione_squadra($conn, $_SESSION["id"], $row["id"])){

            if($row["modalità"] == "solo"){    

              ?>

                <form method="POST">
                    <input type="hidden" name="id_torneo" value="<?php echo $row["id"]; ?>">

                    <?php if (!check_iscrizione($conn, $_SESSION["id"], $row["id"])) { ?>
                        <button class="accetta" type="submit" name="iscriviti">Iscriviti</button>
                    <?php } else { ?>
                        <button class="rifiuta" type="submit" name="annulla_iscrizione">Annulla iscrizione</button>
                    <?php } ?>
                </form>

            <?php 
            
            }else{

$iscrizione = check_iscrizione_squadra($conn, $_SESSION["id"], $row["id"]);

if (!$iscrizione) {
?>
    <button class="accetta"
            onclick="window.location.href='file_squadre/iscrivi_squadra.php'">
        Iscrivi squadra
    </button>

<?php
} else {

    $id_squadra = $iscrizione["id_squadra"];
    $nome_squadra = $iscrizione["nome_squadra"];
    $nome_gioco = $iscrizione["nome_gioco"];

?>
    <form method="POST">
        <input type="hidden" name="id_torneo" value="<?php echo $row["id"]; ?>">

        <input type="hidden" name="id_squadra" value="<?php echo $id_squadra; ?>">
        <input type="hidden" name="nome_squadra" value="<?php echo $nome_squadra; ?>">
        <input type="hidden" name="nome_gioco" value="<?php echo $nome_gioco; ?>">

        <button class="rifiuta"
                type="submit"
                name="annulla_iscrizione_squadra"
                style="width: 250px; height: 100px;">
            Annulla iscrizione squadra
        </button>
    </form>
<?php
}

            }
            
            }else{
                echo "<p style = 'color: red; text-align: center'> POSTI ESAURITI </p>";
            }

            }//se non sei il creatore
            ?>

        <?php } elseif (!isset($_SESSION["LOGGED"]) && $row["stato"] == "Fissato") { ?>

            <?php
            if($row["n_posti"] > 0){

            ?>
            <div style="text-align:center;">
                <a href="file_accesso/login.php">Accedi per iscriverti</a>
            </div>

        <?php
            }else{
                echo "<p style = 'color: red; text-align: center'> POSTI ESAURITI </p>";
            }
        }
        
        
        }elseif($row["stato"] == "checkin"){

        
            if($row["modalità"] == "solo"){


            if (check_iscrizione($conn, $_SESSION["id"], $row["id"])) {

            $checkin = get_checkin($conn, $row["id"], $_SESSION["id"]);


                    if ($checkin) {

                    ?><h3 style = "text-align: center">Conferma presenza entro 5 minuti</h3><?php

                    aggiorna_checkin_scaduti($conn);

                
                ?>

                <p id="timer" style = "text-align: center"></p>
                    <div>
                        


                        <form method="POST">
                            <input type="hidden" name="id_checkin" value="<?php echo $checkin['id']; ?>">
                            <button class = "accetta" name="conferma_checkin">Conferma Check-in</button>
                        </form>
                    </div>
                <?php
                    }else{
                        
                    ?><h3 style = "text-align: center; color: green">Check-in effettuato</h3><?php
                    }

                }
            }
            
            if($_SESSION["id"] == $row["id_creatore"]){

            ?>
            <h3 style = "text-align: center">Conferma presenza entro 5 minuti</h3>

            <p style = 'text-align: center; color: yellow'>Check-in in corso</p>

            <?php

            }


        }elseif($row["stato"] == "In corso"){

            elimina_partecipanti_scaduti($conn, $row["id"]);

            if(!check_min_partecipanti($conn, $row["id"])){

                termina_torneo($conn, $row["id"]);
            }

            elimina_checkin_utente($conn, $row["id"]);

            if(check_min_partecipanti($conn, $row["id"])){

                echo "<p style = 'text-align: center; color: green'>Torneo Iniziato!</p>";

                ?><button class = "accetta" style="background:#1d5f7a;color:white" onclick="window.location.href='file_svolgimento/albero_torneo.php?id=<?php echo $_SESSION['id_torneo'] ?>'">Visualizza</button><br><br><?php

                }
        }else{

            echo "<p style = 'text-align: center; color: red'>Torneo terminato</p>";
            
            if(!check_min_partecipanti($conn, $row["id"])){

                echo "<p style = 'text-align: center; color: red'>Partecipanti minimi insufficienti</p>";
            }else{
                ?><button class = "accetta" style="background:#1d5f7a;color:white" onclick="window.location.href='file_svolgimento/albero_torneo.php?id=<?php echo $_SESSION['id_torneo'] ?>'">Visualizza</button><br><br><?php
            }

        }
        
        
        ?>

    </div>
    
    </div><!-- riquadro-info -->

    <!-- =========================
         PARTECIPANTI
    ========================= -->
    <div class="partecipanti-box">

        <?php if($row["modalità"] == "solo"){
        ?><h2>Partecipanti (<?php echo conta_partecipanti($conn, $id)?>)</h2><?php
        }else{
        ?><h2>Squadre partecipanti (<?php echo conta_squadre_torneo($conn, $id)?>)</h2><?php
        } ?>


        <div class="lista-partecipanti">

            <?php
            
            if($row["modalità"] == "solo"){

            /* =========================
                    PARTECIPANTI
            ========================= */

            $sql_partecipanti = "
            SELECT u.id, u.nickname, u.avatar
            FROM partecipanti p
            INNER JOIN utenti u ON p.id_utente = u.id
            WHERE p.id_torneo = ?
            ORDER BY u.nickname
            ";

            $stmt_partecipanti = $conn->prepare($sql_partecipanti);
            $stmt_partecipanti->bind_param("i", $id);
            $stmt_partecipanti->execute();
            $result_partecipanti = $stmt_partecipanti->get_result();


            
            if ($result_partecipanti && mysqli_num_rows($result_partecipanti) > 0) { ?>

                <?php while ($utente = mysqli_fetch_assoc($result_partecipanti)) { ?>

                    <div class="partecipante-card"
                        <?php if (isset($_SESSION["LOGGED"])) {

                            if ($utente["id"] == $_SESSION["id"]) {
                                echo "onclick=\"location.href='profilo.php'\"";
                            } else {
                                echo "onclick=\"location.href='file_admin/info_utente.php?from=<?php echo $from ?>&id={$utente['id']}'\"";
                            }
                        } ?>>
                    <div class="amico-left">
                        <?php insert_immagine2($utente["avatar"], "", 70); ?>

                        <span><?php echo htmlspecialchars($utente["nickname"]); ?></span>

                        <?php


                        if (isset($_SESSION["LOGGED"]) &&
                            $_SESSION["LOGGED"] === true) {

                            if ($utente["id"] == $_SESSION["id"]){
                            echo "(tu)";
                            }
                        }
                        ?>

                    </div><!-- amico left -->

                    </div>

                <?php } ?>

            <?php } else { ?>
                <p>Nessun giocatore iscritto.</p>
            <?php } 
            
            }else{


                /* =========================
                    SQUADRE PARTECIPANTI
                ========================= */

                $sql_partecipanti_squadre = "SELECT s.id, s.nome, s.logo_squadra
                             FROM squadre_partecipanti sp INNER JOIN squadre s ON sp.id_squadra = s.id
                             WHERE sp.id_torneo = ?";

                $stmt_partecipanti_squadre = $conn->prepare($sql_partecipanti_squadre);
                $stmt_partecipanti_squadre->bind_param("i", $id);
                $stmt_partecipanti_squadre->execute();
                $result_partecipanti_squadre = $stmt_partecipanti_squadre->get_result();


                if ($result_partecipanti_squadre && mysqli_num_rows($result_partecipanti_squadre) > 0) { ?>

                
                <?php while ($squadra = mysqli_fetch_assoc($result_partecipanti_squadre)) { ?>

                    <div class="partecipante-card"
                        <?php if (isset($_SESSION["LOGGED"])) {

                                echo "onclick=\"location.href='file_squadre/info_squadra.php?nome={$squadra['nome']}&id={$squadra['id']}'\"";

                        } ?>>
                    <div class="amico-left">
                        <?php Insert_logo_squadra($squadra["logo_squadra"], "file_squadre/", 70); ?>

                        <span><?php echo htmlspecialchars($squadra["nome"]); ?></span>

                        <?php


                        if (isset($_SESSION["LOGGED"]) &&
                            $_SESSION["LOGGED"] === true) {

                            if (check_presenza_in_squadra($conn, $_SESSION["id"], $squadra["id"])){
                            echo "(tua)";
                            }
                        }
                        ?>

                    </div><!-- amico left -->

                    </div>

                <?php } ?>

            <?php } else { ?>
                <p>Nessuna squadra iscritta.</p>
            <?php }


            }
            ?>

        </div>
    </div>

</div><!-- pagina-torneo -->

<!-- =========================
        BOTTONE INDIETRO 
  ========================= -->
<br>

<?php if ($from === "gestione") { ?>

    <button class="input-schermo"
            onclick="window.location.href='file_admin/gestione_tornei.php'">
        Indietro
    </button>

<?php } elseif ($from === "info_tornei_creati") { 

    $id_utente = intval($_GET["id_utente"]);
    $from_lista = $_GET["from_lista"];
    $id_torneo_originale = $_GET["id_torneo_originale"];

?>

    <button class="input-schermo"
            onclick="window.location.href='file_admin/info_tornei_creati.php?id_utente=<?php echo $id_utente; ?>&from=<?php echo $from_lista ?>&id_torneo=<?php echo $id_torneo_originale ?>'">
        Indietro
    </button>

<?php } elseif ($from === "info_notifica") { ?>

    <button class="input-schermo"
            onclick="window.location.href='info_notifica.php?id=<?php echo $_SESSION['id_notifica'] ?>'">
        Indietro
    </button>

<?php } elseif ($from === "squadre") { ?>

    <button class="input-schermo"
            onclick="window.location.href='file_squadre/info_squadra.php?id=<?php echo $_SESSION['id_squadra'] ?>&nome=<?php echo $_SESSION['nome_squadra'] ?>'">
        Indietro
    </button>

<?php } elseif ($from === "tornei_precedenti") { ?>

    <button class="input-schermo"
            onclick="window.location.href='tornei_precedenti.php?id=<?php echo $row['id_gioco']; ?>'">
        Indietro
    </button>

<?php } elseif ($from === "iscrizioni") { ?>

    <button class="input-schermo"
            onclick="window.location.href='iscrizioni.php'">
        Indietro
    </button>

<?php } elseif ($from === "tornei_vinti") {

    $id_utente = intval($_GET["id_utente"]);
    $nickname = $_GET["nickname_utente"];

    ?>

    <button class="input-schermo"
            onclick="window.location.href='tornei_vinti.php?id=<?php echo $id_utente ?>&nickname_utente=<?php echo $nickname ?>'">
        Indietro
    </button>

<?php }elseif ($from === "tornei_partecipati") {

    $id_utente = intval($_GET["id_utente"]);
    $nickname = $_GET["nickname_utente"];

    ?>

    <button class="input-schermo"
            onclick="window.location.href='tornei_partecipati.php?id=<?php echo $id_utente ?>&nickname_utente=<?php echo $nickname ?>'">
        Indietro
    </button>

<?php }elseif ($from === "tornei_precedenti_organizzatore") {
    
    ?>
    <button class="input-schermo"
            onclick="window.location.href='file_admin/tornei_precedenti_organizzatore.php'">
        Indietro
    </button>
    <?php

    }else { ?>

    <button class="input-schermo"
            onclick="window.location.href='tornei_gioco.php?id=<?php echo $row['id_gioco']; ?>'">
        Indietro
    </button>

<?php } ?>

</body>
</html>



<script>
        let deadline = new Date("<?php echo $checkin['deadline']; ?>").getTime();

        let x = setInterval(function () {

            let now = new Date().getTime();
            let distance = deadline - now;

            let minutes = Math.floor(distance / (1000 * 60));
            let seconds = Math.floor((distance % (1000 * 60)) / 1000);

            document.getElementById("timer").innerHTML =
                minutes + "m " + seconds + "s";

            if (distance < 0) {
                clearInterval(x);
                document.getElementById("timer").innerHTML = "SCADUTO";
                document.getElementById("timer").style.color = "red";

                avvia_torneo($conn, $row["id"]);
            }

        }, 1000);
    </script>