<?php

function Insert_immagine($avatar){

    if (!empty($avatar)) {
        echo "<img src='" . $avatar . "' width='250' style='display:block; margin:0 auto;'>";
    } else {
        echo "<img src='utente_generico.png' width='250' style='display:block; margin:0 auto;'>";
    }

}

function Insert_immagine2($avatar, $src, $grandezza){

    if (!empty($avatar)) {
        echo "<img src='" . $src . $avatar . "' width='". $grandezza ."' style='display:block; margin:0 auto;'>";
    } else {
        echo "<img src='" . $src . "utente_generico.png' width='". $grandezza ."' style='display:block; margin:0 auto;'>";
    }

}


function insert_immagine_reazione($src){

    // sicurezza: aggiunge slash se manca
    if (substr($src, -1) !== "/") {
        $src .= "/";
    }

    $immagini = glob($src . "*.{jpg,jpeg,png,gif,webp,JPG,PNG}", GLOB_BRACE);

    if (!empty($immagini)) {

        $random_img = $immagini[array_rand($immagini)];
        $nome_file = basename($random_img);

        if($src === "../img_successo/"){
            insert_immagine2($nome_file, $src, 300);
        }else{
            insert_immagine2($nome_file, $src, 500);
        }

    } else {
        echo "Nessuna immagine trovata in: " . $src;
    }
}




function Insert_immagine_gioco($conn, $id_gioco){

    $sql = "SELECT immagine FROM giochi WHERE id = $id_gioco";
    $result = mysqli_query($conn, $sql);


    if(mysqli_num_rows($result) > 0){

        $row = mysqli_fetch_assoc($result);

        echo "<img src='" . $row["immagine"] . "' width='100'>";

    }else{
        echo "immagine non trovata";
    }

}


function insert_bandiera($conn,$id_nazione, $src){

    $sql = "SELECT bandiera FROM nazioni WHERE id = $id_nazione";
    $result = mysqli_query($conn, $sql);


    if(mysqli_num_rows($result) > 0){

        $row = mysqli_fetch_assoc($result);

        echo "<img src='". $src  . $row["bandiera"] . "' width='100' style='display:block; margin:0 auto;'>";

    }else{
        echo "bandiera non trovata";
    }
}

function mostra_sesso($conn, $id_sesso){
    
    $sql = "SELECT id,nome FROM sessi WHERE id = $id_sesso";
    $result = mysqli_query($conn,$sql);

    if(mysqli_num_rows($result) > 0){

        $row = mysqli_fetch_assoc($result);
        return $row["nome"];
    }else{
        return "sesso non trovato";
    }
}


function mostra_ruolo($conn, $id_ruolo){
    
    $sql = "SELECT id,nome FROM ruoli WHERE id = $id_ruolo";
    $result = mysqli_query($conn,$sql);

    if(mysqli_num_rows($result) > 0){

        $row = mysqli_fetch_assoc($result);
        return $row["nome"];
    }else{
        return "ruolo non trovato";
    }
}


function formattaData($data){

    // Controllo di sicurezza
    if(empty($data)){
        return "";
    }

    // formato DB: yyyy-mm-dd
    //             0123456789
    // vogliamo: dd/mm/yyyy
    
    return substr($data,8,2) . "/" .
           substr($data,5,2) . "/" .
           substr($data,0,4);
}


function formattaDataTime($datetime){

    // controllo sicurezza
    if(empty($datetime)){
        return "";
    }

    // converte il datetime in timestamp
    $timestamp = strtotime($datetime);

    //formato finale
    return date("d/m/Y", $timestamp) . " alle ore " . date("H:i", $timestamp);
}


function stampAvatar($conn, $id){

    $sql_avatar = "SELECT avatar FROM utenti WHERE id =" . $id;
    $result_avatar = mysqli_query($conn, $sql_avatar);

    if($result_avatar && mysqli_num_rows($result_avatar) > 0){

    $row = mysqli_fetch_assoc($result_avatar);

    return $row["avatar"];

    }else{
        echo "Utente non trovato";
    }
}



function invia_notifica($conn, $tipologia, $messaggio, $id_mittente, $id_amico, $id_riferimento){

    /* =========================
       CREAZIONE NOTIFICA
    ========================= */

    $sql_notifica = "INSERT INTO notifiche
    (
        tipologia,
        data_invio,
        messaggio,
        id_mittente,
        id_riferimento
    )

    VALUES
    (
        ?,
        CURRENT_TIMESTAMP,
        ?,
        ?,
        ?
    )";

    $stmt_notifica = $conn->prepare($sql_notifica);

    $stmt_notifica->bind_param(
        "ssii",
        $tipologia,
        $messaggio,
        $id_mittente,
        $id_riferimento
    );

    $stmt_notifica->execute();


    /* =========================
       RECUPERO ID NOTIFICA
    ========================= */

    $id_notifica = $conn->insert_id;


    /* =========================
       ASSOCIA UTENTE-NOTIFICA
    ========================= */

    $sql_associazione = "INSERT INTO utenti_notifiche
    (
        id_notifica,
        id_utente_destinatario,
        stato,
        data_lettura
    )

    VALUES
    (
        ?,
        ?,
        ?,
        NULL
    )";

    $stmt_associazione = $conn->prepare($sql_associazione);

    $stato = "Non letta";

    $stmt_associazione->bind_param(
        "iis",
        $id_notifica,
        $id_amico,
        $stato
    );

    $stmt_associazione->execute();

}

