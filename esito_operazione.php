<?php
session_start();

include "file_accesso/connessione.php";
include "funzioni.php";
include "footer.php";

$base_url = "";
include "navbar.php";

$from = $_GET["from"];

?>

<!DOCTYPE html>
<html>
<head>
    <link href="style.css" rel="stylesheet">
</head>
<body>

<?php

if($from == "aggiungi_gioco"){

    echo "<h2>Gioco aggiunto con successo!</h2>";
    echo "<a href='lista_giochi.php'>visualizza giochi</a>";

}elseif($from == "registrazione"){

    echo "<h1>Persona inserita con successo :)</h1>";
    echo "<h2><a href='file_accesso/login.php'> Torna al Login </a></h2>";

}elseif($from == "crea_torneo"){

    echo "<h1>Torneo creato!</h1>";
    echo "<a href='file_admin/gestione_tornei.php'>Vai alla gestione</a>";

}

echo "<br><br>";
insert_immagine_reazione("img_successo/");

?>
</body>
</html>