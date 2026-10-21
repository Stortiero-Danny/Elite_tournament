<?php

$root = dirname(__DIR__);

$uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
$path = rawurldecode($uri);

// Home predefinita
if ($path === "/") {
    $path = "/home.php";
}

// Consenti soltanto percorsi PHP
if (!str_ends_with($path, ".php")) {
    http_response_code(404);
    exit("Pagina non trovata");
}

// Individua il file richiesto
$file = realpath($root . $path);

// Verifica che sia un file PHP interno al progetto
if (
    $file === false ||
    !is_file($file) ||
    !str_starts_with($file, $root . DIRECTORY_SEPARATOR)
) {
    http_response_code(404);
    exit("Pagina non trovata");
}

// Impedisce l'esecuzione diretta di alcuni file interni
$interni = [
    "connessione.php",
    "funzioni.php",
    "navbar.php",
    "footer.php"
];

if (
    str_starts_with($file, $root . DIRECTORY_SEPARATOR . "api" . DIRECTORY_SEPARATOR) ||
    in_array(basename($file), $interni, true)
) {
    http_response_code(404);
    exit("Pagina non trovata");
}

// Imposta la cartella di lavoro in base alla pagina richiesta
chdir(dirname($file));

// Esegue la pagina PHP
require $file;