<?php

/*
    TODO:
    * Show playlist name in edit playlist page.
    * Create a filter for list of playlist contents (to only show invalid for instance)
    * Loading indication while loading filesystem (put load filesystem in an ajax script?)
    * Bug: check out of sync when double click on directory
    * Printout permissions of playlists?
    * Make screenshots
*/

require_once('config.php');
require_once('file_handling.php');
require_once('Filesystem.php');

$reload = isset($_GET['reload']);
load_global($reload);


echo "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN'\n'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>";
echo "\n<html>";
echo "\n    <head>";
echo "\n        <title>".APPLICATION_NAME." v.".APPLICATION_VERSION."</title>";
echo "\n        <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />";
echo "\n        <meta http-equiv='Content-Language' content='en' />";
echo "\n        <link rel=\"stylesheet\" type=\"text/css\" href=\"".EXTJS_PATH."/resources/css/ext-all-notheme.css\">";
echo "\n        <link rel=\"stylesheet\" type=\"text/css\" title=\"access\" href=\"".EXTJS_PATH."/resources/css/xtheme-".EXTJS_THEME.".css\" />";
echo "\n        <link rel='stylesheet' href='./style.css' type='text/css' />";
echo "\n        <script type=\"text/javascript\" src=\"".EXTJS_PATH."/adapter/ext/ext-base.js\"></script>";
echo "\n        <script type=\"text/javascript\" src=\"".EXTJS_PATH."/ext-all-debug.js\"></script>";
echo "\n        <script type=\"text/javascript\" src=\"ext.js\"></script>";
echo "\n    </head>";
echo "\n    <body>";
echo "\n        <div id='container'>";
echo "\n            <div class='content' id='header'>".APPLICATION_NAME." v.".APPLICATION_VERSION."</div>";
echo "\n            <div class='content' id='messages'></div>";
echo "\n            <div class='content' id='tree'></div>";
//echo "\n            <div class='content' id='footer'><a href=\"unit_tests.php\">Unit tests</a></div>";
echo "\n            <script type='text/javascript'>";
echo "\n                javascript:render('".ROOT_DIRECTORY."', '".((empty($_GET['playlist'])) ? '' : $_GET['playlist'])."');";
echo "\n            </script>";
echo "\n        </div>";
echo "\n    </body>";
echo "\n</html>";


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
        $media_tree->remove_empty_nodes();

        $_SESSION[SESSION_PLAYLISTS] = serialize($playlist_tree);
        $_SESSION[SESSION_MEDIA] = serialize($media_tree);
    }
}

?>