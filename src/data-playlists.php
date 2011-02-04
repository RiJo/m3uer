<?php

if (isset($_GET['root']) && isset($_SESSION[SESSION_PLAYLISTS])) {
    $root = $_GET['root'];
    $platlists = $_SESSION[SESSION_PLAYLISTS];

    json_encode($platlists);
}

?>