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


?>

<h1> Crea Squadra </h1>

<button class="input-schermo"
    onclick="window.location.href='squadre.php'">

        Indietro

</button>

<br>

<div class = "riquadro">

<form method = "POST" action = "check_crea_squadra.php" enctype="multipart/form-data">

<label> Nome Squadra </label>
<input type = "text" name = "nome" placeholder = "inserisci un nome per la tua squadra" required>

<br><br>

<label> Logo Squadra (Facoltativo) </label>
<input type = "file" name="logo" accept="image/*" disabled>

<p class="funzione-sospesa">
    ⚠ Caricamento immagini temporaneamente non disponibile.
    La funzionalità verrà ripristinata con un prossimo aggiornamento.
</p>

<br><br>

<input type = "submit" name="submit_form" value = "Crea">

</form>

</diV>


</body>
</html>