function elimina_notifica($conn, $id_notifica)
{
    $conn->begin_transaction();

    try {

        /* 1. elimina relazioni utente-notifica */
        $sql1 = "DELETE FROM utenti_notifiche WHERE id_notifica = ?";
        $stmt1 = $conn->prepare($sql1);
        $stmt1->bind_param("i", $id_notifica);
        $stmt1->execute();

        /* 2. elimina notifica */
        $sql2 = "DELETE FROM notifiche WHERE id = ?";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param("i", $id_notifica);
        $stmt2->execute();

        $conn->commit();

    } catch (Exception $e) {
        $conn->rollback();
        die("Errore eliminazione notifica: " . $e->getMessage());
    }
}



function conta_amici($conn, $id_utente){

    $sql_count = "SELECT COUNT(*) AS totale
              FROM amicizie
              WHERE (utente_id1 = ?
              OR utente_id2 = ?)";

    $stmt_count = $conn->prepare($sql_count);

    $stmt_count->bind_param(
        "ii",
        $id_utente,
        $id_utente
    );

    $stmt_count->execute();

    $result_count = $stmt_count->get_result();

    $row_count = $result_count->fetch_assoc();

    return $row_count["totale"];

}


function invia_richiesta($conn, $id_mittente, $id_amico)
{
    $sql_richiesta = "
        INSERT INTO richieste_amicizia
        (id_mittente, id_destinatario)
        VALUES (?, ?)
    ";

    $stmt_richiesta = $conn->prepare($sql_richiesta);

    if (!$stmt_richiesta) {
        die("Errore prepare: " . $conn->error);
    }

    $stmt_richiesta->bind_param(
        "ii",
        $id_mittente,
        $id_amico
    );

    $stmt_richiesta->execute();
}


function check_richiesta($conn, $id_utente){

    $sql = "
        SELECT 1
        FROM richieste_amicizia
        WHERE id_destinatario = ?
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_utente);
    $stmt->execute();

    $stmt->store_result();

    return $stmt->num_rows > 0;
}



function elimina_richiesta($conn, $id_utente, $id_mittente){

    $sql_richiesta = "DELETE FROM richieste_amicizia WHERE id_mittente = ? AND id_destinatario = ?";

    $stmt_richiesta = $conn->prepare($sql_richiesta);

    if (!$stmt_richiesta) {
        die("Errore prepare: " . $conn->error);
    }

    $stmt_richiesta->bind_param(
        "ii",
        $id_mittente,
        $id_utente
    );

    $stmt_richiesta->execute();


}


function conta_tornei_creati($conn, $id_creatore){

    $sql_count = "SELECT COUNT(*) AS totale
              FROM tornei
              WHERE id_creatore = ?";

    $stmt_count = $conn->prepare($sql_count);

    $stmt_count->bind_param(
        "i",
        $id_creatore
    );

    $stmt_count->execute();

    $result_count = $stmt_count->get_result();

    $row_count = $result_count->fetch_assoc();

    return $row_count["totale"];

}


function Insert_logo_squadra($logo_squadra, $src, $grandezza){

if (!empty($logo_squadra)) {
        echo "<img src='" . $src . $logo_squadra . "' width='". $grandezza ."' style='display:block; margin:0 auto;'>";
    } else {
        echo "<img src='" . $src . "img_squadre/default_team.png' width='". $grandezza ."' style='display:block; margin:0 auto;'>";
    }

}



function invia_richiesta_squadra($conn, $id_mittente, $id_amico, $id_squadra)
{
    $sql_richiesta = "
        INSERT INTO richieste_squadra
        (id_mittente, id_destinatario, id_squadra)
        VALUES (?, ?, ?)
    ";

    $stmt_richiesta = $conn->prepare($sql_richiesta);

    if (!$stmt_richiesta) {
        die("Errore prepare: " . $conn->error);
    }

    $stmt_richiesta->bind_param(
        "iii",
        $id_mittente,
        $id_amico,
        $id_squadra
    );

    $stmt_richiesta->execute();
}


function elimina_richiesta_squadra($conn, $id_utente, $id_squadra){

    $sql_richiesta = "DELETE FROM richieste_squadra WHERE id_destinatario = ? AND id_squadra = ?";

    $stmt_richiesta = $conn->prepare($sql_richiesta);

    if (!$stmt_richiesta) {
        die("Errore prepare: " . $conn->error);
    }

    $stmt_richiesta->bind_param(
        "ii",
        $id_utente,
        $id_squadra
    );

    $stmt_richiesta->execute();


}


