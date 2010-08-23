<?php

/*

TODO:
    * Smart way of collapsing certain directories
    * icons depending on filetype
    * Create a list of invalid paths in playlists when loaded (to locate moved files)

*/

session_start();

require_once('Tree.php');

//~ define('DIRECTORY_SEPARATOR',   '/');
define('ROOT_DIRECTORY',        '/multimedia');
define('PLAYLISTS_DIRECTORY',   '.');
define('SESSION_TREE_KEY',      'olljkkk');




function echo_header() {
    echo "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN'\n'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>";
    echo "\n<html><head>";
    echo "\n<title>m3uer</title>";
    echo "<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />";
    echo "<meta http-equiv='Content-Language' content='en' />";
    echo "\n<script type='text/javascript'>";
    echo "\nfunction toggle(id) {
    var wrapper = document.getElementById('wrapper:'+id);
    var image = document.getElementById('image:'+id);
    wrapper.style.display = (wrapper.style.display != 'none' ? 'none' : '' );
    image.src = (wrapper.style.display == 'none' ? './plus.png' : './minus.png');
}";
    echo "\n</script>";
    echo "\n</head><body>";
}

function echo_footer() {
    echo "\n</body></html>";
}

function echo_playlists() {
    $extensions = array('m3u'); 

    echo "Playlists";
    $directory = opendir(PLAYLISTS_DIRECTORY);
    while (false !== ($file = readdir($directory))) {
        $full_path = PLAYLISTS_DIRECTORY.DIRECTORY_SEPARATOR.$file;
        $file_info = pathinfo($full_path);

        if (isset($file_info['extension']) && in_array($file_info['extension'], $extensions)) {
            echo "<br> * <a href='index.php?playlist=$full_path'>$full_path</a>";
        }
    }
    closedir($directory);
}



function path_to_array($path) {
    return array_diff(explode('/', trim($path)), array(''));
}





function load_tree($playlist = null, $reload_session = false) {
    // load filestructure (may be cached in a session)
    $tree = null;
    if (!isset($_SESSION[SESSION_TREE_KEY]) || $reload_session) {
        $tree = new Node();
        $tree->value = DIRECTORY_SEPARATOR;
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

    $folders = path_to_array($path);

    $pathinfo = pathinfo($path);
    //~ die(print_r($value,true));
    //~ $value['path'] = $path;
    //~ $value['exists'] = array('filesystem');

    if (is_dir($path)) {
        $directory = opendir($path);
        while (false !== ($file = readdir($directory))) {
            $full_path = $path.DIRECTORY_SEPARATOR.$file;
            $file_info = pathinfo($full_path);

            if (!in_array($file, $skip_directories)) {
                if (!isset($file_info['extension']) || in_array($file_info['extension'], $extensions)) {
                    $tree->insert($folders, 'path', $path);
                    $tree->insert($folders, 'dirname', $pathinfo['dirname']);
                    $tree->insert($folders, 'basename', $pathinfo['basename']);
                    $tree->insert($folders, 'filename', $pathinfo['filename']);
                    $tree->insert($folders, 'exists', true);
                    $tree->insert($folders, 'in_playlist', false);

                    load_filesystem($tree, $full_path);
                }
            }
        }
        closedir($directory);
    }
    else {
        $tree->insert($folders, 'path', $path);
        $tree->insert($folders, 'dirname', $pathinfo['dirname']);
        $tree->insert($folders, 'basename', $pathinfo['basename']);
        $tree->insert($folders, 'filename', $pathinfo['filename']);
        $tree->insert($folders, 'exists', true);
        $tree->insert($folders, 'in_playlist', false);
    }
}

function load_playlist(&$tree, $path) {
    $handle = fopen($path, "rb");
    if ($handle) {
        $contents = fread($handle, filesize($path));
        fclose($handle);

        $broken = array();
        foreach (explode(chr(10), $contents) as $line) {
            $line = trim($line);
            if ($line[0] != '#' && strlen($line) > 0) {
                $folders = path_to_array($line);
                if ($tree->exists($folders))
                    $tree->insert($folders, 'in_playlist', true);
                else
                    array_push($broken, $line);
            }
        }
        echo "<b>Broken paths:</b><br><font color='#cc0000'>".implode('<br>', $broken)."</font>";
    }
    else {
        die("Error: could not open file '$path' for reading");
    }
    
}





function callback_before($node, $level) {
    $indentation = 30;

    $checked = $node->evaluate('in_playlist', true);
    $checked = ($checked ? 'checked' : '');
    if ($node->is_leaf()) {
        // file
        echo "\n".str_repeat('    ', $level)."<div class='file'>";
        echo "\n".str_repeat('    ', $level)."    <img src='./empty.png'>";
        echo "\n".str_repeat('    ', $level)."    <input type='checkbox' id='check:".$node->value['path']."' $checked>";
        echo "\n".str_repeat('    ', $level)."    <label for='check:".$node->value['path']."'>File: ".$node->value['basename']."</label>";
        echo "\n".str_repeat('    ', $level)."</div>";
    }
    else {
        // directory
        echo "\n".str_repeat('    ', $level)."<div class='directory'>";
        echo "\n".str_repeat('    ', $level)."    <img src='./plus.png' id='image:".$node->value['path']."' onClick=\"javascript:toggle('".$node->value['path']."')\">";
        echo "\n".str_repeat('    ', $level)."    <input type='checkbox' id='check:".$node->value['path']."' $checked>";
        echo "\n".str_repeat('    ', $level)."    <label for='check:".$node->value['path']."'>Directory: ".$node->value['basename']."</label>";
        echo "\n".str_repeat('    ', $level)."    <div class='contents' id='wrapper:".$node->value['path']."' style='margin-left:".$indentation."px; display:none;'>";
    }
}

function callback_after($node, $level) {
    if (!$node->is_leaf()) {
        // directory
        echo "\n".str_repeat('    ', $level)."    </div>";
        echo "\n".str_repeat('    ', $level)."</div>";
    }
}


echo_header();

if (isset($_GET['playlist'])) {
    $playlist = trim($_GET['playlist']);
    if (!file_exists($playlist))
        die("Could not locate playlist \"$playlist\"");

    $tree = load_tree($playlist);

    echo "<form>";
    $tree->iterate('callback_before', 'callback_after', 1);
    echo "<input type='submit' value='Generate playlist'>";
    echo "<form>";
}
else {
    echo_playlists();
}

echo_footer();

?>