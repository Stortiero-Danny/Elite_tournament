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
    <style>
        .mod{
            width: 70;
            height: 70;
            font-size: 30px;
            border-radius: 20%;
        }
    </style>
</head>
<body>

<?php

$base_url = "../";
include "../navbar.php";

$id_torneo = intval($_GET["id"]);
$id_match = intval($_GET["id_match"]);


$sql = "SELECT mp.id,
               u.id as id_utente,
               u.avatar,
               u.nickname,
               mp.punteggio,
               t.id_creatore,
               mt.id_match_successivo,
               mt.stato,
               mt.id_vincitore
        FROM match_partecipanti mp
        INNER JOIN match_torneo mt ON mp.id_match = mt.id
        INNER JOIN tornei t ON mt.id_torneo = t.id
        INNER JOIN utenti u ON mp.id_partecipante = u.id
        WHERE mp.id_match = $id_match";

$result = mysqli_query($conn, $sql);


if(isset($_POST["add_point"])){

    $id_match_partecipante = intval($_POST["id_match_partecipante"]);

    mysqli_query($conn,"
        UPDATE match_partecipanti
        SET punteggio = punteggio + 1
        WHERE id = $id_match_partecipante
    ");

    header("Location: info_match.php?id=$id_torneo&id_match=$id_match");
    exit();
}

if(isset($_POST["remove_point"])){

    $id_match_partecipante = intval($_POST["id_match_partecipante"]);

    mysqli_query($conn,"
        UPDATE match_partecipanti
        SET punteggio = GREATEST(0, punteggio - 1)
        WHERE id = $id_match_partecipante
    ");

    header("Location: info_match.php?id=$id_torneo&id_match=$id_match");
    exit();
}



if(isset($_POST["proclama_vincitore"])){

    $id_match_partecipante = intval($_POST["id_match_partecipante"]);

    $id_match_successivo = intval($_POST["id_match_successivo"]);
    
    $id_vincitore = intval($_POST["id_utente"]);

    //proclama vincitore del match
    mysqli_query($conn,"
        UPDATE match_torneo
        SET id_vincitore = $id_vincitore, stato = 'giocato' 
        WHERE id = $id_match
    ");


    if($id_match_successivo == 0){

    //dichiara il torneo terminato
    termina_torneo($conn, $id_torneo);


    }else{
        //conduci il giocatore al round successivo
        aggiungi_partecipante_match($conn, $id_match_successivo, $id_vincitore);
    }


    header("Location: info_match.php?id=$id_torneo&id_match=$id_match");
    exit();
}

?>

<h1>Info Match</h1>

<?php

if(verifica_partecipanti_match($conn, $id_match)){




if($result && mysqli_num_rows($result) > 0){

    $partecipanti = [];

while($row = mysqli_fetch_assoc($result)){
    $partecipanti[] = $row;
}

if(count($partecipanti) == 2){
?>

<div class="match-container">

    <div class="giocatore">
        <?php Insert_immagine2($partecipanti[0]["avatar"], "../", 250); ?>
        <h3><?php echo htmlspecialchars($partecipanti[0]["nickname"]); ?></h3>
        <h2><?php echo $partecipanti[0]["punteggio"]; ?></h2>

        <?php
        if($_SESSION["id"] == $partecipanti[0]["id_creatore"] && $partecipanti[1]["stato"] != "giocato"){
        ?>
        <form method="POST">

        <input  type="hidden"
                name="id_match_partecipante"
                value='<?php echo $partecipanti[0]["id"]; ?>'>

        <input  type="hidden"
                name="id_match_successivo"
                value='<?php echo $partecipanti[0]["id_match_successivo"]; ?>'>

        <input  type="hidden"
                name="id_utente"
                value='<?php echo $partecipanti[0]["id_utente"]; ?>'>

        <button class = "mod" type="submit" name="add_point">
            +
        </button>

        <button class = "mod" type="submit" name="remove_point">
            -
        </button>

        <br>
        <button class="accetta" type="submit" name="proclama_vincitore" onclick="return conferma_vincitore('<?php echo $partecipanti[0]['nickname'] ?>');">
            proclama vincitore
        </button>

    </form>
    <?php
            }

        if($partecipanti[0]["stato"] == "giocato"){

                if($partecipanti[0]["id_vincitore"] == $partecipanti[0]["id_utente"]){
                    echo "<p style='color: green'> VINCITORE! </p>";
                }
            }
        ?>

    </div>

    <div class="vs">
        VS
    </div>

    <div class="giocatore">
        <?php Insert_immagine2($partecipanti[1]["avatar"], "../", 250); ?>
        <h3><?php echo htmlspecialchars($partecipanti[1]["nickname"]); ?></h3>
        <h2><?php echo $partecipanti[1]["punteggio"]; ?></h2>

        <?php
        if($_SESSION["id"] == $partecipanti[1]["id_creatore"] && $partecipanti[1]["stato"] != "giocato"){
        ?>
        <form method="POST">

        <input  type="hidden"
                name="id_match_partecipante"
                value='<?php echo $partecipanti[1]["id"]; ?>'>

                
        <input  type="hidden"
                name="id_match_successivo"
                value='<?php echo $partecipanti[1]["id_match_successivo"]; ?>'>


        <input  type="hidden"
                name="id_utente"
                value='<?php echo $partecipanti[1]["id_utente"]; ?>'>


        <button class = "mod" type="submit" name="add_point">
            +
        </button>

        <button class = "mod" type="submit" name="remove_point">
            -
        </button>

        <br>
        <button class="accetta" type="submit" name="proclama_vincitore" onclick="return conferma_vincitore('<?php echo $partecipanti[1]['nickname'] ?>');">
            proclama vincitore
        </button>

    </form>
        <?php
            }

            if($partecipanti[1]["stato"] == "giocato"){

                if($partecipanti[1]["id_vincitore"] == $partecipanti[1]["id_utente"]){
                    echo "<p style='color: green'> VINCITORE! </p>";
                }
            }
        ?>
    </div>

</div>

<?php
}

}else{
    echo "<h3> Match non trovato :( </h3>";
}


if(check_presenza_match($conn, $id_match, $_SESSION['id']) && $partecipanti[1]["stato"] != "giocato"){

    echo "<p style='color: red'>Ricordati di utilizzare i reclami per confermare le tue vittorie!</p>";
}

?>
<br>
<button class="input-schermo"
    onclick="window.location.href='reclami.php?id=<?php echo $id_torneo; ?>&id_match=<?php echo $id_match; ?>'">
    Reclami
</button>


<?php

}else{

    $result_round = mysqli_query($conn, "SELECT round FROM match_torneo WHERE id = $id_match");

    if($result_round && mysqli_num_rows($result_round) > 0){

        $row_round = mysqli_fetch_assoc($result_round);

        if($row_round["round"] == 1){
            echo "<h3>Match nullo per l'assenza di uno sfidante</h3>";
        }else{
             echo "<h3>In attesa della presenza di entrambi gli sfidanti</h3>";
        }

    }else{
        echo "Match non trovato";
    }

}
?>

<br>
<button class="input-schermo"
    onclick="window.location.href='albero_torneo.php?id=<?php echo $id_torneo; ?>'">
    Indietro
</button>

</body>
</html>


<script>

function conferma_vincitore($nickname){

    return confirm(
        "Vuoi assegnare la vittoria a " + $nickname + "?"
    );
}

</script>