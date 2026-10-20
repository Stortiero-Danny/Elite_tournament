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
</head>

<body>

<?php
include "navbar.php";

$id_utente = intval($_GET["id"]);
$nickname = $_GET["nickname_utente"];

// Prepared Statement
$stmt = $conn->prepare("
    SELECT DISTINCT
        t.*,
        t.id AS id_torneo,
        g.nome AS nome_gioco,
        g.immagine,
        st.nome AS stato
    FROM tornei t
    INNER JOIN giochi g
        ON t.id_gioco = g.id
    INNER JOIN stato_torneo st
        ON t.id_stato = st.id
    INNER JOIN match_torneo mt
        ON mt.id_torneo = t.id
    WHERE mt.id_vincitore = ?
      AND mt.id_match_successivo IS NULL
      AND t.id_stato IN (2,3)
");

$stmt->bind_param("i", $id_utente);
$stmt->execute();

$result = $stmt->get_result();

if($_SESSION["from"] == "profilo"){

    ?>
    <h1>Tornei che hai vinto (<?php echo conta_tornei_vinti($conn, $id_utente); ?>)</h1>

    <button class="input-schermo"
        onclick="window.location.href='profilo.php'">
        Indietro
    </button>

    <?php
    
}else{

    ?>
    <h1>Tornei che <?php echo htmlspecialchars($nickname); ?> ha vinto (<?php echo conta_tornei_vinti($conn, $id_utente); ?>)</h1>

    <button class="input-schermo"
        onclick="window.location.href='file_admin/info_utente.php?from=tornei_vinti&id=<?php echo $id_utente; ?>'">
        Indietro
    </button>

    <?php

}
?>


<div class="contenitore-tornei">

<?php

if($result && $result->num_rows > 0){

    while($row = $result->fetch_assoc()){

?>

    <div class="riquadro-home"
         onclick="window.location.href='info_torneo.php?id=<?php echo $row['id_torneo']; ?>&from=tornei_vinti&id_utente=<?php echo $id_utente; ?>&nickname_utente=<?php echo urlencode($nickname); ?>'">

        <h2><?php echo htmlspecialchars($row["nome_gioco"]); ?></h2>

        <img src="<?php echo htmlspecialchars($row["immagine"]); ?>" width="300">

        <br><br>

        Data: <?php echo formattaData($row["data"]); ?>

        <br>

        Ora: <?php echo htmlspecialchars($row["ora"]); ?>

        <br>

        <?php
        if($row["stato"] == "In corso"){
            echo "Stato: <span style='color: green;'>".$row["stato"]."</span>";
        }else{
            echo "Stato: <span style='color: red;'>".$row["stato"]."</span>";
        }
        ?>

        <br><br><br>

    </div>

<?php

    }

}else{

    echo "<h2>Ancora nessuno al momento</h2>";

}

?>

</div>

</body>
</html>