<?php

require_once('config.php');
require_once('file_handling.php');

if (isset($_GET['root']) && isset($_GET['name'])) {
    $playlist_path = $_GET['root'].DIRECTORY_SEPARATOR.$_GET['name'];

    die($playlist_path);

    touch($playlist);

    echo "Playlist created successfully";
}
else
    die("Playlist could create playlist: Invalid aguments given");

?>