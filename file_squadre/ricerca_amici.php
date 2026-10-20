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
    u.avatar
FROM utenti u
INNER JOIN amicizie a
    ON (
        a.utente_id1 = LEAST(?, u.id)
        AND a.utente_id2 = GREATEST(?, u.id)
    )
WHERE u.id != ?
AND u.nickname LIKE ?
ORDER BY u.nickname";

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
    $id_squadra = $_SESSION["id_squadra"];


//sql per gli utenti a cui si è già mandata una richiesta
$sql_richiesta = "
    SELECT 1
    FROM richieste_squadra
    WHERE
    id_destinatario = ? AND id_squadra = ?
";

$stmt_richiesta = $conn->prepare($sql_richiesta);
$stmt_richiesta->bind_param("ii", $id_amico, $id_squadra);
$stmt_richiesta->execute();

$result_richiesta = $stmt_richiesta->get_result();
$richiesta_inviata = ($result_richiesta->num_rows > 0);




$sql_check = "
    SELECT 1
    FROM utenti_squadre
    WHERE id_utente = ?
    AND id_squadra = ?
";

$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ii", $id_amico, $id_squadra);
$stmt_check->execute();

$result_check = $stmt_check->get_result();

?>

<div class="amico-card">

    <div class="amico-left">

        <?php Insert_immagine2($row["avatar"], $base_url, 90); ?>

        <div class="amico-info">
            <h2><?php echo htmlspecialchars($row["nickname"]); ?></h2>
        </div>

    </div>

    <div class="rimuovi-right">

<?php

    if($result_check && mysqli_num_rows($result_check) > 0){

        ?><span style="font-size:25px; color: green">Già in squadra</span><?php

    }else if($richiesta_inviata){

        ?>
        <form method = "POST" action="aggiungi_membro.php">

        <input type="hidden"
               name="id_amico"
               value="<?php echo $row['id']; ?>">
        
        <button class = "sblocca"
                type="submit"
                name="annulla_invito"
                onclick="event.stopPropagation();">
                Annulla Invito
                </button>

        </form>
        <?php

    }else { ?>

    <form method="POST" action="aggiungi_membro.php">

        <input type="hidden"
               name="id_amico"
               value="<?php echo $row['id']; ?>">

        <button type="submit"
                name="aggiungi"
                style="background:none;border:none;cursor:pointer;"
                onclick="event.stopPropagation();">

            <?php Insert_immagine2("../file_amicizie/new_friend.png", "", 80); ?>

        </button>

    </form>

<?php } ?>

    </div>

</div>

<?php
    }

} else {
    echo "<h3>Nessun utente trovato</h3>";
}

?>