<?php
session_start();
include "../file_accesso/connessione.php";
include "../funzioni.php";
include "../footer.php";
?>


<!DOCTYPE html>
<head>
    <title> Tabella utenti </title>

    <link href="../style.css" rel = "stylesheet">

    <Style>

    </Style>
</head>
<body>

<?php

$base_url = "../";
include "../navbar.php";

$id_corrente = $_SESSION["id"];
$_SESSION["from"] = "lista_utenti";


$sql = "SELECT u.id, u.nickname, u.Nome, u.Cognome, u.email, u.avatar, r.nome as nome_ruolo
        FROM utenti u INNER JOIN ruoli r ON u.id_ruolo = r.id
        WHERE u.id != $id_corrente";
$result = mysqli_query($conn, $sql);

?>

<h1> Lista utenti </h1>

<table border = "1" cellpadding = "5">
    <thead>
        <tr>
        <th> Id </th>
        <th> Nickname </th>
        <th> Nome </th>
        <th> Cognome </th>
        <th> Email </th>
        <th> Avatar </th>
        <th> Ruolo </th>
        <tr>
    </thead>


<tbody>
<?php

if(mysqli_num_rows($result) > 0){

    while($row = mysqli_fetch_assoc($result)){

    if(!empty($row["avatar"])){
        $avatar = "<img src='../" . $row["avatar"] . "' width='100' style='display:block; margin:0 auto;'>";
    }else{
        $avatar = "<img src='../utente_generico.png' width='100' style='display:block; margin:0 auto;'>";
    }

?><div ><?php
    echo "<tr onclick=\"window.location.href='info_utente.php?id={$row['id']}&id_torneo=0'\" style='cursor:pointer'>";
    echo "<td>{$row['id']}</td>";
    echo "<td>{$row['nickname']}</td>";
    echo "<td>{$row['Nome']}</td>";
    echo "<td>{$row['Cognome']}</td>";
    echo "<td>{$row['email']}</td>";
    echo "<td>{$avatar}</td>";
    echo "<td>{$row['nome_ruolo']}</td>";
    echo "</tr>";
?></div><?php    

    }
}else{
    echo "<tr><td colspan='9'>Nessun dato trovato</td></tr>";
}

?>
</tbody>
</table>

</body>
</html>

<?php
mysqli_close($conn);
?>