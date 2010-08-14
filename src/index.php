<?php

session_start();

require_once('Tree.php');

//~ define('DIRECTORY_SEPARATOR',   '/');
define('ROOT_DIRECTORY',        '/multimedia');
define('PLAYLISTS_DIRECTORY',   '/tmp');
define('SESSION_TREE_KEY',      'test');




function echo_header() {
    echo "\n<html><head><title>foobar</title>";
    echo "\n<script type='text/javascript'>";
    echo "\nfunction toggle(obj) {
	var el = document.getElementById(obj);
	el.style.display = (el.style.display != 'none' ? 'none' : '' );
}";
    echo "\n</script>";
    echo "\n</head><body>";
}

function echo_footer() {
    echo "\n</body></html>";
}





function load_tree($playlist = null, $reload_session = false) {
    // load filestructure (may be cached in a session)
    $tree = null;
    if (!isset($_SESSION[SESSION_TREE_KEY]) || $reload_session) {
        $tree = new Tree();
        load_filesystem($tree, ROOT_DIRECTORY);
        $_SESSION[SESSION_TREE_KEY] = serialize($tree);
    }
    else {
        $tree = unserialize($_SESSION[SESSION_TREE_KEY]);
    }
    // load playlist
    if ($playlist) {
        load_playlist($tree, $playlist);
    }
    return $tree;
}

function load_filesystem(&$tree, $path) {
    $skip_directories = array('.', '..');
    $extensions = array('mp3'); 

    $folders = explode(DIRECTORY_SEPARATOR, $path);

    $value = pathinfo($path);
    $value['path'] = $path;
    $value['exists'] = array('filesystem');

    if (is_dir($path)) {
        $directory = opendir($path);
        while (false !== ($file = readdir($directory))) {
            $full_path = $path.DIRECTORY_SEPARATOR.$file;
            $file_info = pathinfo($full_path.'/'.$file);

            if (!in_array($file, $skip_directories)) {
                if (!isset($file_info['extension']) || in_array($file_info['extension'], $extensions)) {
                    $tree->insert($folders, $value);
                    load_filesystem($tree, $full_path);
                }
            }
        }
        closedir($directory);
    }
    else {
        $tree->insert($folders, $value);
    }
}

function load_playlist(&$tree, $path) {
    // stub
}





function callback_before($node, $level) {
    $indentation = 30;
    if ($node->is_leaf()) {
        // file
        echo "\n".str_repeat('    ', $level)."<div class='file'>";
        echo "\n".str_repeat('    ', $level)."    <input type='checkbox' id='".$node->value['path']."'>";
        echo "\n".str_repeat('    ', $level)."    <label for='".$node->value['path']."'>File: ".$node->value['basename']."</label>";
        echo "\n".str_repeat('    ', $level)."</div>";
    }
    else {
        // directory
        echo "\n".str_repeat('    ', $level)."<div class='directory' style=''>";
        echo "\n".str_repeat('    ', $level)."    <img src='' onClick=\"javascript:toggle('contents:".$node->value['path']."')\">";
        echo "\n".str_repeat('    ', $level)."    <input type='checkbox' id='".$node->value['path']."'>";
        echo "\n".str_repeat('    ', $level)."    <label for='".$node->value['path']."'>Directory: ".$node->value['basename']."</label>";
        echo "\n".str_repeat('    ', $level)."    <div class='contents'  id='contents:".$node->value['path']."' style='margin-left:".$indentation."px; ".(($level>30)?'display:none;':'')."'>";
    }
}

function callback_after($node, $level) {
    if (!$node->is_leaf()) {
        // directory
        echo "\n".str_repeat('    ', $level)."    </div>";
        echo "\n".str_repeat('    ', $level)."</div>";
    }
}




$tree = load_tree('/tmp/playlist.m3u');



echo_header();
echo "<form>";
$tree->iterate('callback_before', 'callback_after');
echo "<input type='submit'>";
echo "<form>";
echo_footer();

?>