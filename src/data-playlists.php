<?php

require_once('config.php');
require_once('Filesystem.php');

if (isset($_GET['root']) && isset($_SESSION[SESSION_PLAYLISTS])) {
    $root = $_GET['root'];
    $playlists = unserialize($_SESSION[SESSION_PLAYLISTS]);

    echo $playlists->to_json();
}

?>