function aggiungi_membro($conn, $id_utente, $id_squadra){


    $sql = "INSERT INTO utenti_squadre (id_utente, id_squadra) VALUES (?, ?)";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("ii", $id_utente, $id_squadra);

    $stmt->execute();

    if($stmt->error){
        die("Errore execute: " . $stmt->error);
    }
}


function svuota_squadra($conn, $id_squadra){

    $sql = "DELETE FROM utenti_squadre WHERE id_squadra = ?";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("i", $id_squadra);

    $stmt->execute();

    if($stmt->error){
        die("Errore execute: " . $stmt->error);
    }
}


function elimina_richieste($conn, $id_squadra){

    $sql = "DELETE FROM richieste_squadra WHERE id_squadra = ?";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("i", $id_squadra);

    $stmt->execute();

    if($stmt->error){
        die("Errore execute: " . $stmt->error);
    }

}


function elimina_squadra($conn, $id_squadra){

    $sql = "DELETE FROM squadre WHERE id = ?";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("i", $id_squadra);

    $stmt->execute();

    if($stmt->error){
        die("Errore execute: " . $stmt->error);
    }

    $stmt->close();

}


function check_richiesta_squadra($conn, $id_utente){

    $sql = "
        SELECT 1 
        FROM richieste_squadra
        WHERE id_destinatario = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_utente);
    $stmt->execute();

    $stmt->store_result();

    return $stmt->num_rows > 0;
}



function header_amici($conn, $id_utente){

    $sql = "SELECT 1 FROM richieste_amicizia WHERE id_destinatario = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_utente);
    $stmt->execute();

    $result = $stmt->get_result();

    if($result->num_rows > 0) {
    
    header("Location: richieste_amicizia.php");
    exit();

    }else{
        
    header("Location: amici.php");
    exit();
    }

}


function header_squadre($conn, $id_utente){

    $sql = "SELECT 1 FROM richieste_squadra WHERE id_destinatario = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_utente);
    $stmt->execute();

    $result = $stmt->get_result();

    if($result->num_rows > 0) {
    
    header("Location: richieste_squadra.php");
    exit();

    }else{
        
    header("Location: squadre.php");
    exit();
    }

}


function header_utenti_bloccati($conn, $id_utente){

    $sql = "SELECT 1 FROM utenti_bloccati WHERE utente_che_blocca = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_utente);
    $stmt->execute();

    $result = $stmt->get_result();

    if($result->num_rows > 0) {
    
    header("Location: utenti_bloccati.php");
    exit();

    }else{
        
    header("Location: amici.php");
    exit();
    }

}




function conta_squadre($conn, $id_utente){

    $sql_count = "SELECT COUNT(*) AS totale
              FROM utenti_squadre
              WHERE id_utente = ?";

    $stmt_count = $conn->prepare($sql_count);

    $stmt_count->bind_param(
        "i",
        $id_utente
    );

    $stmt_count->execute();

    $result_count = $stmt_count->get_result();

    $row_count = $result_count->fetch_assoc();

    return $row_count["totale"];

}


function conta_squadre_torneo($conn, $id_torneo){

    $sql_count = "SELECT COUNT(*) AS totale
              FROM squadre_partecipanti
              WHERE id_torneo = ?";

    $stmt_count = $conn->prepare($sql_count);

    $stmt_count->bind_param(
        "i",
        $id_torneo
    );

    $stmt_count->execute();

    $result_count = $stmt_count->get_result();

    $row_count = $result_count->fetch_assoc();

    return $row_count["totale"];

}


function conta_membri_squadra($conn, $id_squadra){

    $sql_count = "SELECT COUNT(*) AS totale
                  FROM utenti_squadre
                  WHERE id_squadra = ?";

    $stmt_count = $conn->prepare($sql_count);

    $stmt_count->bind_param(
        "i",
        $id_squadra
    );

    $stmt_count->execute();

    $result_count = $stmt_count->get_result();

    $row_count = $result_count->fetch_assoc();

    return $row_count["totale"];

}




function check_amico($conn, $id_utente, $id_amico){

    $sql = "
        SELECT 1
        FROM amicizie
        WHERE
        (utente_id1 = ? AND utente_id2 = ?)
        OR
        (utente_id1 = ? AND utente_id2 = ?)
    ";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "iiii",
        $id_utente,
        $id_amico,
        $id_amico,
        $id_utente
    );

    $stmt->execute();

    if($stmt->get_result()->num_rows > 0){
        return "<p class = 'pixel' style = 'color: green'> Amico </p>";
    }

    // richiesta inviata
    $sql = "
        SELECT 1
        FROM richieste_amicizia
        WHERE id_mittente = ?
        AND id_destinatario = ?
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_utente, $id_amico);
    $stmt->execute();

    if($stmt->get_result()->num_rows > 0){
        return "<p class = 'pixel' style = 'color: yellow'>Richiesta inviata </p>";
    }

    // richiesta ricevuta
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_amico, $id_utente);
    $stmt->execute();

    if($stmt->get_result()->num_rows > 0){
        return "<p class = 'pixel' style = 'color: yellow'>Richiesta ricevuta </p>";
    }

    // utente bloccato da me
    $sql = "
        SELECT 1
        FROM utenti_bloccati
        WHERE utente_che_blocca = ?
        AND utente_bloccato = ?
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_utente, $id_amico);
    $stmt->execute();

    if($stmt->get_result()->num_rows > 0){
        return "<p class = 'pixel' style = 'color: red'> Utente bloccato </p>";
    }

    // io bloccato dall'utente
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_amico, $id_utente);
    $stmt->execute();

    if($stmt->get_result()->num_rows > 0){
        return "<p class = 'pixel' style = 'color: red'> Sei stato bloccato </p>";
    }

    return "non_amico";
}





