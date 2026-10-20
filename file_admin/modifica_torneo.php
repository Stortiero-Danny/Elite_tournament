<?php
session_start();

include "../file_accesso/connessione.php";
include "../funzioni.php";
include "../footer.php";

$base_url = "../";
include "../navbar.php";

?>

<!DOCTYPE html>
<html>
<head>
    <link href="../style.css" rel="stylesheet">
    <title>Modifica Torneo</title>
</head>

<body>

<?php

if (!isset($_GET["id_torneo"])) {
    die("ID torneo mancante");
}

$id = intval($_GET["id_torneo"]);
$from = $_GET["from"];

$sql = "SELECT t.*, 
               g.id as id_gioco,
               g.nome as nome_gioco,
               g.immagine,
               u.nickname as nome_creatore,
               u.avatar as avatar_creatore,
               st.nome as stato,
               md.id as id_mod,
               md.nome as modalita
        FROM tornei t 
        INNER JOIN giochi g ON t.id_gioco = g.id
        INNER JOIN utenti u ON t.id_creatore = u.id
        INNER JOIN stato_torneo st ON t.id_stato = st.id
        INNER JOIN mod_giochi md ON t.id_mod_gioco = md.id
        WHERE t.id = $id";

$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    die("Torneo non trovato");
}

$torneo = mysqli_fetch_assoc($result);
$id_gioco = $torneo["id_gioco"];

?>

<h1>Modifica Torneo</h1>

<div class="riquadro">

<form method="POST" action="check_modifica_torneo.php">

<input type="hidden" name="id_torneo" value="<?php echo $id; ?>">
<input type="hidden" name="from" value="<?php echo $from; ?>">


<label>Gioco</label>
<select id="id_gioco" name="id_gioco" required>

<?php
$sql_giochi = "SELECT id, nome FROM giochi";
$result_giochi = mysqli_query($conn, $sql_giochi);

while ($gioco = mysqli_fetch_assoc($result_giochi)) {
    $selected = ($id_gioco == $gioco['id']) ? "selected" : "";
?>

    <option value="<?= $gioco['id'] ?>" <?= $selected ?>>
        <?= htmlspecialchars($gioco['nome']) ?>
    </option>

<?php } ?>

</select>

<br><br>


<label>Data</label>
<input type="date" id="data" name="data" value="<?php echo $torneo["data"]; ?>" required>

<br><br>


<label>Ora</label>
<input type="time" id="ora" name="ora" value="<?php echo $torneo["ora"]; ?>" required>

<br><br>


<label>Modalità di gioco</label>

<?php echo $torneo["modalita"]; ?>


<select id="modalita" name="modalita" onchange="aggiornaPosti()" style="display:none;">

<?php
$sql = "SELECT id,nome,max_player FROM mod_giochi";
$result = mysqli_query($conn, $sql);

while($row = mysqli_fetch_assoc($result)){

    $selected = ($torneo["id_mod"] == $row["id"]) ? "selected" : "";

    echo "<option value='{$row["id"]}' 
            data-max='{$row["max_player"]}' 
            data-tipo='{$row["nome"]}' 
            $selected>
            {$row["nome"]}
          </option>";
}
?>

</select>


<br><br>

<div id="squadra_div" style="display:none;">
    <label>Numero player per squadra</label>
    <input type="number" id="player_squadra" name="player_squadra" min="2" value="<?php echo $torneo["max_players_squadre"]; ?>">
</div>

<br>

<label>Numero posti</label>
<input type="number" id="n_posti" name="n_posti" min="0" value="<?php echo $torneo["n_posti"]; ?>" required>

<br><br>

<label>Round per ogni match</label>
<input type="number" name="n_round" value="<?php echo $torneo["n_round"]; ?>" required>

<br><br>

<label>Regolamento</label>
<textarea name="regolamento" rows="10" cols="50" required><?php echo htmlspecialchars($torneo["regolamento"]); ?></textarea>

<br><br>

<br><br>

<input type="submit" value="Salva modifiche">

</form>

</div>

<br>

<button class="input-schermo" onclick="history.back()">Indietro</button>

</body>
</html>

<script>

// BLOCCO ORA SE DATA = OGGI
document.getElementById("data").addEventListener("change", function() {
    const oggi = new Date().toISOString().split("T")[0];
    const oraInput = document.getElementById("ora");

    if (this.value === oggi) {
        const now = new Date();
        let ore = now.getHours().toString().padStart(2, '0');
        let minuti = now.getMinutes().toString().padStart(2, '0');
        oraInput.min = ore + ":" + minuti;
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

    
    let minimo = 0;
    
    let step = maxPlayer;

    if (tipo === "squadre") {

        squadraDiv.style.display = "block";
        player_squadra.required = true;

        const valore = parseInt(player_squadra.value) || 0;

        if (valore > 0) {
            minimo = valore;
            step = valore;
        }

    } else {

        squadraDiv.style.display = "none";
        player_squadra.required = false;
        player_squadra.value = "";

    }

    inputPosti.min = minimo;
    inputPosti.step = step;

    // NON sovrascrivere se già valido
    if (!inputPosti.value || inputPosti.value < minimo) {
        inputPosti.value = minimo;
    }
}

// avvio iniziale
window.onload = aggiornaPosti;

</script>

<?php mysqli_close($conn); ?>