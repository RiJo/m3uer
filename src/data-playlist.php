<?php 

require_once('config.php');
require_once('file_handling.php');
require_once('Filesystem.php');

function load_playlist($root, $playlist) {
    $path = $root.DIRECTORY_SEPARATOR.$playlist;
    $handle = fopen($path, 'r')
        or die("Error: could not open file '$path' for reading");

    $contents = fread($handle, filesize($path));
    fclose($handle);

    $result = array(
        'valid' => array(),
        'invalid' => array(),
        'comments' => array()
    );
    $comments = array();
    foreach (explode(LINE_BREAK, $contents) as $line) {
        $line = trim($line);
        if (strlen($line) > 0) {
            if ($line[0] == COMMENT_SYMBOL) {
                array_push($result['comments'], trim(substr($line, 1)));
            }
            else {
                $file = simplify_path($root.DIRECTORY_SEPARATOR.$line);
                
                if (file_exists($file))
                    array_push($result['valid'], $file);
                else
                    array_push($result['invalid'], $file);
            }
        }
    }

    return $result;
}

if (isset($_GET['root']) && isset($_GET['path']) && isset($_SESSION[SESSION_FILESYSTEM])) {
    $root = $_GET['root'];
    $playlist = $_GET['path'];
    $filesystem = unserialize($_SESSION[SESSION_FILESYSTEM]);

    if (!file_exists($playlist))
        die("Could not locate playlist \"$playlist\"");

    $result = load_playlist($root, $playlist);

    $filesystem->check_paths($result['valid']);

    //~ die("<pre>".print_r($result, true)."</pre>");

    echo $filesystem->to_json();
}

?>