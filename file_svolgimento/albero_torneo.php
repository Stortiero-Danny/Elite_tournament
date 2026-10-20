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
        .bracket{
            position: relative;
            display: flex;
            align-items: flex-start;
            gap: 120px;
            padding: 20px;
        }

        .round{
            display: flex;
            flex-direction: column;
        }

        .match{
            position: relative;
            width: 220px;
            padding: 10px;
            border: 1px solid #444;
            border-radius: 10px;
            margin-bottom: 20px;
            cursor: pointer;
            background: #0072b3;
            transition: 0.2s;
        }

        .match:hover{
            transform: scale(1.03);
            background: #015788;
        }

        .player{
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .vs{
            text-align: center;
            font-weight: bold;
            margin: 5px 0;
        }

        img{
            border-radius: 50%;
            object-fit: cover;
        }

        /* SVG overlay */
        #bracket-lines{
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
        }
    </style>
</head>

<body>

<?php
$base_url = "../";
include "../navbar.php";

$id_torneo = isset($_GET["id"]) ? intval($_GET["id"]) : 0;

$rounds = get_rounds($conn, $id_torneo);
?>

<h1>Albero dei Match</h1>

<div class="bracket">

    <svg id="bracket-lines"></svg>

    <?php
    $round_index = 0;

    foreach($rounds as $round){

        $matches = get_match_round($conn, $id_torneo, $round);

        echo '<div class="round">';

        $numero_match = 1;

        while($m = $matches->fetch_assoc()){

            $partecipanti = get_partecipanti_match($conn, $m["id"]);
            ?>

            <div class="match"
                data-match="<?php echo $m['id']; ?>"
                data-next="<?php echo $m['id_match_successivo']; ?>"

                onclick="window.location.href='info_match.php?id=<?php echo $id_torneo; ?>&id_match=<?php echo $m['id']; ?>'">

                <strong>Match <?php echo $numero_match; ?></strong>

                <br><br>

                <?php if(isset($partecipanti[0])){ ?>
                    <div class="player">
                        <img src="../<?php echo $partecipanti[0]["avatar"]; ?>" width="32" height="32">
                        <?php echo htmlspecialchars($partecipanti[0]["nickname"]); ?>
                    </div>
                <?php }else{
                    ?><em>Sfidante assente</em><?php
                } ?>

                <div class="vs">VS</div>

                <?php if(isset($partecipanti[1])){ ?>
                    <div class="player">
                        <img src="../<?php echo $partecipanti[1]["avatar"]; ?>" width="32" height="32">
                        <?php echo htmlspecialchars($partecipanti[1]["nickname"]); ?>
                    </div>
                <?php } else { 
                    ?>
                    <em>Sfidante assente</em>
                <?php
                    
                    if($m["round"] == 1 && !check_presenza_match($conn, $m['id_match_successivo'], $partecipanti[0]["id_utente"])){

                        //proclama vincitore
                        $id_vincitore = $partecipanti[0]["id_utente"];
                        $id_match = $m["id"];

                        mysqli_query($conn, "
                            UPDATE match_torneo
                            SET id_vincitore = $id_vincitore,
                            stato = 'giocato'
                            WHERE id = $id_match
                        ");

                        aggiungi_partecipante_match($conn, $m['id_match_successivo'], $id_vincitore);
                    }
                 } ?>

            </div>

            <?php
            $numero_match++;
        }

        echo '</div>';
        $round_index++;
    }

    
    ?>

    <?php

$vincitore = get_vincitore_torneo($conn, $id_torneo);

?>

<div class="round winner-column">

    <div class="winner-box">

        <h3>🏆 Campione</h3>

        <?php if($vincitore){ ?>

            <img src="../<?php echo $vincitore["avatar"]; ?>" width="80" height="80">

            <br><br>

            <strong><?php echo htmlspecialchars($vincitore["nickname"]); ?></strong>

        <?php }else{ ?>

            <div class="winner-placeholder">
                In attesa...
            </div>

        <?php } ?>

    </div>

</div>

</div>

<br>

<button class="input-schermo"
    onclick="window.location.href='../info_torneo.php?id=<?php echo $id_torneo; ?>&from=<?php echo $_SESSION['from'];?>'">
    Indietro
</button>



</body>
</html>