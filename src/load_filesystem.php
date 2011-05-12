<?php

require_once('config.php');
require_once('file_handling.php');
require_once('Filesystem.php');

$reload = isset($_GET['reload']);
echo load_global($reload);

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

    return "ok";
}

?>