<?php
session_start();

include "../footer.php";
include "../funzioni.php";

$base_url = "../";
include "../navbar.php";

//se l'utente è ancora loggato mandalo direttamente alla Home
if (isset($_SESSION["LOGGED"]) && $_SESSION["LOGGED"] === true) {
    header("Location: ../profilo.php");
    exit();
}

?>


<!DOCTYPE HTML>

<html lang = "it">
    <meta charset = "UTF-8">

<head>
    <title> Accesso utenti </title>
    <link href="../style.css" rel = "stylesheet">
    
    <style>
    
    </style>
</head>

<body>
    <h1> Accesso account </h1>
    
    <form method = "POST" action = "check_login.php">

<div class = "riquadro">
    <label> email: </label>
    <input type = "email" name = "email" placeholder="esempio@gmail.com" required><br><br>

    <label> password: </label>
    <input type = "password" name = "password" placeholder="Secret41" required><br><br>

    <input type = "submit" name = "submit_form" value = "LOGIN">
    <br>
    
    <a href = "Registrazione.php" > Non hai un account? </a><br><br>

</div>
    
    </form>
</body>
</html>

<script>

//per il fade-in
window.addEventListener("load", () => {
    setTimeout(() => {
        document.body.classList.add("loaded");
    }, 100); // piccolo delay
});


//per il fade-out
document.querySelectorAll("a").forEach(link => {
    link.addEventListener("click", function(e) {
        if (this.target === "_blank") return;

        e.preventDefault();
        const href = this.href;

        document.body.classList.remove("loaded");

        setTimeout(() => {
            window.location.href = href;
        }, 250);
    });
});

</script>