function aggiungi_partecipante($conn, $id_utente, $id_torneo){

    $sql = "INSERT INTO partecipanti(id_utente, id_torneo)VALUES(?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_utente, $id_torneo);

    if(!$stmt->execute()){
            die($stmt->error);
        }else{
            aggiungi_iscrizione($conn, $id_torneo);
        }

}


function aggiungi_iscrizione($conn, $id_torneo){

        $sql = "UPDATE `tornei` t INNER JOIN mod_giochi mg ON t.id_mod_gioco = mg.id
            SET t.n_posti = (t.n_posti - mg.max_player) WHERE t.id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_torneo);

        if(!$stmt->execute()){
            die($stmt->error);
        }
}


function togli_partecipante($conn, $id_utente, $id_torneo){

    $sql = "DELETE FROM `partecipanti` WHERE (id_utente = ? AND id_torneo = ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_utente, $id_torneo);

    if(!$stmt->execute()){
            die($stmt->error);
        }else{
            annulla_iscrizione($conn, $id_torneo);
        }

}


function togli_squadra($conn, $id_squadra, $id_torneo){

    $sql = "DELETE FROM `squadre_partecipanti` WHERE (id_squadra = ? AND id_torneo = ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_squadra, $id_torneo);

    if(!$stmt->execute()){
            die($stmt->error);
        }else{
            annulla_iscrizione($conn, $id_torneo);
        }

}


function annulla_iscrizione($conn, $id_torneo){

    $sql = "UPDATE `tornei` t INNER JOIN mod_giochi mg ON t.id_mod_gioco = mg.id
            SET t.n_posti = (t.n_posti + mg.max_player) WHERE t.id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_torneo);

    if(!$stmt->execute()){
        die($stmt->error);
    }
}


function check_iscrizione($conn, $id_utente, $id_torneo){

    $sql = "SELECT 1 FROM partecipanti WHERE id_utente = ? AND id_torneo = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_utente, $id_torneo);
    $stmt->execute();

    $stmt->store_result();

    $presente = $stmt->num_rows > 0;

    $stmt->close();

    return $presente;

}


function check_iscrizione_squadra($conn, $id_utente, $id_torneo){

    $sql = "SELECT sp.id_squadra, s.nome as nome_squadra, g.nome as nome_gioco
            FROM squadre_partecipanti sp
            INNER JOIN squadre s ON sp.id_squadra = s.id
            INNER JOIN utenti_squadre us ON us.id_squadra = s.id
            INNER JOIN utenti u ON u.id = us.id_utente
            INNER JOIN tornei t ON t.id = sp.id_torneo
            INNER JOIN giochi g ON t.id_gioco = g.id
            WHERE sp.id_torneo = ? AND us.id_utente = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_torneo, $id_utente);
    $stmt->execute();

    $result = $stmt->get_result();

    $row = $result->fetch_assoc();

    $stmt->close();

    return $row ?: false;
}


function check_squadra_disponibile($conn, $id_squadra){

    $sql = "SELECT id_torneo FROM squadre_partecipanti WHERE id_squadra = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_squadra);
    $stmt->execute();

    $result = $stmt->get_result();

    if($row = $result->fetch_assoc()){
        return $row["id_torneo"];
    }

    return false;

}


function check_tornei_terminati($conn, $id_gioco){

    $sql = "SELECT 1
         FROM tornei t 
         INNER JOIN giochi g ON t.id_gioco = g.id
         INNER JOIN stato_torneo st ON t.id_stato = st.id
         WHERE g.id = ? AND st.nome = 'Terminato'";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_gioco);
    $stmt->execute();

    $stmt->store_result();

    return $stmt->num_rows > 0; //restituisce un true o false

}

function conta_partecipanti($conn, $id_torneo){

    $sql_count = "SELECT COUNT(*) AS totale
                  FROM partecipanti
                  WHERE id_torneo = ?";

    $stmt_count = $conn->prepare($sql_count);

    $stmt_count->bind_param("i",$id_torneo);

    $stmt_count->execute();

    $result_count = $stmt_count->get_result();

    $row_count = $result_count->fetch_assoc();

    return $row_count["totale"];

}



