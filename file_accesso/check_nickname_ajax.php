<?php
include "connessione.php";

if(isset($_POST["nickname"])){

    $nickname = mysqli_real_escape_string($conn, $_POST["nickname"]);

    $id_utente = isset($_POST["id_utente"]) ? intval($_POST["id_utente"]) : 0;

    if($id_utente > 0){
        $sql = "SELECT id FROM utenti WHERE nickname = '$nickname' AND id != $id_utente";
    }else{
        $sql = "SELECT id FROM utenti WHERE nickname = '$nickname'";
    }
    
    $result = mysqli_query($conn, $sql);

    if(mysqli_num_rows($result) > 0){
        echo "esiste";
    } else {
        echo "libero";
    }
}

mysqli_close($conn);
?>