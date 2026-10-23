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

$id_squadra = intval($_GET["id"]);

$sql = "SELECT nome, logo_squadra FROM squadre WHERE id = " . $id_squadra;
$result = mysqli_query($conn, $sql);

?>

<h1> Modifica Squadra </h1>

<button class="input-schermo"
    onclick="window.location.href='squadre.php'">

        Indietro

</button>


<br>

<?php

if($result && mysqli_num_rows($result) > 0){

    while($row = mysqli_fetch_assoc($result)){

?>

<div class = "riquadro">

<form method = "POST" action = "check_mod_squadra.php" enctype="multipart/form-data">

<input type="hidden" name="id" value="<?php echo $id_squadra ?>">

<label> Nome Squadra </label>
<input type = "text" name = "nome" placeholder = "inserisci un nome per la tua squadra" value = '<?php echo $row["nome"] ?>'>

<br><br>

<label> Logo Squadra</label>
<?php Insert_logo_squadra($row["logo_squadra"], "", 250);?>
<input type = "file" name="logo" accept="image/*" disabled>

<p class="funzione-sospesa">
    ⚠ Caricamento immagini temporaneamente non disponibile.
    La funzionalità verrà ripristinata con un prossimo aggiornamento.
</p>

<br><br>

<input type = "submit" name="submit_form" value = "Applica modifiche">

</form>

</diV>

<?php
    }
}else{
    echo "<h2>Errore: squadra non trovata</h2>";
}

?>


</body>
</html>