function comunica_elimina_torneo($conn, $id_utente, $id_torneo){


    $sql = "SELECT p.id_utente, g.nome as nome_gioco
        FROM partecipanti p INNER JOIN tornei t ON p.id_torneo = t.id
                            INNER JOIN giochi g ON t.id_gioco = g.id
        WHERE p.id_torneo = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_torneo);
    $stmt->execute();

    $result = $stmt->get_result();

    while($row = $result->fetch_assoc()){

    invia_notifica(
        $conn,
        "comunicazione",
        $_SESSION["nickname"] . " ha cancellato il torneo di " . $row["nome_gioco"],
        $id_utente,
        $row["id_utente"],
        NULL
        );
    }
}



function comunica_elimina_torneo_squadre($conn, $id_utente, $id_torneo){


    $sql = "SELECT us.id_utente, g.nome as nome_gioco
            FROM squadre_partecipanti sp INNER JOIN squadre s ON sp.id_squadra = s.id
                                         INNER JOIN utenti_squadre us ON s.id = us.id_squadra
                                         INNER JOIN tornei t ON sp.id_torneo = t.id
                                         INNER JOIN giochi g ON t.id_gioco = g.id
            WHERE sp.id_torneo = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_torneo);
    $stmt->execute();

    $result = $stmt->get_result();

    while($row = $result->fetch_assoc()){

    invia_notifica(
        $conn,
        "comunicazione",
        $_SESSION["nickname"] . " ha cancellato il torneo di " . $row["nome_gioco"],
        $id_utente,
        $row["id_utente"],
        NULL
        );
    }
}



function check_presenza_in_squadra($conn, $id_utente, $id_squadra){


    $sql = "SELECT 1 FROM utenti_squadre
            WHERE id_utente = ? AND id_squadra = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_utente, $id_squadra);
    $stmt->execute();

    $stmt->store_result();

    $presente = $stmt->num_rows > 0;

    $stmt->close();

    return $presente;


}


