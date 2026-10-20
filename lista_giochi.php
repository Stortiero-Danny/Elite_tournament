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
    <title>Lista giochi</title>
</head>

<body>

<?php
$base_url = "";
include "navbar.php";

$sql = "SELECT id, nome, immagine FROM giochi";
$result = mysqli_query($conn, $sql);

?>

<h1>Lista giochi</h1>

<?php
if(isset($_SESSION["LOGGED"]) && $_SESSION["LOGGED"] === true && ($_SESSION["id_ruolo"] == 2 || $_SESSION["id_ruolo"] == 3)){
?>
    <button class = "input-schermo" onclick = "window.location.href='file_admin/aggiungi_gioco.php'">aggiungi gioco</button>
<?php
}
?>

<div class="contenitore-giochi">

<?php

if ($result && mysqli_num_rows($result) > 0) {

    while ($row = mysqli_fetch_assoc($result)) {
?>

    <div class="riquadro-giochi" onclick = "window.location.href='info_giochi.php?id=<?php echo $row['id'];?>'">

        <?php insert_immagine2($row["immagine"], $base_url, 300); ?>

        <div class="info-gioco">
            <h2><?php echo htmlspecialchars($row["nome"]); ?></h2>

            <div class="categorie">
                <strong>Categorie:</strong><br>

<?php
        $id_gioco = (int)$row["id"];

        $sql2 = "SELECT c.nome as nome_categoria
                 FROM giochi_categorie gc
                 INNER JOIN categorie c ON c.id = gc.id_categoria
                 WHERE gc.id_gioco = $id_gioco";

        $result2 = mysqli_query($conn, $sql2);

        if ($result2 && mysqli_num_rows($result2) > 0) {
            while ($cat = mysqli_fetch_assoc($result2)) {
                echo htmlspecialchars($cat["nome_categoria"]) . "<br>";
            }
        } else {
            echo "Nessuna categoria";
        }
?>

            </div><!-- categorie -->

        </div><!-- info gioco -->

<?php
if(isset($_SESSION["LOGGED"]) && $_SESSION["LOGGED"] === true && ($_SESSION["id_ruolo"] == 2 || $_SESSION["id_ruolo"] == 3)){
?>

        <div class="azioni-gioco">

    <?php
    //verifico se il torneo che sto per eliminare ha dei tornei, in quel caso il gioco non potrà essere eliminato
    $sql_controllo = "SELECT t.id as id_torneo
                  FROM tornei t INNER JOIN giochi g ON t.id_gioco = g.id
                  WHERE g.id = $id_gioco";

    $result_controllo = mysqli_query($conn, $sql_controllo);

    if(mysqli_num_rows($result_controllo) == 0){

    ?>

    <form method="POST" action="file_admin/elimina_gioco.php" 
          onsubmit="return confermaEliminazione();">

        <input type="hidden" name="id_gioco" value="<?php echo $row['id']; ?>">

        <button type="submit" class="btn-elimina" onclick="event.stopPropagation();">
            Elimina
        </button>

    </form>
<?php
}
?>

</div><!-- azioni gioco -->

<?php
}
?>


    </div><!-- riquadro gioco -->

<?php
    }

} else {
    echo "<h2>Nessun gioco trovato :(</h2>";
}
?>

</div><!-- contenitore giochi -->

</body>
</html>

<script>
    function confermaEliminazione(){
    return confirm("Sei sicuro di voler eliminare questo gioco?");
}
</script>