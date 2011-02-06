<?php

if (isset($_GET['root']) && isset($_SESSION[SESSION_PLAYLISTS])) {
    $root = $_GET['root'];
    $playlists = $_SESSION[SESSION_PLAYLISTS];

    json_encode($playlists);
}

?>