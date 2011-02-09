<?php

/*
    TODO:
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

function echo_body() {
    echo "<a href=\"unit_tests.php\">Unit tests</a>";
    echo "<div id='tree-div'></div>";
}

function echo_footer() {
    echo "\n</body></html>";
}

////////////////////////////////////////////////////////////////////////////////
//   LOAD   ////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

function load_global($force_reload = false) {
    $extensions = array(
        SESSION_PLAYLISTS   => explode(',', PLAYLIST_FORMATS),
        SESSION_MEDIA       => explode(',', MEDIA_FORMATS),
    );

    if (!isset($_SESSION[SESSION_PLAYLISTS]) || !isset($_SESSION[SESSION_MEDIA]) || $force_reload) {
        $filesystem_trees = load_filesystem(ROOT_DIRECTORY, $extensions);

        $playlist_tree = new Filesystem(ROOT_DIRECTORY, $filesystem_trees[SESSION_PLAYLISTS], false);
        $media_tree = new Filesystem(ROOT_DIRECTORY, $filesystem_trees[SESSION_MEDIA], true);

        $_SESSION[SESSION_PLAYLISTS] = serialize($playlist_tree);
        $_SESSION[SESSION_MEDIA] = serialize($media_tree);
    }
}

////////////////////////////////////////////////////////////////////////////////
//   PRINTOUT   ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

load_global();

echo_header();
echo_body();
echo_footer();

?>