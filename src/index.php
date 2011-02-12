<?php

/*
    TODO:
    * Fix renderTo variable
    * Loading indication while loading filesystem
    * Parent node is not checked on load when all childs are checked
    * icons depending on filetype
    * Order trees
    * error messages when something fails
    * Show invalid paths in playlist
*/

require_once('config.php');
require_once('file_handling.php');
require_once('Filesystem.php');

$reload = isset($_GET['reload']);
load_global($reload);

echo_header();
echo_body();
echo_footer();

////////////////////////////////////////////////////////////////////////////////
//   PRINTOUT   ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

function echo_header() {
    echo "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN'\n'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>";
    echo "\n<html><head>";
    echo "\n<title>".APPLICATION_NAME." v.".APPLICATION_VERSION."</title>";
    echo "\n<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />";
    echo "\n<meta http-equiv='Content-Language' content='en' />";
    //echo "\n<link rel='stylesheet' href='./style.css' type='text/css' />";

    // Ext JS
    echo "\n<link rel=\"stylesheet\" type=\"text/css\" href=\"".EXTJS_PATH."/resources/css/ext-all-notheme.css\">";
    echo "<link rel=\"stylesheet\" type=\"text/css\" title=\"access\" href=\"".EXTJS_PATH."/resources/css/xtheme-".EXTJS_THEME.".css\" />";
    echo "\n<script type=\"text/javascript\" src=\"".EXTJS_PATH."/adapter/ext/ext-base.js\"></script>";
    echo "\n<script type=\"text/javascript\" src=\"".EXTJS_PATH."/ext-all-debug.js\"></script>";
    echo "\n<script type=\"text/javascript\" src=\"ext.js\"></script>";

    echo "\n<script type='text/javascript'>";
    echo "\n    javascript:render('".ROOT_DIRECTORY."', '".((empty($_GET['playlist'])) ? '' : $_GET['playlist'])."');";
    echo "\n</script>";
    echo "\n</head><body>";
}

function echo_body() {
    //echo "<a href=\"unit_tests.php\">Unit tests</a>";
    echo "<div id='tree-div'></div>";
}

function echo_footer() {
    echo "\n</body></html>";
}

////////////////////////////////////////////////////////////////////////////////
//   LOAD   ////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

function load_global($force_reload = false) {
    if (!isset($_SESSION[SESSION_PLAYLISTS]) || !isset($_SESSION[SESSION_MEDIA]) || $force_reload) {
        // Load filesystem
        $extensions = array(
            SESSION_PLAYLISTS => explode(',', PLAYLIST_FORMATS),
            SESSION_MEDIA => explode(',', MEDIA_FORMATS),
        );
        $skip_patterns = (SKIP_FILE_PATTERNS == '') ? array() : explode(',', SKIP_FILE_PATTERNS);
        $filesystem_trees = load_filesystem(ROOT_DIRECTORY, $extensions, $skip_patterns);

        // Build playlists tree
        $playlist_tree = new Filesystem(ROOT_DIRECTORY, false);
        $playlist_tree->add($filesystem_trees['directories']);
        $playlist_tree->add($filesystem_trees[SESSION_PLAYLISTS]);
        $playlist_tree->expand($filesystem_trees[SESSION_PLAYLISTS]);

        // Build media tree
        $media_tree = new Filesystem(ROOT_DIRECTORY, $filesystem_trees[SESSION_MEDIA], true);
        $media_tree->add($filesystem_trees['directories']);
        $media_tree->add($filesystem_trees[SESSION_MEDIA]);

        $_SESSION[SESSION_PLAYLISTS] = serialize($playlist_tree);
        $_SESSION[SESSION_MEDIA] = serialize($media_tree);
    }
}

?>