<?php
session_start();
include "../file_accesso/connessione.php";
include "../funzioni.php";
include "../footer.php";
?>  

<!DOCTYPE html>
<html>
<head>
    <title>Crea Torneo</title>
    <link href="../style.css" rel = "stylesheet">
</head>

<body>

<?php
$base_url = "../";
include "../navbar.php";

?>

<h1>Crea il tuo torneo</h1>

<div class = "riquadro">

<form id="formTorneo" method="POST" action = "check_crea_torneo.php">

<label>Gioco</label>
<select name="id_gioco">
<?php
$sql = "SELECT id,nome FROM giochi";
$result = mysqli_query($conn, $sql);

if(mysqli_num_rows($result) > 0){
    while($row = mysqli_fetch_assoc($result)){
        echo "<option value='" . $row["id"] . "'>" . $row["nome"] . "</option>";
    }
}
?>
</select>

<br><br>

<label>Data</label><br>
<input type="date" name="data" id = "data" min="<?php echo date('Y-m-d'); ?>" required><br><br>

<label>Ora</label><br>
<input type="time" name="ora" id = "ora" min="<?php echo time(); ?>" required><br><br>

<label>Modalità di gioco</label><br>

<select id="modalita" name="modalita" onchange="aggiornaPosti()">
<?php
$sql = "SELECT id,nome,max_player FROM mod_giochi";
$result = mysqli_query($conn, $sql);

if(mysqli_num_rows($result) > 0){
    while($row = mysqli_fetch_assoc($result)){
        echo "<option value='{$row["id"]}' data-max='{$row["max_player"]}' data-tipo='{$row["nome"]}'>{$row["nome"]} </option>";
    }
}
?>
</select>

<br><br>

<!-- Squadre -->
<div id="squadra_div" style="display:none;">
    <label>Numero player per squadra</label><br>
    <input type="number" id="player_squadra" name="player_squadra" min="4" oninput="aggiornaPosti()">
</div>

<br>

<label>Numero posti</label><br>
<input type="number" id="n_posti" name="n_posti" required><br><br>

<label>Round per ogni match</label><br>
<input type="number" id="n_round" name="n_round" min = 1 required><br><br>

<label>Regolamento</label><br>
<textarea name = "regolamento" rows="10" cols="50" required></textarea><br><br><br>


<input type = "submit" name = "submit_form" value = "crea"><br><br>

</form>

</div>

<button type="button" class="input-schermo" id="btnIndietro">
    Indietro
</button>







<script>

document.getElementById("data").addEventListener("change", function() {
    const oggi = new Date().toISOString().split("T")[0];
    const oraInput = document.getElementById("ora");

    if (this.value === oggi) {
        const now = new Date();
        let ore = now.getHours().toString().padStart(2, '0');
        let minuti = now.getMinutes().toString().padStart(2, '0');
        oraInput.min = ore + ":" + minuti;//genera il messaggio di errore
    } else {
        oraInput.min = "";
    }
});


function aggiornaPosti() {

    const select = document.getElementById("modalita");
    const selected = select.options[select.selectedIndex];

    const maxPlayer = parseInt(selected.getAttribute("data-max"));
    const tipo = selected.getAttribute("data-tipo").toLowerCase();

    const inputPosti = document.getElementById("n_posti");
    const squadraDiv = document.getElementById("squadra_div");
    const player_squadra = document.getElementById("player_squadra");

    let minimo = maxPlayer * 2;
    let step = maxPlayer;

    // modalità squadre
    if (tipo === "squadre") {
        squadraDiv.style.display = "block";

        // rende obbligatorio SOLO qui
        player_squadra.required = true;

        const valore = parseInt(player_squadra.value) || 0;

        if (valore > 0) {
            minimo = valore * 2;
            step = valore;
        }

    } else {
        squadraDiv.style.display = "none";

        // NON obbligatorio
        player_squadra.required = false;

        // reset valore per sicurezza
        player_squadra.value = "";
    }

    // applica valori
    inputPosti.min = minimo;
    inputPosti.step = step;
    inputPosti.value = minimo;
}

// esegue al caricamento pagina
window.onload = aggiornaPosti;





function formCompleto() {
    const campi = document.querySelectorAll(
        "#formTorneo input[required], #formTorneo textarea[required], #formTorneo select[required]"
    );

    for (const campo of campi) {
        if (campo.value.trim() === "") {
            return false;
        }
    }

    return true;
}

document.getElementById("btnIndietro").addEventListener("click", function () {

    if (formCompleto()) {

        if (!confirm("Hai compilato il modulo. Sei sicuro di voler uscire senza salvare?")) {
            return;
        }
    }

    window.location.href = "gestione_tornei.php";
});
</script>

</body>
</html>