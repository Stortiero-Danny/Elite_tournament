<?php
require_once __DIR__ . "/connessione.php";
require_once __DIR__ . "/../funzioni.php";
?>

<!DOCTYPE html>
<html>
<head>
    <link href="../style.css" rel="stylesheet">
    <title>Registrazione</title>
</head>

<body>

<?php
$base_url = "../";
include "../navbar.php";
?>

<h1>Registrati</h1>

<div class="riquadro">

<form method="POST" action="check_registrazione.php" 
      onsubmit="return controllaForm()" 
      enctype="multipart/form-data">

<!-- NICKNAME -->
<label>Nickname</label>
<input type="text" pattern="^[a-zA-Z0-9_]{4,60}$"
       title="Da 4 a 60 caratteri, solo lettere, numeri e _"
       id = "nickname" name="nickname" required>

    <span id="nickname_msg" style="color:red;"></span>

<br><br>

<!-- NOME -->
<label>Nome</label>
<input type="text" pattern="^[A-Za-zÀ-ÖØ-öø-ÿ]{2,60}$"
       title="Solo lettere, minimo 2 caratteri"
       name="nome" required>

<br><br>

<!-- COGNOME -->
<label>Cognome</label>
<input type="text" pattern="^[A-Za-zÀ-ÖØ-öø-ÿ]{2,60}$"
       title="Solo lettere, minimo 2 caratteri"
       name="cognome" required>

<br><br>

<!-- EMAIL -->
<label>Email</label>
<input type="email" id="email" name="email" required>
<span id="email_msg" style="color:red;"></span>

<br><br>

<!-- DATA -->
<label>Data di nascita</label>
<input type="date" id="data" name="data" required>

<br><br>

<!-- SESSO -->
<label>Sesso</label>

<?php
$sql = "SELECT id, nome FROM sessi";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<label class='radio-option'>
                <input type='radio' name='id_sesso' value='".htmlspecialchars($row["id"])."' required>
                ".htmlspecialchars($row["nome"])."
              </label>";
    }
}
?>

<br><br>

<!-- NAZIONE -->
<label>Nazione</label>
<select name="nazione" required>

<?php
$sql = "SELECT id, nome FROM nazioni ORDER BY nome";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<option value='".htmlspecialchars($row["id"])."'>"
             .htmlspecialchars($row["nome"])."</option>";
    }
}
?>

</select>

<br><br>

<!-- PASSWORD -->
<label>Password</label>
<input type="password" id="password" name="password"
       pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$"
       title="Minimo 8 caratteri, una maiuscola, una minuscola e un numero"
       required>

<br><br>

<label>Ripeti password</label>
<input type="password" id="password_repeat" required>

<br><br>

<!-- AVATAR -->
<label>Avatar (facoltativo)</label>
<input type="file"
       name="avatar"
       accept="image/*"
       disabled>

<p class="funzione-sospesa">
    ⚠ Caricamento immagini temporaneamente non disponibile.
    La funzionalità verrà ripristinata con un prossimo aggiornamento.
</p>

<br><br>

<a href="login.php">Hai già un account?</a>

<br><br>

<span id="error" style="color:red;"></span>

<br><br>

<input type="submit" name="submit_form" value="Salva">

</form>
</div>

<?php
include "../footer.php";
?>

<script>

//controllo email in tempo reale
let emailValida = false;

document.getElementById("email").addEventListener("blur", function () {

    const email = this.value;
    const msg = document.getElementById("email_msg");

    if (email === "") return;

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "check_email_ajax.php", true);
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

    xhr.send("email=" + encodeURIComponent(email));
});




//controllo nickname in tempo reale
let nicknameValido = false;

document.getElementById("nickname").addEventListener("blur", function () {

    const nickname = this.value;
    const msg = document.getElementById("nickname_msg");

    if (nickname === "") return;

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "check_nickname_ajax.php", true);
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

    xhr.send("nickname=" + encodeURIComponent(nickname));
});






function controllaForm(){

    const p1 = document.getElementById("password").value;
    const p2 = document.getElementById("password_repeat").value;
    const error = document.getElementById("error");

    if(p1 !== p2){
        error.textContent = "Le password non coincidono!";
        return false;
    }

    if(!emailValida){
        error.textContent = "Email non valida o già registrata!";
        return false;
    }

    if(!nicknameValido){
        error.textContent = "Nickname non disponibile!";
        return false;
    }

    error.textContent = "";
    return true;
}

</script>

</body>
</html>