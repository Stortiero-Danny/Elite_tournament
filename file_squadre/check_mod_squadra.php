<?php
session_start();

include "../file_accesso/connessione.php";
include "../funzioni.php";
include "../footer.php";

/* controllo accesso */
if (!isset($_POST["id"])) {
    header("Location: squadre.php");
    exit();
}

$nome = trim($_POST["nome"]);
$id_squadra = intval($_POST["id"]);


/* =========================
   CONTROLLO NOME
========================= */

$check = "SELECT id FROM squadre WHERE nome = ? AND id != ?";

$stmt_check = $conn->prepare($check);
$stmt_check->bind_param("si", $nome, $id_squadra);
$stmt_check->execute();

$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {

    echo "<h2 style='color:red;'>Nome squadra già esistente!</h2>";
    ?>
    <a href="mod_squadra.php?id=<?php echo $id_squadra; ?>">
        Torna indietro
    </a>
    <?php
    exit();
}


/* =========================
   RECUPERO VECCHIO LOGO
========================= */

$sql_logo = "SELECT logo_squadra FROM squadre WHERE id = ?";

$stmt_logo = $conn->prepare($sql_logo);
$stmt_logo->bind_param("i", $id_squadra);
$stmt_logo->execute();

$result_logo = $stmt_logo->get_result();
$row_logo = $result_logo->fetch_assoc();

$vecchioLogo = $row_logo["logo_squadra"] ?? null;


/* =========================
   UPLOAD NUOVO LOGO
========================= */

$percorsoLogo = null;

if (isset($_FILES['logo']) && $_FILES['logo']['error'] === 0) {

    $nomeFile = time() . "_" . basename($_FILES['logo']['name']);

    $destinazione = "img_squadre/" . $nomeFile;

    if (move_uploaded_file($_FILES['logo']['tmp_name'], $destinazione)) {

        $percorsoLogo = $destinazione;

        /* elimina vecchio logo */
        if (
            !empty($vecchioLogo) &&
            $vecchioLogo != "default_team.png" &&
            file_exists($vecchioLogo)
        ) {

            unlink($vecchioLogo);

        }

    } else {

        die("Errore upload immagine");

    }
}


/* =========================
   UPDATE SQUADRA
========================= */

if ($percorsoLogo) {

    $sql_update = "
        UPDATE squadre
        SET nome = ?, logo_squadra = ?
        WHERE id = ?
    ";

    $stmt = $conn->prepare($sql_update);

    $stmt->bind_param(
        "ssi",
        $nome,
        $percorsoLogo,
        $id_squadra
    );

} else {

    $sql_update = "
        UPDATE squadre
        SET nome = ?
        WHERE id = ?
    ";

    $stmt = $conn->prepare($sql_update);

    $stmt->bind_param(
        "si",
        $nome,
        $id_squadra
    );
}


/* =========================
   ESECUZIONE
========================= */

if ($stmt->execute()) {

    header("Location: squadre.php");
    exit();

} else {

    echo "<h2>Errore: " . $stmt->error . "</h2>";

    ?>
    <a href="mod_squadra.php?id=<?php echo $id_squadra; ?>">
        Riprova
    </a>
    <?php

    echo "<br><br>";

    insert_immagine_reazione("../img_errore/");
}
?>