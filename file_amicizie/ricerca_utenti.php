<?php
session_start();
include "../file_accesso/connessione.php";
include "../funzioni.php";

$base_url = "../";

if (!isset($_SESSION["id"])) exit();

$id_utente = $_SESSION["id"];

$ricerca = trim($_GET["ricerca"] ?? "");
$like = ($ricerca === "") ? "%" : $ricerca . "%";


/* =========================
   QUERY UTENTI + STATO AMICIZIA
========================= */

$sql = "
SELECT
    u.id,
    u.nickname,
    u.avatar,

    /* esiste amicizia */
    EXISTS(
        SELECT 1
        FROM amicizie a
        WHERE
            a.utente_id1 = LEAST(?, u.id)
            AND a.utente_id2 = GREATEST(?, u.id)
    ) AS esiste

FROM utenti u

WHERE u.id != ?
AND u.nickname LIKE ?

ORDER BY u.nickname
";

$stmt = $conn->prepare($sql);


$stmt->bind_param(
    "iiis",
    $id_utente,
    $id_utente,
    $id_utente,
    $like
);

$stmt->execute();
$result = $stmt->get_result();


/* =========================
   OUTPUT
========================= */

if ($result && $result->num_rows > 0) {

    while ($row = $result->fetch_assoc()) {

    $id_amico = $row["id"];


//sql per gli utenti a cui si è già mandata una richiesta
$sql_richiesta = "
    SELECT 1
    FROM richieste_amicizia
    WHERE
    (id_mittente = ? AND id_destinatario = ?)
    OR
    (id_mittente = ? AND id_destinatario = ?)
";

$stmt_richiesta = $conn->prepare($sql_richiesta);
$stmt_richiesta->bind_param("iiii", $id_utente, $id_amico, $id_amico, $id_utente);
$stmt_richiesta->execute();

$result_richiesta = $stmt_richiesta->get_result();
$richiesta_inviata = ($result_richiesta->num_rows > 0);



//sql per gli utenti bloccati
$sql_blocco = "
    SELECT utente_che_blocca, utente_bloccato
    FROM utenti_bloccati
    WHERE
    (utente_che_blocca = ? AND utente_bloccato = ?)
    OR
    (utente_che_blocca = ? AND utente_bloccato = ?)
";

$stmt_blocco = $conn->prepare($sql_blocco);
$stmt_blocco->bind_param("iiii", $id_utente, $id_amico, $id_amico, $id_utente);
$stmt_blocco->execute();

$result_blocco = $stmt_blocco->get_result();
$blocco = ($result_blocco->num_rows > 0);



?>

<div class="amico-card"
onclick="window.location.href='../file_admin/info_utente.php?id=<?php echo $row['id']; ?>&from=ricerca_utenti'">

    <div class="amico-left">

        <?php Insert_immagine2($row["avatar"], $base_url, 90); ?>

        <div class="amico-info">
            <h2><?php echo htmlspecialchars($row["nickname"]); ?></h2>
        </div>

    </div>

    <div class="rimuovi-right">

<?php

    if ($richiesta_inviata) { ?>

    <span style="font-size:25px; color: yellow">Richiesta in sospeso</span>

<?php }else if($blocco){

    $row = $result_blocco->fetch_assoc();

    if($id_utente == $row["utente_che_blocca"]){?>

    <span style="font-size:25px; color: red">Hai bloccato questo utente</span>

    <?php
    }else{?>

    <span style="font-size:25px; color: red">Sei stato bloccato</span>

    <?php
    }


} else if (!$row["esiste"]) { ?>

    <form method="POST" action="aggiungi_amico.php">

        <input type="hidden"
               name="id_amico"
               value="<?php echo $row['id']; ?>">

        <button type="submit"
                name="aggiungi"
                style="background:none;border:none;cursor:pointer;"
                onclick="event.stopPropagation();">

            <?php Insert_immagine2("new_friend.png", "", 80); ?>

        </button>

    </form>

<?php } else { ?>

    <span style="font-size:25px; color: green">Già amico</span>

<?php } ?>

    </div>

</div>

<?php
    }

} else {
    echo "<h3>Nessun utente trovato</h3>";
}
?>