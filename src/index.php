<?php

/*

TODO:
    * Smart way of collapsing certain directories
    * icons depending on filetype
    * Create a list of invalid paths in playlists when loaded (to locate moved files)
    * One path: playlists, then relative paths (root directory, find m3u:s recursive)
    * skip empty directories
    * store files/playlists in session variaable

*/

session_start();

require_once('Tree.php');

define('ROOT_DIRECTORY',   '/multimedia');
//define('ROOT_DIRECTORY',   '/share/HDA_DATA/Qmultimedia/Musik');
define('SESSION_KEY_FILES',         'f');
define('SESSION_KEY_PLAYLISTS',     'p');




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

    echo "Playlists<ul>";
    foreach (get_files(ROOT_DIRECTORY, $extensions) as $path=>$info) {
        if (isset($info['extension']) && in_array($info['extension'], $extensions)) {
            echo "<br><li><a href='".basename($_SERVER['PHP_SELF'])."?playlist=$info[path]'>$info[filename]</a></li>";
        }
    }
    echo "</ul>";
}



function path_to_array($path) {
    $path = str_replace(ROOT_DIRECTORY.DIRECTORY_SEPARATOR, '', $path);

    $skip_directories = array('', '.');
    $array = explode(DIRECTORY_SEPARATOR, trim($path));

    while (($index = array_search('..', $array))) {
        array_splice($array, $index - 1, 2);
    }

    return array_diff($array, $skip_directories);
}

function get_files($path, $extensions) {
    if (!is_dir($path))
        die("\"$path\" is not a directory");

    $directory = opendir($path);
    if (!$directory)
        die("Could not open directory \"$path\"");

    $files = array();

    $files[$path] = pathinfo($path);
    $files[$path]['path'] = $path;
    //~ echo "<br>Path: $path: ".print_r($files,true);

    while (false !== ($file = readdir($directory))) {
        $full_path = $path.DIRECTORY_SEPARATOR.$file;
        $file_info = pathinfo($full_path);
        $file_info['path'] = $full_path;

        if (is_dir($full_path) && !in_array($file, array('.', '..'))) {
            $files = array_merge($files, get_files($full_path, $extensions));
        }
        elseif (isset($file_info['extension']) && in_array($file_info['extension'], $extensions)) {
            $files[$full_path] = $file_info;
        }
    }
    closedir($directory);
    return $files;
}





function load_tree($playlist = null, $reload_session = false) {
    // load filestructure (may be cached in a session)
    $tree = null;
    if (!isset($_SESSION[SESSION_KEY_FILES]) || $reload_session) {
        $tree = new Node();
        $tree->value = DIRECTORY_SEPARATOR;
        load_filesystem($tree);
        $_SESSION[SESSION_KEY_FILES] = serialize($tree);
    }
    else {
        $tree = unserialize($_SESSION[SESSION_KEY_FILES]);
    }
    // load playlist
    if ($playlist) {
        load_playlist($tree, $playlist);
    }
    return $tree;
}

function load_filesystem(&$tree) {
    $extensions = array('mp3'); 

    foreach (get_files(ROOT_DIRECTORY, $extensions) as $path=>$info) {
        if (strlen($info['filename']) > 0) {
            $folders = path_to_array($path);
            $tree->insert($folders, 'path', $info['path']);
            $tree->insert($folders, 'dirname', $info['dirname']);
            $tree->insert($folders, 'basename', $info['basename']);
            $tree->insert($folders, 'filename', $info['filename']);
            $tree->insert($folders, 'exists', true);
        }
    }
}

function load_playlist(&$tree, $path) {
    $handle = fopen($path, "rb");
    if ($handle) {
        $contents = fread($handle, filesize($path));
        fclose($handle);
        
        $info = pathinfo($path);
        $directory = $info['dirname'];

        $broken = array();
        foreach (explode(chr(10), $contents) as $line) {
            $line = trim($line);
            if (strlen($line) > 0 && $line[0] != '#') {
                $file = $directory.DIRECTORY_SEPARATOR.$line;
                $folders = path_to_array($file);
                //~ echo "<br>Loading: ".print_r($folders,true);
                if ($tree->exists($folders))
                    $tree->insert($folders, 'in_playlist', true);
                else
                    array_push($broken, $file);
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
    $playlist = $_GET['playlist'];
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

//~ $files = get_files(ROOT_DIRECTORY, array('m3u'));
//~ echo "<pre>".print_r($files,true)."</pre>";

echo_footer();

?>