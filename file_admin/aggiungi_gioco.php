<?php
session_start();
include "../file_accesso/connessione.php";
include "../funzioni.php";
include "../footer.php";

$base_url = "../";
include "../navbar.php";

$sql = "SELECT id,nome,descrizione FROM categorie";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Aggiungi Gioco</title>
    <link href="../style.css" rel="stylesheet">
</head>
<body>

<h1>Aggiungi Gioco</h1>

<button class = "input-schermo" onclick="window.location.href='../lista_giochi.php'"> Indietro </button>

<div class="riquadro">

<form id="form_gioco" method="POST" action="check_aggiungi_gioco.php" enctype="multipart/form-data">

<label>Nome</label>
<input type="text" name="nome_gioco" required>

<br><br>

<label>Seleziona i generi</label>

<!-- GRIGLIA -->
<div class="griglia-categorie">

<?php
if(mysqli_num_rows($result) > 0){
    while($row = mysqli_fetch_assoc($result)){
?>

    <label class="categoria-card">
    <input type="checkbox" name="categorie[]" value="<?php echo $row['id']; ?>">

    <span>
        <?php echo $row['nome']; ?>
    </span>

    <span class="tooltip">
        <?php echo htmlspecialchars($row['descrizione']); ?>
    </span>
</label>

<?php
    }
}
?>
</div>

<!-- errore per nessuna categoria selezionata -->
<h2 id="errore" style="color:red;"></h2>

<br>

<label> Immagine del gioco </label>

<input type = "file" name = "immagine_gioco" accept = "image/*" required>

<br><br>

<input type="submit" value="Aggiungi gioco">

</form>

</div>

</body>
</html>


<script>
document.getElementById("form_gioco").addEventListener("submit", function(e) {

    const checked = document.querySelectorAll("input[name='categorie[]']:checked");
    const errore = document.getElementById("errore");

    if (checked.length === 0) {
        e.preventDefault();
        errore.textContent = "Seleziona almeno una categoria!";
    } else {
        errore.textContent = "";
    }

});
</script>