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
    </style>
</head>

<body>

<?php

$base_url = "../";
include "../navbar.php";

$from = $_GET["from"];
$id_utente = intval($_GET["id"]);
$nickname_utente = $_GET["nickname_utente"];

$_SESSION["id_utente"] = $id_utente;

if($from == "info_squadra"){
    $_SESSION["from"] = "squadre_utente";
}else{
    $_SESSION["from"] = $from;
}
$_SESSION["nickname_utente"] = $nickname_utente;


$sql = "SELECT s.id, s.nome, s.logo_squadra, s.id_creatore
        FROM squadre s INNER JOIN utenti_squadre us ON s.id = us.id_squadra
        WHERE us.id_utente = " . $id_utente;
$result = mysqli_query($conn, $sql);

?>

<h1> Squadre di <?php echo $nickname_utente ?></h1>

<button class = "input-schermo" onclick="window.location.href='../file_admin/info_utente.php?from=<?php echo $_SESSION['from'] ?>&id=<?php echo $id_utente ?>'"> indietro </button>


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

    </div>


    </div><!-- amico-card -->

    <?php

    }

}else{
    echo "<h2> Questo utente non fa parte di nessuna squadra </h2>";
}


?>

</div><!-- contenitore amici -->

</body>
</html>