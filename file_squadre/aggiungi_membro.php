<?php
session_start();

include "../file_accesso/connessione.php";
include "../funzioni.php";


if(isset($_POST["annulla_invito"])) {

    $id_amico = intval($_POST["id_amico"]);
    $id_squadra = $_SESSION["id_squadra"];

    $sql = "DELETE FROM richieste_squadra WHERE id_destinatario = ? AND id_squadra = ?";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("ii", $id_amico, $id_squadra);

    $stmt->execute();

    if($stmt->error){
        die("Errore execute: " . $stmt->error);
    }

    $stmt->close();


}


if(isset($_POST["aggiungi"])) {

    $id_amico = intval($_POST["id_amico"]);
    $id_utente = $_SESSION["id"];
    $id_squadra = $_SESSION["id_squadra"];
    $nome_squadra = $_SESSION["nome_squadra"];

    /* =========================
       CONTROLLO ESISTENZA
    ========================= */

    $sql_check = "
        SELECT 1
        FROM utenti_squadre
        WHERE
        (id_utente = ? AND id_squadra = ?)
    ";

    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ii", $id_amico, $id_squadra);
    $stmt_check->execute();

    $result = $stmt_check->get_result();

    if($result->num_rows == 0) {

    
        /* =========================
           Richiesta di entrata in squadra
        ========================= */


        invia_richiesta_squadra($conn, $id_utente, $id_amico, $id_squadra);
        
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <link href="../style.css" rel="stylesheet">

    <style>
        button{
            border-radius: 10px;
        }

        .ricerca-box{
            text-align:center;
            margin:20px auto;

            width: 50%;
        }

        .ricerca-input{
            width:300px;
            padding:10px;
            border-radius:10px;
            border:none;
            font-size:16px;
        }

        .contenitore-amici{
            margin-top:20px;
        }
    </style>
</head>

<body>

<?php

$base_url = "../";
include "../navbar.php";

echo $_SESSION["from"];
?>


<h1> Aggiungi un membro dalla lista amici </h1>

<button class = "input-schermo" onclick="window.location.href='info_squadra.php?id=<?php echo $_SESSION['id_squadra'] ?>&nome=<?php echo $_SESSION['nome_squadra'] ?>'"> indietro </button>


<div class="ricerca-box">

    <input
        id="ricerca"
        class="ricerca-input"
        type="text"
        placeholder="Cerca nickname..."
        autocomplete="off"
    >

</div>



<!-- =========================
     RISULTATI
========================= -->

<div id="risultati" class="contenitore-amici"></div>

<?php
include "../footer.php";
?>

<script>

const input = document.getElementById("ricerca");
const box = document.getElementById("risultati");

function cerca(valore){

    fetch("ricerca_amici.php?ricerca=" + encodeURIComponent(valore))
        .then(res => res.text())
        .then(data => {
            box.innerHTML = data;
        });
}

/* prima load (tutti utenti) */
cerca("");

input.addEventListener("input", function(){
    cerca(this.value);
});

</script>



</body>
</html>