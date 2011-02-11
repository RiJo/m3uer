<?php

/*
    TODO:
    * What happens if nothing is selected when saving playlist?
    * Button to reload filesystem
    * Loading indication while loading filesystem
    * do a CREDO check before calling program stable
        * C - done
        * R - done
        * E - done
        * D - 
        * O - done
    * Create an ignore-file-list (for dotfiles: .@__thumb)
    * Parent node is not checked on load when all childs are checked
    * Not expand all nodes (only relevant ones)
    * icons depending on filetype
    * Order trees
    * error messages when something fails
    * Show invalid paths in playlist
    * Make filenames consistent: rename Ext.js, fix dashes
    * Whu use a treeloader?
            var tree = new Tree.TreePanel({
                el:'tree-div',
                useArrows:true,
                autoScroll:true,
                animate:true,        
                containerScroll: true, 
                loader: new Tree.TreeLoader({
                    dataUrl:'fetchTreeData.php'
                })        
            }); 
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
    echo "\n<link rel=\"stylesheet\" type=\"text/css\" href=\"".EXTJS_PATH."/resources/css/ext-all.css\">";

    // Ext.js
    echo "\n<script type=\"text/javascript\" src=\"".EXTJS_PATH."/adapter/ext/ext-base.js\"></script>";
    echo "\n<script type=\"text/javascript\" src=\"".EXTJS_PATH."/ext-all-debug.js\"></script>";
    echo "\n<script type=\"text/javascript\" src=\"playlist_tree.js\"></script>";

    //echo "\n<script type=\"text/javascript\" src=\"check-tree-tristate.js\"></script>";

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
    $extensions = array(
        SESSION_PLAYLISTS   => explode(',', PLAYLIST_FORMATS),
        SESSION_MEDIA       => explode(',', MEDIA_FORMATS),
    );
    $skip_patterns = (SKIP_FILE_PATTERNS == '') ? array() : explode(',', SKIP_FILE_PATTERNS);

    if (!isset($_SESSION[SESSION_PLAYLISTS]) || !isset($_SESSION[SESSION_MEDIA]) || $force_reload) {
        $filesystem_trees = load_filesystem(ROOT_DIRECTORY, $extensions, $skip_patterns);

        $playlist_tree = new Filesystem(ROOT_DIRECTORY, $filesystem_trees[SESSION_PLAYLISTS], false);
        $media_tree = new Filesystem(ROOT_DIRECTORY, $filesystem_trees[SESSION_MEDIA], true);

        $_SESSION[SESSION_PLAYLISTS] = serialize($playlist_tree);
        $_SESSION[SESSION_MEDIA] = serialize($media_tree);
    }
}

////////////////////////////////////////////////////////////////////////////////
//   PRINTOUT   ////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

load_global(true);

echo_header();
echo_body();
echo_footer();

?>