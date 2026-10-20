<?php
include "connessione.php";

if(isset($_POST["email"])){

    $email = mysqli_real_escape_string($conn, $_POST["email"]);//Con mysqli_real_escape_string() i caratteri pericolosi vengono escapati: '  →  \'

    $id_utente = isset($_POST["id_utente"]) ? intval($_POST["id_utente"]) : 0;

    if($id_utente > 0){
        // modifica profilo → ignora la sua email
        $sql = "SELECT id FROM utenti WHERE email = '$email' AND id != $id_utente";
    } else {
        // registrazione normale
        $sql = "SELECT id FROM utenti WHERE email = '$email'";
    }

    $result = mysqli_query($conn, $sql);

    if(mysqli_num_rows($result) > 0){
        echo "esiste";
    } else {
        echo "libera";
    }
}

mysqli_close($conn);
?>