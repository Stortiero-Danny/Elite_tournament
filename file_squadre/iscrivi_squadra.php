<?php
session_start();

require_once __DIR__ . "/../file_accesso/connessione.php";
require_once __DIR__ . "/../funzioni.php";

if(isset($_POST["iscrivi_squadra"])){

    $id_squadra = intval($_POST["id_squadra"]);
    $nome_squadra = $_POST["nome_squadra"];

    $id_utente = $_SESSION["id"];
    $nome_gioco = $_SESSION["nome_gioco"];
    $id_torneo = $_SESSION["id_torneo"];

    $sql = "INSERT INTO squadre_partecipanti(id_squadra, id_torneo)VALUES(?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt -> bind_param("ii", $id_squadra, $id_torneo);
    $stmt -> execute();

    if($stmt->error){
        die("Errore execute: " . $stmt->error);
    }else{


    aggiungi_iscrizione($conn, $id_torneo);


    $sql = "SELECT id_utente
        FROM utenti_squadre
        WHERE id_squadra = ?
        AND id_utente != ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_squadra, $_SESSION["id"]);
    $stmt->execute();

    $result = $stmt->get_result();

    $stmt->close();

    while($row = $result->fetch_assoc()){

    invia_notifica(
        $conn,
        "torneo",
        $_SESSION["nickname"] . " ha iscritto la squadra " . $nome_squadra . " a questo torneo di " . $nome_gioco,
        $id_utente,
        $row["id_utente"],
        $id_torneo
        );
    }

        header("Location: ../info_torneo.php?id=".$id_torneo);
        exit();

    }


}


?>

<!DOCTYPE html>
<html>

<head>
    <link href="../style.css" rel="stylesheet">

    <style>
        /* Modifica dimensione logo */
    .amico-left img{
        width: 170px;
        height: 170px;
    }

    .grassetto {
        font-weight: bold;
    }

    .accetta{
        width: 180px;
        height: 80px;
    }
    </style>
</head>

<body>

<?php
$base_url = "../";
include "../navbar.php";

include "../footer.php";

$_SESSION["from"] = "iscrivi_squadra";


/* =========================
   SQUADRE PARTECIPANTI
========================= */
$sql_partecipanti_squadre = "SELECT s.id, s.nome, s.logo_squadra
                             FROM squadre_partecipanti sp INNER JOIN squadre s ON sp.id_squadra = s.id
                             WHERE sp.id_torneo = ?";

$stmt_partecipanti_squadre = $conn->prepare($sql_partecipanti_squadre);
$stmt_partecipanti_squadre->bind_param("i", $_SESSION["id_torneo"]);
$stmt_partecipanti_squadre->execute();
$result_partecipanti_squadre = $stmt_partecipanti_squadre->get_result();




$sql = "SELECT s.id, s.nome, s.logo_squadra FROM squadre s INNER JOIN utenti_squadre us ON s.id = us.id_squadra
        WHERE us.id_utente = " . $_SESSION["id"];

$result = mysqli_query($conn, $sql);

?>

<h1> Seleziona la squadra da iscrivere </h1>

<label style = "text-align:center"> La squadra dovrà contenere esattamente <span class = "grassetto"> <?php echo $_SESSION["max_giocatori"] ?> membri </span> per partecipare a questo torneo</label><br>

<button class = "input-schermo" onclick="window.location.href='../info_torneo.php?id=<?php echo $_SESSION['id_torneo'] ?>'"> indietro </button>

<div class="contenitore-amici">

<?php

if($result && mysqli_num_rows($result) > 0){

    while($row = mysqli_fetch_assoc($result)){

    if(conta_membri_squadra($conn, $row["id"]) != $_SESSION["max_giocatori"] || check_presenza_in_squadra($conn, $_SESSION["creatore"], $row["id"]) || check_duplicati($conn, $row["id"], $_SESSION["id_torneo"])){
        $color = "background-color: #263c46;";
    }else{
        $color = "background-color: #4285a2;";
    }

        ?>
    <div class="amico-card" style = "<?php echo $color ?>" onclick="window.location.href='info_squadra.php?id=<?php echo $row['id']; ?>&nome=<?php echo $row['nome']; ?>'">

    <div class="amico-left">

        <?php
        Insert_logo_squadra($row["logo_squadra"],"",400);
        ?>

        <h2> <?php echo $row["nome"] ?> </h2>

    </div>

    <div class="amico-right">

        <?php

        $duplicati = check_duplicati($conn, $row["id"], $_SESSION["id_torneo"]);


        if(check_presenza_in_squadra($conn, $_SESSION["creatore"], $row["id"])){
            echo "<p style = 'color: red'>Creatore del torneo presente!</p>";
            echo "<p style = 'color: red'>(" . $_SESSION["nick_creatore"] . ")</p>";

        }elseif(conta_membri_squadra($conn, $row["id"]) != $_SESSION["max_giocatori"]){

            echo "<p style = 'color: red'>Numero membri inadatto! (" . conta_membri_squadra($conn, $row["id"]) . "/". $_SESSION["max_giocatori"] . ")</p>";

        }elseif(count($duplicati) > 0) {

            echo "<p style='color:red'>Ci sono giocatori duplicati:</p>";

            foreach ($duplicati as $d) {
                echo "<p style='color:red'>
                    " . $d["nickname"] . " (Squadra: " . $d["squadra"] . ")
                </p>";
            }

        }else{
            ?>
                <form method = "POST">

                    <input type = "hidden" name = "id_squadra" value = "<?php echo $row["id"]; ?>">

                    <input type = "hidden" name = "nome_squadra" value = "<?php echo $row["nome"]; ?>">

                    <button class = "accetta"
                            type="submit"
                            name="iscrivi_squadra"
                            onclick="event.stopPropagation();">
                            Iscrivi squadra
                    </button>


                </form>

            <?php
        }

        ?>
    
    </div>


    </div><!-- amico-card -->

    <?php

    }

}else{

    echo "<h2> Non hai nessuna squadra :( </h2>";
    echo "Vai alla <a href = 'squadre.php'> sezione squadre </a> per crearne una";

}

?>

</div>

</body>
</html>