function check_duplicati($conn, $id_squadra, $id_torneo){

    $duplicati = [];

    $sql_partecipanti_squadre = "SELECT s.id, s.nome, s.logo_squadra
                                 FROM squadre_partecipanti sp 
                                 INNER JOIN squadre s ON sp.id_squadra = s.id
                                 WHERE sp.id_torneo = ?";

    $stmt = $conn->prepare($sql_partecipanti_squadre);
    $stmt->bind_param("i", $id_torneo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {

        while ($squadra = $result->fetch_assoc()) {

            $sql = "SELECT u.id, u.nickname 
                    FROM utenti_squadre us 
                    INNER JOIN utenti u ON us.id_utente = u.id  
                    WHERE us.id_squadra = ?";

            $stmt2 = $conn->prepare($sql);
            $stmt2->bind_param("i", $squadra["id"]);
            $stmt2->execute();
            $result2 = $stmt2->get_result();

            if ($result2 && $result2->num_rows > 0) {

                while ($row = $result2->fetch_assoc()) {

                    if (check_presenza_in_squadra($conn, $row["id"], $id_squadra)) {

                        $duplicati[] = [
                            "id" => $row["id"],
                            "nickname" => $row["nickname"],
                            "squadra" => $squadra["nome"]
                        ];
                    }
                }
            }
        }
    }

    return $duplicati;
}



function crea_checkin_utente($conn, $id_torneo, $id_utente){

    $deadline = date('Y-m-d H:i:s', strtotime('+5 minutes'));

    $stmt = $conn->prepare("
        INSERT INTO checkin (id_torneo, id_utente, deadline)
        VALUES (?, ?, ?)
    ");

    $stmt->bind_param("iis", $id_torneo, $id_utente, $deadline);
    $stmt->execute();
}


function elimina_checkin_utente($conn, $id_torneo){

    $stmt = $conn->prepare("
        DELETE FROM checkin WHERE id_torneo = ?
    ");

    $stmt->bind_param("i", $id_torneo);
    $stmt->execute();
}


function crea_checkin_squadra($conn, $id_torneo, $id_squadra){

    $deadline = date('Y-m-d H:i:s', strtotime('+5 minutes'));

    $stmt = $conn->prepare("
        INSERT INTO checkin (id_torneo, id_squadra, deadline)
        VALUES (?, ?, ?)
    ");

    $stmt->bind_param("iis", $id_torneo, $id_squadra, $deadline);
    $stmt->execute();
}



function aggiorna_checkin_scaduti($conn){

    $stmt = $conn->prepare("
        UPDATE checkin
        SET stato = 'scaduto'
        WHERE stato = 'in_attesa'
        AND deadline < NOW()
    ");

    $stmt->execute();
}



function get_checkin($conn, $id_torneo, $id_utente){

    $stmt = $conn->prepare("
        SELECT id, deadline
        FROM checkin
        WHERE id_torneo = ?
        AND id_utente = ?
        AND stato = 'in_attesa'
    ");

    $stmt->bind_param("ii", $id_torneo, $id_utente);
    $stmt->execute();

    return $stmt->get_result()->fetch_assoc();
}


function rimuovi_no_show($conn){

    // utenti
    $conn->query("
        DELETE p
        FROM partecipanti p
        INNER JOIN checkin c ON c.id_utente = p.id_utente
        WHERE c.stato = 'scaduto'
    ");

    // squadre
    $conn->query("
        DELETE sp
        FROM squadre_partecipanti sp
        INNER JOIN checkin c ON c.id_squadra = sp.id_squadra
        WHERE c.stato = 'scaduto'
    ");
}


function avvia_torneo($conn, $id_torneo){

    // 1. evita doppia esecuzione
    if(esistono_match($conn, $id_torneo)){
        return;
    }

    // 2. recupera giocatori
    $giocatori = get_giocatori_torneo($conn, $id_torneo);

    if(count($giocatori) < 2){
        return;
    }

    shuffle($giocatori);

    // 3. CREA STRUTTURA COMPLETA (NON SOLO PRIMO ROUND)
    genera_struttura_torneo($conn, $id_torneo, $giocatori);

    // 4. aggiorna stato torneo SOLO alla fine
    $stmt = $conn->prepare("
        UPDATE tornei
        SET id_stato = 2
        WHERE id = ?
    ");

    $stmt->bind_param("i", $id_torneo);
    $stmt->execute();
}


function check_checkin($conn, $id_utente, $id_torneo){

    $stmt = $conn->prepare("
        SELECT id
        FROM checkin
        WHERE id_torneo = ?
        AND id_utente = ?
    ");

    $stmt->bind_param("ii", $id_torneo, $id_utente);
    $stmt->execute();

    return ($stmt->get_result()->num_rows > 0);
}


function checkin_completato($conn, $id_torneo){

    $stmt = $conn->prepare("
        SELECT
            COUNT(*) AS totale,
            SUM(CASE WHEN stato = 'in_attesa' THEN 1 ELSE 0 END) AS in_attesa
        FROM checkin
        WHERE id_torneo = ?
    ");

    $stmt->bind_param("i", $id_torneo);
    $stmt->execute();

    $row = $stmt->get_result()->fetch_assoc();

    if($row["totale"] == 0){
        return false;
    }

    return ($row["in_attesa"] == 0);
}


function scheduler_checkin($conn){

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
}


function check_min_partecipanti($conn, $id_torneo){

    $sql = "SELECT id_utente FROM partecipanti WHERE id_torneo = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_torneo);
    $stmt->execute();

    $stmt->store_result();

    return $stmt->num_rows > 1; //restituisce un true o false

}


function termina_torneo($conn, $id_torneo){

    $sql = "UPDATE tornei SET id_stato = 3 WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_torneo);
    $stmt->execute();

}



function elimina_partecipanti_scaduti($conn, $id_torneo){

    $sql = "SELECT id_utente FROM checkin WHERE id_torneo = " . $id_torneo . " AND stato != 'verificato'";
    $result = mysqli_query($conn, $sql);

    if($result && mysqli_num_rows($result) > 0){

        while($row = mysqli_fetch_assoc($result)){

            $id_utente = $row["id_utente"];

            $stmt = $conn->prepare("
                DELETE FROM partecipanti
                WHERE id_torneo = ?
                AND id_utente = ?
            ");

            $stmt->bind_param("ii", $id_torneo, $id_utente);
            $stmt->execute();

            annulla_iscrizione($conn, $id_torneo);

        }

    }

}


function get_giocatori_torneo($conn, $id_torneo){

    $stmt = $conn->prepare("
        SELECT id_utente
        FROM partecipanti
        WHERE id_torneo = ?
    ");

    $stmt->bind_param("i", $id_torneo);
    $stmt->execute();

    $result = $stmt->get_result();

    $giocatori = [];

    while($row = $result->fetch_assoc()){
        $giocatori[] = $row["id_utente"];
    }

    return $giocatori;
}


function crea_match($conn, $id_torneo, $round){

    $stmt = $conn->prepare("
        INSERT INTO match_torneo
        (id_torneo, round, stato)
        VALUES (?, ?, 'in attesa')
    ");

    $stmt->bind_param(
        "ii",
        $id_torneo,
        $round
    );

    $stmt->execute();

    return $conn->insert_id;
}


function esistono_match($conn, $id_torneo){

    $stmt = $conn->prepare("
        SELECT id
        FROM match_torneo
        WHERE id_torneo = ?
        LIMIT 1
    ");

    $stmt->bind_param("i", $id_torneo);
    $stmt->execute();

    return $stmt->get_result()->num_rows > 0;
}


function aggiungi_partecipante_match(
    $conn,
    $id_match,
    $id_utente
){

    $tipo = "utente";

    $stmt = $conn->prepare("
        INSERT INTO match_partecipanti
        (id_match, id_partecipante, tipo, punteggio)
        VALUES (?, ?, ?, 0)
    ");

    $stmt->bind_param(
        "iis",
        $id_match,
        $id_utente,
        $tipo
    );

    $stmt->execute();
}


function genera_match($conn, $id_torneo, $giocatori){

    shuffle($giocatori);

    $posizione = 1;

    for($i = 0; $i < count($giocatori); $i += 2){

        $id_match = crea_match(
            $conn,
            $id_torneo,
            $posizione
        );

        aggiungi_partecipante_match(
            $conn,
            $id_match,
            $giocatori[$i]
        );

        if(isset($giocatori[$i + 1])){

            aggiungi_partecipante_match(
                $conn,
                $id_match,
                $giocatori[$i + 1]
            );
        }

        $posizione++;
    }
}



function imposta_vincitore_match(
    $conn,
    $id_match,
    $id_vincitore
){

    $stmt = $conn->prepare("
        UPDATE match_torneo
        SET
            id_vincitore = ?,
            stato = 'giocato'
        WHERE id = ?
    ");

    $stmt->bind_param(
        "ii",
        $id_vincitore,
        $id_match
    );

    $stmt->execute();
}


function match_concluso($conn, $id_match){

    $stmt = $conn->prepare("
        SELECT stato
        FROM match_torneo
        WHERE id = ?
    ");

    $stmt->bind_param("i", $id_match);
    $stmt->execute();

    $row = $stmt->get_result()->fetch_assoc();

    return $row["stato"] == "giocato";
}


function get_match_torneo($conn, $id_torneo){

    $stmt = $conn->prepare("
        SELECT *
        FROM match_torneo
        WHERE id_torneo = ?
        ORDER BY round
    ");

    $stmt->bind_param("i", $id_torneo);
    $stmt->execute();

    return $stmt->get_result();
}

function get_rounds($conn, $id_torneo){

    $stmt = $conn->prepare("
        SELECT DISTINCT round
        FROM match_torneo
        WHERE id_torneo = ?
        ORDER BY round ASC
    ");

    $stmt->bind_param("i", $id_torneo);
    $stmt->execute();

    $result = $stmt->get_result();

    $rounds = [];

    while($row = $result->fetch_assoc()){
        $rounds[] = $row["round"];
    }

    return $rounds;
}



function get_match_round(
    $conn,
    $id_torneo,
    $round
){

    $stmt = $conn->prepare("
        SELECT *
        FROM match_torneo
        WHERE id_torneo = ?
        AND round = ?
        ORDER BY round
    ");

    $stmt->bind_param(
        "ii",
        $id_torneo,
        $round
    );

    $stmt->execute();

    return $stmt->get_result();
}




function conta_match($conn, $id_torneo){

    $sql_count = "SELECT COUNT(*) as totale FROM `tornei` t INNER JOIN match_torneo m ON t.id = m.id_torneo
            WHERE t.id = ?";

    $stmt_count = $conn->prepare($sql_count);

    $stmt_count->bind_param("i",$id_torneo);

    $stmt_count->execute();

    $result_count = $stmt_count->get_result();

    $row_count = $result_count->fetch_assoc();

    return $row_count["totale"];

}


function get_partecipanti_match($conn, $id_match){

    $stmt = $conn->prepare("
        SELECT u.id as id_utente, u.nickname, u.avatar
        FROM match_partecipanti mp
        INNER JOIN utenti u
            ON u.id = mp.id_partecipante
        WHERE mp.id_match = ?
        AND mp.tipo = 'utente'
        ORDER BY mp.id
    ");

    $stmt->bind_param("i", $id_match);
    $stmt->execute();

    $result = $stmt->get_result();

    $partecipanti = [];

    while($row = $result->fetch_assoc()){
        $partecipanti[] = [
            "id_utente" => $row["id_utente"],
            "nickname" => $row["nickname"],
            "avatar" => $row["avatar"]
        ];
    }

    return $partecipanti;
}


function conta_tornei_partecipati($conn, $id_utente){

    $sql_count = "SELECT COUNT(*) as totale FROM `tornei` t INNER JOIN partecipanti p ON t.id = p.id_torneo
            WHERE p.id_utente = ? AND (t.id_stato = 2 OR t.id_stato = 3)";

    $stmt_count = $conn->prepare($sql_count);

    $stmt_count->bind_param("i",$id_utente);

    $stmt_count->execute();

    $result_count = $stmt_count->get_result();

    $row_count = $result_count->fetch_assoc();

    return $row_count["totale"];

}



function check_presenza_match($conn, $id_match, $id_utente){

    $sql = "SELECT 1 FROM match_partecipanti WHERE id_match = ? AND id_partecipante = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_match, $id_utente);
    $stmt->execute();

    $stmt->store_result();

    return $stmt->num_rows > 0; //restituisce un true o false

}


function verifica_partecipanti_match($conn, $id_match){

    $sql = "SELECT id_partecipante FROM match_partecipanti WHERE id_match = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_match);
    $stmt->execute();

    $stmt->store_result();

    return $stmt->num_rows > 1; //restituisce un true o false


}




function assegna_vincitore($conn, $id_match, $id_vincitore){

    // aggiorna match
    $stmt = $conn->prepare("
        UPDATE match_torneo
        SET id_vincitore = ?, stato = 'giocato'
        WHERE id = ?
    ");

    $stmt->bind_param("ii", $id_vincitore, $id_match);
    $stmt->execute();

    // trova match successivo
    $stmt = $conn->prepare("
        SELECT id_match_successivo
        FROM match_torneo
        WHERE id = ?
    ");

    $stmt->bind_param("i", $id_match);
    $stmt->execute();

    $res = $stmt->get_result()->fetch_assoc();

    if(!$res || !$res["id_match_successivo"]) return;

    $next = $res["id_match_successivo"];

    // inserisci vincitore nel prossimo match
    $stmt = $conn->prepare("
        INSERT INTO match_partecipanti
        (id_match, id_partecipante, tipo)
        VALUES (?, ?, 'utente')
    ");

    $stmt->bind_param("ii", $next, $id_vincitore);
    $stmt->execute();
}


function assegna_partecipante($conn, $id_match, $id_utente){

    $stmt = $conn->prepare("
        INSERT INTO match_partecipanti (id_match, id_partecipante, tipo)
        VALUES (?, ?, 'utente')
    ");

    $stmt->bind_param("ii", $id_match, $id_utente);
    $stmt->execute();
}


function collega_match($conn, $id_from, $id_to){

    $stmt = $conn->prepare("
        UPDATE match_torneo
        SET id_match_successivo = ?
        WHERE id = ?
    ");

    $stmt->bind_param("ii", $id_to, $id_from);
    $stmt->execute();
}


function genera_struttura_torneo($conn, $id_torneo, $giocatori){

    shuffle($giocatori);

    $round = 1;
    $matches = [];

    // 1. crea primo round
    for($i = 0; $i < count($giocatori); $i += 2){

        $id_match = crea_match($conn, $id_torneo, $round);

        $p1 = $giocatori[$i];
        $p2 = $giocatori[$i+1] ?? null;

        assegna_partecipante($conn, $id_match, $p1);

        if($p2){
            assegna_partecipante($conn, $id_match, $p2);
        }

        $matches[] = $id_match;
    }

    // 2. crea round successivi (vuoti)
    while(count($matches) > 1){

        $round++;
        $next_matches = [];

        for($i = 0; $i < count($matches); $i += 2){

            $id_match = crea_match($conn, $id_torneo, $round);

            $m1 = $matches[$i];
            $m2 = $matches[$i+1] ?? null;

            collega_match($conn, $m1, $id_match);
            if($m2) collega_match($conn, $m2, $id_match);

            $next_matches[] = $id_match;
        }

        $matches = $next_matches;
    }
}



function get_vincitore_torneo($conn, $id_torneo){

    $stmt = $conn->prepare("
        SELECT u.nickname, u.avatar
        FROM match_torneo mt
        INNER JOIN utenti u
            ON u.id = mt.id_vincitore
        WHERE mt.id_torneo = ?
          AND mt.id_match_successivo IS NULL
        LIMIT 1
    ");

    $stmt->bind_param("i", $id_torneo);
    $stmt->execute();

    return $stmt->get_result()->fetch_assoc();
}



function conta_tornei_vinti($conn, $id_utente){

    $stmt = $conn->prepare("
        SELECT COUNT(*) AS totale
        FROM match_torneo
        WHERE id_vincitore = ?
          AND id_match_successivo IS NULL
    ");

    $stmt->bind_param("i", $id_utente);
    $stmt->execute();

    $row = $stmt->get_result()->fetch_assoc();

    return $row["totale"];
}


function conta_notifiche($conn, $id_utente){

    $sql = "SELECT COUNT(*) as totali FROM utenti_notifiche WHERE id_utente_destinatario = ?";
    $stmt = $conn->prepare($sql);

    $stmt->bind_param("i", $id_utente);
    $stmt->execute();

    $row = $stmt->get_result()->fetch_assoc();

    return $row["totali"];

}


function libera_notifiche($conn, $tot_notifiche, $id_utente){

    $sql = "SELECT 
            n.id AS id_notifica,
            n.tipologia AS tipologia_notifica,
            n.data_invio AS data_notifica,
            n.messaggio AS messaggio_notifica,
            n.id_mittente,
            un.stato

        FROM notifiche n

        INNER JOIN utenti_notifiche un
            ON n.id = un.id_notifica

        WHERE un.id_utente_destinatario = $id_utente

        ORDER BY n.data_invio";

    $result = mysqli_query($conn, $sql);


    if ($result && mysqli_num_rows($result) > 0) {

    while ($tot_notifiche > 30 && $row = mysqli_fetch_assoc($result)) {

            elimina_notifica($conn, $row["id_notifica"]);

            $tot_notifiche--;
        }

    }
}

?>