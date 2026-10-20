<?php
session_start();
include "../file_accesso/connessione.php";
include "../funzioni.php";
include "../footer.php";

$base_url = "../";
include "../navbar.php";

$id_gioco = intval($_GET["id"]);
$nome_gioco = $_GET["nome"];

$sql = "SELECT id,nome,descrizione FROM categorie";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Modifica Gioco</title>
    <link href="../style.css" rel="stylesheet">
</head>
<body>

<h1>Modifica Gioco</h1>

<button class = "input-schermo" onclick="window.location.href='../Lista_giochi.php'"> Indietro </button>

<div class="riquadro">

<form id="form_gioco" method="POST" action="check_modifica_gioco.php" enctype="multipart/form-data">

<input type="hidden" name="id_gioco" value="<?php echo $id_gioco; ?>">

<label>Nome</label>
<input type="text" name="nome_gioco" value = "<?php echo $nome_gioco?>" required>

<br><br>

<label>Seleziona i generi</label>

<!-- GRIGLIA -->
<div class="griglia-categorie">

<?php
if(mysqli_num_rows($result) > 0){
    while($row = mysqli_fetch_assoc($result)){

        // categorie già associate al gioco
        $sql_sel = "SELECT id_categoria FROM giochi_categorie WHERE id_gioco = $id_gioco";
        $result_sel = mysqli_query($conn, $sql_sel);

// array con gli id selezionati
$categorie_selezionate = [];

if ($result_sel) {
    while ($r = mysqli_fetch_assoc($result_sel)) {
        $categorie_selezionate[] = $r["id_categoria"];
    }
}
?>

    <label class="categoria-card">

        <input type="checkbox" 
       name="categorie[]"
       value="<?php echo $row['id']; ?>"
       <?php echo in_array($row['id'], $categorie_selezionate) ? 'checked' : ''; ?>>

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

<input type = "file" name = "immagine_gioco" accept = "image/*">

<br><br>

<input type="submit" value="Salva modifiche">

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