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

?>