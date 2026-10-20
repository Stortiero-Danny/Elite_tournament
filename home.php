<?php
session_start();

include "file_accesso/connessione.php";
include "funzioni.php";
include "footer.php";
?>

<!DOCTYPE html>
<head>
    <link href="style.css" rel = "stylesheet">
    <style>
    </style>
</head>
<body>

<?php

$base_url = "";
include "navbar.php";

scheduler_checkin($conn);

?><h1 class = "pixel"> Giochi </h1><?php

$sql = "SELECT id, nome, immagine FROM giochi";
$result = mysqli_query($conn, $sql);

?><div class = "contenitore-tornei"><?php

if(mysqli_num_rows($result) > 0){

    while($row = mysqli_fetch_assoc($result)){

    ?>
    <div class="riquadro-home" onclick="window.location.href='tornei_gioco.php?id=<?php echo $row['id']; ?>'">
    <?php

        echo "<h2>" . $row["nome"] . "</h2>";
        insert_immagine2($row["immagine"], "", 300);

    ?>
    </div>
    <?php

    }
}
?>

</div>

</body>
</html>