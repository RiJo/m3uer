<?php

date_default_timezone_set('Europe/Stockholm');

if(!isset($_SESSION))
    session_start();

define('ROOT_DIRECTORY',        '/home/rijo/programming/github/m3uer/src');
//~ define('ROOT_DIRECTORY',   '.');
//~ define('ROOT_DIRECTORY',            '/mnt/qnap/multimedia/Musik');
//~ define('ROOT_DIRECTORY',   '/share/HDA_DATA/Qmultimedia/Musik');

define('APPLICATION_NAME',          'm3uer');
define('APPLICATION_VERSION',       '0.1.0 unstable');

define('LINE_BREAK',                chr(10));
define('COMMENT_SYMBOL',            '#');

define('SESSION_MUSIC',             'music');
define('SESSION_PLAYLISTS',         'playlists');
define('SESSION_TREE',              'tree');

define('PLAYLIST_FORMATS',          'm3u');
define('MEDIA_FORMATS',             'mp3');


// TODO: move to generic file
function get_file_info($path) {
    $real_path = realpath($path);
    $file_info = pathinfo($real_path);
    //~ $file_info['path'] = $path;
    $file_info['path'] = $real_path;
    return $file_info;
}

function simplify_path($path) {
    $temp = array();

    $items = explode(DIRECTORY_SEPARATOR, $path);
    for ($i = count($items) - 1; $i >= 0 ; $i--) {
        if ($items[$i] == ".")
            continue;
        if ($items[$i] == "..")
        {
            if ($i == 0)
                die("Invalid path given: no parent of root directory");
            $i--;
            continue;
        }
        array_unshift($temp, $items[$i]);
    }

    return implode(DIRECTORY_SEPARATOR, $temp);
}

?>