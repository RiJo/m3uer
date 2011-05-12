<?php

/*
    TODO:
    * Clean up ext.js (tons of redundant code :S)
    * FR: Loading indication while loading filesystem (put load filesystem in an ajax script?)

    * Create a filter for list of playlist contents (to only show invalid for instance)
    * FR: Printout permissions of playlists?
    * Bug: cannot save songs containing '&' in the filename to a playlist
*/

require_once('config.php');
require_once('file_handling.php');
require_once('Filesystem.php');

$reload = isset($_GET['reload']);


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
echo "\n        <div id='loading-mask'></div>";
echo "\n        <div id='loading'>";
echo "\n            <span id='loading-message'>Loading. Please wait...</span>";
echo "\n        </div>";
echo "\n        <div id='container'>";
echo "\n            <div class='content' id='header'>".APPLICATION_NAME." v.".APPLICATION_VERSION."</div>";
echo "\n            <div class='content' id='messages'></div>";
echo "\n            <div class='content' id='tree'></div>";
//echo "\n            <div class='content' id='footer'><a href=\"unit_tests.php\">Unit tests</a></div>";

// Load filesystem
echo "\n            <script type='text/javascript'>";
echo "\n                 document.getElementById('loading-message').innerHTML = 'Loading filesystem...';";
echo "\n            </script>";
load_global($reload);

// Load extjs components
echo "\n            <script type='text/javascript'>";
echo "\n                 document.getElementById('loading-message').innerHTML = 'Loading graphics...';";
echo "\n            </script>";
echo "\n            <script type='text/javascript'>";
echo "\n                javascript:render('".ROOT_DIRECTORY."', '".((empty($_GET['playlist'])) ? '' : $_GET['playlist'])."');";
echo "\n            </script>";

// Fade out loader
echo "\n            <script type='text/javascript'>";
echo "\n            Ext.onReady(function(){";
echo "\n                var loadingMask = Ext.get('loading-mask');";
echo "\n                var loading = Ext.get('loading');";
echo "\n                //  Hide loading message";
echo "\n                loading.fadeOut({ duration: 0.2, remove: true });";
echo "\n                //  Hide loading mask";
echo "\n                loadingMask.setOpacity(0.9);";
echo "\n                loadingMask.shift({";
echo "\n                    xy: loading.getXY(),";
echo "\n                    width: loading.getWidth(),";
echo "\n                    height: loading.getHeight(),";
echo "\n                    remove: true,";
echo "\n                    duration: 1,";
echo "\n                    opacity: 0.1,";
echo "\n                    easing: 'bounceOut'";
echo "\n                });";
echo "\n            });";
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