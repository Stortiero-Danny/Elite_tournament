<?php
session_start();

include "../file_accesso/connessione.php";
include "../funzioni.php";


if(isset($_POST["rimuovi"])){

    $id_squadra = $_SESSION["id_squadra"];
    $nome_squadra = $_SESSION["nome_squadra"];
    $id_utente = intval($_POST["id_utente"]);
    $id_mittente = $_SESSION["id"];

    $sql = "DELETE FROM utenti_squadre
            WHERE id_squadra = ?
            AND id_utente = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_squadra, $id_utente);

    if($stmt->execute()){

        invia_notifica(
        $conn,
        "squadra",
        $_SESSION["nickname"] . " ti ha rimosso dalla squadra: " . $nome_squadra,
        $id_mittente,
        $id_utente,
        NULL
    );

        header("Location: info_squadra.php?id=".$id_squadra . "&nome=" . $nome_squadra);
        exit();
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <link href="../style.css" rel="stylesheet">

    <style>

        /* PARTE SINISTRA */
    .logo{
        display: flex;
        align-items: center;
        gap: 20px;
    }


    /* IMMAGINE */
    .logo img{
        width: 250px;
        height: 250px;
        object-fit: cover;
        border-radius: 50%;
    }
    </style>
</head>

<body>

<?php

$base_url = "../";
include "../navbar.php";

$id_squadra = intval($_GET["id"]);
$nome_squadra = $_GET["nome"];

$_SESSION["nome_squadra"] = $nome_squadra;
$_SESSION["id_squadra"] = $id_squadra;

$sql = "SELECT nome, logo_squadra FROM squadre 
        WHERE id =" . $id_squadra;
$result = mysqli_query($conn, $sql);

echo $_SESSION["from"];

?>

<br>

<?php

if($_SESSION["from"] == "richieste"){

    ?><button class = "input-schermo" onclick="window.location.href='richieste_squadra.php'"> indietro </button><?php

}elseif($_SESSION["from"] == "iscrivi_squadra"){

    ?><button class = "input-schermo" onclick="window.location.href='iscrivi_squadra.php?from=<?php echo $_SESSION['from'] ?>'"> indietro </button><?php

}elseif($_SESSION["from"] == "tornei_gioco" || $_SESSION["from"] == "gestione" || $_SESSION["from"] == "iscrizioni"){

    ?><button class = "input-schermo" onclick="window.location.href='../info_torneo.php?from=<?php echo $_SESSION['from'] ?>&id=<?php echo $_SESSION['id_torneo'] ?>'"> indietro </button><?php

}elseif($_SESSION["from"] == "lista_utenti" || $_SESSION["from"] == "tornei_gioco_utente" || $_SESSION["from"] == "profilo-amici"){

    ?><button class = "input-schermo" onclick="window.location.href='visualizza_squadre.php?from=<?php echo $_SESSION['from'] ?>&id=<?php echo $_SESSION['id_utente'] ?>&nickname_utente=<?php echo $_SESSION['nickname_utente'] ?>'"> indietro </button><?php

}else{
    ?><button class = "input-schermo" onclick="window.location.href='squadre.php'"> indietro </button><?php
}

echo "<br>";

if(($id_torneo = check_squadra_disponibile($conn, $id_squadra)) && $_SESSION["from"] == "squadre"){

    echo "<span style = 'color: red'> ATTENZIONE </span>: La squadra è iscritta ad un <a href = '../info_torneo.php?from=". $_SESSION["from"] ."&id=" . $id_torneo . "'>torneo</a> e finché sarà tale o il torneo non verrà concluso, <br> i membri non potranno uscire dalla squadra e non sarà possibile aggiungere o rimuovere eventuali membri";
}


?>

<?php

if($result && mysqli_num_rows($result) > 0){

    while($row = mysqli_fetch_assoc($result)){

?>
    <?php

        $totale_membri = conta_membri_squadra($conn, $id_squadra);

    ?>

<div class="info-squadra-container">

    <div class="info-squadra-left">

        <div class="logo">
        <?php Insert_logo_squadra($row['logo_squadra'], "", 300); ?>
        </div>

        <h1 class="nome-squadra">
            <?php echo htmlspecialchars($row['nome']); ?>
        </h1>

        <div class="numero-membri">
            <h3>Membri: <?php echo $totale_membri; ?></h3>
        </div>

        <?php if(($_SESSION["from"] == "squadre" || $_SESSION["from"] == "iscrivi_squadra") && !check_squadra_disponibile($conn, $id_squadra)){ ?>

            <div class="aggiungi-amico-btn">
                <a href="aggiungi_membro.php">
                    <?php Insert_immagine2(
                        "../file_amicizie/new_friend.png",
                        "",
                        100
                    ); ?>
                </a>
            </div>

        <?php } ?>

    </div>

    <div class="info-squadra-right">

        <h2 class="titolo-membri">Membri della squadra</h2>

        <div class="lista-membri">
<?php

    
    $sql_squadra = "SELECT s.id_creatore, us.id_utente, u.avatar as avatar_utente, u.nickname as nickname_utente, s.nome as nome_squadra FROM squadre s INNER JOIN utenti_squadre us ON s.id = us.id_squadra 
                                                                 INNER JOIN utenti u ON us.id_utente = u.id
            WHERE s.id =" . $id_squadra;

    $result_squadra = mysqli_query($conn, $sql_squadra);

    if($result_squadra && mysqli_num_rows($result_squadra) > 0){

    while($row_squadra = mysqli_fetch_assoc($result_squadra)){

    if($row_squadra["id_utente"] == $_SESSION["id"]){

    ?>
        <div class="amico-card" onclick="window.location.href='../profilo.php'">
    <?php

    }else{


        //tolgo la possibilità di visualizzare altri sotto utenti
        if($_SESSION["from"] == "squadre"){
    ?>
        <div class="amico-card" onclick="window.location.href='../file_admin/info_utente.php?from=info_squadra&id=<?php echo $row_squadra['id_utente'] ?>'">
    <?php
        }else{
            ?><div class="amico-card"><?php
        }

    }
    ?>

    <div class="amico-left">

        <?php
        Insert_immagine2($row_squadra["avatar_utente"],"../",100);
        ?>

        <h2> <?php echo $row_squadra["nickname_utente"] ?> </h2>

        <?php
        if($row_squadra["id_creatore"] == $row_squadra["id_utente"]){
            echo "(capo squadra)";
        }
        
        ?>

    </div>

    <?php
    if($row_squadra["id_creatore"] == $_SESSION["id"] && $row_squadra["id_utente"] != $_SESSION["id"] && !check_squadra_disponibile($conn, $id_squadra)){
        ?>

        <form method = "POST">


        <!-- id_utente da eliminare -->
        <input type="hidden"
       name="id_utente"
       value="<?php echo $row_squadra['id_utente']; ?>">

        <div class="amico-right">

        <button     class = "btn-elimina"
                    type="submit"
                    name="rimuovi"
                    onclick="event.stopPropagation(); return conferma_eliminazione('<?php echo htmlspecialchars($row_squadra['nickname_utente']); ?>')"> rimuovi </button>

        </div>

        </form>

        <?php

            }

    ?>


    </div><!-- amico-card -->

        <?php

    }

    }

    }

    ?>
    
    </div> <!-- lista-membri -->

    </div> <!-- info-squadra-right -->


<?php
}else{
    echo "<h1 Style = 'color:red' >Squadra non trovata</h1>";
}

include "../footer.php";
?>

</body>
</html>


<script>

function conferma_eliminazione(nickname){

    return confirm(
        "Vuoi rimuovere " + nickname + " dalla squadra?"
    );

}

</script>