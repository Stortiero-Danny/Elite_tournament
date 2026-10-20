<?php
session_start();

include "../file_accesso/connessione.php";
include "../funzioni.php";
include "../footer.php";


if(isset($_POST["abbandona"])){

$id_utente = $_SESSION["id"];
$id_squadra = intval($_POST["id_squadra"]);


$sql = "DELETE FROM utenti_squadre WHERE id_utente = ? AND id_squadra = ?";

$stmt = $conn->prepare($sql);
$stmt ->bind_param("ii", $id_utente, $id_squadra);
$stmt ->execute();

}



if(isset($_POST["rimuovi"])){

    $id_squadra = intval($_POST["id_squadra"]);
    $nome_squadra = $_POST["nome_squadra"];

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
        "squadra",
        $_SESSION["nickname"] . " ha eliminato la squadra: " . $nome_squadra,
        $id_utente,
        $row["id_utente"],
        NULL
        );
    }
    

    svuota_squadra($conn, $id_squadra);

    elimina_richieste($conn, $id_squadra);
    
    elimina_squadra($conn, $id_squadra);

    header("Location: squadre.php");
    exit();
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

    .sblocca{
        background-color: #fba758;
    }

    .sblocca:hover {
    background-color: #ff9422;
    transform: scale(1.05);
    }
    </style>
</head>

<body>

<?php

$base_url = "../";
include "../navbar.php";

$sql = "SELECT s.id, s.nome, s.logo_squadra, s.id_creatore
        FROM squadre s INNER JOIN utenti_squadre us ON s.id = us.id_squadra
        WHERE us.id_utente = " . $_SESSION["id"];
$result = mysqli_query($conn, $sql);


?>

<h1> Le tue squadre </h1>


<div class="azioni-amicizia">

<div class="aggiungi-amico-btn">
        <a href="crea_squadra.php">
            <?php Insert_immagine2("plus_button.png", "", 70); ?>
        </a>
</div>



<?php

$id_destinatario = $_SESSION["id"];

$_SESSION["from"] = "squadre";

$sql_richieste = "SELECT 1 
        FROM richieste_squadra
        WHERE id_destinatario = ?";

 $stmt_richieste = $conn->prepare($sql_richieste);
 $stmt_richieste->bind_param(
        "i",
        $id_destinatario
    );

    $stmt_richieste->execute();
    $result_richieste = $stmt_richieste->get_result();



    if($result_richieste && mysqli_num_rows($result_richieste) > 0){

    ?>
    
    <div class="aggiungi-amico-btn">
        <a href="richieste_squadra.php">
            <?php Insert_immagine2("richieste_amicizia_piene.png", "../file_amicizie/", 90); ?>
        </a>
    </div>

    <?php


    }else{

    ?>

    <div class="aggiungi-amico-btn">
        <a href="richieste_squadra.php">
            <?php Insert_immagine2("richieste_amicizia.png", "../file_amicizie/", 90); ?>
        </a>
    </div>

    <?php
    }
?>

</div>

    <br>


<div class="contenitore-amici">

<?php

if($result && mysqli_num_rows($result) > 0){

    while($row = mysqli_fetch_assoc($result)){

    ?>
    <div class="amico-card" onclick="window.location.href='info_squadra.php?id=<?php echo $row['id']; ?>&nome=<?php echo $row['nome']; ?>'">

    <div class="amico-left">

        <?php
        Insert_logo_squadra($row["logo_squadra"],"",400);
        ?>

        <h2> <?php echo $row["nome"] ?> </h2>

    </div>

    <div class="amico-right">

    <?php if($row["id_creatore"] == $_SESSION["id"]){

    ?>
    
    <button class = "sblocca" style = "color: white" onclick="event.stopPropagation(); window.location.href='mod_squadra.php?id=<?php echo $row['id'] ?>'"> Modifica </button>
    
    <?php if(!check_squadra_disponibile($conn, $row["id"])){?>

    <form method = "POST">

    <input type="hidden"
                   name="id_squadra"
                   value="<?php echo $row['id']; ?>">

    <input type="hidden"
                   name="nome_squadra"
                   value="<?php echo $row['nome']; ?>">
    
    <button class = "rifiuta" 
            type="submit"
            name="rimuovi"
            onclick="event.stopPropagation(); return conferma_eliminazione('<?php echo htmlspecialchars($row['nome']); ?>')"> Elimina </button>

    </form>

    <?php

        }

    }elseif(!check_squadra_disponibile($conn, $row["id"])){

    ?>
    <form method = "POST">

    <input type="hidden"
                   name="id_squadra"
                   value="<?php echo $row['id']; ?>">

    
    <button class = "rifiuta" 
            type="submit"
            name="abbandona"
            style = "width: 180px; height: 80px;"
            onclick="event.stopPropagation(); return conferma_uscita('<?php echo htmlspecialchars($row['nome']); ?>')"> Abbandona Squadra </button>

    </form>

    <?php
    } ?>

    </div>


    </div><!-- amico-card -->

    <?php

    }

}else{
    echo "Nessuna squadra creata, perché non iniziare? :)";
}


?>

</div><!-- contenitore amici -->

</body>
</html>


<script>

function conferma_eliminazione(nome){

    return confirm(
        "Sei sicuro di eliminare la squadra" + nome + "?"
    );

}


function conferma_uscita(nome){

    return confirm(
        "Sei sicuro di abbandonare il Team: " + nome + "?"
    );

}

</script>