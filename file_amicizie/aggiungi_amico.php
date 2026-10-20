<?php
session_start();

include "../file_accesso/connessione.php";
include "../funzioni.php";
include "../footer.php";

if (!isset($_SESSION["LOGGED"])) {
    header("Location: file_accesso/login.php");
    exit();
}


if(isset($_POST["aggiungi"])) {

    $id_amico = intval($_POST["id_amico"]);
    $id_utente = $_SESSION["id"];

    /* =========================
       CONTROLLO ESISTENZA
    ========================= */

    $sql_check = "
        SELECT data_creazione
        FROM amicizie
        WHERE
        (utente_id1 = ? AND utente_id2 = ?)
        OR
        (utente_id1 = ? AND utente_id2 = ?)
    ";

    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("iiii", $id_utente, $id_amico, $id_amico, $id_utente);
    $stmt_check->execute();

    $result = $stmt_check->get_result();

    if($result->num_rows == 0) {

    
        /* =========================
           Richiesta di amicizia
        ========================= */


        invia_richiesta($conn, $id_utente, $id_amico);
        
    }
}




$id_utente = $_SESSION["id"];

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
?>


<h1>Aggiungi amico</h1>

<button class="input-schermo"
onclick="window.location.href='amici.php?from=profilo'">

    Indietro

</button>

<!-- =========================
     RICERCA LIVE
========================= -->

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

<script>

const input = document.getElementById("ricerca");
const box = document.getElementById("risultati");

function cerca(valore){

    fetch("ricerca_utenti.php?ricerca=" + encodeURIComponent(valore))
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