<?php

/*
    TODO:
    * Expand node when checked

    * icons depending on filetype
    * sort according to filenames
    * cannot handle single quote (see Fool's Garden)
    * error messages when something fails
*/

require_once('config.php');
require_once('file_handling.php');
require_once('Filesystem.php');

function echo_header() {
    echo "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN'\n'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>";
    echo "\n<html><head>";
    echo "\n<title>".APPLICATION_NAME." v.".APPLICATION_VERSION."</title>";
    echo "\n<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />";
    echo "\n<meta http-equiv='Content-Language' content='en' />";

    echo "\n<link rel='stylesheet' href='./style.css' type='text/css' />";
    echo "\n<link rel=\"stylesheet\" type=\"text/css\" href=\"../ext/resources/css/ext-all.css\">";

    // Ext.js
    echo "\n<script type=\"text/javascript\" src=\"../ext/adapter/ext/ext-base.js\"></script>";
    echo "\n<script type=\"text/javascript\" src=\"../ext/ext-all-debug.js\"></script>";
    echo "\n<script type=\"text/javascript\" src=\"Ext.js\"></script>";

    //echo "\n<script type=\"text/javascript\" src=\"check-tree-tristate.js\"></script>";

    echo "\n<script type='text/javascript'>";
    echo "\n    javascript:render('".ROOT_DIRECTORY."', '".((empty($_GET['playlist'])) ? '' : $_GET['playlist'])."');";
    echo "\n</script>";
    echo "\n</head><body>";
}

function echo_footer() {
    echo "\n</body></html>";
}

function echo_playlists($root, $playlists) {
    echo "\n<h1>Playlists ($root)</h1>";
    echo "\n<ul>";
    foreach ($playlists as $playlist) {
        echo "\n<li><a href='".basename($_SERVER['PHP_SELF'])."?playlist=$playlist'>$playlist</a></li>";
    }
    echo "\n</ul>";
}

////////////////////////////////////////////////////////////////////////////////
//   LOAD   ////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

function load_filessytem($root, $extensions, $reload_session = false) {
    if (!isset($_SESSION[SESSION_FILESYSTEM]) || $reload_session) {
        $tree = new Filesystem();
        $tree->load($root, $extensions);

        $_SESSION[SESSION_FILESYSTEM] = serialize($tree);
    }
}

function load_playlists($root, $extensions, $reload_session = false) {
    $playlists = null;
    if (!isset($_SESSION[SESSION_PLAYLISTS]) || $reload_session) {
        $playlists = get_files($root, '.', $extensions);
        // TODO: place this somewhere else?
        for ($i = 0; $i < count($playlists); $i++)
            $playlists[$i] = str_replace($root.DIRECTORY_SEPARATOR, '', $playlists[$i]);
        $_SESSION[SESSION_PLAYLISTS] = serialize($playlists);
    }
    else {
        $playlists = unserialize($_SESSION[SESSION_PLAYLISTS]);
    }
    return $playlists;
}

////////////////////////////////////////////////////////////////////////////////
//   PRINTOUT   ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

load_filessytem(ROOT_DIRECTORY, explode(',', MEDIA_FORMATS), true);

echo_header();

if (isset($_GET['playlist']) && !empty($_GET['playlist'])) {
    // foo
    echo "<div id='tree-div'></div>";
}
else {
    $playlists = load_playlists(ROOT_DIRECTORY, explode(',', PLAYLIST_FORMATS));
    //~ echo "Playlists:<br><pre>".print_r($playlists, true)."</pre>";
    echo_playlists(ROOT_DIRECTORY, $playlists);
}

echo "<a href=\"unit_tests.php\">Unit tests</a>";

echo_footer();

?>