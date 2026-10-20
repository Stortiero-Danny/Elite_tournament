<?php
$server = "localhost";
$user = "root";
$password = "";
$dbname = "torneo_videogiochi";

$conn = mysqli_connect($server,$user,$password,$dbname);

if(!$conn){
    die ("Connessione fallita" . mysqli_connect_error());
}

?>