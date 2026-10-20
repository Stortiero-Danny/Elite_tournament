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

?>

<h1> Aggiungi reclamo </h1>

<button class="input-schermo"
    onclick="window.location.href='reclami.php?id=<?php echo $id_torneo; ?>&id_match=<?php echo $id_match; ?>'">
    Indietro
</button>


<div class = "riquadro">
<form method="POST"
      enctype="multipart/form-data"
      action="check_aggiungi_reclamo.php?id=<?php echo $id_torneo; ?>&id_match=<?php echo $id_match; ?>">
      
<label> Immagine </label>
<input type = "file" name = "immagine" accept="image/*" required>

<label> Descrizione (facoltativa) </label>
<textarea name = "descrizione" rows="10" cols="50"></textarea><br><br><br>


<input type = "submit" name = "submit_form" value = "Carica">

</form>
</div>


</body>
</html>