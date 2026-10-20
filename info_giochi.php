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


$id_gioco = intval($_GET["id"]);

$sql = "SELECT nome, immagine 
        FROM giochi WHERE id = $id_gioco";

$result = mysqli_query($conn, $sql);


if($result && mysqli_num_rows($result) > 0){

    $row = mysqli_fetch_assoc($result);

    if(isset($_SESSION["LOGGED"]) && $_SESSION["LOGGED"] === true && ($_SESSION["id_ruolo"] == 2 || $_SESSION["id_ruolo"] == 3)){
    ?>

    <br>
    <button class="input-schermo" onclick="window.location.href='file_admin/modifica_gioco.php?id=<?php echo $id_gioco ?>&nome=<?php echo $row['nome'] ?>'">Modifica</button>

<?php
    }
    
    echo "<h1> " . $row['nome'] . "</h1>";
    Insert_immagine2($row["immagine"], $base_url, 500);

    $sql2 = "SELECT c.nome as nome_categoria, descrizione
                 FROM giochi_categorie gc
                 INNER JOIN categorie c ON c.id = gc.id_categoria
                 WHERE gc.id_gioco = $id_gioco";

        $result2 = mysqli_query($conn, $sql2);

        ?>    
            <table border = "1" cellpadding = "5">
                <thead>
                        <tr>
                            <th> Categoria </th>
                            <th> Descrizione </th>
                        <tr>
                      </thead>
            <tbody>
            <?php

        echo "<br>";

        if ($result2 && mysqli_num_rows($result2) > 0) {
            while ($cat = mysqli_fetch_assoc($result2)) {

                echo "</tr>";
                echo "<td>{$cat["nome_categoria"]}</td>";
                echo "<td>{$cat["descrizione"]}</td>";
                echo "</tr>";

            }
        } else {
            echo "Nessuna categoria";
        }
}

?>

</tbody>
</table>

<br><br>
<button class = "input-schermo" onclick="window.location.href='Lista_giochi.php'"> Indietro </button><br>

</body>
</html>