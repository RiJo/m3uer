<?php

date_default_timezone_set('Europe/Stockholm');

if(!isset($_SESSION))
    session_start();

define('APPLICATION_NAME',          'm3uer');
define('APPLICATION_VERSION',       '0.2.0-beta');

define('ROOT_DIRECTORY',            '/home/rijo/programming/github/m3uer/src');
//define('ROOT_DIRECTORY',            '/share/HDA_DATA/Qmultimedia/Musik');

// Ext JS
define('EXTJS_PATH',                '../ext');
define('EXTJS_THEME',               'blue');

define('LINE_BREAK',                chr(10));
define('COMMENT_SYMBOL',            '#');
define('SESSION_MEDIA',             'media');
define('SESSION_PLAYLIST',          'playlist');
define('SESSION_PLAYLISTS',         'playlists');

// Following macros are comma separated lists
define('SKIP_FILE_PATTERNS',        '(^\.)');
define('PLAYLIST_FORMATS',          'm3u');
define('MEDIA_FORMATS',             'mp3,wav');

?>