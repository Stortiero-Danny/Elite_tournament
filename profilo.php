<?php
session_start();

include "file_accesso/connessione.php";
include "funzioni.php";
include "footer.php";

if (!isset($_SESSION["LOGGED"]) || $_SESSION["LOGGED"] !== true) {
    header("Location: file_accesso/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<link href="style.css" rel="stylesheet">

<style>

/* CONTENITORE CENTRALE PROFILO + STATISTICHE */
.profilo-container{
    display: flex;
    justify-content: center;
    align-items: flex-start;
    gap: 30px;
    max-width: 1000px;
    margin: 40px auto;
}

/* RIQUADRO PROFILO */
.riquadro{
    width: 520px;
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

/* AVATAR */
.profilo-avatar img{
    border-radius: 50%;
}

/* BOX STATISTICHE */
.profilo-box{
    width: 260px;
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

/* SINGOLA STATISTICA */
.profilo-stat{
    margin: 12px 0;
    padding: 12px;
    border-radius: 12px;
    background: #f5f5f5;
    cursor: pointer;
    transition: 0.2s;
}

.profilo-stat:hover{
    transform: scale(1.03);
    background: #e9e9e9;
}

.profilo-stat-numero{
    font-size: 22px;
    font-weight: bold;
    display: block;
}

.profilo-stat-testo{
    font-size: 14px;
    color: #555;
}

.centrale{
    text-align: center;
}
</style>

</head>
<body>

<?php
$base_url = "";
include "navbar.php";


$_SESSION["from"] = "profilo";
?>

<div class="profilo-container">

    <!-- =========================
         PROFILO
    ========================= -->
    <div class="riquadro">

        <h1 class="centrale">
            <?php echo $_SESSION["nickname"]; ?>
        </h1>

        <div class="profilo-avatar">
            <?php Insert_immagine2($_SESSION["avatar"], "", 250); ?>
        </div>

        <br>

        <?php insert_bandiera($conn, $_SESSION["id_nazione"], ""); ?>

        <h2>Informazioni utente</h2>

        <?php
        echo "<label>Nome: " . $_SESSION["nome"] . "</label>";
        echo "<br><label>Cognome: " . $_SESSION["cognome"] . "</label>";
        echo "<br><label>Email: " . $_SESSION["email"] . "</label>";
        echo "<br><label>Data di nascita: " . formattaData($_SESSION["data_nascita"]) . "</label>";

        $sesso = mostra_sesso($conn, $_SESSION["id_sesso"]);
        echo "<br><label>Sesso: " . $sesso . "</label>";

        $ruolo = mostra_ruolo($conn, $_SESSION["id_ruolo"]);
        echo "<br><label>Ruolo: " . $ruolo . "</label>";
        ?>

        <br><br>

        <button class="input-corto" onclick="window.location.href='mod_profilo.php'">
            Modifica profilo
        </button>

    </div>

    <!-- =========================
         STATISTICHE
    ========================= -->
    <div class="profilo-box">

        <?php
        $tot_tornei = conta_tornei_partecipati($conn, $_SESSION["id"]) ?? 0;
        $tot_tornei_vinti = conta_tornei_vinti($conn, $_SESSION["id"]) ?? 0;
        ?>

        <h2>Statistiche</h2>

        <div class="profilo-stat"
            onclick="window.location.href='tornei_vinti.php?id=<?php echo $_SESSION['id'] ?>&nickname_utente=<?php echo $_SESSION['nickname'] ?>'">

            <span class="profilo-stat-numero"><?php echo $tot_tornei_vinti ?></span>
            <span class="profilo-stat-testo">Tornei vinti</span>
        </div>

        <div class="profilo-stat"
            onclick="window.location.href='tornei_partecipati.php?id=<?php echo $_SESSION['id'] ?>&nickname_utente=<?php echo $_SESSION['nickname'] ?>'">

            <span class="profilo-stat-numero"><?php echo $tot_tornei ?></span>
            <span class="profilo-stat-testo">Tornei partecipati</span>
        </div>

    </div>

</div>

</body>
</html>

<script>
// evita cache back button
document.documentElement.style.display = "none";

window.addEventListener("pageshow", function (event) {
    const isBack =
        event.persisted ||
        (performance.getEntriesByType("navigation")[0]?.type === "back_forward");

    if (isBack) {
        window.location.reload();
    } else {
        document.documentElement.style.display = "block";
    }
});
</script>