<?php
include "file_accesso/connessione.php";

if (!isset($base_url)) {
    $base_url = "";
}
?>

<div class="navbar">
    <div class="nav-left">
        <a href="<?php echo $base_url; ?>home.php"><?php insert_immagine2("Elite-Tournament-logo.png", $base_url, 120); ?></a>
        <a href="<?php echo $base_url; ?>lista_giochi.php">Lista giochi</a>

        <?php if (isset($_SESSION["LOGGED"]) && $_SESSION["LOGGED"] === true){
            
            ?>
                <a href="<?php echo $base_url; ?>iscrizioni.php">Iscrizioni</a>
            <?php

            if($_SESSION["id_ruolo"] == 2 || $_SESSION["id_ruolo"] == 3){

            ?>
                <a href="<?php echo $base_url; ?>file_admin/lista_utenti.php">Lista utenti</a>
                <a href="<?php echo $base_url; ?>file_admin/gestione_tornei.php">Tornei</a>
            <?php

            }
        } ?>
    </div>

    <div class="nav-right">
        <?php if (isset($_SESSION["LOGGED"]) && $_SESSION["LOGGED"] === true): 
            
            
            $id_corrente = $_SESSION["id"];

            $sql = "SELECT n.id
            FROM `notifiche` n INNER JOIN utenti_notifiche un ON n.id = un.id_notifica
				   INNER JOIN utenti u ON u.id = un.id_utente_destinatario
                   WHERE un.id_utente_destinatario = $id_corrente AND un.stato = 'Non letta'";

            $result = mysqli_query($conn, $sql);

            if($result && mysqli_num_rows($result) == 0){

            ?>
                <a href="<?php echo $base_url; ?>notifiche.php">
                    <?php insert_immagine2("notifiche/notifica-vuota.png", $base_url, 30);
                           ?>
                </a>
            <?php
            }else{

                ?>
                    <a href="<?php echo $base_url; ?>notifiche.php">
                        <?php insert_immagine2("notifiche/notifica-piena.png", $base_url, 35); ?>
                    </a>
                <?php
            } ?>


            <?php if(check_richiesta($conn, $_SESSION["id"]) > 0){
            ?>

            <a href="<?php echo $base_url; ?>file_amicizie/amici.php">
                <?php insert_immagine2($base_url . "file_amicizie/amici-pieni.png", "", 65); ?>
            </a>

            <?php
            }else{
            ?>

            <a href="<?php echo $base_url; ?>file_amicizie/amici.php">
                <?php insert_immagine2($base_url . "file_amicizie/amici.png", "", 45); ?>
            </a>

            <?php
            }


            if(check_richiesta_squadra($conn, $_SESSION["id"]) > 0){
            ?>

            <a href="<?php echo $base_url; ?>file_squadre/squadre.php">
                <?php insert_immagine2($base_url . "file_squadre/Team-pieno.png", "", 65); ?>
            </a>

            <?php
            }else{
            ?>

            <a href="<?php echo $base_url; ?>file_squadre/squadre.php">
                <?php insert_immagine2($base_url . "file_squadre/Team.png", "", 65); ?>
            </a>

            <?php
            }

            ?>

            
            <a class = "profilo-avatar-nav-bar" href="<?php echo $base_url; ?>profilo.php">
                <?php insert_immagine2($_SESSION["avatar"], $base_url, 50); ?>
            </a>
            

            <a href="<?php echo $base_url; ?>file_accesso/logout.php" onclick = "return conferma_logout()">Logout</a>
        <?php else: ?>
            <a href="<?php echo $base_url; ?>file_accesso/login.php">Login</a>
        <?php endif; ?>
    </div>
</div>

<script>

function conferma_logout(){
        return confirm("Vuoi uscire dal tuo account?");
}

</script>