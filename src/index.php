<?php

/*

TODO:
    * Smart way of collapsing certain directories
    * icons depending on filetype

*/

session_start();

require_once('Tree.php');

//~ define('DIRECTORY_SEPARATOR',   '/');
define('ROOT_DIRECTORY',        '/multimedia');
define('PLAYLISTS_DIRECTORY',   '/tmp');
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




function path_to_array($path) {
    //~ mb_detect_encoding($path, "UTF-8") == "UTF-8" ? : $path = utf8_encode($path);
    return array_diff(explode(DIRECTORY_SEPARATOR, $path), array(''));
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
            $file_info = pathinfo($full_path.'/'.$file);

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
    $file = fopen($path, 'r');
    if ($file) {
        while (!feof($file)) {
            $buffer = fgets($file, 4096);
            $buffer = str_replace(chr(10), '', $buffer);
            $buffer = str_replace(chr(13), '', $buffer);
            //~ mb_detect_encoding($buffer, "UTF-8") == "UTF-8" ? : $buffer = utf8_encode($buffer);
            //~ if ($buffer[0] != '#') {
                //~ echo "\n<br>$buffer = ".print_r(path_to_array($buffer),true);
                $tree->insert(path_to_array($buffer), 'in_playlist', true);
            //~ }
        }
        fclose($file);
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




$tree = load_tree('./test.m3u');



echo_header();
echo "<form>";
$tree->iterate('callback_before', 'callback_after', 1);
echo "<input type='submit' value='Generate playlist'>";
echo "<form>";
echo_footer();

?>