<?php

$sql_tornei = "SELECT t.id, mg.nome as modalita FROM tornei t INNER JOIN mod_giochi mg ON t.id_mod_gioco = mg.id  
               WHERE id_stato = 4";
$result_tornei = mysqli_query($conn, $sql_tornei);

if($result_tornei && mysqli_num_rows($result_tornei) > 0){

    while($row = mysqli_fetch_assoc($result_tornei)){
        
            if($row["modalita"] == "solo"){

            $sql_partecipanti = "SELECT id_utente as id FROM partecipanti WHERE id_torneo = " . $row["id"];
            $result_partecipanti = mysqli_query($conn, $sql_partecipanti);

            if($result_partecipanti && mysqli_num_rows($result_partecipanti)){

                while($partecipante = mysqli_fetch_assoc($result_partecipanti)){


                    if(!check_checkin($conn, $partecipante["id"], $row["id"])){
                        crea_checkin_utente($conn, $row["id"], $partecipante["id"]);
                    }

                }

            }
        }
    


    }

}


aggiorna_checkin_scaduti($conn);

$sql = "
    SELECT id
    FROM tornei
    WHERE id_stato = 4
";

$result = mysqli_query($conn, $sql);

while($torneo = mysqli_fetch_assoc($result)){

    if(checkin_completato($conn, $torneo["id"])){

        avvia_torneo($conn, $torneo["id"]);
    }
}
?>