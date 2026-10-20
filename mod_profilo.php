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
</head>

<body>

<?php
$base_url = "";
include "navbar.php";
?>

<h1>Modifica Profilo</h1>

<div class="riquadro">
<form method="POST" action="check_mod_profilo.php" onsubmit="return controllaForm()" enctype="multipart/form-data">

<label>Avatar</label>
<?php Insert_immagine2($_SESSION["avatar"], "", 250); ?><br>
<input type="file" name="avatar" accept="image/*">

<br>

<label>Nickname</label>
<input type="text" id="nickname" name="nickname"
       pattern="^[a-zA-Z0-9_]{4,60}$"
       title="Da 4 a 60 caratteri, solo lettere, numeri e _"
       value="<?php echo htmlspecialchars($_SESSION["nickname"]); ?>" required>

<span id="nickname_msg" style="color:red;"></span>
<br><br>

<label>Nome</label>
<input type="text" name="nome"
       pattern="^[A-Za-zÀ-ÖØ-öø-ÿ]{2,60}$"
       value="<?php echo htmlspecialchars($_SESSION["nome"]); ?>" required><br><br>

<label>Cognome</label>
<input type="text" name="cognome"
       pattern="^[A-Za-zÀ-ÖØ-öø-ÿ]{2,60}$"
       value="<?php echo htmlspecialchars($_SESSION["cognome"]); ?>" required><br><br>

<label>Email</label>
<input type="email" id="email" name="email"
       value="<?php echo htmlspecialchars($_SESSION["email"]); ?>" required>

<span id="email_msg" style="color:red;"></span>
<br><br>

<label>Data di nascita</label>
<input type="date" name="data"
       value="<?php echo $_SESSION["data_nascita"]; ?>" required><br><br>

<?php
$sql = "SELECT id, nome FROM sessi";
$result = mysqli_query($conn,$sql);
?>

<label>Sesso:</label>

<?php
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $checked = ($_SESSION["id_sesso"] == $row["id"]) ? "checked" : "";
        echo "<label class='radio-option'>
                <input type='radio' name='id_sesso' value='".htmlspecialchars($row["id"])."' $checked required>
                ".htmlspecialchars($row["nome"])."
              </label>";
    }
}
?>

<br>

<?php
$sql = "SELECT id, nome FROM nazioni ORDER BY nome";
$result = mysqli_query($conn,$sql);
?>

<label>Nazione</label>
<select name="nazione" required>

<?php
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
?>
<option value="<?= $row['id'] ?>"
<?= ($_SESSION["id_nazione"] == $row['id']) ? "selected" : "" ?>>
<?= htmlspecialchars($row['nome']) ?>
</option>
<?php
    }
}
?>
</select>

<br><br>

<span id="error" style="color:red;"></span>

<br><br>

<input type="submit" name="submit_form" value="Applica modifiche">

</form>
</div>

<button class="input-schermo" onclick="window.location.href='profilo.php'">Indietro</button>

</body>
</html>

<script>

// stato validazione
let emailValida = true;
let nicknameValido = true;

// ======================
// CONTROLLO EMAIL AJAX
// ======================
document.getElementById("email").addEventListener("blur", function () {

    const email = this.value;
    const msg = document.getElementById("email_msg");

    if (email === "") return;

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "file_accesso/check_email_ajax.php", true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {

            if (xhr.responseText.trim() === "esiste") {
                msg.textContent = "Email già registrata!";
                emailValida = false;
            } else {
                msg.textContent = "";
                emailValida = true;
            }
        }
    };

    xhr.send(
        "email=" + encodeURIComponent(email) +
        "&id_utente=" + <?php echo $_SESSION["id"]; ?>
    );
});

// ======================
// CONTROLLO NICKNAME AJAX
// ======================
document.getElementById("nickname").addEventListener("blur", function () {

    const nickname = this.value;
    const msg = document.getElementById("nickname_msg");

    if (nickname === "") return;

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "file_accesso/check_nickname_ajax.php", true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {

            if (xhr.responseText.trim() === "esiste") {
                msg.textContent = "Nickname già utilizzato!";
                nicknameValido = false;
            } else {
                msg.textContent = "";
                nicknameValido = true;
            }
        }
    };

    xhr.send(
        "nickname=" + encodeURIComponent(nickname) +
        "&id_utente=" + <?php echo $_SESSION["id"]; ?>
    );
});

// ======================
// CONTROLLO FINALE FORM
// ======================
function controllaForm() {

    const error = document.getElementById("error");

    if (!emailValida) {
        error.textContent = "Email non valida o già registrata!";
        return false;
    }

    if (!nicknameValido) {
        error.textContent = "Nickname non disponibile!";
        return false;
    }

    error.textContent = "";
    return true;
}

</script>