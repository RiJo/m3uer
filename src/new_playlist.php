<?php

require_once('config.php');
require_once('file_handling.php');
require_once('Filesystem.php');

if (isset($_GET['root']) && isset($_GET['name'])) {
    $root = $_GET['root'];
    $path = $_GET['path'];
    $name = $_GET['name'];

    $playlist_path = $path.DIRECTORY_SEPARATOR.$name;
    $playlist_file_info = pathinfo($playlist_path);

    //~ die("root: $root, path: $path, name: $name<br><pre>".print_r($playlist_file_info, true));

    $extension = isset($playlist_file_info['extension']) ? $playlist_file_info['extension'] : '';
    if (!in_array($extension, explode(',', PLAYLIST_FORMATS)))
        die('Could not create playlist: Invalid file extension');

    // Create file
    @touch($playlist_path)
        or die("Could not create file $playlist_path");

    // Add new file to session
    $playlists = unserialize($_SESSION[SESSION_PLAYLISTS]);
    $relative_path = make_relative_path($root, $playlist_path, false);
    $playlists->add($relative_path);
    $_SESSION[SESSION_PLAYLISTS] = serialize($playlists);

    //~ die("<pre>".print_r(json_decode($playlists->to_json()), true)."</pre>");

    echo "Playlist created successfully";
}
else
    die("Playlist could create playlist: Invalid aguments given");

?>